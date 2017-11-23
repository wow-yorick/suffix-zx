<?php

namespace Drupal\commerce_shipping;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Registers event subscribers for non-required modules.
 */
class CommerceShippingServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // We cannot use the module handler as the container is not yet compiled.
    // @see \Drupal\Core\DrupalKernel::compileContainer()
    $modules = $container->getParameter('container.modules');

    if (isset($modules['commerce_tax'])) {
      $container->register('commerce_shipping.customer_profile_subscriber', 'Drupal\commerce_shipping\EventSubscriber\CustomerProfileSubscriber')
        ->addTag('event_subscriber');
    }
  }

}
