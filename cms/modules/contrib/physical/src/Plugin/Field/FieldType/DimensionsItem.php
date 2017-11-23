<?php

namespace Drupal\physical\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\physical\Length;

/**
 * Plugin implementation of the 'physical_dimensions' field type.
 *
 * @FieldType(
 *   id = "physical_dimensions",
 *   label = @Translation("Dimensions"),
 *   description = @Translation("This field stores the length, width, height numbers and a unit of measure."),
 *   category = @Translation("Physical"),
 *   default_widget = "physical_dimensions_default",
 *   default_formatter = "physical_dimensions_default"
 * )
 */
class DimensionsItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'length' => [
          'description' => 'The length.',
          'type' => 'numeric',
          'default' => 0,
          'precision' => 19,
          'scale' => 6,
        ],
        'width' => [
          'description' => 'The width.',
          'type' => 'numeric',
          'size' => 'normal',
          'precision' => 19,
          'scale' => 6,
        ],
        'height' => [
          'description' => 'The height.',
          'type' => 'numeric',
          'size' => 'normal',
          'precision' => 19,
          'scale' => 6,
        ],
        'unit' => [
          'description' => 'The unit.',
          'type' => 'varchar',
          'length' => '255',
          'default' => '',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['length'] = DataDefinition::create('string')
      ->setLabel(t('Length'));
    $properties['width'] = DataDefinition::create('string')
      ->setLabel(t('Width'));
    $properties['height'] = DataDefinition::create('string')
      ->setLabel(t('Height'));
    $properties['unit'] = DataDefinition::create('string')
      ->setLabel(t('Unit'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();
    $constraints[] = $manager->create('Dimensions', []);

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // The field is empty if the unit is empty or all numbers are empty.
    if (empty($this->unit)) {
      return TRUE;
    }
    if ($this->checkEmpty($this->length) && $this->checkEmpty($this->width) && $this->checkEmpty($this->height)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Checks whether the given value is NULL or an empty string.
   *
   * Excludes zeroes from the check because they are considered valid numbers
   * in the context of this field.
   *
   * @param string $value
   *   The value.
   *
   * @return bool
   *   TRUE if the value is empty, FALSE otherwise.
   */
  protected function checkEmpty($value) {
    return is_null($value) || $value === '';
  }

  /**
   * Gets the length as a value object.
   *
   * @return \Drupal\physical\Length
   *   The length.
   */
  public function getLength() {
    return new Length($this->length, $this->unit);
  }

  /**
   * Gets the width as a value object.
   *
   * @return \Drupal\physical\Length
   *   The width.
   */
  public function getWidth() {
    return new Length($this->width, $this->unit);
  }

  /**
   * Gets the height as a value object.
   *
   * @return \Drupal\physical\Length
   *   The height.
   */
  public function getHeight() {
    return new Length($this->height, $this->unit);
  }

}
