<?php
/**
 * @file
 * Contains \Drupal\message_thread\Plugin\Derivative\DynamicLocalTasks.
 */

namespace Drupal\message_thread\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;
use Drupal\Core\Routing\RouteProviderInterface;


/**
 * Defines dynamic local tasks.
 */
class DynamicLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The template storage manager.
   */
  protected $templateStorage;

  protected $entityTypeManager;

  protected $routeProvider;

  /**
   * Constructs the message thread template  form.
   *
   * @param \Drupal\message\MessagePurgePluginManager $purge_manager
   *   The message purge plugin manager service.
   */
  public function __construct($base_plugin_id, EntityStorageInterface $template_storage, EntityTypeManager $entity_type_manager, RouteProviderInterface $route_provider) {
    $this->templateStorage = $template_storage;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager')->getStorage('message_template'),
      $container->get('entity_type.manager'),
      $container->get('router.route_provider')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

//    $this->derivatives['message_thread.edit'] = $base_plugin_definition;
//    $this->derivatives['message_thread.edit']['title'] = t('Edit');
//    $this->derivatives['message_thread.edit']['route_name'] = 'entity.message_thread.edit_form';
//    $this->derivatives['message_thread.edit']['base_route'] = 'entity.message_thread.canonical';
//    $this->derivatives['message_thread.edit']['weight'] = 0;
//
    // Create tabs for each message thread type
    $thread_templates = $this->entityTypeManager->getListBuilder('message_thread_template')->load();
    foreach ($thread_templates as $name => $template) {
      $settings = $template->getSettings();

      // Thread page tabs
      $view_route = 'view.' . $settings['thread_view_id'] . '.' . $settings['thread_view_display_id'];
      $exists = count($this->routeProvider->getRoutesByNames([$view_route])) === 1;
      if (!$exists) {
        continue;
      }
      // User page tab
      $view = Views::getView($settings['thread_view_id']);
      $view->setDisplay($settings['thread_view_display_id']);
      if (!$view->hasUrl()) {
        continue;
      }
      $this->derivatives['message_thread.' . $name . '.user'] = $base_plugin_definition;
      $this->derivatives['message_thread.' . $name . '.user']['title'] = $template->label();
      $this->derivatives['message_thread.' . $name . '.user']['route_name'] = 'message_thread.' . $name;
      $this->derivatives['message_thread.' . $name . '.user']['base_route'] = 'entity.user.canonical';
      $this->derivatives['message_thread.' . $name . '.user']['weight'] = 100;

//      $this->derivatives['message_thread.' . $name . '.message_thread'] = $base_plugin_definition;
//      $this->derivatives['message_thread.' . $name . '.message_thread']['title'] = $template->label();
//      $this->derivatives['message_thread.' . $name . '.message_thread']['route_name'] = 'message_thread.' . $name;
//      $this->derivatives['message_thread.' . $name . '.message_thread']['route_parameters']['message_thread'] = '{message_thread}';
//      $this->derivatives['message_thread.' . $name . '.message_thread']['base_route'] = 'entity.message_thread.canonical';
//      $this->derivatives['message_thread.' . $name . '.message_thread']['weight'] = 100;



    }

    return $this->derivatives;
  }
}
