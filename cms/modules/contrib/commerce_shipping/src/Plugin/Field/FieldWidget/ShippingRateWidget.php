<?php

namespace Drupal\commerce_shipping\Plugin\Field\FieldWidget;

use Drupal\commerce_price\Entity\Currency;
use Drupal\commerce_price\NumberFormatterFactoryInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of 'commerce_shipping_rate'.
 *
 * @FieldWidget(
 *   id = "commerce_shipping_rate",
 *   label = @Translation("Shipping rate"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ShippingRateWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The number formatter.
   *
   * @var \CommerceGuys\Intl\Formatter\NumberFormatterInterface
   */
  protected $numberFormatter;

  /**
   * Constructs a new ShippingRateWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_price\NumberFormatterFactoryInterface $number_formatter_factory
   *   The number formatter factory.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, NumberFormatterFactoryInterface $number_formatter_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->entityTypeManager = $entity_type_manager;
    $this->numberFormatter = $number_formatter_factory->createInstance();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('commerce_price.number_formatter_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $items[$delta]->getEntity();
    $use_default_package_type = empty($shipment->getPackageType());
    /** @var \Drupal\commerce_shipping\ShippingMethodStorageInterface $shipping_method_storage */
    $shipping_method_storage = $this->entityTypeManager->getStorage('commerce_shipping_method');
    $shipping_methods = $shipping_method_storage->loadMultipleForShipment($shipment);
    $options = [];
    foreach ($shipping_methods as $shipping_method) {
      $shipping_method_plugin = $shipping_method->getPlugin();
      if ($use_default_package_type) {
        $shipment->setPackageType($shipping_method_plugin->getDefaultPackageType());
      }
      $shipping_rates = $shipping_method_plugin->calculateRates($shipment);
      foreach ($shipping_rates as $shipping_rate) {
        $service = $shipping_rate->getService();
        $amount = $shipping_rate->getAmount();
        // @todo Refactor the number formatter to work with just a currency code.
        $currency = Currency::load($amount->getCurrencyCode());

        $option_id = $shipping_method->id() . '--' . $service->getId();
        $option_label = $this->t('@service: @amount', [
          '@service' => $service->getLabel(),
          '@amount' => $this->numberFormatter->formatCurrency($amount->getNumber(), $currency),
        ]);
        $options[$option_id] = [
          'id' => $option_id,
          'label' => $option_label,
          'shipping_method_id' => $shipping_method->id(),
          'shipping_rate' => $shipping_rate,
        ];
      }
    }

    if (!empty($shipment->getShippingMethodId()) && array_key_exists($shipment->getShippingMethodId(), $shipping_methods)) {
      $default_value = $shipment->getShippingMethodId() . '--' . $shipment->getShippingService();
    }
    else {
      $option_ids = array_keys($options);
      $default_value = reset($option_ids);
    }
    $element['#type'] = 'radios';
    $element['#options'] = array_column($options, 'label', 'id');
    $element['#default_value'] = $default_value;
    // Store relevant data for extractFormValues().
    foreach ($options as $option_id => $option) {
      $element[$option_id]['#shipping_method_id'] = $option['shipping_method_id'];
      $element[$option_id]['#shipping_rate'] = $option['shipping_rate'];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = array_merge($form['#parents'], [$field_name, 0]);
    $element = NestedArray::getValue($form, [$field_name, 'widget', 0]);
    $selected_value = NestedArray::getValue($form_state->getValues(), $parents, $key_exists);
    if ($selected_value) {
      $shipping_method_id = $element[$selected_value]['#shipping_method_id'];
      /** @var \Drupal\commerce_shipping\ShippingRate $shipping_rate */
      $shipping_rate = $element[$selected_value]['#shipping_rate'];
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $shipment = $items[0]->getEntity();
      // @todo This should be done by selectRate() but the plugin doesn't
      // have access to the parent entity ID yet.
      $shipment->setShippingMethodId($shipping_method_id);

      $shipping_method_storage = $this->entityTypeManager->getStorage('commerce_shipping_method');
      /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
      $shipping_method = $shipping_method_storage->load($shipping_method_id);
      $shipping_method->getPlugin()->selectRate($shipment, $shipping_rate);

      // Put delta mapping in $form_state, so that flagErrors() can use it.
      $field_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
      foreach ($items as $delta => $item) {
        $field_state['original_deltas'][$delta] = $delta;
      }
      static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $entity_type == 'commerce_shipment' && $field_name == 'shipping_method';
  }

}
