<?php

/**
 * @file
 * Contains \Drupal\message_thread\Routing.
 */

namespace Drupal\message_thread\Routing;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\views\Views;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;



/**
 * Defines dynamic routes.
 */
class MessageThreadRoutes implements ContainerInjectionInterface {

  /**
   * The template storage manager.
   */
  protected $templateStorage;

  protected $entityTypeManager;

  /**
   * Constructs the message thread template  form.
   *
   * @param \Drupal\message\MessagePurgePluginManager $purge_manager
   *   The message purge plugin manager service.
   */
  public function __construct(EntityTypeManager $entity_type_manager, $template_storage) {
    $this->templateStorage = $template_storage;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.manager')->getStorage('message_template')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {

    $route_collection = new RouteCollection();
    // Create a route for each template
    $thread_templates = $this->entityTypeManager->getListBuilder('message_thread_template')->load();

    foreach ($thread_templates as $name => $template) {
      $settings = $template->getSettings();

      $view = Views::getView($settings['thread_view_id']);
      $view->setDisplay($settings['thread_view_display_id']);
      $url = $view->getUrl()->toString();

      // This is not going to work if the View is not placed in the User page
      $url = str_replace('%2A', '{user}', $url);
      $route = new Route(
        $url,
        [
          '_controller' => '\Drupal\message_thread\Controller\MessageThreadController::inBox',
          '_title' => $template->label(),
        ],
        [
          '_permission' => 'access content'
        ]
      );
      $route_collection->add('message_thread.' . $name, $route);
    }

    $route = (new Route('/message-thread/{message_thread}'))
      ->setDefaults([
        '_entity_view' => 'message_thread.full',
        '_title_callback' => 'Drupal\message_thread\Controller\MessageThreadController::messageThreadTitle',
      ])
      ->setRequirement('message_thread', '\d+')
      ->setRequirement('_entity_access', 'message_thread.view');
    $route_collection->add('entity.message_thread.canonical', $route);

    return $route_collection;

  }

}