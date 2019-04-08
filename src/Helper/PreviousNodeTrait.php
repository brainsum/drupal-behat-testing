<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Drupal;
use Drupal\node\NodeInterface;
use Exception;
use RuntimeException;
use function count;

/**
 * Class PreviousNodeTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Helper
 */
trait PreviousNodeTrait {

  /**
   * The previous node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $previousNode;

  /**
   * Remove the previous node, if exists.
   *
   * @AfterStep
   */
  public function previousNodeCleanup(AfterStepScope $scope): void {
    if (
      $this->previousNode() !== NULL
      && $scope->getTestResult()->getResultCode() === 99
    ) {
      $this->previousNode()->delete();
      echo "Cleanup executed after failed step, deleted node: {$this->previousNode()->id()}";
    }
  }

  /**
   * Set the previous node.
   *
   * @param int $nodeId
   *   The node id.
   */
  public function setPreviousNode(int $nodeId): void {
    $state = Drupal::state();
    $state->set('behat_testing.previous_node_id', $nodeId);

    $node = NULL;

    try {
      /** @var \Drupal\node\NodeInterface $node */
      $node = Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($nodeId);
    }
    catch (Exception $exception) {
      // Pass.
    }

    if ($node === NULL) {
      throw new RuntimeException('Previous node could not be loaded.');
    }

    $this->previousNode = $node;
  }

  /**
   * Return the previous node.
   *
   * @return \Drupal\node\NodeInterface
   *   The node, or NULL.
   */
  public function previousNode(): NodeInterface {
    $state = Drupal::state();
    $nodeId = $state->get('behat_testing.previous_node_id');

    if ($nodeId === NULL) {
      throw new RuntimeException('ID for previous node was not found.');
    }

    if (
      isset($this->previousNode)
      && $this->previousNode->id() === $nodeId
    ) {
      return $this->previousNode;
    }

    $node = NULL;

    try {
      /** @var \Drupal\node\NodeInterface $node */
      $node = Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($nodeId);
    }
    catch (Exception $exception) {
      // Pass.
    }

    if ($node === NULL) {
      throw new RuntimeException('Previous node could not be loaded.');
    }

    $this->previousNode = $node;
    return $this->previousNode;
  }

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
