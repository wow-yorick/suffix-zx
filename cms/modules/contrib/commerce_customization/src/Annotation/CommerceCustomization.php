<?php

namespace Drupal\commerce_customization\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Commerce customization item annotation object.
 *
 * @see \Drupal\commerce_customization\Plugin\CommerceCustomizationManager
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceCustomization extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
