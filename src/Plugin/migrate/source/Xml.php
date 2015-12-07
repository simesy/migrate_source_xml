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
   * An array of namespaces to explicitly register before Xpath queries.
   *
   * @var array
   */
  protected $namespaces;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    if (empty($this->readerClass)) {
      $this->readerClass = '\Drupal\migrate_source_xml\Plugin\migrate\source\XmlReader';
    }

    $this->namespaces = $configuration['namespaces'];
  }

  /**
   * Explicitly register namespaces on an XML element.
   *
   * @param \SimpleXMLElement $xml
   *   A SimpleXMLElement to register the namespaces on.
   */
  protected function registerNamespaces(\SimpleXMLElement &$xml) {
    foreach ($this->namespaces as $prefix => $namespace) {
      $xml->registerXPathNamespace($prefix, $namespace);
    }
  }

  /**
   * Lists the namespaces found in the source document(s).
   */
  public function namespaces() {
    return [];
  }

}
