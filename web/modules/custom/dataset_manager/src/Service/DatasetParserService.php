<?php

namespace Drupal\dataset_manager\Service;

use Drupal\file\Entity\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DatasetParserService {

  public function parse(File $file) {
    $uri = $file->getFileUri();
    $real_path = \Drupal::service('file_system')->realpath($uri);
    $ext = pathinfo($real_path, PATHINFO_EXTENSION);

    $data = [];
    if ($ext === 'csv') {
      $rows = array_map('str_getcsv', file($real_path));
    } else {
      $spreadsheet = IOFactory::load($real_path);
      $rows = $spreadsheet->getActiveSheet()->toArray();
    }

    return [
      'columns' => $rows[0],
      'rows' => count($rows) - 1,
    ];
  }
}
