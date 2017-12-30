<?php

namespace Drupal\commerce_order_number\Plugin\Commerce\OrderNumberGenerator;

use Drupal\Core\Plugin\PluginBase;

/**
 * Abstract base class for order number generators.
 */
abstract class OrderNumberGeneratorBase extends PluginBase implements OrderNumberGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

}
