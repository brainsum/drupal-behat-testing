<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use RuntimeException;
use ZipArchive;
use function base64_encode;
use function basename;
use function file_get_contents;
use function sleep;
use function strtolower;
use function tempnam;
use function unlink;

/**
 * Class CkeditorContext.
 *
 * @package Brainsum\DrupalBehatTesting\DrupalExtension\Context
 */
class CkeditorContext extends RawDrupalContext {

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
        $buttonSelector = '.cke_button__bold';
        $selector = 'strong';
        $style = 'decoration';
        break;

      case 'italic':
        $buttonSelector = '.cke_button__italic';
        $selector = 'em';
        $style = 'decoration';
        break;

      case 'strike':
        $buttonSelector = '.cke_button__strike';
        $selector = 's';
        $style = 'decoration';
        break;

      case 'underline':
        $buttonSelector = '.cke_button__underline';
        $selector = 'u';
        $style = 'decoration';
        break;

      case 'heading 1':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Heading 1"]';
        $style = 'heading';
        $selector = 'h1';
        break;

      case 'heading 2':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Heading 2"]';
        $style = 'heading';
        $selector = 'h2';
        break;

      case 'heading 3':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Heading 3"]';
        $style = 'heading';
        $selector = 'h3';
        break;

      case 'heading 4':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Heading 4"]';
        $style = 'heading';
        $selector = 'h4';
        break;

      case 'introduction text':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Introduction text"]';
        $style = 'heading';
        $selector = 'div';
        break;

      case 'blue dark':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Blue (dark)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'blue':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Blue"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'blue light':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Blue (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'pink':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Pink"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'pink light':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Pink (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'green':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Green"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'green light':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Green (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'orange':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Orange"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'orange light':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Orange (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey lightest':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Grey (lightest)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey light':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Grey (light)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey medium':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Grey (medium)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey dark':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Grey (dark)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'grey darkest':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="Grey (darkest)"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'white':
        $buttonSelector = '.cke_combo_button';
        $styleSelector = 'a[title="White"]';
        $style = 'heading';
        $selector = 'span';
        break;

      case 'bulleted list':
        $buttonSelector = '.cke_button__bulletedlist';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'numbered list':
        $buttonSelector = '.cke_button__numberedlist';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'outdented text':
        $buttonSelector = '.cke_button__outdent';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'indented text':
        $buttonSelector = '.cke_button__indent';
        $selector = 'li';
        $style = 'decoration';
        break;

      case 'horizontal line':
        $buttonSelector = '.cke_button__horizontalrule';
        $selector = 'hr';
        $style = 'decoration';
        break;

      case 'block quote':
        $buttonSelector = '.cke_button__blockquote';
        $selector = 'blockquote';
        $style = 'decoration';
        break;
    }

    $button = $page->find('css', $buttonSelector);

    if (!$button) {
      throw new RuntimeException("$buttonSelector not found");
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
      $styleToSelect = $page->find('css', $styleSelector);

      if (!$styleSelector) {
        throw new RuntimeException("Style selector: $type was not found");
      }
      $styleToSelect->click();
      $session->switchToIFrame(NULL);
      $page = $session->getPage();
    }

    $session->executeScript('jQuery(".cke_wysiwyg_frame").attr("id","test-cke-iframe");');
    $session->switchToIFrame('test-cke-iframe');

    $frameSession = $this->getSession();
    $framePage = $frameSession->getPage();

    $frameSession->executeScript("
      var body = document.getElementsByTagName('BODY');
      document.body.getElementsByTagName('$selector')[0].innerHTML = '$text';
      var e = document.createElement('p');
      document.body.appendChild(e);
      document.body.getElementsByTagName('p')[document.body.getElementsByTagName('p').length - 1].focus();
    ");

    $check = $framePage->find('css', $selector . ':contains("' . $text . '")');

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
