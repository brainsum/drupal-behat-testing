<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\Yaml\Parser;

/**
 * Trait PageResolverTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Helper
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
    $this->pageMapping = (new Parser())->parse($filePath);
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
    if (isset($this->pageMapping()['path'])) {
      return $this->pageMapping()['path'];
    }

    throw new PendingException("Path for '$pageName' is not configured.");
  }

}
