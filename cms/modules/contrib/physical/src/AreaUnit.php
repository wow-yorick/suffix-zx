<?php

namespace Drupal\physical;

/**
 * Provides area units.
 */
final class AreaUnit implements UnitInterface {

  const SQUARE_MILLIMETER = 'mm2';
  const SQUARE_CENTIMETER = 'cm2';
  const SQUARE_METER = 'm2';
  const SQUARE_INCH = 'in2';
  const SQUARE_FOOT = 'ft2';

  /**
   * {@inheritdoc}
   */
  public static function getLabels() {
    return [
      self::SQUARE_MILLIMETER => t('mm²'),
      self::SQUARE_CENTIMETER => t('cm²'),
      self::SQUARE_METER => t('m²'),
      self::SQUARE_INCH => t('in²'),
      self::SQUARE_FOOT => t('ft²'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseUnit() {
    return self::SQUARE_METER;
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseFactor($unit) {
    self::assertExists($unit);
    $factors = [
      self::SQUARE_MILLIMETER => '0.000001',
      self::SQUARE_CENTIMETER => '0.0001',
      self::SQUARE_METER => '1',
      self::SQUARE_INCH => '0.0006451600',
      self::SQUARE_FOOT => '0.09290304',
    ];

    return $factors[$unit];
  }

  /**
   * {@inheritdoc}
   */
  public static function assertExists($unit) {
    $allowed_units = [
      self::SQUARE_MILLIMETER, self::SQUARE_CENTIMETER, self::SQUARE_METER,
      self::SQUARE_INCH, self::SQUARE_FOOT,
    ];
    if (!in_array($unit, $allowed_units)) {
      throw new \InvalidArgumentException(sprintf('Invalid area unit "%s" provided.', $unit));
    }
  }

}
