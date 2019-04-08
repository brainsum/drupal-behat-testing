<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Behat\Mink\Driver\DriverInterface;
use Brainsum\DrupalBehatTesting\Helper\PageResolverTrait;
use Brainsum\DrupalBehatTesting\Helper\SpinTrait;
use Brainsum\DrupalBehatTesting\Helper\ViewportTrait;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use PHPUnit\Framework\Assert;

/**
 * Class GenericContext.
 *
 * @package Brainsum\DrupalBehatTesting\DrupalExtension\Context
 */
class GenericContext extends RawDrupalContext {

  use SpinTrait;
  use ViewportTrait;
  use PageResolverTrait;

  /**
   * ElFeatureContext constructor.
   *
   * @param string $pageMapFilePath
   *   Pathname for the map config file.
   */
  public function __construct(string $pageMapFilePath) {
    $this->loadPageMapping($pageMapFilePath);
  }

  /**
   * {@inheritdoc}
   */
  protected function sessionDriver(): DriverInterface {
    return $this->getSession()->getDriver();
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
    $this->visitPath($this->resolvePageNameToPath($pageName));
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
    $path = $this->resolvePageNameToPath($pageName);

    $currentPath = parse_url(
      $this->getSession()->getCurrentUrl(),
      PHP_URL_PATH
    );

    Assert::assertSame($path, $currentPath);
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
