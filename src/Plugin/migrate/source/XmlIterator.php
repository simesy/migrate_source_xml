<?php

/**
 * @file
 * Contains \Drupal\migrate_source_xml\Plugin\migrate\source\XmlIterator.
 */

namespace Drupal\migrate_source_xml\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drupal\migrate_plus\Plugin\migrate\source\UrlIterator;

/**
 * Uses one or more XmlReaders to produce a single iterator.
 *
 * This class is independent from XmlReader primarily to support multiple
 * input XML documents in a single migration.
 */
class XmlIterator extends UrlIterator {

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
   * Constructs a new XmlIterator.
   */
  public function __construct(Url $xml_source) {
    parent::__construct($xml_source);

    foreach ($this->urlSource->fieldSelectors() as $field_name => $xpath) {
      if (substr($xpath, 0, 3) === '..\\') {
        $this->parentElementsOfInterest[] = str_replace('..\\', '', $xpath);
      }
    }
  }

  protected function createReader() {
    $reader_class = $this->urlSource->getReaderClass();
    return new $reader_class(
            $this->sourceUrls[$this->activeUrl],
                  $this->urlSource,
                  $this->urlSource->itemSelector(),
                  $this->parentElementsOfInterest);
  }
}
