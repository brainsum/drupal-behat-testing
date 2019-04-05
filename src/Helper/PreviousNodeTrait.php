<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Drupal;
use Drupal\node\NodeInterface;
use Exception;
use RuntimeException;

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

}
