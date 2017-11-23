<?php

namespace Drupal\Tests\commerce_shipping\FunctionalJavascript;

use Drupal\commerce_shipping\Entity\ShippingMethod;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Tests\commerce\FunctionalJavascript\JavascriptTestTrait;

/**
 * Tests the shipping method UI.
 *
 * @group commerce_shipping
 */
class ShippingMethodTest extends CommerceBrowserTestBase {

  use JavascriptTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_shipping',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_shipping_method',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests creating a shipping method.
   */
  public function testShippingMethodCreation() {
    $this->drupalGet('admin/commerce/config/shipping-methods');
    $this->getSession()->getPage()->clickLink('Add shipping method');
    $this->assertSession()->addressEquals('admin/commerce/config/shipping-methods/add');
    $this->assertSession()->fieldExists('name[0][value]');
    $this->getSession()->getPage()->fillField('plugin[0][target_plugin_id]', 'flat_rate');
    $this->waitForAjaxToFinish();

    $name = $this->randomMachineName(8);
    $edit = [
      'name[0][value]' => $name,
      'plugin[0][target_plugin_configuration][flat_rate][rate_label]' => 'Test label',
      'plugin[0][target_plugin_configuration][flat_rate][rate_amount][number]' => '10.00',
    ];

    $this->submitForm($edit, 'Save');
    $this->assertSession()->addressEquals('admin/commerce/config/shipping-methods');
    $this->assertSession()->pageTextContains("Saved the $name shipping method.");
    $shipping_method_count = $this->getSession()->getPage()->find('xpath', '//table/tbody/tr/td[text()="' . $name . '"]');
    $this->assertEquals(count($shipping_method_count), 1, 'shipping method exists in the table.');

    $shipping_method = ShippingMethod::load(1);
    $plugin = $shipping_method->getPlugin();
    $this->assertEquals(['number' => '10.00', 'currency_code' => 'USD'], $plugin->getConfiguration()['rate_amount']);
  }

  /**
   * Tests editing a shipping method.
   */
  public function testShippingMethodEditing() {
    $shipping_method = $this->createEntity('commerce_shipping_method', [
      'name' => $this->randomMachineName(8),
      'status' => TRUE,
      'plugin' => [
        'target_plugin_id' => 'flat_rate',
        'target_plugin_configuration' => [
          'rate_label' => 'Test label',
          'rate_amount' => [
            'number' => '10.00',
            'currency_code' => 'USD',
          ],
        ],
      ],
    ]);

    /** @var \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface $plugin */
    $plugin = $shipping_method->getPlugin();
    $this->assertEquals(['number' => '10.00', 'currency_code' => 'USD'], $plugin->getConfiguration()['rate_amount']);

    $this->drupalGet($shipping_method->toUrl('edit-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('name[0][value]');
    $new_shipping_method_name = $this->randomMachineName(8);
    $edit = [
      'name[0][value]' => $new_shipping_method_name,
      'plugin[0][target_plugin_configuration][flat_rate][rate_amount][number]' => '20.00',
    ];
    $this->submitForm($edit, 'Save');

    \Drupal::service('entity_type.manager')->getStorage('commerce_shipping_method')->resetCache([$shipping_method->id()]);
    $shipping_method_changed = ShippingMethod::load($shipping_method->id());
    $this->assertEquals($new_shipping_method_name, $shipping_method_changed->getName(), 'The shipping method name successfully updated.');
    /** @var \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface $plugin */
    $plugin = $shipping_method_changed->getPlugin();
    $this->assertEquals(['number' => '20.00', 'currency_code' => 'USD'], $plugin->getConfiguration()['rate_amount']);
  }

  /**
   * Tests deleting a shipping method.
   */
  public function testShippingMethodDeletion() {
    $shipping_method = $this->createEntity('commerce_shipping_method', [
      'name' => $this->randomMachineName(8),
    ]);
    $this->drupalGet($shipping_method->toUrl('delete-form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('This action cannot be undone.');
    $this->submitForm([], t('Delete'));

    \Drupal::service('entity_type.manager')->getStorage('commerce_shipping_method')->resetCache([$shipping_method->id()]);
    $shipping_method_exists = (bool) ShippingMethod::load($shipping_method->id());
    $this->assertFalse($shipping_method_exists, 'The new shipping method has been deleted from the database using UI.');
  }

}
