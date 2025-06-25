<?php

namespace Drupal\dataset_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dataset_manager\Service\DatasetParserService;

class DatasetUploadForm extends FormBase {

  protected $parser;

  public function __construct(DatasetParserService $parser) {
    $this->parser = $parser;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dataset_manager.parser')
    );
  }

  public function getFormId() {
    return 'dataset_upload_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
  $form['drop_area'] = [
    '#markup' => '<div id="drop-area" style="border: 2px dashed #aaa; padding: 30px; text-align: center; margin-bottom: 20px;">
                    <strong>Drag & Drop your file here</strong><br>
                    <em>(Only .csv or .xlsx files, max 5MB)</em>
                  </div>
                  <div id="file-info" style="margin-top: 10px;"></div>',
  ];
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Upload'),
  ];

  if ($summary = $form_state->get('summary')) {
    $form['summary'] = [
      '#theme' => 'dataset_summary',
      '#filename' => $summary['filename'],
      '#filesize' => $summary['filesize'],
      '#columns' => $summary['columns'],
      '#rows' => $summary['rows'],
    ];
    if ($dataset_id = $form_state->get('dataset_id')) {
        $form['analyse'] = [
            '#type' => 'link',
            '#title' => $this->t('Analyse Dataset'),
            '#url' => \Drupal\Core\Url::fromUri("internal:/dataset/{$dataset_id}/edit-metadata"),
            '#attributes' => [
            'class' => ['button', 'button--primary'],
            'style' => 'margin-top: 20px;',
            ],
        ];
    }

  }
  $form['#attached']['library'][] = 'dataset_manager/dropzone-style';
  $form['#attached']['library'][] = 'dataset_manager/dragdrop';

  return $form;
}


public function submitForm(array &$form, FormStateInterface $form_state) {
  $fid = \Drupal::service('tempstore.private')->get('dataset_manager')->get('uploaded_fid');

  if (!$fid || !$file = File::load($fid)) {
    $this->messenger()->addError($this->t('No uploaded file found.'));
    return;
  }

  $file->setPermanent();
  $file->save();

  try {
    $summary = $this->parser->parse($file);

    $dataset = \Drupal::entityTypeManager()->getStorage('dataset')->create([
      'filename' => $file->getFilename(),
      'filesize' => $file->getSize(),
      'columns' => implode(', ', $summary['columns']),
      'rows' => $summary['rows'],
    ]);
    $dataset->save();

    $this->messenger()->addStatus($this->t('File uploaded and analyzed successfully.'));

    $form_state->set('summary', [
    'filename' => $file->getFilename(),
    'filesize' => $file->getSize(),
    'columns' => $summary['columns'],
    'rows' => $summary['rows'],
    ]);
    $form_state->set('dataset_id', $dataset->id());
    $form_state->setRebuild(TRUE);

    // Clear the session
    \Drupal::service('tempstore.private')->get('dataset_manager')->delete('uploaded_fid');

  } catch (\Exception $e) {
    $this->messenger()->addError($this->t('Failed to parse file: @error', [
      '@error' => $e->getMessage(),
    ]));
  }
}

}
