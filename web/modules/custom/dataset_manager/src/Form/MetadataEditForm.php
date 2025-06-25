<?php

namespace Drupal\dataset_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dataset_manager\Service\AIMetadataService;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\workflows\Entity\Workflow;
use Drupal\workflows\ContentModerationState;

class MetadataEditForm extends FormBase {

  protected $entityTypeManager;
  protected $aiService;
  protected $languageManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, AIMetadataService $aiService, LanguageManagerInterface $languageManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->aiService = $aiService;
    $this->languageManager = $languageManager;

  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('dataset_manager.ai'),
      $container->get('language_manager')
    );
  }

  public function getFormId() {
    return 'metadata_edit_form';
  }

 public function buildForm(array $form, FormStateInterface $form_state, $dataset = NULL) {
  if (!$dataset) {
    throw new \InvalidArgumentException('Dataset ID is required.');
  }

  /** @var \Drupal\dataset_manager\Entity\Dataset $dataset_entity */
  $dataset_entity = $this->entityTypeManager->getStorage('dataset')->load($dataset);
  if (!$dataset_entity) {
    throw new \InvalidArgumentException('Dataset not found.');
  }

  $langcode = $this->languageManager->getCurrentLanguage()->getId();
  $columns = $dataset_entity->get('columns')->value;
  $metadata = $this->aiService->generateMetadata($columns, $langcode);

  $workflow = Workflow::load('dataset_workflow');
    $state_options = [];

if ($workflow && $workflow->getTypePlugin()) {
  foreach ($workflow->getTypePlugin()->getStates() as $state_id => $state) {
    $state_options[$state_id] = $state->label();
  }
}

    // Get current moderation state
    $current_state = $dataset_entity->get('moderation_state')->value;

    $form['moderation_state'] = [
        '#type' => 'select',
        '#title' => $this->t('Moderation state'),
        '#options' => $state_options,
        '#default_value' => $dataset_entity->get('moderation_state')->value ?? 'draft',
        '#required' => TRUE,
    ];

    $form['revision_log_message'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Revision log message'),
    '#default_value' => '',
    '#description' => $this->t('Provide a short description of the changes for this revision.'),
    '#maxlength' => 255,
    '#required' => FALSE,
    ];

  // Prefill values
  $form['title'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Title'),
    '#default_value' => $dataset_entity->get('title')->value ?: $metadata['title'],
    '#required' => TRUE,
  ];

  $form['description'] = [
    '#type' => 'textarea',
    '#title' => $this->t('Description'),
    '#default_value' => $dataset_entity->get('description')->value ?: $metadata['description'],
    '#required' => TRUE,
  ];

  $form['tags'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Tags'),
    '#default_value' => $dataset_entity->get('tags')->value ?: implode(', ', $metadata['tags']),
  ];

  $form['categories'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Categories'),
    '#default_value' => $dataset_entity->get('categories')->value ?: implode(', ', $metadata['categories']),
  ];

  $form['dataset_id'] = ['#type' => 'value', '#value' => $dataset];
  $form['dataset_entity'] = ['#type' => 'value', '#value' => $dataset_entity];

  // AJAX preview output wrapper
  $form['preview_wrapper'] = [
    '#type' => 'container',
    '#attributes' => ['id' => 'preview-wrapper'],
  ];

  // Actions
  $form['actions'] = ['#type' => 'actions'];

  $form['actions']['preview'] = [
    '#type' => 'submit',
    '#value' => $this->t('Preview'),
    '#submit' => ['::previewSubmit'],
    '#ajax' => [
      'callback' => '::previewCallback',
      'wrapper' => 'preview-wrapper',
    ],
    '#limit_validation_errors' => [['title'], ['description'], ['tags'], ['categories']],
  ];

  $form['actions']['autosave'] = [
    '#type' => 'submit',
    '#value' => $this->t('Autosave'),
    '#submit' => ['::autosaveSubmit'],
    '#ajax' => [
      'callback' => '::autosaveCallback',
      'wrapper' => 'preview-wrapper',
    ],
    '#attributes' => ['class' => ['visually-hidden']],
  ];

  $form['current_state'] = [
  '#type' => 'item',
  '#title' => $this->t('Current moderation state'),
  '#markup' => $dataset_entity->get('moderation_state')->value,
];

  $form['actions']['save'] = [
    '#type' => 'submit',
    '#value' => $this->t('Save Metadata'),
    '#submit' => ['::submitMetadata'],
  ];


  // Attach autosave JS
  $form['#attached']['library'][] = 'dataset_manager/metadata_autosave';

  return $form;
}
public function previewSubmit(array &$form, FormStateInterface $form_state) {
  $form_state->setRebuild(TRUE);
}

public function previewCallback(array &$form, FormStateInterface $form_state) {
  return [
    '#type' => 'markup',
    '#markup' => '<div class="preview-box">
      <h3>🔍 <strong>Preview</strong></h3>
      <p><strong>Title:</strong> ' . $form_state->getValue('title') . '</p>
      <p><strong>Description:</strong> ' . nl2br($form_state->getValue('description')) . '</p>
      <p><strong>Tags:</strong> ' . $form_state->getValue('tags') . '</p>
      <p><strong>Categories:</strong> ' . $form_state->getValue('categories') . '</p>
        </div>',
    '#cache' => ['max-age' => 0],

  ];
}
public function submitMetadata(array &$form, FormStateInterface $form_state) {
  $this->saveDataset($form, $form_state, 'published');
}

public function autosaveSubmit(array &$form, FormStateInterface $form_state) {
  $this->saveDataset($form, $form_state, 'draft', TRUE);
}

public function autosaveCallback(array &$form, FormStateInterface $form_state) {
  return [
    '#markup' => '<div class="autosave-message">✅ Autosaved at ' . date('H:i:s') . '</div>',
  ];
}
private function saveDataset(array &$form, FormStateInterface $form_state, $state = 'draft', $is_autosave = FALSE) {
  /** @var \Drupal\dataset_manager\Entity\Dataset $dataset */
  $dataset = $this->entityTypeManager->getStorage('dataset')->load($form_state->getValue('dataset_id'));

  $dataset->setNewRevision(TRUE);
  $dataset->isDefaultRevision($state === 'published');
//   $dataset->moderation_state = $state;
  $dataset->set('moderation_state', $form_state->getValue('moderation_state'));

  $dataset->set('title', $form_state->getValue('title'));
  $dataset->set('description', ['value' => $form_state->getValue('description'), 'format' => 'plain_text']);
  $dataset->set('tags', $form_state->getValue('tags'));
  $dataset->set('categories', $form_state->getValue('categories'));

  // Save revision metadata
  $dataset->setRevisionLogMessage($form_state->getValue('revision_log_message') ?? '');
  $dataset->setRevisionUserId(\Drupal::currentUser()->id());
  $dataset->setRevisionCreationTime(REQUEST_TIME);

  $dataset->save();

  if (!$is_autosave) {
    $this->messenger()->addStatus($this->t('Metadata saved as %state.', ['%state' => ucfirst($state)]));
  }
}
public function submitForm(array &$form, FormStateInterface $form_state) {
  // Required by FormInterface but not used since we use custom submit handlers.
}

}
