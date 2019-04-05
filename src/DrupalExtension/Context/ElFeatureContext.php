<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Brainsum\DrupalBehatTesting\Helper\ElPageResolverTrait;
use Brainsum\DrupalBehatTesting\Helper\PreviousNodeTrait;
use Drupal;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use RuntimeException;

/**
 * Defines application features from the specific context.
 */
class ElFeatureContext extends TietoContext {

  use ElPageResolverTrait;
  use PreviousNodeTrait;

  /**
   * Node creation.
   *
   * @Given I create a node with title :title of type :type and save it as :status
   */
  public function iCreateANodeWithTitleOftypeAndSaveItAs($title, $type, $status): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = Drupal::entityTypeManager();
    /** @var \Drupal\taxonomy\TermStorageInterface $termStorage */
    $termStorage = $entityTypeManager->getStorage('taxonomy_term');
    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $entityTypeManager->getStorage('node');

    $term = $termStorage->loadByProperties(['name' => 'automated test']);
    if ($term) {
      $term = reset($term);
    }
    else {
      $term = $termStorage->create([
        'vid' => 'keyword',
        'langcode' => 'en',
        'name' => 'automated test',
      ]);
      $term->save();
    }
    $termId = $term->id();

    $session = $this->getSession();
    $page = $session->getPage();

    switch ($status) {
      case 'published':
        $state = 1;
        break;

      case 'draft':
        $state = 0;
        break;

      case 'publish_old':
        $state = 1;
        break;

      default:
        $state = 0;
    }

    $date = DrupalDateTime::createFromTimestamp(time())
      ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $nodeData = [
      'type' => $type,
      'title' => $title,
      'body' => 'Behat automated test body',
      'field_ingress' => 'Behat automated test content',
      'status' => $state,
      'field_keyword' => $termId,
      'field_url_link' => 'internal:/',
    ];

    if ($type === 'service_alert') {
      $nodeData['field_affected_users'] = 'admin';
      $nodeData['field_start_time'] = $date;
    }

    /** @var \Drupal\node\NodeInterface $node */
    $node = $nodeStorage->create($nodeData);

    if ($status === 'published_old') {
      $created = $node->getCreatedTime();
      $created = $date = DrupalDateTime::createFromTimestamp($created)
        ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      $node->set('field_rss_date', $created);
    }

    $node->save();

    if ($node->id()) {
      $this->visitPath("/node/{$node->id()}/edit");

      $statusButton = NULL;

      switch ($status) {
        case 'published':
          $statusButton = $page->find('xpath', "//input[@value='Publish']");
          break;

        case 'draft':
          $statusButton = $page->find('xpath', "//input[@value='Save']");
          break;

        case 'published_old':
          $statusButton = $page->find('xpath', "//input[@value='Publish']");
          break;
      }

      if ($statusButton === NULL) {
        throw new RuntimeException("Status button for the '$status' state was not found.");
      }

      $statusButton->click();

      $this->setPreviousNode($node->id());
      $this->visitPath("/node/{$node->id()}/edit");
    }
    else {
      throw new RuntimeException('Node creation failed!');
    }
  }

  /**
   * Then I :action previously created node.
   *
   * @Then I :action previously created node
   */
  public function iPreviouslyCreatedNode(string $action): void {
    $fragment = $action === 'view' ? '' : '/edit';
    $this->visitPath("/node/{$this->previousNode()->id()}{$fragment}");
  }

  /**
   * Then I should see the text :title and :date.
   *
   * @Then I should see the text :title and :date
   */
  public function iShouldSeeTheTextAnd($title, $date) {
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
