<?php

namespace Drupal\physical;

/**
 * Provides a value object for weight amounts.
 *
 * Technically this is mass, not weight, but we prioritized user expectations
 * over correctness.
 *
 * Usage example:
 * @code
 *   $weight = new Weight('100', WeightUnit::KILOGRAM);
 * @endcode
 */
final class Weight extends Measurement {

  /**
   * The measurement type.
   *
   * @var string
   */
  protected $type = MeasurementType::WEIGHT;

}
