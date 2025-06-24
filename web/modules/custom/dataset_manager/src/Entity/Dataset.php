<?php

namespace Drupal\dataset_manager\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Dataset entity.
 *
 * @ContentEntityType(
 *   id = "dataset",
 *   label = @Translation("Dataset"),
 *   base_table = "dataset",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "filename",
 *   },
 *   admin_permission = "administer dataset entity",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *   },
 * )
 */
class Dataset extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setReadOnly(TRUE);

    $fields['filename'] = BaseFieldDefinition::create('string')
      ->setLabel(t('File name'))
      ->setRequired(TRUE);

    $fields['filesize'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('File size'));

    $fields['columns'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Column names'));

    $fields['rows'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Row count'));

    return $fields;
  }
}
