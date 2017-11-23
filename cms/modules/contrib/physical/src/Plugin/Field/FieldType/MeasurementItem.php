<?php

namespace Drupal\physical\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\physical\Measurement;
use Drupal\physical\MeasurementType;

/**
 * Plugin implementation of the 'physical_measurement' field type.
 *
 * @FieldType(
 *   id = "physical_measurement",
 *   label = @Translation("Measurement"),
 *   description = @Translation("This field stores a number and a unit of measure."),
 *   category = @Translation("Physical"),
 *   default_widget = "physical_measurement_default",
 *   default_formatter = "physical_measurement_default"
 * )
 */
class MeasurementItem extends FieldItemBase implements PreconfiguredFieldUiOptionsInterface {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['number'] = DataDefinition::create('string')
      ->setLabel(t('Number'));
    $properties['unit'] = DataDefinition::create('string')
      ->setLabel(t('Unit'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'number' => [
          'description' => 'The number.',
          'type' => 'numeric',
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
  public static function mainPropertyName() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();
    $constraints[] = $manager->create('ComplexData', [
      'number' => [
        'Regex' => [
          'pattern' => '/^[+-]?((\d+(\.\d*)?)|(\.\d+))$/i',
        ],
      ],
    ]);

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->number === NULL || $this->number === '' || empty($this->unit);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Allow callers to pass a Measurement value object as the field item value.
    if ($values instanceof Measurement) {
      $measurement = $values;
      $values = [
        'number' => $measurement->getNumber(),
        'unit' => $measurement->getUnit(),
      ];
    }
    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'measurement_type' => MeasurementType::LENGTH,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element['measurement_type'] = [
      '#type' => 'radios',
      '#title' => t('Measurement type'),
      '#options' => MeasurementType::getLabels(),
      '#default_value' => $this->getSetting('measurement_type'),
      '#required' => TRUE,
      '#disabled' => $has_data,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    $options = [];
    // Expose an individual field in the Field UI for each measurement type.
    foreach (MeasurementType::getLabels() as $type => $label) {
      $options[$type] = [
        'label' => $label,
        'field_storage_config' => [
          'settings' => [
            'measurement_type' => $type,
          ],
        ],
      ];
    }

    return $options;
  }

  /**
   * Gets the Measurement value object for the current field item.
   *
   * @return \Drupal\physical\Measurement
   *   A subclass of Measurement (Length, Volume, etc).
   */
  public function toMeasurement() {
    $class = MeasurementType::getClass($this->getSetting('measurement_type'));
    return new $class($this->number, $this->unit);
  }

}
