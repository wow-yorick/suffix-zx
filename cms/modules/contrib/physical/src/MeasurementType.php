<?php

namespace Drupal\physical;

/**
 * Provides measurement types.
 */
final class MeasurementType {

  const AREA = 'area';
  const LENGTH = 'length';
  const VOLUME = 'volume';
  const WEIGHT = 'weight';

  /**
   * Gets the labels for the defined measurement types.
   *
   * @return array
   *   An array of labels keyed by measurement type.
   */
  public static function getLabels() {
    return [
      self::AREA => t('Area'),
      self::LENGTH => t('Length'),
      self::VOLUME => t('Volume'),
      self::WEIGHT => t('Weight'),
    ];
  }

  /**
   * Gets the class for the given measurement type.
   *
   * @param string $type
   *   The measurement type.
   *
   * @return string
   *   The fully qualified class name.
   */
  public static function getClass($type) {
    self::assertExists($type);
    $classes = [
      self::AREA => Area::class,
      self::LENGTH => Length::class,
      self::VOLUME => Volume::class,
      self::WEIGHT => Weight::class,
    ];

    return $classes[$type];
  }

  /**
   * Gets the unit class for the given measurement type.
   *
   * @param string $type
   *   The measurement type.
   *
   * @return string
   *   The fully qualified class name.
   */
  public static function getUnitClass($type) {
    self::assertExists($type);
    $classes = [
      self::AREA => AreaUnit::class,
      self::LENGTH => LengthUnit::class,
      self::VOLUME => VolumeUnit::class,
      self::WEIGHT => WeightUnit::class,
    ];

    return $classes[$type];
  }

  /**
   * Asserts that the given measurement type exists.
   *
   * @param string $type
   *   The measurement type.
   *
   * @throws \InvalidArgumentException
   */
  public static function assertExists($type) {
    $allowed_types = [
      self::AREA, self::LENGTH, self::VOLUME, self::WEIGHT,
    ];
    if (!in_array($type, $allowed_types)) {
      throw new \InvalidArgumentException(sprintf('Invalid measurement type "%s" provided.', $type));
    }
  }

}
