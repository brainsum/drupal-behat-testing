<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Brainsum\DrupalBehatTesting\Page\AddPageNodePage;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class NodeContext.
 *
 * Handles node-related steps.
 */
class ElNodeContext extends RawDrupalContext {

  protected $pageAddPage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    AddPageNodePage $addPageNodePage
  ) {
    $this->pageAddPage = $addPageNodePage;
  }

  /**
   * @When I navigate to the "Page add" page
   */
  function iNavigateToNodePageAdd() {
    $this->pageAddPage->open();
  }

}
