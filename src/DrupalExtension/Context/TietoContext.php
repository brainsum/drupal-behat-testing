<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use DateInterval;
use Drupal;
use Drupal\Core\Datetime\DrupalDateTime;
use PHPUnit\Framework\Assert;
use RuntimeException;

/**
 * Class TietoContext.
 *
 * @package Brainsum\DrupalBehatTesting\DrupalExtension\Context
 */
class TietoContext extends GenericContext {

  /**
   * A date formatted with the tieto date format.
   *
   * @var string
   */
  public $tietoDate;

  /**
   * When I click on the Edit this page link.
   *
   * @When I click on the Edit this page link
   */
  public function iClickOnTheEditThisPageLink(): void {
    $button = $this->getSession()
      ->getPage()
      ->find('css', '.edit-mode');

    if ($button === NULL) {
      throw new RuntimeException("The 'Edit mode' link was not found.");
    }

    $button->click();
  }

  /**
   * When I click the Submit button.
   *
   * @When I click the Submit button
   */
  public function iClickTheSubmitButton(): void {
    // *[@id="edit-submit"] XPath.
    $button = $this->getSession()
      ->getPage()
      ->findById('edit-submit');

    if ($button === NULL) {
      throw new RuntimeException("The 'Submit' button was not found.");
    }

    $button->click();
  }

