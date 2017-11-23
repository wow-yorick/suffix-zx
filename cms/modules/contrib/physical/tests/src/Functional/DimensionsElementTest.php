<?php

namespace Drupal\Tests\physical\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the physical_dimensions form element.
 *
 * @group physical
 */
class DimensionsElementTest extends BrowserTestBase {

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
    $this->drupalGet('/physical/dimensions_test_form/TRUE');
    $this->assertSession()->fieldExists('dimensions[length]');
    $this->assertSession()->fieldExists('dimensions[width]');
    $this->assertSession()->fieldExists('dimensions[height]');
    // Default value.
    $this->assertSession()->fieldValueEquals('dimensions[length]', '1.92');
    $this->assertSession()->fieldValueEquals('dimensions[width]', '2.5');
    $this->assertSession()->fieldValueEquals('dimensions[height]', '2.1');

    // Invalid submit.
    $edit = [
      'dimensions[length]' => 'invalid',
      'dimensions[width]' => '2.5',
      'dimensions[height]' => '2.1',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Length must be a number.');

    // Valid submit.
    $edit = [
      'dimensions[length]' => '10.99',
      'dimensions[width]' => '2.5',
      'dimensions[height]' => '2.1',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Length: "10.99", width: "2.5", height: "2.1", unit: "m".');
  }

  /**
   * Tests the element with multiple units.
   */
  public function testMultipleCurrency() {
    $this->drupalGet('/physical/dimensions_test_form');
    $this->assertSession()->fieldExists('dimensions[length]');
    $this->assertSession()->fieldExists('dimensions[width]');
    $this->assertSession()->fieldExists('dimensions[height]');
    $this->assertSession()->fieldExists('dimensions[unit]');
    // Default value.
    $this->assertSession()->fieldValueEquals('dimensions[length]', '1.92');
    $this->assertSession()->fieldValueEquals('dimensions[width]', '2.5');
    $this->assertSession()->fieldValueEquals('dimensions[height]', '2.1');
    $this->assertSession()->optionExists('dimensions[unit]', 'in');
    $element = $this->assertSession()->optionExists('dimensions[unit]', 'm');
    $this->assertTrue($element->isSelected());

    // Invalid submit.
    $edit = [
      'dimensions[length]' => 'invalid',
      'dimensions[width]' => '2.5',
      'dimensions[height]' => '2.1',
      'dimensions[unit]' => 'm',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Length must be a number.');

    // Valid submit.
    $edit = [
      'dimensions[length]' => '10.99',
      'dimensions[width]' => '2.5',
      'dimensions[height]' => '2.1',
      'dimensions[unit]' => 'in',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Length: "10.99", width: "2.5", height: "2.1", unit: "in".');
  }

  /**
   * Tests the element with a non-English length format.
   */
  public function testLocalFormat() {
    // French uses a comma as a decimal separator.
    ConfigurableLanguage::createFromLangcode('fr')->save();
    $this->config('system.site')->set('default_langcode', 'fr')->save();

    $this->drupalGet('/physical/dimensions_test_form');
    $this->assertSession()->fieldExists('dimensions[length]');
    $this->assertSession()->fieldExists('dimensions[width]');
    $this->assertSession()->fieldExists('dimensions[height]');
    // Default value.
    $this->assertSession()->fieldValueEquals('dimensions[length]', '1,92');
    $this->assertSession()->fieldValueEquals('dimensions[width]', '2,5');
    $this->assertSession()->fieldValueEquals('dimensions[height]', '2,1');

    // Valid submit.
    $edit = [
      'dimensions[length]' => '10,99',
      'dimensions[width]' => '2,5',
      'dimensions[height]' => '2,1',
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Length: "10.99", width: "2.5", height: "2.1", unit: "m".');
  }

}
