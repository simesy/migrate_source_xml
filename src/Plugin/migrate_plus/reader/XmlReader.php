<?php

/**
 * @file
 * Contains \Drupal\migrate_source_xml\Plugin\migrate_plus\reader\XmlReader.
 */

namespace Drupal\migrate_source_xml\Plugin\migrate_plus\reader;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate_plus\ReaderPluginBase;

/**
 * Obtain XML data for migration.
 *
 * @Reader(
 *   id = "xml",
 *   title = @Translation("XML")
 * )
 */
class XmlReader extends ReaderPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a ReaderPlugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
