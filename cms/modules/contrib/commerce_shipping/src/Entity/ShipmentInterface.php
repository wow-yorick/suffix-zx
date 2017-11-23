<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageTypeInterface as PackageTypePluginInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\physical\Weight;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Defines the interface for shipments.
 */
interface ShipmentInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Populates the shipment from the given proposed shipment.
   *
   * @param \Drupal\commerce_shipping\ProposedShipment $proposed_shipment
   *   The proposed shipment.
   */
  public function populateFromProposedShipment(ProposedShipment $proposed_shipment);

  /**
   * Gets the parent order.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface|null
   *   The order, or NULL if unknown.
   */
  public function getOrder();

  /**
   * Gets the parent order ID.
   *
   * @return int|null
   *   The order ID, or NULL if unknown.
   */
  public function getOrderId();

  /**
   * Gets the package type.
   *
   * @return \Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageTypeInterface|null
   *   The shipment package type, or NULL if unknown.
   */
  public function getPackageType();

  /**
   * Sets the package type.
   *
   * @param \Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageTypeInterface $package_type
   *   The package type.
   *
   * @return $this
   */
  public function setPackageType(PackageTypePluginInterface $package_type);

  /**
   * Gets the shipping method.
   *
   * @return \Drupal\commerce_shipping\Entity\ShippingMethodInterface|null
   *   The shipping method, or NULL if unknown.
   */
  public function getShippingMethod();

  /**
   * Sets the shipping method.
   *
   * @param \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method
   *   The shipping method.
   *
   * @return $this
   */
  public function setShippingMethod(ShippingMethodInterface $shipping_method);

  /**
   * Gets the shipping method ID.
   *
   * @return int|null
   *   The shipping method ID, or NULL if unknown.
   */
  public function getShippingMethodId();

  /**
   * Sets the shipping method ID.
   *
   * @param int $shipping_method_id
   *   The shipping method ID.
   *
   * @return $this
   */
  public function setShippingMethodId($shipping_method_id);

  /**
   * Gets the shipping service.
   *
   * @return string|null
   *   The shipping service, or NULL if unknown.
   */
  public function getShippingService();

  /**
   * Sets the shipping service.
   *
   * @param string $shipping_service
   *   The shipping service.
   *
   * @return $this
   */
  public function setShippingService($shipping_service);

  /**
   * Gets the shipping profile.
   *
   * @return \Drupal\profile\Entity\ProfileInterface
   *   The shipping profile.
   */
  public function getShippingProfile();

  /**
   * Sets the shipping profile.
   *
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The shipping profile.
   *
   * @return $this
   */
  public function setShippingProfile(ProfileInterface $profile);

  /**
   * Gets the shipment title.
   *
   * @return string
   *   The shipment title.
   */
  public function getTitle();

  /**
   * Sets the shipment title.
   *
   * @param string $title
   *   The shipment title.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Gets the shipment items.
   *
   * @return \Drupal\commerce_shipping\ShipmentItem[]
   *   The shipment items.
   */
  public function getItems();

  /**
   * Sets the shipment items.
   *
   * @param \Drupal\commerce_shipping\ShipmentItem[] $shipment_items
   *   The shipment items.
   *
   * @return $this
   */
  public function setItems(array $shipment_items);

  /**
   * Gets whether the shipment has items.
   *
   * @return bool
   *   TRUE if the shipment has items, FALSE otherwise.
   */
  public function hasItems();

  /**
   * Adds a shipment item.
   *
   * @param \Drupal\commerce_shipping\ShipmentItem $shipment_item
   *   The shipment item.
   *
   * @return $this
   */
  public function addItem(ShipmentItem $shipment_item);

  /**
   * Removes a shipment item.
   *
   * @param \Drupal\commerce_shipping\ShipmentItem $shipment_item
   *   The shipment item.
   *
   * @return $this
   */
  public function removeItem(ShipmentItem $shipment_item);

  /**
   * Gets the total declared value.
   *
   * Represents the sum of the declared values of all shipment items.
   *
   * @return \Drupal\commerce_price\Price
   *   The total declared value.
   */
  public function getTotalDeclaredValue();

  /**
   * Gets the shipment weight.
   *
   * Calculated by adding the weight of each item to the
   * weight of the package type.
   *
   * @return \Drupal\physical\Weight|null
   *   The shipment weight, or NULL if unknown.
   */
  public function getWeight();

  /**
   * Sets the shipment weight.
   *
   * @param \Drupal\physical\Weight $weight
   *   The shipment weight.
   *
   * @return $this
   */
  public function setWeight(Weight $weight);

  /**
   * Gets the shipment amount.
   *
   * Represents the cost of shipping the shipment using
   * the selected shipping method and service.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The shipment amount, or NULL if unknown.
   */
  public function getAmount();

  /**
   * Sets the shipment amount.
   *
   * @param \Drupal\commerce_price\Price $amount
   *   The shipment amount.
   *
   * @return $this
   */
  public function setAmount(Price $amount);

  /**
   * Gets the shipment tracking code.
   *
   * Only available if shipping method supports tracking and the shipment
   * itself has been shipped.
   *
   * @return string|null
   *   The shipment tracking code, or NULL if unknown.
   */
  public function getTrackingCode();

  /**
   * Sets the shipment tracking code.
   *
   * @param string $tracking_code
   *   The shipment tracking code.
   *
   * @return $this
   */
  public function setTrackingCode($tracking_code);

  /**
   * Gets the shipment state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The shipment state.
   */
  public function getState();

  /**
   * Gets a shipment data value with the given key.
   *
   * Used to store temporary data.
   *
   * @param string $key
   *   The key.
   * @param mixed $default
   *   The default value.
   *
   * @return array
   *   The shipment data.
   */
  public function getData($key, $default = NULL);

  /**
   * Sets a shipment data value with the given key.
   *
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   *
   * @return $this
   */
  public function setData($key, $value);

  /**
   * Gets the shipment creation timestamp.
   *
   * @return int
   *   The shipment creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the shipment creation timestamp.
   *
   * @param int $timestamp
   *   The shipment creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the shipment shipped timestamp.
   *
   * @return int
   *   The shipment shipped timestamp.
   */
  public function getShippedTime();

  /**
   * Sets the shipment shipped timestamp.
   *
   * @param int $timestamp
   *   The shipment shipped timestamp.
   *
   * @return $this
   */
  public function setShippedTime($timestamp);

}
