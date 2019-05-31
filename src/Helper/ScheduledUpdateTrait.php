<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\scheduled_updates\ScheduledUpdateInterface;
use RuntimeException;

/**
 * Trait ScheduledUpdateTrait.
 */
trait ScheduledUpdateTrait {

  /**
   * Storage for scheduled updates.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $scheduledUpdateStorage;

  /**
   * The scheduled update.
   *
   * @var \Drupal\scheduled_updates\ScheduledUpdateInterface
   */
  protected $scheduledUpdate;

  /**
   * Sets the scheduled update.
   *
   * @param \Drupal\scheduled_updates\ScheduledUpdateInterface $scheduledUpdate
   *   The scheduled update.
   */
  public function setScheduledUpdate(ScheduledUpdateInterface $scheduledUpdate): void {
    $this->scheduledUpdate = $scheduledUpdate;
  }

  /**
   * Returns the scheduled update.
   *
   * @return \Drupal\scheduled_updates\ScheduledUpdateInterface
   *   The scheduled update.
   */
  public function scheduledUpdate(): ScheduledUpdateInterface {
    return $this->scheduledUpdate;
  }

  /**
   * Returns the scheduled update storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The scheduled update storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function scheduledUpdateStorage(): EntityStorageInterface {
    if ($this->scheduledUpdateStorage === NULL) {
      $this->scheduledUpdateStorage = Drupal::entityTypeManager()
        ->getStorage('scheduled_update');
    }

    return $this->scheduledUpdateStorage;
  }

  /**
   * Generate a scheduled update.
   *
   * @param int $updateTimestamp
   *   Update timestamp.
   * @param string $type
   *   Scheduling type.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity which we want to schedule.
   *
   * @return \Drupal\scheduled_updates\ScheduledUpdateInterface
   *   The scheduled update.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function generateScheduling(int $updateTimestamp, string $type, EntityInterface $entity): ScheduledUpdateInterface {
    $values = [
      'type' => $this->scheduleTypeMachineName($type),
      'update_timestamp' => $updateTimestamp,
      'entity_ids' => ['target_id' => $entity->id()],
    ];

    /** @var \Drupal\scheduled_updates\ScheduledUpdateInterface $update */
    $update = $this->scheduledUpdateStorage()->create($values);
    $this->setScheduledUpdate($update);
    return $this->scheduledUpdate();
  }

  /**
   * Reload the scheduled update from the database.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function reloadScheduledUpdate(): void {
    $sid = $this->scheduledUpdate()->id();
    /** @var \Drupal\scheduled_updates\ScheduledUpdateInterface $loaded */
    $loaded = $this->scheduledUpdateStorage()->load($sid);

    if ($loaded === NULL) {
      throw new RuntimeException("The scheduled update ({$sid}) could not be reloaded.");
    }

    $this->setScheduledUpdate($loaded);
  }

  /**
   * Return the type machine name.
   *
   * @param string $type
   *   Scheduling type.
   *
   * @return string
   *   The type machine name.
   *
   * @throws \RuntimeException
   */
  abstract protected function scheduleTypeMachineName(string $type): string;

  /**
   * Return the field for the type.
   *
   * @param string $type
   *   The type name.
   *
   * @return string
   *   The schedule field.
   */
  abstract protected function scheduleFieldName(string $type): string;

}
