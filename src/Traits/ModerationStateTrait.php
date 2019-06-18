<?php

namespace Brainsum\DrupalBehatTesting\Traits;

/**
 * Trait ModerationStateTrait.
 *
 * @todo: Refactor.
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
