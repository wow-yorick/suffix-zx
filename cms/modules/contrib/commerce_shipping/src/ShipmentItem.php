<?php

namespace Drupal\commerce_shipping;

/**
 * Represents a shipment item.
 */
final class ShipmentItem {

  /**
   * The source order item ID.
   *
   * @var string
   */
  protected $orderItemId;

  /**
   * The title.
   *
   * @var string
   */
  protected $title;

  /**
   * The quantity.
   *
   * @var float
   */
  protected $quantity;

  /**
   * The weight.
   *
   * @var \Drupal\physical\Weight
   */
  protected $weight;

  /**
   * The declared value.
   *
   * @var \Drupal\commerce_price\Price
   */
  protected $declaredValue;

  /**
   * The tariff code.
   *
   * @var string
   */
  protected $tariffCode;

  /**
   * Constructs a new ShipmentItem object.
   *
   * @param array $definition
   *   The definition.
   */
  public function __construct(array $definition) {
    foreach (['order_item_id', 'title', 'quantity', 'weight', 'declared_value'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new \InvalidArgumentException(sprintf('Missing required property "%s".', $required_property));
      }
    }

    $this->orderItemId = $definition['order_item_id'];
    $this->title = $definition['title'];
    $this->quantity = $definition['quantity'];
    $this->weight = $definition['weight'];
    $this->declaredValue = $definition['declared_value'];
    if (!empty($definition['tariff_code'])) {
      $this->tariffCode = $definition['tariff_code'];
    }
  }

  /**
   * Gets the source order item ID.
   *
   * Note that an order item might correspond to multiple shipment items,
   * depending on the used packer.
   *
   * @return string
   *   The order item ID.
   */
  public function getOrderItemId() {
    return $this->orderItemId;
  }

  /**
   * Gets the title.
   *
   * Can be used on customs forms as a description.
   *
   * @return string
   *   The title.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Gets the quantity.
   *
   * @return float
   *   The quantity.
   */
  public function getQuantity() {
    return $this->quantity;
  }

  /**
   * Gets the weight.
   *
   * Represents the weight of the entire shipment item (unit weight * quantity).
   *
   * @return \Drupal\physical\Weight
   *   The weight.
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * Gets the declared value.
   *
   * Represents the value of the entire shipment item (unit value * quantity).
   * Needed on customs forms.
   *
   * @return \Drupal\commerce_price\Price
   *   The declared value.
   */
  public function getDeclaredValue() {
    return $this->declaredValue;
  }

  /**
   * Gets the tariff code.
   *
   * This could be a Harmonized System (HS) code, or a Harmonized Tariff
   * Schedule (HTS) code. Needed on customs forms.
   *
   * @return string|null
   *   The tariff code, or NULL if not defined.
   */
  public function getTariffCode() {
    return $this->tariffCode;
  }

}
