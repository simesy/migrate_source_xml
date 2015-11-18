<?php

/**
 * @file
 * Contains Drupal\migrate_source_xml\Plugin\migrate\source\Xml.
 */

namespace Drupal\migrate_source_xml\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Source plugin for retrieving XML data.
 *
 * @MigrateSource(
 *   id = "xml"
 * )
 */
class Xml extends SourcePluginBase {
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
   * The source URLs to load XML from.
   *
   * @var array
   */
  protected $sourceUrls = [];

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
   * Information on the source fields to be extracted from the XML.
   *
   * @var array[]
   *   Array of field information keyed by field names. A 'label' subkey
   *   describes the field for migration tools; an 'xpath' subkey provides the
   *   xpath (relative to the individual item) for obtaining the value.
   */
  protected $fields = [];

  /**
   * Description of the unique ID fields for this source.
   *
   * @var array[]
   *   Each array member is keyed by a field name, with a value that is an
   *   array with a single member with key 'type' and value a column type such
   *   as 'integer'.
   */
  protected $ids = [];

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

    if (!is_array($configuration['urls'])) {
      $configuration['urls'] = [$configuration['urls']];
    }

    $this->sourceUrls = $configuration['urls'];
    $this->activeUrl = NULL;
    $this->elementQuery = $configuration['item_xpath'];
    $this->idQuery = $configuration['id_query'];
    $this->iteratorClass = $iterator_class;
    $this->namespaces = $namespaces;
    $this->fields = $configuration['fields'];
    $this->ids = $configuration['ids'];
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
   * Return a string representing the source query.
   *
   * @return string
   *   source query
   */
  public function __toString() {
    // Clump the urls into a string
    // This could cause a problem when using
    // a lot of urls, may need to hash.
    $urls = implode(', ', $this->sourceUrls);
    return 'urls = ' . $urls .
           ' | item xpath = ' . $this->elementQuery .
           ' | item ID xpath = ' . $this->idQuery;
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
   * Gets the source URLs where the XML is located.
   *
   * @return array
   *   Array of URLs
   */
  public function sourceUrls() {
    return $this->sourceUrls;
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
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [];
    foreach ($fields as $field_name => $field_info) {
      $fields[$field_name] = isset($field_info['label']) ? $field_info['label'] : $field_name;
    }
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return $this->ids;
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
