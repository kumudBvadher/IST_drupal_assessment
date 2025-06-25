<?php

namespace Drupal\dataset_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;

class UploadAjaxController extends ControllerBase {

  public function upload(Request $request) {
    $uploaded_file = $request->files->get('file');

    if (!$uploaded_file) {
      return new JsonResponse(['error' => 'No file uploaded.'], 400);
    }

    $filename = $uploaded_file->getClientOriginalName();
    $destination = 'public://datasets/';
    \Drupal::service('file_system')->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $real_path = \Drupal::service('file_system')->realpath($destination);
    $target_path = $real_path . '/' . $filename;

    if (!@move_uploaded_file($uploaded_file->getPathname(), $target_path)) {
      return new JsonResponse(['error' => 'Failed to move uploaded file.'], 500);
    }

    $file = File::create([
      'uri' => $destination . $filename,
      'status' => 0,
    ]);
    $file->save();

    \Drupal::service('tempstore.private')->get('dataset_manager')->set('uploaded_fid', $file->id());

    return new JsonResponse([
      'fid' => $file->id(),
      'filename' => $file->getFilename(),
      'filesize' => $file->getSize(),
    ]);
  }
}
