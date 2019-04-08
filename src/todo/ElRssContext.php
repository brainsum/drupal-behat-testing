<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Behat\Behat\Tester\Exception\PendingException;
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

  /**
   * Then I should see the text :title and :date.
   *
   * @Then I should see the text :title and :date
   */
  public function iShouldSeeTheTextAnd($title, $date): void {
    throw new PendingException();

    $page = $this->getSession()->getPage();
    switch ($date) {
      case 'pubdate':
        $field_selector = 'field_first_publish_date';
        break;
    }
    $date_text = $this->previousNode()->get($field_selector)->value;
    $time_stamp = strtotime($date_text);

    // $formatted_date = format_date($time_stamp, 'custom', 'D, d M Y H:i:s O');
    $dt = new DateTime();
    $dt->setTimestamp($time_stamp);
    $formatted_date = $dt->format('D, d M Y H:i:s');
    $zone = new DateTime();
    $zone->setTimezone(new DateTimeZone(drupal_get_user_timezone()));
    $formatted_zone = $zone->format('O');
    $formatted_date = $formatted_date . ' ' . $formatted_zone;
    $date_text = '/<pubDate>' . str_replace('+', '\+', $formatted_date) . '<\/pubDate>/im';
    $page_content = $page->getText();

    $pattern_title = '/' . $title . '/im';
    $pattern_date = $date_text;

    $matched_title = FALSE;
    $matched_date = FALSE;

    preg_match_all($pattern_title, $page_content, $matched_title);
    preg_match_all($pattern_date, $page_content, $matched_date);
    $nodes = $page->findAll('xpath', '//html[text()="' . $title . '"]');

    if (!$matched_title[0]) {
      throw new Exception("Node with title ('$title') not found on rss feed!");
    }

    if (!$matched_date[0]) {
      throw new Exception("Date ('$formatted_date') not found on rss feed!");
    }
  }

}
