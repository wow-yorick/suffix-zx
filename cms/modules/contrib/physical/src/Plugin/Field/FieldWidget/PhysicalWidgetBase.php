<?php

namespace Drupal\physical\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides base functionality for physical widgets.
 */
abstract class PhysicalWidgetBase extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_unit' => '',
      'allow_unit_change' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['default_unit'] = [
      '#type' => 'select',
      '#title' => $this->t('Default unit'),
      '#options' => $this->getUnits(),
      '#default_value' => $this->getSetting('default_unit') ?: $this->getDefaultUnit(),
    ];
    $element['allow_unit_change'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to select a different unit of measurement on forms.'),
      '#default_value' => $this->getSetting('allow_unit_change'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Default unit: @unit', ['@unit' => $this->getDefaultUnit()]);
    if (!$this->getSetting('allow_unit_change')) {
      $summary[] = $this->t('User cannot modify the unit of measurement.');
    }
    else {
      $summary[] = $this->t('User can modify the unit of measurement.');
    }

    return $summary;
  }

  /**
   * Gets the available units for the current field.
   *
   * @return array
   *   The unit labels, keyed by unit.
   */
  protected function getUnits() {
    /** @var \Drupal\physical\UnitInterface $unit_class */
    $unit_class = $this->getUnitClass();
    return $unit_class::getLabels();
  }

  /**
   * Gets the default unit for the current field.
   *
   * @return string
   *   The default unit.
   */
  protected function getDefaultUnit() {
    $default_unit = $this->getSetting('default_unit');
    if (!$default_unit) {
      /** @var \Drupal\physical\UnitInterface $unit_class */
      $unit_class = $this->getUnitClass();
      $default_unit = $unit_class::getBaseUnit();
    }

    return $default_unit;
  }

  /**
   * Gets the unit class for the current field.
   *
   * @return string
   *   The fully qualified class name.
   */
  abstract protected function getUnitClass();

}
