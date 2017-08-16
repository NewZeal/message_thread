<?php

namespace Drupal\message_thread;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the comment entity.
 *
 * @see \Drupal\comment\Entity\Comment.
 */
class MessageThreadAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   *
   * Link the activities to the permissions. checkAccess is called with the
   * $operation as defined in the routing.yml file.
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Return early if we have bypass or create any template permissions.
    if ($account->hasPermission('bypass message thread access control') || $account->hasPermission($operation . ' any message thread template')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    $params = [$entity, $operation, $account];
    ksm($params);
    /** @var \Drupal\Core\Access\AccessResult[] $results */
    $results = $this
      ->moduleHandler()
      ->invokeAll('message_thread_access_control', array($params));

    foreach ($results as $result) {
      if ($result->isNeutral()) {
        continue;
      }

      return $result;
    }

    return AccessResult::allowedIfHasPermission($account, $operation . ' ' . $entity->bundle() . ' message thread')->cachePerPermissions();
  }

  /**
   * {@inheritdoc}
   *
   * Separate from the checkAccess because the entity does not yet exist, it
   * will be created during the 'add' process.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Return early if we have bypass or create any template permissions.
    if ($account->hasPermission('bypass message thread access control') || $account->hasPermission('create any message template')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    /** @var \Drupal\Core\Access\AccessResult[] $results */
    $results = $this->moduleHandler()->invokeAll('message_message thread_create_access_control', [$entity_bundle,
      $account]);

    foreach ($results as $result) {
      if ($result->isNeutral()) {
        continue;
      }

      return $result;
    }

    // When we have a bundle, check access on that bundle.
    if ($entity_bundle) {
      return AccessResult::allowedIfHasPermission($account, 'create ' . $entity_bundle . ' message_thread')
        ->cachePerPermissions();
    }

    // With no bundle, e.g. on message thread/add, check access to any message thread bundle.
    // @todo: perhaps change this method to a service as in NodeAddAccessCheck.
    foreach (\Drupal::entityManager()->getStorage('message_thread_template')->loadMultiple() as $template) {
      $access = AccessResult::allowedIfHasPermission($account, 'create ' . $template->id() . ' message_thread');

      // If access is allowed to any of the existing bundles return allowed.
      if ($access->isAllowed()) {
        return $access->cachePerPermissions();
      }
    }

    return AccessResult::neutral()->cachePerPermissions();
  }

}
