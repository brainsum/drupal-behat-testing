<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Brainsum\DrupalBehatTesting\Helper\SpinTrait;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use PHPUnit\Framework\Assert;
use RuntimeException;
use function strtolower;
use function file_put_contents;

/**
 * Class GenericContext.
 *
 * @package Brainsum\DrupalBehatTesting\DrupalExtension\Context
 */
class GenericContext extends RawDrupalContext {

  use SpinTrait;

  /**
   * Set viewport size.
   *
   * @BeforeStep
   */
  public function beforeStep(): void {
    $driver = $this->getSession()->getDriver();
    if ($driver instanceof Selenium2Driver) {
      $driver->resizeWindow(1920, 4000, 'current');
    }
  }

  /**
   * Then I visit the :pageName page.
   *
   * This includes an action of visiting the page.
   * For assertion only see iAmOnThePage() function instead.
   *
   * @Then I visit the :pageName page
   */
  public function iVisitThePage(string $pageName): void {
    $this->visitPath($this->pathForPage($pageName));
  }

  /**
   * Given I am on the :pageName page.
   *
   * This is only for assertion.
   * No action (eg. visiting a page) is performed here.
   * For that see iVisitThePage() function instead.
   *
   * @Given I am on the :pageName page
   */
  public function iAmOnThePage(string $pageName): void {
    $path = $this->pathForPage($pageName);

    $currentPath = parse_url(
      $this->getSession()->getCurrentUrl(),
      PHP_URL_PATH
    );

    Assert::assertSame($path, $currentPath);
  }

  /**
   * Return path for page name.
   *
   * @param string $pageName
   *   Name of the page.
   *
   * @return string
   *   Name of the path.
   *
   * @todo: FIXME.
   */
  protected function pathForPage(string $pageName): string {
    $pageName = strtolower($pageName);

    switch ($pageName) {
      case 'Home':
        return $this->pageNameToPath('home');

      case 'Page add':
        return $this->pageNameToPath('page_add');

      case 'Landing pages':
        return $this->pageNameToPath('entity.landing_page.collection');
    }

    throw new RuntimeException("Path for the given page '$pageName' not found.");
  }

  /**
   * When I wait for the page to be loaded.
   *
   * @When I wait for the page to be loaded
   */
  public function iWaitForThePageToBeLoaded(): void {
    $this->getSession()->wait(20000, "document.readyState === 'complete'");
  }

}
