<?php

namespace Drupal\Tests\commerce_shipping\FunctionalJavascript;

use Drupal\commerce_checkout\Entity\CheckoutFlow;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Tests\commerce\FunctionalJavascript\JavascriptTestTrait;

/**
 * Tests the "Shipping information" checkout pane.
 *
 * @group commerce_shipping
 */
class CheckoutPaneTest extends CommerceBrowserTestBase {

  use JavascriptTestTrait;

  /**
   * First sample product.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $firstProduct;

  /**
   * Second sample product.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $secondProduct;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_payment',
    'commerce_payment_example',
    'commerce_shipping_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'access checkout',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Limit the available countries.
    $this->store->shipping_countries = ['US', 'FR', 'DE'];
    $this->store->save();

    /** @var \Drupal\commerce_payment\Entity\PaymentGateway $gateway */
    $gateway = PaymentGateway::create([
      'id' => 'example_onsite',
      'label' => 'Example',
      'plugin' => 'example_onsite',
    ]);
    $gateway->getPlugin()->setConfiguration([
      'api_key' => '2342fewfsfs',
      'payment_method_types' => ['credit_card'],
    ]);
    $gateway->save();

    $product_variation_type = ProductVariationType::load('default');
    $product_variation_type->setTraits(['purchasable_entity_shippable']);
    $product_variation_type->save();

    $order_type = OrderType::load('default');
    $order_type->setThirdPartySetting('commerce_checkout', 'checkout_flow', 'shipping');
    $order_type->setThirdPartySetting('commerce_shipping', 'shipment_type', 'default');
    $order_type->save();

    // Create the order field.
    $field_definition = commerce_shipping_build_shipment_field_definition($order_type->id());
    \Drupal::service('commerce.configurable_field_manager')->createField($field_definition);

    // Install the variation trait.
    $trait_manager = \Drupal::service('plugin.manager.commerce_entity_trait');
    $trait = $trait_manager->createInstance('purchasable_entity_shippable');
    $trait_manager->installTrait($trait, 'commerce_product_variation', 'default');

