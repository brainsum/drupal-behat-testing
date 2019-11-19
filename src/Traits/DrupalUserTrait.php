<?php

namespace Brainsum\DrupalBehatTesting\Traits;

use Drupal;
use Drupal\DrupalExtension\Manager\DrupalUserManagerInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use RuntimeException;
use function method_exists;
use function reset;

/**
 * Trait DrupalUserTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Traits
 *
 * @todo: Move storage/access code into a service so state can be shared between classes.
 */
trait DrupalUserTrait {

  /**
   * The behat user manager from drupal-extension.
   *
   * @var \Drupal\DrupalExtension\Manager\DrupalUserManagerInterface
   */
  private $behatUserManager;

  /**
   * The Drupal user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  private $drupalUserStorage;

  /**
   * The Drupal user.
   *
   * @var \Drupal\user\UserInterface
   */
  private $drupalUser;

  /**
   * Sets the drupal user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The Drupal user.
   */
  protected function setDrupalUser(UserInterface $user): void {
    $this->drupalUser = $user;
  }

  /**
   * Returns the drupal user.
   *
   * @return \Drupal\user\UserInterface
   *   The drupal user.
   */
  protected function drupalUser(): UserInterface {
    return $this->drupalUser;
  }

  /**
   * Sets the behat user manager.
   *
   * @var \Drupal\DrupalExtension\Manager\DrupalUserManagerInterface $manager
   *   The behat user manager.
   */
  protected function setBehatUserManager(DrupalUserManagerInterface $manager): void {
    $this->behatUserManager = $manager;
  }

  /**
   * Returns the behat user manager.
   *
   * @return \Drupal\DrupalExtension\Manager\DrupalUserManagerInterface
   *   The behat user manager.
   */
  protected function behatUserManager(): DrupalUserManagerInterface {
    if ($this->behatUserManager === NULL) {
      if (method_exists($this, 'getUserManager')) {
        $this->behatUserManager = $this->getUserManager();
      }
      else {
        throw new RuntimeException('The behat user manager cannot be found or automatically set.');
      }
    }

    return $this->behatUserManager;
  }

  /**
   * Sets the Drupal user storage.
   *
   * @param \Drupal\user\UserStorageInterface $userStorage
   *   The user storage.
   */
  protected function setDrupalUserStorage(UserStorageInterface $userStorage): void {
    $this->drupalUserStorage = $userStorage;
  }

  /**
   * Return the Drupal user storage.
   *
   * @return \Drupal\user\UserStorageInterface
   *   The Drupal user storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function drupalUserStorage(): UserStorageInterface {
    if ($this->drupalUserStorage === NULL) {
      /** @var \Drupal\user\UserStorageInterface $storage */
      $storage = Drupal::entityTypeManager()->getStorage('user');
      $this->setDrupalUserStorage($storage);
    }

    return $this->drupalUserStorage;
  }

  /**
   * Returns the current user as a drupal User instance.
   *
   * @return \Drupal\user\UserInterface
   *   The current drupal user.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadCurrentDrupalUser(): UserInterface {
    /** @var \stdClass $behatUser */
    $behatUser = $this->behatUserManager()->getCurrentUser();

    if ($behatUser === FALSE) {
      throw new RuntimeException('Anonymous user is not allowed to create content.');
    }

    /** @var \Drupal\user\UserInterface[] $drupalUsers */
    $drupalUsers = $this->drupalUserStorage()->loadByProperties([
      'name' => $behatUser->name,
    ]);

    if (empty($drupalUsers)) {
      throw new RuntimeException("Drupal user could not be loaded for Behat user '{$behatUser->name}'.");
    }

    /** @var \Drupal\user\UserInterface $drupalUser */
    $drupalUser = reset($drupalUsers);
    $this->setDrupalUser($drupalUser);
    return $this->drupalUser();
  }

}
