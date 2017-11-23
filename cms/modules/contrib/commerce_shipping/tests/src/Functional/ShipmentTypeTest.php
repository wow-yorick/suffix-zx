<?php

namespace Drupal\Tests\commerce_shipping\Functional;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentType;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the shipment type UI.
 *
 * @group commerce_shipping
 */
class ShipmentTypeTest extends CommerceBrowserTestBase {

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
      'administer commerce_shipment_type',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests whether the default shipment type was created.
   */
  public function testDefaultShipmentType() {
    $shipment_type = ShipmentType::load('default');
    $this->assertNotNull($shipment_type);
  }

  /**
   * Tests the shipment type listing.
   */
  public function testListShipmentType() {
    $title = strtolower($this->randomMachineName(8));
    $table_selector = '//table/tbody/tr';

    // The shipment shows one default shipment type.
    $this->drupalGet('admin/commerce/config/shipment-types');
    $shipment_types = $this->getSession()->getDriver()->find($table_selector);
    $this->assertEquals(1, count($shipment_types));

    // Create a new commerce shipment type and see if the list has two shipment types.
    $this->createEntity('commerce_shipment_type', [
      'id' => $title,
      'label' => $title,
    ]);
    $this->drupalGet('admin/commerce/config/shipment-types');
    $shipment_types = $this->getSession()->getDriver()->find($table_selector);
    $this->assertEquals(2, count($shipment_types));
  }

  /**
   * Tests creating a shipment type.
   */
  public function testCreateShipmentType() {
    $this->drupalGet('admin/commerce/config/shipment-types/add');
    $edit = [
      'id' => 'foo',
      'label' => 'Foo label',
    ];
    $this->submitForm($edit, 'Save');

    $shipment_type = ShipmentType::load($edit['id']);
    $this->assertNotEmpty($shipment_type);
    $this->assertEquals('Foo label', $shipment_type->label());
  }

  /**
   * Tests updating a shipment type.
   */
  public function testUpdateShipmentType() {
    $shipment_type = $this->createEntity('commerce_shipment_type', [
      'id' => 'foo',
      'label' => 'Foo label',
    ]);

    $this->drupalGet('admin/commerce/config/shipment-types/foo/edit');
    $edit = [
      'label' => $this->randomMachineName(8),
    ];
    $this->submitForm($edit, 'Save');

    $changed = ShipmentType::load($shipment_type->id());
    $this->assertEquals($edit['label'], $changed->label());
  }

  /**
   * Tests deleting a shipment type.
   */
  public function testDeleteShipmentType() {
    $type = $this->createEntity('commerce_shipment_type', [
      'id' => 'foo',
      'label' => 'Foo label',
    ]);

    // Create a shipment.
    $shipment = $this->createEntity('commerce_shipment', [
      'type' => 'foo',
      'order_id' => 10,
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'Test',
          'quantity' => 1,
          'weight' => new Weight(0, 'g'),
          'declared_value' => new Price('1', 'USD'),
        ]),
      ],
    ]);

    // Try to delete the shipment type.
    $this->drupalGet('admin/commerce/config/shipment-types/foo/delete');
    $this->assertSession()->pageTextContains(t('@type is used by 1 shipment on your site. You can not remove this shipment type until you have removed all of the @type shipments.', ['@type' => $type->label()]));
    $this->assertSession()->pageTextNotContains('This action cannot be undone.');
    $this->assertSession()->pageTextNotContains('The shipment type deletion confirmation form is not available');

    // Deleting the shipment type when its not being referenced by a shipment.
    $shipment->delete();
    $this->drupalGet('admin/commerce/config/shipment-types/foo/delete');
    $this->assertSession()->pageTextContains(t('Are you sure you want to delete the shipment type @type?', ['@type' => $type->label()]));
    $this->saveHtmlOutput();
    $this->assertSession()->pageTextContains('This action cannot be undone.');
    $this->submitForm([], 'Delete');
    $type_exists = (bool) ShipmentType::load($type->id());
    $this->assertEmpty($type_exists);
  }

}
