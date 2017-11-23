<?php

namespace Drupal\physical;

/**
 * Provides a value object for length amounts.
 *
 * Usage example:
 * @code
 *   $height = new Length('1.90', LengthUnit::METER);
 * @endcode
 */
final class Length extends Measurement {

  /**
   * The measurement type.
   *
   * @var string
   */
  protected $type = MeasurementType::LENGTH;

}
