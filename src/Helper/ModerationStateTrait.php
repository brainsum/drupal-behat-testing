<?php

namespace Brainsum\DrupalBehatTesting\Helper;

/**
 * Trait ModerationStateTrait.
 *
 * phpcs:disable
 * @deprecated Moved to the Brainsum\DrupalBehatTesting\Traits namespace.
 * phpcs:enable
 */
trait ModerationStateTrait {

  /**
   * Returns the machine name for a state.
   *
   * @param string $state
   *   The human-readable state name.
   *
   * @return string
   *   The machine name.
   *
   * @throws \RuntimeException
   */
  abstract protected function stateMachineName(string $state): string;

}
