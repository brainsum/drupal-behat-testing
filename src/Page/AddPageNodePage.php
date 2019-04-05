<?php

namespace Brainsum\DrupalBehatTesting\Page;

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Page object for the "/node/add/page" page.
 *
 * @package DockerBehat\Custom\Page
 */
class AddPageNodePage extends Page {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    Session $session,
    Factory $factory,
    array $parameters = []
  ) {
    parent::__construct($session, $factory, $parameters);

    $this->path = '/node/add/page';
  }

}
