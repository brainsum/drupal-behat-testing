<?php

namespace Brainsum\DrupalBehatTesting\DrupalExtension\Context;

use Brainsum\DrupalBehatTesting\Helper\PreviousNodeTrait;
use Drupal;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use RuntimeException;
use function reset;

/**
 * Defines application features from the specific context.
 */
class ElFeatureContext extends TietoContext {

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

    if (
      ($terms = $termStorage->loadByProperties(['name' => 'automated test']))
      && !empty($terms)
    ) {
      $term = reset($terms);
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

    $page = $this->getSession()->getPage();

    switch ($status) {
      case 'published':
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

    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $entityTypeManager->getStorage('node');

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

}
