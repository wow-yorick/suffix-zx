<?php

namespace Drupal\physical;

/**
 * Provides a value object for volume amounts.
 *
 * Usage example:
 * @code
 *   $volume = new Volume('3.00', VolumeUnit::LITER);
 * @endcode
 */
final class Volume extends Measurement {

  /**
   * The measurement type.
   *
   * @var string
   */
  protected $type = MeasurementType::VOLUME;

}
