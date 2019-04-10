<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Brainsum\DrupalBehatTesting\Helper\PreviousNodeTrait;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use RuntimeException;
use function count;

/**
 * Class PreviousNodeContext.
 *
 * @package Brainsum\DrupalBehatTesting\DrupalExtension\Context
 */
class PreviousNodeContext extends RawDrupalContext {

  use PreviousNodeTrait;

  /**
   * Then I :action previously created node.
   *
   * @Then I :action previously created node
   */
  public function iPreviouslyCreatedNode(string $action): void {
    $fragment = $action === 'view' ? '' : '/edit';
    $this->visitPath("/node/{$this->previousNode()->id()}{$fragment}");
  }

  /**
   * Then I should not see the previously created node.
   *
   * @Then I should not see the previously created node
   */
  public function iShouldNotSeeThePreviouslyCreatedNode(): void {
    $page = $this->getSession()->getPage();
    $title = $this->previousNode()->get('title')->value;
    $nodes = $page->findAll('xpath', '//html[text()="' . $title . '"]');

    if (count($nodes) > 0) {
      throw new RuntimeException('Node not found on rss feed!');
    }
  }

}
