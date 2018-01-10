<?php

namespace Drupal\commerce_customization\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'commerce_customization_widget' widget.
 *
 * @FieldWidget(
 *   id = "commerce_customization_widget",
 *   label = @Translation("Commerce customization widget"),
 *   field_types = {
 *     "commerce_customization_type"
 *   }
 * )
 */
class CommerceCustomizationWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'commerce_customization/admin';
    $definitions = \Drupal::service('plugin.manager.commerce_customization')->getDefinitions();
    $options = array_map(function ($value) {
      return $value['label']->__toString();
    }, $definitions);

    $id = implode('-', ['commerce-customization', $items->getName(), $delta]);
    $element = $element + [
      '#type' => 'container',
      '#prefix' => "<div id='{$id}'>",
      '#suffix' => "</div>",
    ];

    $ajax_name = $items->getName() . "[$delta][trigger_ajax]";

    // We need to validate only the select value, or we will not be able to get
    // it in the form_state.
    $element['ajax_trigger'] = [
      '#type' => 'submit',
      '#value' => $this->t('Load plugin'),
      '#name' => $ajax_name,
      '#submit' => [[$this, 'ajaxSubmit']],
      '#limit_validation_errors' => [
        [$items->getName(), $delta, 'value'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxRefresh'],
        'wrapper' => $id,
      ],
    ];

    $element['value'] = [
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#options' => $options,
      '#empty_option' => $this->t('- Select a customization -'),
      '#attributes' => ['commerce-customization-trigger' => $ajax_name],
    ];

    // This value is set on ajax, if its not set, we show the plugin if there is
    // data saved already.
    $plugin = "";
    if ($form_state->has("commerce_customization_plugin_{$delta}")) {
      $plugin = $form_state->get("commerce_customization_plugin_{$delta}");
    }
    elseif (isset($items[$delta]->value) && $items[$delta]->value) {
      $plugin = $items[$delta]->value;
    }
    // $plugin is "" if the select is on the empty option.
    if ($plugin) {
      // Retrieve the form with data and pass to the form builder.
      $data = isset($items[$delta]->data) ? unserialize($items[$delta]->data) : NULL;

      // Instance the plugin if it exists.
      $definitions = \Drupal::service('plugin.manager.commerce_customization')->getDefinitions();
      if (!isset($definitions[$plugin])) {
        $element['data'] = $this->t("This custom widget plugin doesn't exists anymore.");
      }
      else {
        $instance = \Drupal::service('plugin.manager.commerce_customization')->createInstance($plugin);
        // Load the plugin form passing the data from the field.
        $element['data'] = $instance->getConfigForm($items, $delta, $element, $form, $form_state, $data);
        $element['data']['#tree'] = TRUE;
      }
    }

    return $element;
  }

  /**
   * Load the plugin form as needed.
   */
  public function ajaxRefresh($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    list($field_name, $delta, $value) = explode('[', str_replace(']', '', $triggering_element['#name']));
    $plugin = $form_state->getValue($field_name)[$delta]['value'];
    $form[$field_name]['widget'][$delta]['_weight']['#access'] = FALSE;
    return $form[$field_name]['widget'][$delta];
  }

  /**
   * Submits the form to rebuild it.
   */
  public function ajaxSubmit($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    list($field_name, $delta, $value) = explode('[', str_replace(']', '', $triggering_element['#name']));
    $plugin = $form_state->getValue($field_name)[$delta]['value'];
    $form_state->set("commerce_customization_plugin_{$delta}", $plugin);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Serialize the settings.
    $definitions = \Drupal::service('plugin.manager.commerce_customization')->getDefinitions();
    foreach ($values as &$value) {
      if (isset($definitions[$value['value']])) {
        $instance = \Drupal::service('plugin.manager.commerce_customization')->createInstance($value['value']);
        $value['data'] = $instance->massageFormValues($value['data']);
      }
    }

    foreach ($values as &$value) {
      $value['data'] = isset($value['data']) ? serialize($value['data']) : serialize([]);
    }
    return $values;
  }

}
