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
    $form['upload'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Dataset (CSV/XLSX)'),
      '#upload_location' => 'public://datasets/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv xlsx'],
      ],
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload & Analyze'),
    ];

    // Check if we have a parsed summary to display
    if ($summary = $form_state->get('summary')) {
      $form['summary'] = [
        '#theme' => 'dataset_summary',
        '#filename' => $summary['filename'],
        '#filesize' => $summary['filesize'],
        '#columns' => $summary['columns'],
        '#rows' => $summary['rows'],
      ];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('upload')[0];
    $file = File::load($fid);

    if (!$file) {
      $this->messenger()->addError($this->t('Uploaded file could not be loaded.'));
      return;
    }

    $file->setPermanent();
    $file->save();

    try {
      $summary = $this->parser->parse($file);

      // Save dataset entity.
      $dataset = \Drupal::entityTypeManager()->getStorage('dataset')->create([
        'filename' => $file->getFilename(),
        'filesize' => $file->getSize(),
        'columns' => implode(', ', $summary['columns']),
        'rows' => $summary['rows'],
      ]);
      $dataset->save();

      // Show feedback and summary.
      $this->messenger()->addStatus($this->t('File uploaded and analyzed successfully.'));

      $form_state->set('summary', [
        'filename' => $file->getFilename(),
        'filesize' => $file->getSize(),
        'columns' => $summary['columns'],
        'rows' => $summary['rows'],
      ]);
      $form_state->setRebuild(TRUE);

    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Failed to parse file: @error', [
        '@error' => $e->getMessage(),
      ]));
    }
  }
}
