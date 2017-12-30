<?php

namespace Drupal\commerce_order_number;

/**
 * Provides a value object for incrementing order numbers. This is used for
 * storing the information about the last placed order entity's order number.
 */
class OrderNumber {

  /**
   * The increment number.
   *
   * @var int
   */
  protected $incrementNumber;

  /**
   * The (formatted) year string.
   *
   * @var string
   */
  protected $year;

  /**
   * The (formatted) month string.
   *
   * @var string
   */
  protected $month;

  /**
   * The formatted order number.
   *
   * @var string
   */
  protected $value;

  /**
   * Constructs a new OrderNumber object.
   *
   * @param int $increment_number
   *   The increment number.
   * @param string $year
   *   The (formatted) year string.
   * @param string $month
   *   The (formatted) month string.
   */
  public function __construct($increment_number, $year, $month) {
    $this->incrementNumber = $increment_number;
    $this->year = $year;
    $this->month = $month;
  }

  /**
   * Gets the increment number.
   *
   * @return int
   *   The increment number.
   */
  public function getIncrementNumber() {
    return $this->incrementNumber;
  }

  /**
   * Increases the increment number by one.
   *
   * @return int
   *   The increment number.
   */
  public function increment() {
    $this->incrementNumber++;
    return $this->incrementNumber;
  }

  /**
   * Gets the year.
   *
   * @return string
   *   The year.
   */
  public function getYear() {
    return $this->year;
  }

  /**
   * Gets the month.
   *
   * @return string
   *   The month.
   */
  public function getMonth() {
    return $this->month;
  }

  /**
   * Gets the formatted order number.
   *
   * @return string
   *   The formatted order number.
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Sets the formatted order number.
   *
   * @param string $value
   *   The formatted order number.
   *
   * @return $this
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

}
