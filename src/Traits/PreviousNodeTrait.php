<?php

namespace Brainsum\DrupalBehatTesting\Traits;

use Drupal;
use Drupal\Component\Utility\Crypt;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use RuntimeException;
use function array_merge;

/**
 * Class PreviousNodeTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Traits
 *
 * @todo: Move storage/access code into a service so state can be shared between classes.
 */
trait PreviousNodeTrait {

  // @todo: Maybe don't force this relation.
  use DrupalUserTrait;

  /**
   * The Drupal node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $previousNode;

  /**
   * Returns the node storage.
   *
   * @return \Drupal\node\NodeStorageInterface
   *   The node storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function nodeStorage(): NodeStorageInterface {
    if ($this->nodeStorage === NULL) {
      $this->nodeStorage = Drupal::entityTypeManager()->getStorage('node');
    }

    return $this->nodeStorage;
  }

  /**
   * Set the previous node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   */
  protected function setPreviousNode(NodeInterface $node): void {
    $this->previousNode = $node;
  }

  /**
   * Return the previous node.
   *
   * @return \Drupal\node\NodeInterface
   *   The previous node.
   *
   * @note: Assumes $this->previousNode was not unset manually.
   */
  protected function previousNode(): NodeInterface {
    return $this->previousNode;
  }

  /**
   * Generate a basic node with the additionally supplied values and save it.
   *
   * @param string $bundle
   *   The bundle machine name.
   * @param array $additionalValues
   *   (Optional) additional field/value pairs.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  protected function generateNode(string $bundle, array $additionalValues = []): NodeInterface {
    $values = [
      'type' => $bundle,
      'uid' => $this->loadCurrentDrupalUser()->id(),
      'title' => 'Behat testing | ' . bin2hex(Crypt::randomBytes(10)),
      'body' => bin2hex(Crypt::randomBytes(10)),
    ];

    $values = array_merge($values, $additionalValues);

    /** @var \Drupal\node\NodeInterface $newNode */
    $newNode = $this->nodeStorage()->create($values);
    $newNode->save();
    return $newNode;
  }

  /**
   * Generate an project-specific node.
   *
   * Might be used to abstract project-specific node generations to avoid code
   * duplication.
   *
   * @param string $bundle
   *   Human-readable content type/bundle.
   * @param string $moderationState
   *   Human-readable moderation state.
   * @param array $additionalValues
   *   (Optional) additional field/value pairs.
   *
   * @return \Drupal\node\NodeInterface
   *   The saved node.
   *
   * @throws \Exception
   */
  abstract protected function generateProjectNode(
    string $bundle,
    string $moderationState,
    array $additionalValues = []
  ): NodeInterface;

  /**
   * Returns the machine name for a node type.
   *
   * @param string $type
   *   The human-readable node type.
   *
   * @return string
   *   The machine name.
   *
   * @throws \RuntimeException
   */
  abstract protected function nodeTypeMachineName(string $type): string;

  /**
   * Loads the previous node.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node, or NULL.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadPreviousNode(): ?NodeInterface {
    $nid = $this->previousNode()->id();
    $this->nodeStorage()->resetCache([$nid]);
    /** @var \Drupal\node\NodeInterface|null $node */
    $node = $this->nodeStorage()->load($nid);
    return $node;
  }

  /**
   * Checks if the previousNode was deleted or not.
   *
   * @return bool
   *   TRUE, if it was deleted, FALSE otherwise.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @todo: Make this more bulletproof.
   */
  protected function previousNodeWasDeleted(): bool {
    return $this->loadPreviousNode() === NULL;
  }

  /**
   * Reload the previous node from the database.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function reloadPreviousNode(): void {
    /** @var \Drupal\node\NodeInterface $loaded */
    $loaded = $this->loadPreviousNode();

    if ($loaded === NULL) {
      throw new RuntimeException("The node ({$this->previousNode()->id()}) could not be reloaded.");
    }

    $this->setPreviousNode($loaded);
  }

  /**
   * Cleans up the previous node.
   *
   * @AfterScenario
   */
  public function cleanupPreviousNode(): void {
    if ($this->previousNode !== NULL) {
      $this->previousNode->delete();
      $this->previousNode = NULL;
    }
  }

}
