<?php

namespace Drupal\Tests\physical\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the physical_measurement form element.
 *
 * @group physical
 */
class MeasurementElementTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'physical_test',
    'language',
  ];

  /**
   * Tests the element with a single unit.
   */
  public function testSingleUnit() {
    $this->drupalGet('/physical/measurement_test_form/TRUE');
    $this->assertSession()->fieldExists('height[number]');
    // Default value.
    $this->assertSession()->fieldValueEquals('height[number]', '1.92');

    // Invalid submit.
    $edit = [
      'height[number]' => 'invalid',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Height must be a number.');

    // Valid submit.
    $edit = [
      'height[number]' => '10.99',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('The number is "10.99" and the unit is "m".');
  }

  /**
   * Tests the element with multiple units.
   */
  public function testMultipleCurrency() {
    $this->drupalGet('/physical/measurement_test_form');
    $this->assertSession()->fieldExists('height[number]');
    $this->assertSession()->fieldExists('height[unit]');
    // Default value.
    $this->assertSession()->fieldValueEquals('height[number]', '1.92');
    $this->assertSession()->optionExists('height[unit]', 'in');
    $element = $this->assertSession()->optionExists('height[unit]', 'm');
    $this->assertTrue($element->isSelected());

    // Invalid submit.
    $edit = [
      'height[number]' => 'invalid',
      'height[unit]' => 'm',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Height must be a number.');

    // Valid submit.
    $edit = [
      'height[number]' => '10.99',
      'height[unit]' => 'in',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('The number is "10.99" and the unit is "in".');
  }

  /**
   * Tests the element with a non-English number format.
   */
  public function testLocalFormat() {
    // French uses a comma as a decimal separator.
    ConfigurableLanguage::createFromLangcode('fr')->save();
    $this->config('system.site')->set('default_langcode', 'fr')->save();

    $this->drupalGet('/physical/measurement_test_form');
    $this->assertSession()->fieldExists('height[number]');
    // Default value.
    $this->assertSession()->fieldValueEquals('height[number]', '1,92');

    // Valid submit.
    $edit = [
      'height[number]' => '10,99',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('The number is "10.99" and the unit is "m".');
  }

}
