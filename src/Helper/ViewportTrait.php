<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Selenium2Driver;

/**
 * Trait ViewportTrait.
 *
 * @package Brainsum\DrupalBehatTesting\Helper
 */
trait ViewportTrait {

  /**
   * Set viewport size.
   *
   * @BeforeStep
   */
  public function setViewportBeforeStep(): void {
    if (
      ($driver = $this->sessionDriver())
      && $driver instanceof Selenium2Driver
    ) {
      $driver->resizeWindow(1920, 4000, 'current');
    }
  }

  /**
   * Returns the driver for the current session.
   *
   * @return \Behat\Mink\Driver\DriverInterface
   *   The driver.
   */
  abstract protected function sessionDriver(): DriverInterface;

}
