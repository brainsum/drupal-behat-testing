<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use RuntimeException;
use function reset;
use function strlen;

/**
 * Defines application features from the specific context.
 */
class TspFeatureContext extends TietoContext {

  /**
   * Then I edit a node under :parent corner with keyword :keyword.
   *
   * @Then I edit a node under :parent corner with keyword :keyword
   */
  public function iEditANodeUnderParentWithKeyword($parent, $keyword): void {

    if (strlen($parent) > 1) {
      $query = Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('title', $parent);

      $nids = $query->execute();

      if (empty($nids)) {
        throw new RuntimeException("The given parent was not found.. ('$parent')");
      }

      $corner_id = reset($nids);
      $book = [
        'bid' => Drupal::config('tsp.book_settings')->get('default_book_id'),
        'pid' => $corner_id,

      ];
    }
    $term = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $keyword]);
    if ($term) {
      $term = reset($term);
      $term_id = $term->get('tid')->value;
    }
    else {
      $term = Term::create([
        'vid' => 'keyword',
        'langcode' => 'en',
        'name' => $keyword,
      ]);
      $inserted = $term->save();
      $term_id = $term->id();
    }

    $nodeValues = [
      'type' => 'page',
      'title' => 'Behat: Test ' . $parent,
      'body' => 'test body ' . $parent,
      'field_description' => 'Automated test for ' . $parent,
      'status' => 1,
      'field_keyword' => $term_id,
    ];

    if (!empty($book)) {
      $nodeValues['book'] = $book;
    }

    $node = Node::create($nodeValues);
    $node->save();

    if (!$node->nid) {
      throw new RuntimeException('Node creation failed!');
    }

    $this->visitPath('/node/' . $node->get('nid')->value . '/edit');
  }

  /**
   * Selects and expands book navigation entry.
   *
   * Example usage: Then I expand "Training".
   *
   * @Then I expand :arg1
   */
  public function iExpand($arg1): void {
    $session = $this->getSession();
    $page = $session->getPage();

    $element = $page->find('css', 'span:contains("' . $arg1 . '")');

    if (!$element) {
      throw new RuntimeException("Element containing: $arg1 not found");
    }

    $extender = $element->getParent()->find('css', '.book-list-item-expander');

    if (!$extender) {
      throw new RuntimeException("Drop down arrow not found for $arg1");
    }

    $extender->click();
  }

  /**
   * Checks the checkbox in the book navigation.
   *
   * Example usage: Then I check the "A nice title"
   *
   * @Then I check the :arg1
   */
  public function iCheckThe($arg1): void {
    $session = $this->getSession();
    $page = $session->getPage();

    $element = $page->find('css', 'span:contains("' . $arg1 . '")');

    if (!$element) {
      throw new RuntimeException("Element containing: $arg1 not found");
    }

    $extender = $element->getParent()->find('css', '.book-list-item-checkbox');

    if (!$extender) {
      throw new RuntimeException("Select for: $arg1 not found");
    }

    $extender->click();
  }

  /**
   * Enter value into an input and select it from the autocomplete dropdown.
   *
   * Types given value into an input field appearing on a modal window and
   * simulates that an item being selected from the autocomplete result list.
   *
   * Example usage: I enter "A nice title" for ".article-title" on modal popup
   * and select from autocomplete.
   *
   * @Then I enter :value for :field on modal popup and select from autocomplete
   */
  public function iEnterValueForFieldOnModalPopupAndSelectFromAutocomplete($value, $field): void {
    $session = $this->getSession();
    $driver = $session->getDriver();
    $page = $session->getPage();

    $element = $page->find('css', 'input[name="' . $field . '"]');
    if (!isset($element)) {
      throw new RuntimeException("Element not found with the given CSS selector: $field");
    }

    $element->focus();
    $element->setValue($value);

    // Wait for ajax to finish.
    $this->getSession()->wait(1000, '(0 === jQuery.active)');

    // Now some JS-hack is necessary to be able to use
    // the result list of autocomplete feature:
    // First, we imitate any search result appearing by copying over from
    // the contrib module's code:
    // renderItem() function in its js/autocomplete.js file:
    $driver->executeScript("
      \$ = jQuery;
      var \$line = \$('<li>').addClass('linkit-result');
      \$line.addClass('ui-menu-item');
      \$line.append(\$('<span>').html('Test title').addClass('linkit-result--title'));
      \$line.append(\$('<span>').html('Test description').addClass('linkit-result--description'));
      \$line.appendTo(document.querySelector('#drupal-modal > ul'));
    ");

    // Second, for this search result item
    // we set a valid URL path existing in Drupal:
    $driver->executeScript("document.querySelector('#drupal-modal > ul > li:nth-child(1)').jQuery321055708775364045392 = {uiAutocompleteItem: {path: '/filter/tips'}};");
    // This supposed not to work, because jQuery uses a hash
    // (eg. 321055708775364045392) in the property name,
    // which changes on every page load.
    // Third, we make the parent element visible:
    $driver->executeScript("
      var obj = document.querySelector('#drupal-modal > ul');
      var attr = obj.getAttribute('style');
      attr = obj.getAttribute('style').replace(/display: none;/gi, 'display: block;');
      obj.setAttribute('style', attr);
    ");
    // It's simpler and does the same:
    // @todo: $driver->executeScript("document.querySelector('#drupal-modal > ul').style.display = 'block';");
    // @see: https://stackoverflow.com/questions/23050885/how-to-change-the-style-of-an-element-using-selenium
  }

}
