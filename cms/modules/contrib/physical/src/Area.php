<?php

namespace Drupal\physical;

/**
 * Provides a value object for area amounts.
 *
 * Usage example:
 * @code
 *   $area = new Area('7.00', AreaUnit::SQUARE_CENTIMETER);
 * @endcode
 */
final class Area extends Measurement {

  /**
   * The measurement type.
   *
   * @var string
   */
  protected $type = MeasurementType::AREA;

}
