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
   * The iterator object to employ while processing the source.
   *
   * @var \Drupal\migrate_source_xml\Plugin\migrate\source\XMLReader
   */
  protected $reader;

  /**
   * The XMLReader object serving as a cursor over the XML source.
   *
   * @return XMLReader
   *   XMLReader
   */
  public function getReader() {
    return $this->reader;
  }

  /**
   * An array of namespaces to explicitly register before Xpath queries.
   *
   * @var array
   */
  protected $namespaces;

  /**
   * The query string used to recognize elements being iterated.
   *
   * This is an xpath-like expression.
   *
   * @var string
   */
  protected $elementQuery = '';

  /**
   * The iterator class used to traverse the XML.
   *
   * @var string
   */
  protected $iteratorClass = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, array $namespaces = []) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    if (empty($configuration['iterator_class'])) {
      $iterator_class = '\Drupal\migrate_source_xml\Plugin\migrate\source\XmlIterator';
    }
    else {
      $iterator_class = $configuration['iterator_class'];
    }

    $this->elementQuery = $configuration['item_xpath'];
    $this->idQuery = $configuration['id_query'];
    $this->iteratorClass = $iterator_class;
    $this->namespaces = $namespaces;
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
   * Gets the iterator class used to traverse the XML.
   *
   * @return string
   *   The name of the class to be used for low-level XML processing.
   */
  public function iteratorClass() {
    return $this->iteratorClass;
  }

  /**
   * Gets the xpath-like query controlling the iterated elements.
   *
   * Matching elements will be presented by the iterator. Most xpath syntax
   * is supported (it is evaluated by \SimpleXMLElement::xpath), however the
   * SimpleXMLElement object is rooted at the context node and has no ancestors
   * available.
   *
   * @return string
   *   An xpath-like expression.
   */
  public function elementQuery() {
    return $this->elementQuery;
  }

  /**
   * Return a count of all available source records.
   *
   * @return int
   *   The number of available source records.
   */
  public function computeCount() {
    $count = 0;
    foreach ($this->sourceUrls as $url) {
      $iterator = new $this->iteratorClass($this);
      $count += $iterator->count();
    }

    return $count;
  }

  /**
   * Creates and returns a filtered Iterator over the documents.
   *
   * @return \Iterator
   *   An iterator over the documents providing source rows that match the
   *   configured elementQuery.
   */
  protected function initializeIterator() {
    $iterator_class = $this->iteratorClass();
    $iterator = new $iterator_class($this);

    return $iterator;
  }

  /**
   * Lists the namespaces found in the source document(s).
   */
  public function namespaces() {
    return [];
  }

  /**
   * Return the xpaths used to populate each configured field.
   *
   * @return string[]
   *   Array of xpaths, keyed by field name.
   */
  public function fieldXpaths() {
    $fields = [];
    foreach ($this->configuration['fields'] as $field_name => $field_info) {
      if (isset($field_info['xpath'])) {
        $fields[$field_name] = $field_info['xpath'];
      }
    }
    return $fields;
  }

}
