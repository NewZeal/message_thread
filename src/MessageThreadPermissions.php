<?php

namespace Drupal\message_thread;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains \Drupal\message_thread\MessageThreadPermissions.
 */
class MessageThreadPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a TaxonomyViewsIntegratorPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Get permissions for Message Thread .
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];

    foreach ($this->entityManager->getStorage('message_thread_template')->loadMultiple() as $template) {
      $permissions += [
        'create and receive ' . $template->id() . ' message threads' => [
          'title' => $this->t('Able to participate in %thread threads', ['%thread' => $template->label()]),
        ],
        'view own ' . $template->id() . ' message thread tab' => [
          'title' => $this->t('View own %thread tab', [
            '%thread' => $template->label(),
          ]),
        ],
      ];
    }

    return $permissions;
  }

}