  /**
   * When I click on the ":itemName" menu item.
   *
   * @When I click on the ":itemName" menu item
   */
  public function iClickOnTheMenuItem(string $itemName): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $element = $this->getSession()
      ->getPage()
      ->find('css', '#block-tieto-admin-user-menu > ul > li.menu-item.menu-item--expanded > ul > li > a:contains("' . $itemName . '")');
    $element->click();
  }

  /**
   * When I click on the home button.
   *
   * @When I click on the home button
   */
  public function iClickOnTheHomeButton(): void {
    $link = $this->getSession()
      ->getPage()
      ->findLink('Home');

    if ($link === NULL) {
      throw new RuntimeException("The 'Home' link was not found.");
    }

    $link->click();
  }

  /**
   * When I hover over the ":tabName" navigation tab.
   *
   * @When I hover over the ":tabName" navigation tab
   */
  public function iHoverOverTheNavigationTab(string $tabName): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $element = $this->getSession()
      ->getPage()
      ->find('css', ".region-header nav > ul > li.menu-item.menu-item--expanded > :contains('" . $tabName . "')");

    if (NULL === $element) {
      throw new RuntimeException("The '$tabName' navigation tab was not found.");
    }

    $element->mouseOver();
  }

  /**
   * Then I should see the ":tabName" navigation tab.
   *
   * @Then I should see the ":tabName" navigation tab
   */
  public function iShouldSeeTheNavigationTab(string $tabName): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $selector = ".region-header nav > ul > li.menu-item.menu-item--expanded > :contains('" . $tabName . "')";
    $element = $this->getSession()
      ->getPage()
      ->find('css', $selector);

    if (!$element) {
      Assert::fail("The '$tabName' navigation tab was not found.");
    }

    if (!$element->isVisible()) {
      Assert::fail("The '$tabName' navigation tab is not visible.");
    }
  }

  /**
   * Then I should not see the ":tabName" navigation tab.
   *
   * @Then I should not see the ":tabName" navigation tab
   */
  public function iShouldNotSeeTheNameNavigationTab(string $tabName): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $selector = ".region-header nav > ul > li.menu-item.menu-item--expanded > :contains('" . $tabName . "')";
    $element = $this->getSession()
      ->getPage()
      ->find('css', $selector);

    if (!$element) {
      Assert::fail("The '$tabName' navigation tab was not found.");
    }

    if ($element->isVisible()) {
      Assert::fail("The '$tabName' navigation tab is displayed.");
    }
  }

  /**
   * Then I should see the ":itemName" menu item.
   *
   * @Then I should see the ":itemName" menu item
   */
  public function iShouldSeeTheMenuItem(string $itemName): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $selector = ".region-header nav > ul > li.menu-item.menu-item--expanded > ul > li > :contains('" . $itemName . "')";
    $element = $this->getSession()
      ->getPage()
      ->find('css', $selector);

    if (!$element) {
      Assert::fail("The '$itemName' navigation tab was not found.");
    }

    if (!$element->isVisible()) {
      Assert::fail("The '$itemName' navigation tab is not displayed.");
    }
  }

  /**
   * Then I should not see the ":itemName" menu item.
   *
   * @Then I should not see the ":itemName" menu item
   */
  public function iShouldNotSeeTheMenuItem(string $itemName): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $selector = ".region-header nav > ul > li.menu-item.menu-item--expanded > ul > li > :contains('" . $itemName . "')";
    $element = $this->getSession()
      ->getPage()
      ->find('css', $selector);

    if (!$element) {
      Assert::fail("The '$itemName' navigation tab was not found.");
    }

    if ($element->isVisible()) {
      Assert::fail("The '$itemName' navigation tab is displayed.");
    }
  }

  /**
   * Return selector for field.
   *
   * @param string $fieldName
   *   The field name.
   *
   * @return string
   *   The selector.
   *
   * @throws \RuntimeException
   */
  protected function selectorForField(string $fieldName): string {
    switch ($fieldName) {
      case 'scheduled publish date':
        return '#edit-scheduled-publish-date-wrapper .form-item-scheduled-publish-date-form-inline-entity-form-update-timestamp-0-value-';

      case 'scheduled unpublish date':
        return '#edit-scheduled-unpublish-date-wrapper .form-item-scheduled-unpublish-date-form-inline-entity-form-update-timestamp-0-value-';

      case 'scheduled trash date':
        return '#edit-scheduled-trash-date-wrapper .form-item-scheduled-trash-date-form-inline-entity-form-update-timestamp-0-value-';
    }

    throw new RuntimeException("Selector for field '$fieldName' was not found.");
  }

  /**
   * Offsets a timestamp.
   *
   * @param int $timestamp
   *   The timestamp.
   * @param string $offset
   *   The offset string, e.g "+1 month".
   *
   * @return int
   *   The timestamp with the offset added.
   */
  public function offsetTimestamp(int $timestamp, string $offset): int {
    return DrupalDateTime::createFromTimestamp($timestamp)
      ->add(DateInterval::createFromDateString($offset))
      ->getTimestamp();
  }

  /**
   * Then I set a :field to plus one day.
   *
   * @Then I set a :field to plus one day
   */
  public function iSetFieldToPlusOneDay(string $field): void {
    $now = Drupal::time()->getCurrentTime();
    $dateTime = DrupalDateTime::createFromTimestamp(
      $this->offsetTimestamp($now, '+1 day'),
      drupal_get_user_timezone()
    );

    $time = $dateTime->getTimestamp();

    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = Drupal::service('date.formatter');

    $fieldSelector = $this->selectorForField($field);
    $page = $this->getSession()->getPage();

    $dateField = $page->find('css', $fieldSelector . 'date input');
    $dateField->setValue($dateFormatter->format($time, 'custom', 'd M Y'));

    $hourField = $page->find('css', $fieldSelector . 'time input');
    $hourField->setValue($dateFormatter->format($time, 'custom', 'H:i'));

    $this->tietoDate = $dateFormatter->format($time, 'custom', 'd M Y - G:i T');
  }

  /**
   * Then I set the Authored date to the past.
   *
   * @Then I set the Authored date to the past
   *
   * @throws RuntimeException
   */
  public function iSetAuthoredOnFieldToMinusOneDay(): void {
    $page = $this->getSession()->getPage();

    $button = $page->find('css', '.node-form-author summary');

    if ($button === NULL) {
      throw new RuntimeException("The field 'Authored date' was not found.");
    }

    $button->click();

    $now = Drupal::time()->getCurrentTime();
    $dateTime = DrupalDateTime::createFromTimestamp(
      $this->offsetTimestamp($now, '-1 day'),
      drupal_get_user_timezone()
    );

    $time = $dateTime->getTimestamp();

    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = Drupal::service('date.formatter');

    $dateField = $page->find('css', '.form-item-created-0-value-date input');
    $dateField->setValue($dateFormatter->format($time, 'custom', 'd M Y'));

    $hourField = $page->find('css', '.form-item-created-0-value-time input');
    $hourField->setValue($dateFormatter->format($time, 'custom', 'H:i'));
  }

  // @todo: fix these:
  // ------------------

  /**
   * @Given I click the :arg1 element
   */
  public function iClickTheElement($selector): void {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $selector);

    if (empty($element)) {
      throw new RuntimeException("No html element found for the selector ('$selector')");
    }

    $element->click();
  }

  /**
   * @Then I fill in wysiwyg on field :locator with :value
   *
   * @see: https://gist.github.com/johnennewdeeson/240e2b60c23ea3217887
   */
  public function iFillInWysiwygOnFieldWith($locator, $value): void {
    /** @var \Behat\Mink\Element\NodeElement $element */
    $element = $this->getSession()->getPage()->findField($locator);

    if (NULL === $element) {
      throw new RuntimeException('Could not find WYSIWYG with locator: ' . $locator, $this->getSession());
    }

    $fieldId = $element->getAttribute('id');

    if (empty($fieldId)) {
      throw new RuntimeException('Could not find an id for field with locator: ' . $locator);
    }

    $this->getSession()
      ->executeScript("CKEDITOR.instances[\"$fieldId\"].setData(\"$value\");");
  }

  /**
   * Then I fill in select2 input ":field" with ":value" and select ":entry".
   *
   * @Then I fill in select2 input ":field" with ":value" and select ":entry"
   *
   * @todo: Refactor according to community context
   * @see: https://github.com/novaway/BehatCommonContext
   */
  public function iFillInSelect2InputWithAndSelect($field, $value, $entry): void {
    $session = $this->getSession();
    $page = $session->getPage();

    $inputField = $page->findField($field);
    if (NULL === $inputField) {
      throw new RuntimeException('No field found');
    }

    $fieldParent = $inputField->getParent();
    $addButton = $fieldParent->find('css', 'button[class="add button"]');
    $addButton->press();

    $select2Input = $fieldParent->find('css', '.select2-input');
    if (!$select2Input) {
      throw new RuntimeException('No input found');
    }
    $select2Input->setValue($value);

    $session->wait(1000);

    $chosenResults = $page->findAll('css', '.select2-results li');
    /** @var \Behat\Mink\Element\NodeElement $result */
    foreach ($chosenResults as $result) {
      if ($result->getText() === $entry) {
        $result->click();
        break;
      }
    }
  }

  /**
   * @Then I should see the scheduled date notice under the :action button
   */
  public function iShouldSeeTheScheduledDateNotificationUnderTheButton($action): void {
    $action = strtolower($action);
    $session = $this->getSession();
    $page = $session->getPage();

    switch ($action) {
      case 'archive':
        $parent_class = 'trash';
        break;

      case 'publish':
        $parent_class = 'unpublished';
        break;

      case 'unpublish':
        $parent_class = 'unpublished-content';
        break;

      default:
        $parent_class = FALSE;
    }

    $parent_selector = '.form-action-moderation-state-' . $parent_class . '-button-wrapper';
    $selector = $parent_selector . ' .moderation-state-' . $parent_class . '-action-description';
    $text_wrapper = $page->find('css', $selector);
    if (!$text_wrapper) {
      throw new RuntimeException("The scheduled dates wrapper was not found under button ('$action')");
    }
    $text = $text_wrapper->getText();
    $search_text = 'Scheduled ' . $action . ' date: ' . $this->tietoDate;
    if ($text !== $search_text) {
      throw new RuntimeException("Date not found or incorrect under the '{$action}' button date: '{$this->tietoDate}'");
    }
  }

}
