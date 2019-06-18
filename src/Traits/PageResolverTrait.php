<?php

namespace Brainsum\DrupalBehatTesting\Traits;

use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\Yaml\Parser;
use function file_get_contents;

/**
 * Trait PageResolverTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Traits
 */
trait PageResolverTrait {

  /**
   * Page mapping.
   *
   * Associative array for the page map.
   *
   * @var array
   */
  protected $pageMapping;

  /**
   * Load the page mapping config from file.
   *
   * @param string $filePath
   *   File path.
   */
  protected function loadPageMapping(string $filePath): void {
    $this->pageMapping = (new Parser())->parse(file_get_contents($filePath));
  }

  /**
   * Resolve a page.
   *
   * @param string $pageName
   *   The page name.
   *
   * @return string
   *   The page path.
   */
  protected function resolvePageNameToPath(string $pageName): string {
    if (isset($this->pageMapping()[$pageName]['path'])) {
      return $this->pageMapping()[$pageName]['path'];
    }

    throw new PendingException("Path for '$pageName' is not configured.");
  }

  /**
   * Returns the page mapping.
   *
   * @return array
   *   The page mapping.
   */
  protected function pageMapping(): array {
    if (empty($this->pageMapping)) {
      return [];
    }

    return $this->pageMapping;
  }

}
