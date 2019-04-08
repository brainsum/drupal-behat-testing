<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Behat\Mink\Driver\DriverInterface;
use Brainsum\DrupalBehatTesting\Helper\PageResolverTrait;
use Brainsum\DrupalBehatTesting\Helper\PreviousNodeTrait;
use Brainsum\DrupalBehatTesting\Helper\SpinTrait;
use Brainsum\DrupalBehatTesting\Helper\ViewportTrait;
use Drupal;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use RuntimeException;

/**
 * Class RssContext.
 *
 * Defines necessary steps used for testing the RSS syndication feature.
 */
class ElRssContext extends RawDrupalContext {

  use SpinTrait;
  use PageResolverTrait;
  use PreviousNodeTrait;
  use ViewportTrait;

  /**
   * {@inheritdoc}
   */
  protected function sessionDriver(): DriverInterface {
    return $this->getSession()->getDriver();
  }

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
   * Remove the previous node, if exists.
   *
   * @AfterStep
   */
  public function after(AfterStepScope $scope): void {
    if (
      $this->previousNode() !== NULL
      && $scope->getTestResult()->getResultCode() === 99
    ) {
      $this->previousNode()->delete();
      echo "Cleanup executed after failed step, deleted node: {$this->previousNode()->id()}";
    }
  }

  /**
   * @When I click Generate RSS URL submit button
   */
  public function iClickGenerateRssUrlSubmitButton(): void {
    // *[@id="edit-submit"] XPath.
    $this->getSession()
      ->getPage()
      ->find('css', '#views-exposed-form-news-rss-page > div > div > input[type="submit"]')
      ->click();
  }

  /**
   * @Then I should not see the previously created node
   */
  public function iShouldNotSeeThePreviouslyCreatedNode(): void {
    $page = $this->getSession()->getPage();

    $title = $this->previousNode()->get('title')->value;

    $nodes = $page->findAll('xpath', '//html[text()="' . $title . '"]');

    if (count($nodes) > 0) {
      throw new RuntimeException('Node not found on rss feed!');
    }
  }

  /**
   * Then the RSS date should be the :dateType date.
   *
   * @Then the RSS date should be the :dateType date
   */
  public function theRssDateShouldBeTheDate(string $dateType): void {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = Drupal::service('date.formatter');

    switch ($dateType) {
      case 'First publish':
        $stamp = strtotime($this->previousNode()->get('field_first_publish_date')->value);
        $date = $dateFormatter->format($stamp, 'custom', 'd M Y/H:i');
        break;

      case 'Authored on':
        // @todo: This should be "changed"?
        $date = DrupalDateTime::createFromTimestamp((int) $this->previousNode()->getCreatedTime())
          ->format('d M Y/H:i');
        break;

      default:
        $date = DrupalDateTime::createFromTimestamp((int) $this->previousNode()->getCreatedTime())
          ->format('d M Y/H:i');
    }

    $rssDate = $dateFormatter->format(strtotime($this->previousNode()->get('field_rss_date')->value), 'custom', 'd M Y/H:i');

    if ($rssDate !== $date) {
      throw new RuntimeException("The date: ('$date') of type ('$dateType') is not equal to rss date: ('$rssDate') with title ('{$this->previousNode()->getTitle()}')");
    }
  }

//  /**
//   * @When /^(?:|I )fill in select2 input "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" and select "(?P<entry>(?:[^"]|\\")*)"$/
//   *
//   * @todo: Refactor according to community context
//   * @see: https://github.com/novaway/BehatCommonContext
//   */
//  public function iFillInSelectInputWithAndSelect($field, $value, $entry) {
//    $page = $this->getSession()->getPage();
//
//    $inputField = $page->find('css', $field);
//    if (!$inputField) {
//      throw new \RuntimeException('No field found');
//    }
//
//    $choice = $inputField->getParent()->find('css', '.select2-selection');
//    if (!$choice) {
//      throw new \RuntimeException('No select2 choice found');
//    }
//    $choice->press();
//
//    $select2Input = $page->find('css', '.select2-search__field');
//    if (!$select2Input) {
//      throw new \RuntimeException('No input found');
//    }
//    $select2Input->setValue($value);
//
//    $this->getSession()->wait(1000);
//
//    $chosenResults = $page->findAll('css', '.select2-results li');
//    /** @var \Behat\Mink\Element\NodeElement $result */
//    foreach ($chosenResults as $result) {
//      if ($result->getText() === $entry) {
//        $result->click();
//        break;
//      }
//    }
//  }

}
