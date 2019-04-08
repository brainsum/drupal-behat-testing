<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Driver\DriverInterface;

/**
 * Trait ViewportTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Helper
 */
trait ViewportTrait {

  /**
   * Returns the driver for the current session.
   *
   * @return \Behat\Mink\Driver\DriverInterface
   *   The driver.
   */
  abstract protected function sessionDriver(): DriverInterface;

  /**
   * Set viewport size.
   *
   * @BeforeStep
   */
  public function beforeStep(): void {
    if (
      ($driver = $this->sessionDriver())
      && $driver instanceof Selenium2Driver
    ) {
      $driver->resizeWindow(1920, 4000, 'current');
    }
  }

}
