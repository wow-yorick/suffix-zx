<?php

namespace Drupal\Tests\blazy\Unit\Form;

use Drupal\blazy\Form\BlazyAdmin;
use Drupal\Tests\UnitTestCase;
use Drupal\Tests\blazy\Traits\BlazyUnitTestTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the Blazy admin form.
 *
 * @coversDefaultClass \Drupal\blazy\Form\BlazyAdmin
 * @group blazy
 */
class BlazyAdminUnitTest extends UnitTestCase {

  use BlazyUnitTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityDisplayRepository = $this->getMock('\Drupal\Core\Entity\EntityDisplayRepositoryInterface');
    $this->typedConfig = $this->getMock('\Drupal\Core\Config\TypedConfigManagerInterface');
    $this->blazyManager = $this->getMock('\Drupal\blazy\BlazyManagerInterface');
  }

  /**
   * @covers ::create
   * @covers ::__construct
   * @covers ::getEntityDisplayRepository
   * @covers ::getTypedConfig
   * @covers ::blazyManager
   */
  public function testBlazyAdminCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $exception = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;

    $map = [
      ['entity_display.repository', $exception, $this->entityDisplayRepository],
      ['config.typed', $exception, $this->typedConfig],
      ['blazy.manager', $exception, $this->blazyManager],
    ];

    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $blazyAdmin = BlazyAdmin::create($container);
    $this->assertInstanceOf(BlazyAdmin::class, $blazyAdmin);

    $this->assertInstanceOf('\Drupal\Core\Entity\EntityDisplayRepositoryInterface', $blazyAdmin->getEntityDisplayRepository());
    $this->assertInstanceOf('\Drupal\Core\Config\TypedConfigManagerInterface', $blazyAdmin->getTypedConfig());
    $this->assertInstanceOf('\Drupal\blazy\BlazyManagerInterface', $blazyAdmin->blazyManager());
  }

}
