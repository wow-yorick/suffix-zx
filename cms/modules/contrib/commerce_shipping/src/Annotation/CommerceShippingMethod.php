<?php

namespace Drupal\commerce_shipping\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the shipping method plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\ShippingMethod.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceShippingMethod extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The shipping method label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The supported shipping services.
   *
   * An array of labels keyed by ID.
   *
   * @var array
   */
  public $services = [];

}
