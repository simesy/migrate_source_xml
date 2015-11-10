<?php

/**
 * @file
 * Contains \Drupal\migrate_source_xml\Plugin\migrate\source\MigrateXmlIterator.
 */

namespace Drupal\migrate_source_xml\Plugin\migrate\source;

/**
 * Uses one or more MigrateXmlReaders to produce a single iterator.
 *
 * This class is independent from MigrateXmlReader primarily to support multiple
 * input XML documents in a single migration.
 */
class MigrateXmlIterator implements \Iterator, \Countable {
  /**
   * Reference to the Xml source plugin over which we are iterating.
   *
   * @var \Drupal\migrate_source_xml\Plugin\migrate\source\Xml
   */
  protected $xmlSource;

  /**
   * Copy of the source URLs listed in the xmlSource.
   *
   * @var array
   */
  protected $sourceUrls;

  /**
   * Holds our current position within the $sourceUrls array.
   *
   * @var int
   */
  protected $activeUrl = NULL;

  /**
   * The MigrateXmlReader currently in use.
   *
   * @var \Drupal\migrate_source_xml\Plugin\migrate\source\MigrateXmlReader
   */
  protected $reader = NULL;

  /**
   * At all times, contains the value that should be returned by current().
   *
   * @var \SimpleXmlElement
   */
  protected $currentElement = NULL;

  /**
   * At all times, contains the key that should be returned by key().
   *
   * @var string
   */
  protected $currentKey = NULL;

  /**
   * Names of source fields that should always be retained.
   *
   * This retention supports references back to them after the underlying reader
   * has passed them.
   *
   * @var array
   */
  protected $parentElementsOfInterest = [];

  /**
   * Constructs a new MigrateXmlIterator.
   */
  public function __construct(Xml $xml_source) {
    $this->xmlSource = $xml_source;

    $this->sourceUrls = $this->xmlSource->sourceUrls();

    foreach ($this->xmlSource->fieldXpaths() as $field_name => $xpath) {
      if (substr($xpath, 0, 3) === '..\\') {
        $this->parentElementsOfInterest[] = str_replace('..\\', '', $xpath);
      }
    }
  }

  /**
   * Returns the name of the MigrateXmlReader class to employ when iterating.
   *
   * @todo
   *   Define an interface for this class so actual substitution of the default
   *   one would be cleaner.
   *
   * @return string
   *   The name of the MigrateXmlReader class.
   */
  public function getReaderClassName() {
    return '\Drupal\migrate_source_xml\Plugin\migrate\source\MigrateXmlReader';
  }

  /**
   * Generates a migration source row (associative array) at this position.
   *
   * @return array
   *   The migration source row.
   */
  public function current() {
    if ($this->valid()) {
      return $this->currentElement;
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->currentKey;
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    $this->currentElement = NULL;
    $this->currentKey = NULL;

    if (isset($this->reader)) {
      // Attempt to load the next row.
      $this->reader->next();
    }

    // Test the reader for a valid row.
    if (isset($this->reader) && $this->reader->valid()) {
      $this->currentElement = $this->reader->current();
      $this->currentKey = $this->reader->key();
    }
    else {
      // The current source is at the end, try to load the next source.
      if ($this->nextSource()) {
        if ($this->reader->valid()) {
          $this->currentElement = $this->reader->current();
          $this->currentKey = $this->reader->key();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return $this->currentElement !== NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->reader = NULL;
    $this->activeUrl = NULL;
    $this->next();
  }

  /**
   * Advances the reader to the next source from sourceUrls.
   *
   * @return bool
   *   TRUE if a valid source was loaded
   */
  public function nextSource() {
    // Return value.
    $status = FALSE;

    while ($this->activeUrl === NULL || (count($this->sourceUrls) - 1) > $this->activeUrl) {
      if (is_null($this->activeUrl)) {
        $this->activeUrl = 0;
      }
      else {
        // Increment the activeUrl so we try to load the next source.
        $this->activeUrl = $this->activeUrl + 1;
        if ($this->activeUrl >= count($this->sourceUrls)) {
          return FALSE;
          // Avoid below invalid index into $this->sourceUrls
        }
      }

      $reader_class = $this->getReaderClassName();
      $this->reader = new $reader_class(
        $this->sourceUrls[$this->activeUrl],
              $this->xmlSource,
              $this->xmlSource->elementQuery(),
              $this->parentElementsOfInterest);
      $this->reader->rewind();

      if ($this->reader->valid()) {
        // We have a valid source.
        $status = TRUE;
        break;
      }
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $count = 0;
    foreach ($this as $element) {
      $count++;
    }
    return $count;
  }
}
