<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\ShippingRate
 * @group commerce_shipping
 */
class ShippingRateTest extends UnitTestCase {

  /**
   * The shipping rate.
   *
   * @var \Drupal\commerce_shipping\ShippingRate
   */
  protected $rate;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $service = new ShippingService('test', 'Test');
    $amount = new Price('10.00', 'USD');
    $delivery_date = DrupalDateTime::createFromArray([
      'year' => 2016,
      'month' => 11,
      'day' => 24,
    ], 'UTC', ['langcode' => 'en']);
    $this->rate = new ShippingRate('test_id', $service, $amount, $delivery_date, 'Arrives right away');
  }

  /**
   * @covers ::getId
   */
  public function testGetId() {
    $this->assertEquals('test_id', $this->rate->getId());
  }

  /**
   * @covers ::getService
   */
  public function testGetService() {
    $this->assertInstanceOf(ShippingService::class, $this->rate->getService());
    $this->assertEquals('test', $this->rate->getService()->getId());
  }

  /**
   * @covers ::getAmount
   */
  public function testGetAmount() {
    $this->assertInstanceOf(Price::class, $this->rate->getAmount());
    $this->assertEquals('10.00', $this->rate->getAmount()->getNumber());
  }

  /**
   * @covers ::getDeliveryDate
   */
  public function testGetDeliveryDate() {
    $this->assertInstanceOf(DrupalDateTime::class, $this->rate->getDeliveryDate());
    $this->assertEquals('2016-11-24', $this->rate->getDeliveryDate()->format('Y-m-d'));
  }

  /**
   * @covers ::getDeliveryTerms
   */
  public function testGetDeliveryTerms() {
    $this->assertEquals('Arrives right away', $this->rate->getDeliveryTerms());
  }

}
