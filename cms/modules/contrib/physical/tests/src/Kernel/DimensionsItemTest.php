<?php

namespace Drupal\Tests\physical\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\physical\Length;

/**
 * Tests the 'physical_dimensions' field type.
 *
 * @group physical
 */
class DimensionsItemTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'physical',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_dimensions',
      'entity_type' => 'entity_test',
      'type' => 'physical_dimensions',
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'test_dimensions',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ]);
    $field->save();
  }

  /**
   * Tests the field.
   */
  public function testField() {
    /** @var \Drupal\entity_test\Entity\EntityTest $entity */
    $entity = EntityTest::create([
      'test_dimensions' => [
        'length' => '5',
        'width' => '7',
        'height' => '2',
        'unit' => 'in',
      ],
    ]);
    $entity->save();
    $entity = $this->reloadEntity($entity);

    /** @var \Drupal\physical\Plugin\Field\FieldType\DimensionItem $item */
    $item = $entity->get('test_dimensions')->first();

    $length = $item->getLength();
    $this->assertInstanceOf(Length::class, $length);
    $this->assertEquals('5', $length->getNumber());
    $this->assertEquals('in', $length->getUnit());

    $width = $item->getWidth();
    $this->assertInstanceOf(Length::class, $width);
    $this->assertEquals('7', $width->getNumber());
    $this->assertEquals('in', $width->getUnit());

    $height = $item->getHeight();
    $this->assertInstanceOf(Length::class, $height);
    $this->assertEquals('2', $height->getNumber());
    $this->assertEquals('in', $height->getUnit());
  }

}
