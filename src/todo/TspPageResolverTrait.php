<?php

namespace Brainsum\DrupalBehatTesting\Helper;

use Behat\Behat\Tester\Exception\PendingException;

/**
 * Trait PageResolverTrait.
 *
 * @package DockerBehat\Custom
 */
trait TspPageResolverTrait {

  /**
   * Map.
   *
   * @var array
   *   Map of page names to paths.
   */
  static private $pageNamePathMap = [
    'home'                                    => '/home',
    'page_add'                                => '/node/add/page',
    'tieto_error_pages.404'                   => '/error-404',
    'entity.landing_page.collection'          => '/admin/structure/tsp_landing_page',
    'entity.landing_page.edit_form'           => '/admin/structure/tsp_landing_page/{landing_page}/edit',
    'entity.landing_page.delete_form'         => '/admin/structure/tsp_landing_page/{landing_page}/delete',
    'tsp_landing_page.landing_page_add'       => '/admin/structure/tsp_landing_page/add',
    'tsp_landing_page.landing_page_settings'  => '/admin/structure/tsp_landing_page_settings',
  ];

  /**
   * Return a path to a page name.
   *
   * @param string $pageName
   *   The machine name of a page.
   *
   * @return string
   *   The URL path alias of the given page.
   */
  protected function pageNameToPath($pageName): string {
    if (isset(static::$pageNamePathMap[$pageName])) {
      return static::$pageNamePathMap[$pageName];
    }

    throw new PendingException('Todo: iAmOnTheHomePage got an invalid argument.');
  }

}
