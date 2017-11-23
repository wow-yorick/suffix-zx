<?php

namespace Drupal\physical;

/**
 * Provides the interface for unit classes.
 *
 * A unit class defines all possible units for a measurement type.
 */
interface UnitInterface {

  /**
   * Gets the labels for the defined units.
   *
   * @return array
   *   An array of labels keyed by unit.
   */
  public static function getLabels();

  /**
   * Gets the base unit.
   *
   * @return string
   *   The base unit.
   */
  public static function getBaseUnit();

  /**
   * Gets the base factor for the given unit.
   *
   * Used for converting measurements to and from the base unit.
   * Use multiplication to convert a measurement to the base unit, and
   * division to convert from the base unit (into the given unit).
   *
   * @param string $unit
   *   The unit.
   *
   * @return string
   *   The base factor.
   */
  public static function getBaseFactor($unit);

  /**
   * Asserts that the given unit exists.
   *
   * @param string $unit
   *   The unit.
   *
   * @throws \InvalidArgumentException
   */
  public static function assertExists($unit);

}