    // Create two products.
    $variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => strtolower($this->randomMachineName()),
      'price' => [
        'number' => '7.99',
        'currency_code' => 'USD',
      ],
    ]);
    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $this->firstProduct = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => 'Conference hat',
      'variations' => [$variation],
      'stores' => [$this->store],
    ]);

    $variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => strtolower($this->randomMachineName()),
      'price' => [
        'number' => '8.99',
        'currency_code' => 'USD',
      ],
    ]);
    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $this->secondProduct = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => 'Conference bow tie',
      'variations' => [$variation],
      'stores' => [$this->store],
    ]);

    // Create two flat rate shipping methods.
    $first_shipping_method = $this->createEntity('commerce_shipping_method', [
      'name' => 'Overnight shipping',
      'stores' => [$this->store->id()],
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [
          'rate_label' => 'Overnight shipping',
          'rate_amount' => [
            'number' => '19.99',
            'currency_code' => 'USD',
          ],
        ],
      ],
    ]);
    $second_shipping_method = $this->createEntity('commerce_shipping_method', [
      'name' => 'Standard shipping',
      'stores' => [$this->store->id()],
      // Ensure that Standard shipping shows before overnight shipping.
      'weight' => -10,
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [
          'rate_label' => 'Standard shipping',
          'rate_amount' => [
            'number' => '9.99',
            'currency_code' => 'USD',
          ],
        ],
      ],
    ]);
    $second_store = $this->createStore();
    // Should never be shown cause it doesn't belong to the order's store.
    $third_shipping_method = $this->createEntity('commerce_shipping_method', [
      'name' => 'Secret shipping',
      'stores' => [$second_store->id()],
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [
          'rate_label' => 'Secret shipping',
          'rate_amount' => [
            'number' => '9.99',
            'currency_code' => 'USD',
          ],
        ],
      ],
    ]);
  }

  /**
   * Tests checkout with a single shipment.
   */
  public function testSingleShipment() {
    $this->drupalGet($this->firstProduct->toUrl()->toString());
    $this->submitForm([], 'Add to cart');
    $this->drupalGet($this->secondProduct->toUrl()->toString());
    $this->submitForm([], 'Add to cart');

    $this->drupalGet('checkout/1');
    $this->assertSession()->pageTextContains('Shipping information');
    $this->assertSession()->pageTextNotContains('Shipping method');

    $address = [
      'given_name' => 'John',
      'family_name' => 'Smith',
      'address_line1' => '1098 Alta Ave',
      'locality' => 'Mountain View',
      'administrative_area' => 'CA',
      'postal_code' => '94043',
    ];
    $address_prefix = 'shipping_information[shipping_profile][address][0][address]';
    // Confirm that the country list has been restricted.
    $this->assertOptions($address_prefix . '[country_code]', ['US', 'FR', 'DE']);

    $page = $this->getSession()->getPage();
    $page->fillField($address_prefix . '[country_code]', 'US');
    $this->waitForAjaxToFinish();
    foreach ($address as $property => $value) {
      $page->fillField($address_prefix . '[' . $property . ']', $value);
    }
    $page->findButton('Recalculate shipping')->click();
    $this->waitForAjaxToFinish();

    $this->assertSession()->pageTextContains('Shipping method');
    $first_radio_button = $page->findField('Standard shipping: $9.99');
    $second_radio_button = $page->findField('Overnight shipping: $19.99');
    $this->assertNotNull($first_radio_button);
    $this->assertNotNull($second_radio_button);
    $this->assertTrue($first_radio_button->getAttribute('checked'));
    $this->submitForm([
      'payment_information[add_payment_method][payment_details][number]' => '4111111111111111',
      'payment_information[add_payment_method][payment_details][expiration][month]' => '02',
      'payment_information[add_payment_method][payment_details][expiration][year]' => '2020',
      'payment_information[add_payment_method][payment_details][security_code]' => '123',
      'payment_information[add_payment_method][billing_information][address][0][address][given_name]' => 'Johnny',
      'payment_information[add_payment_method][billing_information][address][0][address][family_name]' => 'Appleseed',
      'payment_information[add_payment_method][billing_information][address][0][address][address_line1]' => '123 New York Drive',
      'payment_information[add_payment_method][billing_information][address][0][address][locality]' => 'New York City',
      'payment_information[add_payment_method][billing_information][address][0][address][administrative_area]' => 'NY',
      'payment_information[add_payment_method][billing_information][address][0][address][postal_code]' => '10001',
    ], 'Continue to review');

    // Confirm that the review is rendered correctly.
    $this->assertSession()->pageTextContains('Shipping information');
    foreach ($address as $property => $value) {
      $this->assertSession()->pageTextContains($value);
    }
    $this->assertSession()->pageTextContains('Standard shipping');
    $this->assertSession()->pageTextNotContains('Secret shipping');

    // Confirm the integrity of the shipment.
    $this->submitForm([], 'Pay and complete purchase');
    $order = Order::load(1);
    $shipments = $order->shipments->referencedEntities();
    $this->assertCount(1, $shipments);
    /** @var \Drupal\commerce_shipping\Entity|ShipmentInterface $shipment */
    $shipment = reset($shipments);
    $this->assertEquals('custom_box', $shipment->getPackageType()->getId());
    $this->assertEquals('Mountain View', $shipment->getShippingProfile()->address->locality);
    $this->assertEquals('Standard shipping', $shipment->getShippingMethod()->label());
    $this->assertEquals('default', $shipment->getShippingService());
    $this->assertEquals('9.99', $shipment->getAmount()->getNumber());
    $this->assertCount(2, $shipment->getItems());
    $this->assertEquals('draft', $shipment->getState()->value);
    // Confirm that the order total contains the shipment amount.
    $this->assertEquals(new Price('26.97', 'USD'), $order->getTotalPrice());
  }

  /**
   * Tests checkout with multiple shipments.
   */
  public function testMultipleShipments() {
    $this->drupalGet($this->firstProduct->toUrl()->toString());
    $this->submitForm([], 'Add to cart');
    $this->drupalGet($this->secondProduct->toUrl()->toString());
    $this->submitForm([], 'Add to cart');

    $this->drupalGet('checkout/1');
    $this->assertSession()->pageTextContains('Shipping information');
    $this->assertSession()->pageTextNotContains('Shipping method');

    $address = [
      'given_name' => 'John',
      'family_name' => 'Smith',
      'address_line1' => '38 rue du sentier',
      'locality' => 'Paris',
      'postal_code' => '75002',
    ];
    $address_prefix = 'shipping_information[shipping_profile][address][0][address]';
    $page = $this->getSession()->getPage();
    $page->fillField($address_prefix . '[country_code]', 'FR');
    $this->waitForAjaxToFinish();
    foreach ($address as $property => $value) {
      $page->fillField($address_prefix . '[' . $property . ']', $value);
    }
    $page->findButton('Recalculate shipping')->click();
    $this->waitForAjaxToFinish();

    foreach ([0, 1] as $shipment_index) {
      $label_index = $shipment_index + 1;
      $this->assertSession()->pageTextContains('Shipment #' . $label_index);
      $first_radio_button = $page->findField('shipping_information[shipments][' . $shipment_index . '][shipping_method][0]');
      $second_radio_button = $page->findField('shipping_information[shipments][' . $shipment_index . '][shipping_method][0]');
      $this->assertNotNull($first_radio_button);
      $this->assertNotNull($second_radio_button);
      // The radio buttons don't have access to their own labels.
      $selector = '//fieldset[@data-drupal-selector="edit-shipping-information-shipments-0-shipping-method-0"]';
      $this->assertSession()->elementTextContains('xpath', $selector, 'Standard shipping: $9.99');
      $this->assertSession()->elementTextContains('xpath', $selector, 'Overnight shipping: $19.99');
    }
    $this->submitForm([
      'payment_information[add_payment_method][payment_details][number]' => '4111111111111111',
      'payment_information[add_payment_method][payment_details][expiration][month]' => '02',
      'payment_information[add_payment_method][payment_details][expiration][year]' => '2020',
      'payment_information[add_payment_method][payment_details][security_code]' => '123',
      'payment_information[add_payment_method][billing_information][address][0][address][given_name]' => 'Johnny',
      'payment_information[add_payment_method][billing_information][address][0][address][family_name]' => 'Appleseed',
      'payment_information[add_payment_method][billing_information][address][0][address][address_line1]' => '123 New York Drive',
      'payment_information[add_payment_method][billing_information][address][0][address][locality]' => 'New York City',
      'payment_information[add_payment_method][billing_information][address][0][address][administrative_area]' => 'NY',
      'payment_information[add_payment_method][billing_information][address][0][address][postal_code]' => '10001',
    ], 'Continue to review');

    // Confirm that the review is rendered correctly.
    $this->assertSession()->pageTextContains('Shipping information');
    foreach ($address as $property => $value) {
      $this->assertSession()->pageTextContains($value);
    }
    $this->assertSession()->pageTextContains('Shipment #1');
    $this->assertSession()->pageTextContains('Shipment #2');
    $this->assertSession()->elementsCount('xpath', '//div[@class="field__item" and .="Standard shipping"]', 2);
    // Confirm the integrity of the shipment.
    $this->submitForm([], 'Pay and complete purchase');
    $order = Order::load(1);
    $shipments = $order->shipments->referencedEntities();
    $this->assertCount(2, $shipments);
    /** @var \Drupal\commerce_shipping\Entity|ShipmentInterface $first_shipment */
    $first_shipment = reset($shipments);
    $this->assertEquals('custom_box', $first_shipment->getPackageType()->getId());
    $this->assertEquals('Paris', $first_shipment->getShippingProfile()->address->locality);
    $this->assertEquals('Standard shipping', $first_shipment->getShippingMethod()->label());
    $this->assertEquals('default', $first_shipment->getShippingService());
    $this->assertEquals('9.99', $first_shipment->getAmount()->getNumber());
    $this->assertEquals('draft', $first_shipment->getState()->value);
    $this->assertCount(1, $first_shipment->getItems());
    $items = $first_shipment->getItems();
    $item = reset($items);
    $this->assertEquals('Conference hat', $item->getTitle());
    $this->assertEquals(1, $item->getQuantity());
    /** @var \Drupal\commerce_shipping\Entity|ShipmentInterface $second_shipment */
    $second_shipment = end($shipments);
    $this->assertEquals('custom_box', $second_shipment->getPackageType()->getId());
    $this->assertEquals('Paris', $second_shipment->getShippingProfile()->address->locality);
    $this->assertEquals('Standard shipping', $second_shipment->getShippingMethod()->label());
    $this->assertEquals('default', $second_shipment->getShippingService());
    $this->assertEquals('9.99', $second_shipment->getAmount()->getNumber());
    $this->assertEquals('draft', $second_shipment->getState()->value);
    $this->assertCount(1, $second_shipment->getItems());
    $items = $second_shipment->getItems();
    $item = reset($items);
    $this->assertEquals('Conference bow tie', $item->getTitle());
    $this->assertEquals(1, $item->getQuantity());
    // Confirm that the order total contains the shipment amounts.
    $this->assertEquals(new Price('36.96', 'USD'), $order->getTotalPrice());
  }

  /**
   * Tests checkout when the shipping profile is not required for showing costs.
   */
  public function testNoRequiredShippingProfile() {
    $checkout_flow = CheckoutFlow::load('shipping');
    $checkout_flow_configuration = $checkout_flow->get('configuration');
    $checkout_flow_configuration['panes']['shipping_information']['require_shipping_profile'] = FALSE;
    $checkout_flow->set('configuration', $checkout_flow_configuration);
    $checkout_flow->save();

    $this->drupalGet($this->firstProduct->toUrl()->toString());
    $this->submitForm([], 'Add to cart');
    $this->drupalGet($this->secondProduct->toUrl()->toString());
    $this->submitForm([], 'Add to cart');

    $this->drupalGet('checkout/1');
    // Confirm that the shipping methods are shown without a profile.
    $this->assertSession()->pageTextContains('Shipping method');
    $page = $this->getSession()->getPage();
    $first_radio_button = $page->findField('Standard shipping: $9.99');
    $second_radio_button = $page->findField('Overnight shipping: $19.99');
    $this->assertNotNull($first_radio_button);
    $this->assertNotNull($second_radio_button);
    $this->assertTrue($first_radio_button->getAttribute('checked'));

    // Complete the order information step.
    $address = [
      'given_name' => 'John',
      'family_name' => 'Smith',
      'address_line1' => '1098 Alta Ave',
      'locality' => 'Mountain View',
      'administrative_area' => 'CA',
      'postal_code' => '94043',
    ];
    $address_prefix = 'shipping_information[shipping_profile][address][0][address]';
    $page->fillField($address_prefix . '[country_code]', 'US');
    $this->waitForAjaxToFinish();
    foreach ($address as $property => $value) {
      $page->fillField($address_prefix . '[' . $property . ']', $value);
    }
    $this->submitForm([
      'payment_information[add_payment_method][payment_details][number]' => '4111111111111111',
      'payment_information[add_payment_method][payment_details][expiration][month]' => '02',
      'payment_information[add_payment_method][payment_details][expiration][year]' => '2020',
      'payment_information[add_payment_method][payment_details][security_code]' => '123',
      'payment_information[add_payment_method][billing_information][address][0][address][given_name]' => 'Johnny',
      'payment_information[add_payment_method][billing_information][address][0][address][family_name]' => 'Appleseed',
      'payment_information[add_payment_method][billing_information][address][0][address][address_line1]' => '123 New York Drive',
      'payment_information[add_payment_method][billing_information][address][0][address][locality]' => 'New York City',
      'payment_information[add_payment_method][billing_information][address][0][address][administrative_area]' => 'NY',
      'payment_information[add_payment_method][billing_information][address][0][address][postal_code]' => '10001',
    ], 'Continue to review');

    // Confirm that the review is rendered correctly.
    $this->assertSession()->pageTextContains('Shipping information');
    foreach ($address as $property => $value) {
      $this->assertSession()->pageTextContains($value);
    }
    $this->assertSession()->pageTextContains('Standard shipping');

    // Confirm the integrity of the shipment.
    $this->submitForm([], 'Pay and complete purchase');
    $order = Order::load(1);
    $shipments = $order->shipments->referencedEntities();
    $this->assertCount(1, $shipments);
    /** @var \Drupal\commerce_shipping\Entity|ShipmentInterface $shipment */
    $shipment = reset($shipments);
    $this->assertEquals('custom_box', $shipment->getPackageType()->getId());
    $this->assertEquals('Mountain View', $shipment->getShippingProfile()->address->locality);
    $this->assertEquals('Standard shipping', $shipment->getShippingMethod()->label());
    $this->assertEquals('default', $shipment->getShippingService());
    $this->assertEquals('9.99', $shipment->getAmount()->getNumber());
    $this->assertCount(2, $shipment->getItems());
    $this->assertEquals('draft', $shipment->getState()->value);
    // Confirm that the order total contains the shipment amounts.
    $this->assertEquals(new Price('26.97', 'USD'), $order->getTotalPrice());
  }

  /**
   * Asserts that a select field has all of the provided options.
   *
   * Core only has assertOption(), this helper decreases the number of needed
   * assertions.
   *
   * @param string $id
   *   ID of select field to assert.
   * @param array $options
   *   Options to assert.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use \Drupal\Component\Utility\SafeMarkup::format() to embed
   *   variables in the message text, not t(). If left blank, a default message
   *   will be displayed.
   */
  protected function assertOptions($id, array $options, $message = '') {
    $elements = $this->xpath('//select[@name="' . $id . '"]/option');
    $found_options = [];
    foreach ($elements as $element) {
      if ($option = $element->getValue()) {
        $found_options[] = $option;
      }
    }
    $this->assertFieldValues($found_options, $options, $message);
  }

}
