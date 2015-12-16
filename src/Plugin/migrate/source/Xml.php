<?php

/**
 * @file
 * Contains Drupal\migrate_source_xml\Plugin\migrate\source\Xml.
 */

namespace Drupal\migrate_source_xml\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate_plus\Plugin\migrate\source\Url;

/**
 * Source plugin for retrieving XML data.
 *
 * @MigrateSource(
 *   id = "xml"
 * )
 */
class Xml extends Url {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    if (empty($this->readerClass)) {
      $this->readerClass = '\Drupal\migrate_source_xml\Plugin\migrate\source\XmlReader';
    }
  }

}
