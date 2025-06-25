<?php

namespace Drupal\dataset_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dataset_manager\Entity\DatasetMetadata;

class DatasetSummaryController extends ControllerBase {

  public function view($dataset) {
    $entity = DatasetMetadata::load($dataset);
    return [
      '#theme' => 'dataset_summary',
      '#dataset' => $entity,
    ];
  }
}
