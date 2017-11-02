<?php

namespace Drupal\Tests\blazy\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Tests\blazy\Traits\BlazyUnitTestTrait;
use Drupal\Tests\blazy\Traits\BlazyManagerUnitTestTrait;

/**
 * @coversDefaultClass \Drupal\blazy\BlazyManager
 *
 * @group blazy
 */
class BlazyManagerUnitTest extends UnitTestCase {

  use BlazyUnitTestTrait;
  use BlazyManagerUnitTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpUnitServices();
    $this->setUpUnitContainer();
    $this->setUpUnitImages();

    $this->blazyManager->setLightboxes('blazy_test');
  }

  /**
   * Tests cases for various methods.
   *
   * @covers ::getEntityTypeManager
   * @covers ::getModuleHandler
   * @covers ::getRenderer
   * @covers ::getCache
   * @covers ::getConfigFactory
   */
  public function testBlazyManagerServiceInstances() {
    $this->assertInstanceOf('\Drupal\Core\Entity\EntityTypeManagerInterface', $this->blazyManager->getEntityTypeManager());
    $this->assertInstanceOf('\Drupal\Core\Extension\ModuleHandlerInterface', $this->blazyManager->getModuleHandler());
    $this->assertInstanceOf('\Drupal\Core\Render\RendererInterface', $this->blazyManager->getRenderer());
    $this->assertInstanceOf('\Drupal\Core\Config\ConfigFactoryInterface', $this->blazyManager->getConfigFactory());
    $this->assertInstanceOf('\Drupal\Core\Cache\CacheBackendInterface', $this->blazyManager->getCache());
  }

  /**
   * Tests cases for config.
   *
   * @covers ::configLoad
   */
  public function testConfigLoad() {
    $blazy = $this->blazyManager->configLoad('blazy');
    $this->assertArrayHasKey('loadInvisible', $blazy);

    $admin_css = $this->blazyManager->configLoad('admin_css', 'blazy.settings');
    $this->assertTrue($admin_css, 'Blazy admin CSS is enabled by default.');

    $responsive_image = $this->blazyManager->configLoad('responsive_image');
    $this->assertTrue($responsive_image, 'Responsive image was disabled by default, yet enabled now.');
  }

  /**
   * Tests cases for config.
   *
   * @covers ::entityLoad
   * @covers ::entityLoadMultiple
   */
  public function testEntityLoadImageStyle() {
    $styles = $this->setUpImageStyle();

    $ids = array_keys($styles);
    $multiple = $this->blazyManager->entityLoadMultiple('image_style', $ids);
    $this->assertArrayHasKey('large', $multiple);

    $expected = $this->blazyManager->entityLoad('large', 'image_style');
    $this->assertEquals($expected, $multiple['large']);
  }

  /**
   * Tests cases for config.
   *
   * @covers ::entityLoad
   * @covers ::entityLoadMultiple
   */
  public function testEntityLoadResponsiveImageStyle() {
    $styles = $this->setUpResponsiveImageStyle();

    $ids = array_keys($styles);
    $multiple = $this->blazyManager->entityLoadMultiple('responsive_image_style', $ids);
    $this->assertArrayHasKey('blazy_picture_test', $multiple);

    $expected = $this->blazyManager->entityLoad('blazy_picture_test', 'responsive_image_style');
    $this->assertEquals($expected, $multiple['blazy_picture_test']);
  }

  /**
   * Test \Drupal\blazy\BlazyManager::cleanUpBreakpoints().
   *
   * @covers ::cleanUpBreakpoints
   * @dataProvider providerTestCleanUpBreakpoints
   */
  public function testCleanUpBreakpoints($breakpoints, $expected_breakpoints, $blazy, $expected_blazy) {
    $settings['blazy'] = $blazy;
    $settings['breakpoints'] = $breakpoints;

    $this->blazyManager->cleanUpBreakpoints($settings);
    $this->assertEquals($expected_breakpoints, $settings['breakpoints']);

    // Verify that Blazy is activated by breakpoints.
    $this->assertEquals($expected_blazy, $settings['blazy']);
  }

  /**
   * Provider for ::testCleanUpBreakpoints().
   */
  public function providerTestCleanUpBreakpoints() {
    return [
      'empty' => [
        [],
        [],
        FALSE,
        FALSE,
      ],
      'not so empty' => [
        $this->getEmptyBreakpoints(),
        [],
        FALSE,
        FALSE,
      ],
      'mixed empty' => [
        $this->getDataBreakpoints(),
        $this->getDataBreakpoints(TRUE),
        FALSE,
        TRUE,
      ],
      'mixed empty blazy enabled first' => [
        $this->getDataBreakpoints(),
        $this->getDataBreakpoints(TRUE),
        FALSE,
        TRUE,
      ],
    ];
  }

  /**
   * Tests for \Drupal\blazy\BlazyManager::preRenderImage().
   *
   * @covers ::getImage
   * @covers ::preRenderImage
   * @dataProvider providerTestPreRenderImage
   */
  public function testPreRenderImage($item, $uri, $content, $expected_image, $expected_render) {
    $build = [];

    $build['item'] = $item ? $this->testItem : [];
    $build['content'] = $content;
    $build['settings']['uri'] = $uri;

    if ($item) {
      $build['item']->_attributes['data-blazy-test'] = TRUE;
    }

    $image = $this->blazyManager->getImage($build);

    $build_image['#build']['settings'] = array_merge($this->getCacheMetaData(), $build['settings']);
    $build_image['#build']['item'] = $build['item'];

    $pre_render = $this->blazyManager->preRenderImage($build_image);

    $check_image = !$expected_image ? empty($image) : !empty($image);
    $this->assertTrue($check_image);

    $check_pre_render = !$expected_render ? TRUE : !empty($pre_render);
    $this->assertTrue($check_pre_render);
  }

  /**
   * Provide test cases for ::testPreRenderImage().
   *
   * @return array
   *   An array of tested data.
   */
  public function providerTestPreRenderImage() {
    $data[] = [
      FALSE,
      '',
      '',
      FALSE,
      FALSE,
    ];
    $data[] = [
      TRUE,
      '',
      '',
      TRUE,
      TRUE,
    ];
    $data[] = [
      TRUE,
      'core/misc/druplicon.png',
      '',
      TRUE,
      TRUE,
    ];
    $data[] = [
      TRUE,
      'core/misc/druplicon.png',
      '<iframe src="//www.youtube.com/watch?v=E03HFA923kw" class="b-lazy"></iframe>',
      TRUE,
      FALSE,
    ];

    return $data;
  }

  /**
   * Tests cases for attachments.
   *
   * @covers ::attach
   * @depends testConfigLoad
   */
  public function testAttach() {
    $attach = [
      'blazy'        => TRUE,
      'grid'         => 0,
      'media'        => TRUE,
      'media_switch' => 'media',
      'ratio'        => 'fluid',
      'style'        => 'column',
    ];

    $attachments = $this->blazyManager->attach($attach);

    $this->assertArrayHasKey('library', $attachments);
    $this->assertArrayHasKey('blazy', $attachments['drupalSettings']);

    $this->assertContains('blazy/media', $attachments['library']);
    $this->assertContains('blazy/ratio', $attachments['library']);
  }

  /**
   * Tests cases for lightboxes.
   *
   * @covers ::getLightboxes
   * @covers ::setLightboxes
   */
  public function testGetLightboxes() {
    $lightboxes = $this->blazyManager->getLightboxes();

    $this->assertNotContains('nixbox', $lightboxes);
  }

}

namespace Drupal\blazy;

if (!function_exists('blazy_test_theme')) {

  /**
   * Dummy function.
   */
  function blazy_test_theme() {
    // Empty block to satisfy coder.
  }

}

if (!function_exists('colorbox_theme')) {

  /**
   * Dummy function.
   */
  function colorbox_theme() {
    // Empty block to satisfy coder.
  }

}

if (!function_exists('photobox_theme')) {

  /**
   * Dummy function.
   */
  function photobox_theme() {
    // Empty block to satisfy coder.
  }

}
