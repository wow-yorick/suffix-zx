<?php

namespace Drupal\Tests\commerce_shipping\Functional;

use Drupal\commerce_shipping\Entity\PackageType;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the package type UI.
 *
 * @group commerce_shipping
 */
class PackageTypeTest extends CommerceBrowserTestBase {

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
      'administer commerce_package_type',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests creating a package type.
   */
  public function testPackageTypeCreation() {
    $this->drupalGet('admin/commerce/config/package-types');
    $this->getSession()->getPage()->clickLink('Add package type');
    $this->assertSession()->addressEquals('admin/commerce/config/package-types/add');

    $edit = [
      'label' => 'Example',
      'dimensions[length]' => '20',
      'dimensions[width]' => '10',
      'dimensions[height]' => '10',
      'dimensions[unit]' => 'in',
      'weight[number]' => '10',
      'weight[unit]' => 'oz',
      // Setting the 'id' can fail if focus switches to another field.
      // This is a bug in the machine name JS that can be reproduced manually.
      'id' => 'example',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->addressEquals('admin/commerce/config/package-types');
    $this->assertSession()->responseContains('Example');

    $package_type = PackageType::load('example');
    $this->assertEquals('example', $package_type->id());
    $this->assertEquals('Example', $package_type->label());
    $this->assertEquals('20', $package_type->getDimensions()['length']);
    $this->assertEquals('10', $package_type->getDimensions()['width']);
    $this->assertEquals('10', $package_type->getDimensions()['height']);
    $this->assertEquals('in', $package_type->getDimensions()['unit']);
    $this->assertEquals('10', $package_type->getWeight()['number']);
    $this->assertEquals('oz', $package_type->getWeight()['unit']);
  }

  /**
   * Testing editing a package type.
   */
  public function testPackageTypeEditing() {
    $values = [
      'id' => 'edit_example',
      'label' => 'Edit example',
      'dimensions' => [
        'length' => '15',
        'width' => '15',
        'height' => '15',
        'unit' => 'm',
      ],
      'weight' => [
        'number' => 0,
        'unit' => 'g',
      ],
    ];
    $package_type = $this->createEntity('commerce_package_type', $values);

    $this->drupalGet('admin/commerce/config/package-types/manage/' . $package_type->id());
    $edit = [
      'dimensions[length]' => '20',
      'weight[number]' => '2',
      'weight[unit]' => 'lb',
    ];
    $this->submitForm($edit, 'Save');

    \Drupal::entityTypeManager()->getStorage('commerce_package_type')->resetCache();
    $package_type = PackageType::load('edit_example');
    $this->assertEquals('edit_example', $package_type->id());
    $this->assertEquals('Edit example', $package_type->label());
    $this->assertEquals('20', $package_type->getDimensions()['length']);
    $this->assertEquals('15', $package_type->getDimensions()['width']);
    $this->assertEquals('15', $package_type->getDimensions()['height']);
    $this->assertEquals('m', $package_type->getDimensions()['unit']);
    $this->assertEquals('2', $package_type->getWeight()['number']);
    $this->assertEquals('lb', $package_type->getWeight()['unit']);
  }

  /**
   * Tests deleting a package type.
   */
  public function testPackageTypeDeletion() {
    $package_type = $this->createEntity('commerce_package_type', [
      'id' => 'for_deletion',
      'label' => 'For deletion',
      'dimensions' => [
        'length' => '15',
        'width' => '15',
        'height' => '15',
        'unit' => 'm',
      ],
      'weight' => [
        'number' => 0,
        'unit' => 'g',
      ],
    ]);
    $this->drupalGet('admin/commerce/config/package-types/manage/' . $package_type->id() . '/delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->addressEquals('admin/commerce/config/package-types');

    $package_type_exists = (bool) PackageType::load('for_deletion');
    $this->assertFalse($package_type_exists, 'The package type has been deleted from the database.');
  }

}
