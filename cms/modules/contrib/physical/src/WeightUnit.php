<?php

namespace Drupal\physical;

/**
 * Provides weight units.
 */
final class WeightUnit implements UnitInterface {

  const GRAM = 'g';
  const KILOGRAM = 'kg';
  const OUNCE = 'oz';
  const POUND = 'lb';

  /**
   * {@inheritdoc}
   */
  public static function getLabels() {
    return [
      self::GRAM => t('g'),
      self::KILOGRAM => t('kg'),
      self::OUNCE => t('oz'),
      self::POUND => t('lb'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseUnit() {
    return self::KILOGRAM;
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseFactor($unit) {
    self::assertExists($unit);
    $factors = [
      self::GRAM => '0.001',
      self::KILOGRAM => '1',
      self::OUNCE => '0.028349523125',
      self::POUND => '0.45359237',
    ];

    return $factors[$unit];
  }

  /**
   * {@inheritdoc}
   */
  public static function assertExists($unit) {
    $allowed_units = [
      self::GRAM, self::KILOGRAM, self::OUNCE, self::POUND,
    ];
    if (!in_array($unit, $allowed_units)) {
      throw new \InvalidArgumentException(sprintf('Invalid weight unit "%s" provided.', $unit));
    }
  }

}
