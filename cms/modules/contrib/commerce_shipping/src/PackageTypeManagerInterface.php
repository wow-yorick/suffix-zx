<?php

namespace Drupal\commerce_shipping;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines the interface for commerce_package_type plugin managers.
 */
interface PackageTypeManagerInterface extends PluginManagerInterface {

  /**
   * Gets the definitions for the given shipping method plugin ID.
   *
   * @param string $shipping_method
   *   The shipping method plugin ID.
   *
   * @return array
   *   The definitions.
   */
  public function getDefinitionsByShippingMethod($shipping_method);

}
