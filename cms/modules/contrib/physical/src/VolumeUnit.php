<?php

namespace Drupal\physical;

/**
 * Provides volume units.
 */
final class VolumeUnit implements UnitInterface {

  const MILLILITER = 'ml';
  const CENTILITER = 'cl';
  const LITER = 'l';
  const CUBIC_MILLIMETER = 'mm3';
  const CUBIC_CENTIMETER = 'cm3';
  const CUBIC_METER = 'm3';
  const CUBIC_INCH = 'in3';
  const CUBIC_FOOT = 'ft3';
  const FLUID_OUNCE = 'fl oz';
  const US_GALLON = 'gal';

  /**
   * {@inheritdoc}
   */
  public static function getLabels() {
    return [
      self::MILLILITER => t('ml'),
      self::CENTILITER => t('cl'),
      self::LITER => t('l'),
      self::CUBIC_MILLIMETER => t('mm³'),
      self::CUBIC_CENTIMETER => t('cm³'),
      self::CUBIC_METER => t('m³'),
      self::CUBIC_INCH => t('in³'),
      self::CUBIC_FOOT => t('ft³'),
      self::FLUID_OUNCE => t('fl oz'),
      self::US_GALLON => t('gal'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseUnit() {
    return self::CUBIC_METER;
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseFactor($unit) {
    self::assertExists($unit);
    $factors = [
      self::MILLILITER => '0.000001',
      self::CENTILITER => '0.00001',
      self::LITER => '0.001',
      self::CUBIC_MILLIMETER => '0.000000001',
      self::CUBIC_CENTIMETER => '0.000001',
      self::CUBIC_METER => '1',
      self::CUBIC_INCH => '0.00001638706',
      self::CUBIC_FOOT => '0.02831685',
      self::FLUID_OUNCE => '0.00002957353',
      self::US_GALLON => '0.0037854118',
    ];

    return $factors[$unit];
  }

  /**
   * {@inheritdoc}
   */
  public static function assertExists($unit) {
    $allowed_units = [
      self::MILLILITER, self::CENTILITER, self::LITER, self::CUBIC_MILLIMETER,
      self::CUBIC_CENTIMETER, self::CUBIC_METER, self::CUBIC_INCH,
      self::CUBIC_FOOT, self::FLUID_OUNCE, self::US_GALLON,
    ];
    if (!in_array($unit, $allowed_units)) {
      throw new \InvalidArgumentException(sprintf('Invalid volume unit "%s" provided.', $unit));
    }
  }

}
