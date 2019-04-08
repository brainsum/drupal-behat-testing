<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Behat\Behat\Tester\Exception\PendingException;

trait ElPageResolverTrait {

  private $pageNamePathMap = [
    'home' => '/home',
    'page_add' => '/node/add/page',
  ];

  /**
   * Return a path to a page name.
   *
   * @param string $pageName
   *   The page name.
   *
   * @return string
   *   The path.
   */
  protected function pageNameToPath($pageName) {
    if (isset($this->pageNamePathMap[$pageName])) {
      return $this->pageNamePathMap[$pageName];
    }

    throw new PendingException('Todo: iAmOnTheHomePage got an invalid argument.');
  }

}
