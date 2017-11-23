<?php

namespace Drupal\physical;

use Drupal\physical\Exception\UnitMismatchException;

/**
 * Provides a base class for measurement value objects.
 */
abstract class Measurement {

  /**
   * The measurement type.
   *
   * Must be defined by each subclass.
   *
   * @var string
   */
  protected $type = '';

  /**
   * The number.
   *
   * @var string
   */
  protected $number;

  /**
   * The unit.
   *
   * @var string
   */
  protected $unit;

  /**
   * Constructs a new Measurement object.
   *
   * @param string $number
   *   The number.
   * @param string $unit
   *   The unit.
   */
  public function __construct($number, $unit) {
    Calculator::assertNumberFormat($number);
    /** @var \Drupal\physical\UnitInterface $units_class */
    $units_class = MeasurementType::getUnitClass($this->type);
    $units_class::assertExists($unit);

    $this->number = (string) $number;
    $this->unit = $unit;
  }

  /**
   * Gets the number.
   *
   * @return string
   *   The number.
   */
  public function getNumber() {
    return $this->number;
  }

  /**
   * Gets the unit.
   *
   * @return string
   *   The unit.
   */
  public function getUnit() {
    return $this->unit;
  }

  /**
   * Gets the string representation of the measurement.
   *
   * @return string
   *   The string representation of the measurement.
   */
  public function __toString() {
    return Calculator::trim($this->number) . ' ' . $this->unit;
  }

  /**
   * Gets the array representation of the measurement.
   *
   * @return array
   *   The array representation of the measurement.
   */
  public function toArray() {
    return ['number' => $this->number, 'unit' => $this->unit];
  }

  /**
   * Converts the current measurement to a new unit.
   *
   * @param string $new_unit
   *   The new unit.
   *
   * @return static
   *   The resulting length.
   */
  public function convert($new_unit) {
    /** @var \Drupal\physical\UnitInterface $units_class */
    $units_class = MeasurementType::getUnitClass($this->type);
    // Convert the number to the base unit, then from there to the new unit.
    $base_number = Calculator::multiply($this->number, $units_class::getBaseFactor($this->unit));
    $new_number = Calculator::divide($base_number, $units_class::getBaseFactor($new_unit));

    return new static($new_number, $new_unit);
  }

  /**
   * Adds the given measurement to the current one.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return static
   *   The resulting measurement.
   */
  public function add(Measurement $measurement) {
    $this->assertSameUnit($this, $measurement);
    $new_number = Calculator::add($this->number, $measurement->getNumber());
    return new static($new_number, $this->unit);
  }

  /**
   * Subtracts the given measurement from the current one.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return static
   *   The resulting measurement.
   */
  public function subtract(Measurement $measurement) {
    $this->assertSameUnit($this, $measurement);
    $new_number = Calculator::subtract($this->number, $measurement->getNumber());
    return new static($new_number, $this->unit);
  }

  /**
   * Multiplies the current measurement by the given number.
   *
   * @param string $number
   *   The number.
   *
   * @return static
   *   The resulting measurement.
   */
  public function multiply($number) {
    $new_number = Calculator::multiply($this->number, $number);
    return new static($new_number, $this->unit);
  }

  /**
   * Divides the current measurement by the given number.
   *
   * @param string $number
   *   The number.
   *
   * @return static
   *   The resulting measurement.
   */
  public function divide($number) {
    $new_number = Calculator::divide($this->number, $number);
    return new static($new_number, $this->unit);
  }

  /**
   * Rounds the current measurement.
   *
   * @param int $precision
   *   The number of decimals to round to.
   * @param int $mode
   *   The rounding mode. One of the following constants: PHP_ROUND_HALF_UP,
   *   PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN, PHP_ROUND_HALF_ODD.
   *
   * @return static
   *   The rounded measurement.
   */
  public function round($precision = 0, $mode = PHP_ROUND_HALF_UP) {
    $new_number = Calculator::round($this->number, $precision, $mode);
    return new static($new_number, $this->unit);
  }

  /**
   * Compares the current measurement with the given one.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return int
   *   0 if both measurements are equal, 1 if the first one is greater,
   *   -1 otherwise.
   */
  public function compareTo(Measurement $measurement) {
    $this->assertSameUnit($this, $measurement);
    return Calculator::compare($this->number, $measurement->getNumber());
  }

  /**
   * Gets whether the current measurement is zero.
   *
   * @return bool
   *   TRUE if the measurement is zero, FALSE otherwise.
   */
  public function isZero() {
    return Calculator::compare($this->number, '0') == 0;
  }

  /**
   * Gets whether the current measurement is equivalent to the given one.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return bool
   *   TRUE if the measurements are equal, FALSE otherwise.
   */
  public function equals(Measurement $measurement) {
    return $this->compareTo($measurement) == 0;
  }

  /**
   * Gets whether the current measurement is greater than the given one.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return bool
   *   TRUE if the current measurement is greater than the given measurement,
   *   FALSE otherwise.
   */
  public function greaterThan(Measurement $measurement) {
    return $this->compareTo($measurement) == 1;
  }

  /**
   * Gets whether the current measurement is greater than or equal to the given one.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return bool
   *   TRUE if the current measurement is greater than or equal to the given
   *   one, FALSE otherwise.
   */
  public function greaterThanOrEqual(Measurement $measurement) {
    return $this->greaterThan($measurement) || $this->equals($measurement);
  }

  /**
   * Gets whether the current measurement is lesser than the given measurement.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return bool
   *   TRUE if the current measurement is lesser than the given one,
   *   FALSE otherwise.
   */
  public function lessThan(Measurement $measurement) {
    return $this->compareTo($measurement) == -1;
  }

  /**
   * Gets whether the current measurement is lesser than or equal to the given measurement.
   *
   * @param \Drupal\physical\Measurement $measurement
   *   The measurement.
   *
   * @return bool
   *   TRUE if the current measurement is lesser than or equal to the given measurement,
   *   FALSE otherwise.
   */
  public function lessThanOrEqual(Measurement $measurement) {
    return $this->lessThan($measurement) || $this->equals($measurement);
  }

  /**
   * Asserts that the two measurements have the same currency.
   *
   * @param \Drupal\physical\Measurement $first_measurement
   *   The first measurement.
   * @param \Drupal\physical\Measurement $second_measurement
   *   The second measurement.
   *
   * @throws \Drupal\physical\Exception\UnitMismatchException
   *   Thrown when the measurements do not have the same currency.
   */
  protected function assertSameUnit(Measurement $first_measurement, Measurement $second_measurement) {
    if ($first_measurement->getUnit() != $second_measurement->getUnit()) {
      throw new UnitMismatchException();
    }
  }

}
