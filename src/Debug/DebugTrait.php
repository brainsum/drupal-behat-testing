<?php

namespace Brainsum\DrupalBehatTesting\Debug;

use Drupal\Core\Entity\FieldableEntityInterface;
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
   * Return an output for the debugger.
   *
   * @return \Symfony\Component\Console\Output\ConsoleOutput
   *   Debugger output.
   */
  public static function consoleOutput(): ConsoleOutput {
    $output = new ConsoleOutput(
      ConsoleOutput::VERBOSITY_NORMAL,
      TRUE
    );
    $output->getFormatter()
      ->setStyle('debug_header', new OutputFormatterStyle('blue'));
    $output->getFormatter()
      ->setStyle('debug_item', new OutputFormatterStyle('cyan'));

    return $output;
  }

  /**
   * Debug function.
   *
   * @param array $data
   *   Data to print.
   */
  public static function debug(array $data): void {
    $output = static::consoleOutput();
    $output->writeln('');
    $output->writeln('---- DEBUG ----');
    foreach ($data as $item) {
      if (is_scalar($item)) {
        $output->writeln($item);
      }
      $output->writeln(json_encode($item));
    }
  }

  /**
   * Print validation message for an entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   */
  public static function validateEntity(FieldableEntityInterface $entity): void {
    $output = static::consoleOutput();
    $output->writeln("Node: {$entity->id()}");

    /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
    foreach ($entity->validate() as $error) {
      $output->writeln("\t{$error->getMessage()}");
    }
    $output->writeln('');
  }

}
