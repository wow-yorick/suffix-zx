<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\PackageType;

use Drupal\Core\Plugin\PluginBase;
use Drupal\physical\Length;
use Drupal\physical\Weight;

/**
 * Defines the class for package types.
 */
class PackageType extends PluginBase implements PackageTypeInterface {

  /**
   * The package type length.
   *
   * @var \Drupal\physical\Length
   */
  protected $length;

  /**
   * The package type width.
   *
   * @var \Drupal\physical\Length
   */
  protected $width;

  /**
   * The package type height.
   *
   * @var \Drupal\physical\Length
   */
  protected $height;

  /**
   * The package type weight.
   *
   * @var \Drupal\physical\Weight
   */
  protected $weight;

  /**
   * Constructs a new PackageType object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $dimensions = $plugin_definition['dimensions'];
    $this->length = new Length($dimensions['length'], $dimensions['unit']);
    $this->width = new Length($dimensions['width'], $dimensions['unit']);
    $this->height = new Length($dimensions['height'], $dimensions['unit']);
    $weight = $plugin_definition['weight'];
    $this->weight = new Weight($weight['number'], $weight['unit']);
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteId() {
    return $this->pluginDefinition['remote_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLength() {
    return $this->length;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

}
