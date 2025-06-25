<?php

namespace Drupal\dataset_manager\Entity;

use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * @ContentEntityType(
 *   id = "dataset",
 *   label = @Translation("Dataset"),
 *   base_table = "dataset",
 *   revision_table = "dataset_revision",
 *   translatable = TRUE,
 *   revisionable = TRUE,
 *   content_moderation = TRUE,
 *   show_revision_ui = TRUE,
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *      "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   admin_permission = "administer dataset entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "filename",
 *     "langcode" = "langcode",
 *     "revision" = "revision_id"
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   },
 *   links = {
 *     "canonical" = "/dataset/{dataset}",
 *     "edit-form" = "/dataset/{dataset}/edit",
 *     "delete-form" = "/dataset/{dataset}/delete",
 *     "collection" = "/admin/content/datasets"
 *   }
 * )
 */
class Dataset extends RevisionableContentEntityBase implements ContentEntityInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setReadOnly(TRUE);

    $fields['filename'] = BaseFieldDefinition::create('string')
        ->setLabel(t('File name'))
        ->setRequired(TRUE)
        ->setTranslatable(FALSE);

    $fields['filesize'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('File size'))
        ->setTranslatable(FALSE);

    $fields['columns'] = BaseFieldDefinition::create('string_long')
        ->setLabel(t('Column names'))
        ->setTranslatable(FALSE);

    $fields['rows'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('Row count'))
        ->setTranslatable(FALSE);

    $fields['title'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Generated Title'))
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
        ->setLabel(t('Generated Description'))
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE);

    $fields['tags'] = BaseFieldDefinition::create('string_long')
        ->setLabel(t('Tags'))
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE);

    $fields['categories'] = BaseFieldDefinition::create('string_long')
        ->setLabel(t('Suggested Categories'))
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Created'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')
        ->setLabel(t('Changed'));
    

    $fields['langcode'] = BaseFieldDefinition::create('language')
        ->setLabel(t('Language code'))
        ->setRequired(TRUE)
        ->setTranslatable(TRUE);

    $fields['revision_log_message'] = BaseFieldDefinition::create('string_long')
        ->setLabel(t('Revision log message'))
        ->setRevisionable(TRUE)
        ->setDisplayConfigurable('form', TRUE);

    $fields['moderation_state'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Moderation state'))
        ->setRevisionable(TRUE)
        ->setTranslatable(FALSE)
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $fields['revision_id'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('Revision ID'))
        ->setReadOnly(TRUE);

    $fields['revision_created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Revision timestamp'))
        ->setRevisionable(TRUE);

    $fields['revision_user'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Revision author'))
        ->setDescription(t('The user ID of the author of the current revision.'))
        ->setSetting('target_type', 'user')
        ->setRevisionable(TRUE);

    $fields['revision_default'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Default revision'))
        ->setDescription(t('A flag indicating whether this is the default revision.'))
        ->setDefaultValue(TRUE)
        ->setRevisionable(TRUE);

    return $fields;
  }
}
