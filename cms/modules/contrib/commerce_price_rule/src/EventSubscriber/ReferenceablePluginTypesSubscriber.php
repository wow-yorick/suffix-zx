<?php

namespace Drupal\commerce_price_rule\EventSubscriber;

use Drupal\commerce\Event\CommerceEvents;
use Drupal\commerce\Event\ReferenceablePluginTypesEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Registers the commerce_price_calculation plugin type.
 */
class ReferenceablePluginTypesSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      CommerceEvents::REFERENCEABLE_PLUGIN_TYPES => ['registerTypes', -100],
    ];
    return $events;
  }

  /**
   * Registers the plugin types.
   *
   * @param \Drupal\commerce\Event\ReferenceablePluginTypesEvent $event
   *   The plugin types event.
   */
  public function registerTypes(ReferenceablePluginTypesEvent $event) {
    $plugin_types = $event->getPluginTypes();
    $plugin_types['commerce_price_rule_calculation'] = $this->t('Price rule calculation');
    $event->setPluginTypes($plugin_types);
  }

}
