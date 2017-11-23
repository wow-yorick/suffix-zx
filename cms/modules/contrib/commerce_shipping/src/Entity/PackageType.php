<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\physical\LengthUnit;
use Drupal\physical\WeightUnit;

/**
 * Defines the package type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_package_type",
 *   label = @Translation("Package type"),
 *   label_singular = @Translation("package type"),
 *   label_plural = @Translation("package types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count package type",
 *     plural = "@count package types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_shipping\PackageTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_shipping\Form\PackageTypeForm",
 *       "edit" = "Drupal\commerce_shipping\Form\PackageTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_package_type",
 *   admin_permission = "administer commerce_package_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "dimensions",
 *     "weight",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/package-types/add",
 *     "edit-form" = "/admin/commerce/config/package-types/manage/{commerce_package_type}",
 *     "delete-form" = "/admin/commerce/config/package-types/manage/{commerce_package_type}/delete",
 *     "collection" = "/admin/commerce/config/package-types"
 *   }
 * )
 */
class PackageType extends ConfigEntityBase implements PackageTypeInterface {

  /**
   * The package type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The package type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The package type dimensions.
   *
   * @var array
   */
  protected $dimensions;

  /**
   * The package type weight.
   *
   * @var array
   */
  protected $weight;

  /**
   * {@inheritdoc}
   */
  public function getDimensions() {
    return $this->dimensions;
  }

  /**
   * {@inheritdoc}
   */
  public function setDimensions(array $dimensions) {
    $this->dimensions = $dimensions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight(array $weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Validate the dimensions and the weight. Can't be done
    // earlier because of the multiple ways a field can be set.
    foreach (['length', 'width', 'height', 'unit'] as $property) {
      if (!array_key_exists($property, (array) $this->dimensions)) {
        throw new EntityMalformedException('The dimensions field must have length, width, height, unit properties.');
      }
    }
    foreach (['number', 'unit'] as $property) {
      if (!array_key_exists($property, (array) $this->weight)) {
        throw new EntityMalformedException('The weight field must have number, unit properties.');
      }
    }
    LengthUnit::assertExists($this->dimensions['unit']);
    WeightUnit::assertExists($this->weight['unit']);
  }

}
