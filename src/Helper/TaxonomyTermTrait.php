<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Drupal;
use Drupal\Component\Utility\Crypt;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;

/**
 * Class TaxonomyTermTrait.
 */
trait TaxonomyTermTrait {

  /**
   * The Drupal term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Generated keyword term.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $keywordTerm;

  /**
   * Generated profiling (unit, role, location) term.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $profilingTerm;

  /**
   * Generate a term.
   *
   * @param string $vocabulary
   *   The vocabulary.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The term.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  private function generateTerm(string $vocabulary): TermInterface {
    $termValues = [
      'vid' => $vocabulary,
      'name' => "Behat test {$vocabulary} | " . bin2hex(Crypt::randomBytes(10)),
    ];

    /** @var \Drupal\taxonomy\TermInterface $term */
    $term = $this->termStorage()->create($termValues);
    $term->save();

    return $term;
  }

  /**
   * Generate a keyword.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The generated keyword.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  protected function generateKeywordTerm(): TermInterface {
    $this->setKeywordTerm($this->generateTerm('keyword'));

    return $this->keywordTerm();
  }

  /**
   * Generate a profiling term.
   *
   * @param string $vocabulary
   *   The vid.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The new term.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  protected function generateProfilingTerm(string $vocabulary): TermInterface {
    $this->setProfilingTerm($this->generateTerm($vocabulary));

    return $this->profilingTerm();
  }

  /**
   * Returns the term storage.
   *
   * @return \Drupal\taxonomy\TermStorageInterface
   *   The term storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function termStorage(): TermStorageInterface {
    if ($this->termStorage === NULL) {
      $this->termStorage = Drupal::entityTypeManager()
        ->getStorage('taxonomy_term');
    }

    return $this->termStorage;
  }

  /**
   * Returns the profiling term.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The profiling.
   */
  public function profilingTerm(): TermInterface {
    return $this->profilingTerm;
  }

  /**
   * Sets the profiling term.
   *
   * @param \Drupal\taxonomy\TermInterface $profilingTerm
   *   The profiling term.
   */
  public function setProfilingTerm(TermInterface $profilingTerm): void {
    $this->profilingTerm = $profilingTerm;
  }

  /**
   * Returns the keyword term.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The keyword.
   */
  public function keywordTerm(): TermInterface {
    return $this->keywordTerm;
  }

  /**
   * Sets the keyword term.
   *
   * @param \Drupal\taxonomy\TermInterface $keywordTerm
   *   The keyword.
   */
  public function setKeywordTerm(TermInterface $keywordTerm): void {
    $this->keywordTerm = $keywordTerm;
  }

  /**
   * Cleans up the keyword term.
   *
   * @AfterScenario
   */
  public function cleanupKeywordTerm(): void {
    if ($this->keywordTerm !== NULL) {
      $this->keywordTerm->delete();
      $this->keywordTerm = NULL;
    }
  }

  /**
   * Cleans up the profiling term.
   *
   * @AfterScenario
   */
  public function cleanupProfilingTerm(): void {
    if ($this->profilingTerm !== NULL) {
      $this->profilingTerm->delete();
      $this->profilingTerm = NULL;
    }
  }

}
