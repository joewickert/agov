<?php

namespace Drupal\agov\EventSubscriber;

use Drupal\config_devel\Event\ConfigDevelSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Config devel event subscriber.
 */
class AgovConfigDevelSubscriber implements EventSubscriberInterface {

  /**
   * The doSave callback.
   *
   * In aGov we provide optional modules and we don't want their config getting
   * into our config objects during development, this handles that.
   */
  public function doSave(ConfigDevelSaveEvent $event) {
    $optional_modules = ['workbench_moderation'];
    $data = $event->getData();

    if (isset($data['third_party_settings'])) {
      foreach ($data['third_party_settings'] as $key => $value) {
        // If this is an optional module, then remove the config.
        if (in_array($key, $optional_modules)) {
          unset($data['third_party_settings'][$key]);

          // Now find the module in the dependencies list and remove that.
          foreach ($data['dependencies']['module'] as $i => $module) {
            if ($module === $key) {
              unset($data['dependencies']['module'][$i]);
            }
          }
        }
      }
    }
    $event->setData($data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['config_devel.save'][] = ['doSave'];
    return $events;
  }

}
