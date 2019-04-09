<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use RuntimeException;
use ZipArchive;
use function reset;
use function strlen;
use function strtolower;
use function sleep;

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

  /**
   * Selects a style for cke and then inputs a test text into it.
   *
   * Only supports what is defined in the switch.
   *
   * Example usage: Then I press the "Heading 1" button and add the text "this
   * should be Heading 1" in content area.
   *
   * @Then I press the :type button and add the text :text in content area
   *
   * @todo: Refactor.
   */
  public function iPressTheButtonAndAddTheTextInContentArea($type, $text): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $type = strtolower($type);
    switch ($type) {
      case 'bold':
        $button_selector = '.cke_button__bold';
        $selector = 'strong';
        $style = 'decoration';
        break;

      case 'italic':
        $button_selector = '.cke_button__italic';
        $selector = 'em';
        $style = 'decoration';
        break;

      case 'strike':
        $button_selector = '.cke_button__strike';
        $selector = 's';
        $style = 'decoration';
        break;

      case 'underline':
        $button_selector = '.cke_button__underline';
        $selector = 'u';
        $style = 'decoration';
        break;

      case 'heading 1':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Heading 1"]';
        $style = 'heading';
        $selector = 'h1';
        break;

      case 'heading 2':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Heading 2"]';
        $style = 'heading';
        $selector = 'h2';
        break;

      case 'heading 3':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Heading 3"]';
        $style = 'heading';
        $selector = 'h3';
        break;

      case 'heading 4':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Heading 4"]';
        $style = 'heading';
        $selector = 'h4';
        break;

      case 'introduction text':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Introduction text"]';
        $style = 'heading';
        $selector = 'div';
        break;

      case 'blue dark':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Blue (dark)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'blue':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Blue"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'blue light':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Blue (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'pink':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Pink"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'pink light':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Pink (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'green':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Green"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'green light':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Green (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'orange':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Orange"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'orange light':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Orange (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey lightest':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Grey (lightest)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey light':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Grey (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey medium':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Grey (medium)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey dark':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Grey (dark)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey darkest':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="Grey (darkest)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'white':
        $button_selector = '.cke_combo_button';
        $style_selector = 'a[title="White"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'bulleted list':
        $button_selector = '.cke_button__bulletedlist';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'numbered list':
        $button_selector = '.cke_button__numberedlist';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'outdented text':
        $button_selector = '.cke_button__outdent';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'indented text':
        $button_selector = '.cke_button__indent';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'horizontal line':
        $button_selector = '.cke_button__horizontalrule';
        $selector = 'hr';
        $style = 'decoration';
        break;

      case 'block quote':
        $button_selector = '.cke_button__blockquote';
        $selector = 'blockquote';
        $style = 'decoration';
        break;
    }

    $button = $page->find('css', $button_selector);
    if (!$button) {
      throw new RuntimeException("$button_selector not found");
    }

    $button->click();

    if ($style === 'heading') {
      $frame_id = $page->find('css', '.cke_combopanel .cke_panel_frame')
        ->getAttribute('id');

      if (!$frame_id) {
        throw new RuntimeException('Styles select not found');
      }

      $session->switchToIFrame($frame_id);
      $page = $session->getPage();
      $style_to_select = $page->find('css', $style_selector);

      if (!$style_selector) {
        throw new RuntimeException("Style selector: $type was not found");
      }
      $style_to_select->click();
      $session->switchToIFrame(NULL);
      $page = $session->getPage();
    }

    $session->executeScript('jQuery(".cke_wysiwyg_frame").attr("id","test-cke-iframe");');
    $session->switchToIFrame('test-cke-iframe');

    $frame_session = $this->getSession();
    $frame_page = $frame_session->getPage();

    $frame_session->executeScript("
      var body = document.getElementsByTagName('BODY');
      document.body.getElementsByTagName('$selector')[0].innerHTML = '$text';
      var e = document.createElement('p');
      document.body.appendChild(e);
      document.body.getElementsByTagName('p')[document.body.getElementsByTagName('p').length - 1].focus();
    ");

    $check = $frame_page->find('css', $selector . ':contains("' . $text . '")');
    if (!$check) {
      throw new RuntimeException('Element not found');
    }
    $session->switchToIFrame(NULL);
    $session = $this->getSession();

    $session->executeScript("jQuery('.cke_button_on').click();");
  }

  /**
   * Uploads a predefined image and adds alt text.
   *
   * Example usage: Then I open the "image" upload form and fill it in.
   *
   * @Then I open the :imageFieldName upload form and fill it in
   */
  public function iOpenTheUploadFormAndFillItIn($imageFieldName): void {
    // @todo: Make configurable.
    $imagePath = '/var/www/html/tests/behat/features/images/tieto-office-building.jpg';
    $time = 5000;
    $session = $this->getSession();
    $page = $session->getPage();
    switch ($imageFieldName) {
      case 'image':
        $testSelector = '.cke_widget_image';
        $button = '.cke_button__drupalimage';
        $form = [
          [
            'upload_field' => 'input[name="files[fid]"]',
            'alt_field' => 'input[name="attributes[alt]"]',
            'image_url' => $imagePath,
            'alt_text' => 'Test alt field',
          ],
        ];
        break;

      case 'double image':
        $testSelector = '.sbs-full-image';
        $button = '.cke_button__doubleimage';
        $form = [
          [
            'upload_field' => 'input[name="files[fid_left]"]',
            'alt_text' => 'Test alt left',
            'image_url' => $imagePath,
            'alt_field' => 'input[name="attributes_left[alt]"]',
          ],
          [
            'upload_field' => 'input[name="files[fid_right]"]',
            'alt_text' => 'Test alt right',
            'image_url' => $imagePath,
            'alt_field' => 'input[name="attributes_right[alt]"]',
          ],
        ];
        break;
    }
    $form_button = $page->find('css', $button);
    $form_button->click();
    // Wait for ajax to finish.
    $this->getSession()->wait($time, '(0 === jQuery.active)');
    foreach ($form as $key => $data) {
      $localFile = $data['image_url'];
      $tempZip[$key] = tempnam('', 'WebDriverZip');
      $zip = new ZipArchive();
      $zip->open($tempZip[$key], ZipArchive::CREATE);
      $zip->addFile($localFile, basename($localFile));
      $zip->close();

      $remotePath = $this->getSession()
        ->getDriver()
        ->getWebDriverSession()
        ->file([
          'file' => base64_encode(file_get_contents($tempZip[$key])),
        ]);
      $input = $page->find('css', $data['upload_field']);
      $input->attachFile($remotePath);

      $this->getSession()->wait($time, '(0 === jQuery.active)');

      unlink($tempZip[$key]);

      $alt_field = $page->find('css', $data['alt_field']);
      $alt_field->setValue($data['alt_text']);

      // Wait for ajax to finish.
      $this->getSession()->wait($time, '(0 === jQuery.active)');
      // @todo Would be better not to rely on explicit waits. Find a better solution to avoid waiting.
      sleep(2);
    }
    $save_button = $page->find('css', '.ui-widget-content .ui-dialog-buttonset .form-submit');
    $save_button->click();

    // Wait for ajax to finish.
    $this->getSession()->wait($time, '(0 === jQuery.active)');

    $session->executeScript('jQuery(".cke_wysiwyg_frame").attr("id","test-cke-iframe");');
    $session->switchToIFrame('test-cke-iframe');
    $session = $this->getSession();
    $page = $session->getPage();
    $test = $page->find('css', $testSelector);

    if (!$test) {
      throw new RuntimeException('Image did not add correctly');
    }

    $session->switchToIFrame(NULL);

  }

}
