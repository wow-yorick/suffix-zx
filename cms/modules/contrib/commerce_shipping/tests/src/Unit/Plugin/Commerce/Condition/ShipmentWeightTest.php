<?php

namespace Drupal\Tests\commerce_shipping\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\Condition\ShipmentWeight;
use Drupal\physical\Weight;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\Plugin\Commerce\Condition\ShipmentWeight
 * @group commerce
 */
class ShipmentWeightTest extends UnitTestCase {

  /**
   * ::covers evaluate.
   */
  public function testMismatchedUnits() {
    $condition = new ShipmentWeight([
      'operator' => '==',
      'weight' => [
        'number' => '1000.00',
        'unit' => 'g',
      ],
    ], 'shipment_weight', ['entity_type' => 'commerce_shipment']);
    $shipment = $this->prophesize(ShipmentInterface::class);
    $shipment->getEntityTypeId()->willReturn('commerce_shipment');
    $shipment->getWeight()->willReturn(new Weight('1.00', 'kg'));
    $shipment = $shipment->reveal();

    $this->assertTrue($condition->evaluate($shipment));
  }

  /**
   * ::covers evaluate.
   *
   * @dataProvider totalWeightProvider
   */
  public function testEvaluate($operator, $weight, $given_weight, $result) {
    $condition = new ShipmentWeight([
      'operator' => $operator,
      'weight' => [
        'number' => $weight,
        'unit' => 'kg',
      ],
    ], 'shipment_weight', ['entity_type' => 'commerce_shipment']);
    $shipment = $this->prophesize(ShipmentInterface::class);
    $shipment->getEntityTypeId()->willReturn('commerce_shipment');
    $shipment->getWeight()->willReturn(new Weight($given_weight, 'kg'));
    $shipment = $shipment->reveal();

    $this->assertEquals($result, $condition->evaluate($shipment));
  }

  /**
   * Data provider for ::testEvaluate.
   *
   * @return array
   *   A list of testEvaluate function arguments.
   */
  public function totalWeightProvider() {
    return [
      ['>', 10, 5, FALSE],
      ['>', 10, 10, FALSE],
      ['>', 10, 11, TRUE],

      ['>=', 10, 5, FALSE],
      ['>=', 10, 10, TRUE],
      ['>=', 10, 11, TRUE],

      ['<', 10, 5, TRUE],
      ['<', 10, 10, FALSE],
      ['<', 10, 11, FALSE],

      ['<=', 10, 5, TRUE],
      ['<=', 10, 10, TRUE],
      ['<=', 10, 11, FALSE],

      ['==', 10, 5, FALSE],
      ['==', 10, 10, TRUE],
      ['==', 10, 11, FALSE],
    ];
  }

}
