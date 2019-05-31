<?php

namespace Brainsum\DrupalBehatTesting\Debug;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use function is_scalar;
use function json_encode;

/**
 * Trait DebugTrait.
 *
 * @package DockerBehat\Custom
 */
trait DebugTrait {

  /**
   * Debug function.
   *
   * @param array $data
   *   Data to print.
   */
  public static function debug(array $data): void {
    $output = new ConsoleOutput(
      ConsoleOutput::VERBOSITY_NORMAL,
      TRUE
    );
    $output->getFormatter()
      ->setStyle('debug_header', new OutputFormatterStyle('blue'));
    $output->writeln('');
    $output->writeln('---- DEBUG ----');
    $output->getFormatter()
      ->setStyle('debug_item', new OutputFormatterStyle('cyan'));
    foreach ($data as $item) {
      if (is_scalar($item)) {
        $output->writeln($item);
      }
      $output->writeln(json_encode($item));
    }
  }

}
