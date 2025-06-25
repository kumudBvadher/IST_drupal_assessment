<?php

namespace Drupal\dataset_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\file\Entity\File;
use Drupal\dataset_manager\Entity\DatasetMetadata;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dataset_manager\Service\DatasetParserService;

class DropzoneUploadController extends ControllerBase {

  protected $parser;

  public function __construct(DatasetParserService $parser) {
    $this->parser = $parser;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dataset_manager.parser')
    );
  }

  public function upload(Request $request) {
    $uploaded = $request->files->get('file');
    if (!$uploaded) {
      return new JsonResponse(['error' => 'No file uploaded.'], 400);
    }

    $directory = 'public://datasets/';
    \Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

    $destination = file_unmanaged_copy($uploaded->getRealPath(), $directory . $uploaded->getClientOriginalName(), FILE_EXISTS_RENAME);
    $file = File::create([
      'uri' => $destination,
    ]);
    $file->save();

    $result = $this->parser->parse($destination);

    $entity = DatasetMetadata::create([
      'filename' => $file->getFilename(),
      'row_count' => $result['row_count'] ?? 0,
      'column_names' => implode(', ', $result['column_names'] ?? []),
      'file_size' => format_size(filesize($destination)),
    ]);
    $entity->save();

    return new JsonResponse(['dataset_id' => $entity->id()]);
  }
}
