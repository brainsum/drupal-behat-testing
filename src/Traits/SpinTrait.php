<?php

namespace Brainsum\DrupalBehatTesting\Traits;

use Exception;
use RuntimeException;
use function debug_backtrace;
use function sleep;

/**
 * Trait SpinTrait.
 */
trait SpinTrait {

  /**
   * Spin method.
   *
   * @param callable $lambda
   *   Callable to wait.
   * @param int $wait
   *   The wait timeout.
   *
   * @return bool
   *   Returns TRUE when callable was executed, otherwise exception is thrown.
   *
   * @throws \Exception
   *
   * @see: http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html
   */
  public function spin(callable $lambda, $wait = 60): bool {
    for ($i = 0; $i < $wait; $i++) {
      try {
        if ($lambda($this)) {
          return TRUE;
        }
      }
      catch (Exception $e) {
        // Do nothing.
      }
      sleep(1);
    }
    $backtrace = debug_backtrace();

    throw new RuntimeException(
      'Timeout thrown by ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . "()\n" .
      $backtrace[1]['file'] . ', line ' . $backtrace[1]['line']
    );
  }

}
