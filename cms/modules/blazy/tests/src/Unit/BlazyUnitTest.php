<?php

namespace Drupal\Tests\blazy\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\blazy\Blazy;
use Drupal\blazy\Dejavu\BlazyDefault;
use Drupal\Tests\blazy\Traits\BlazyUnitTestTrait;
use Drupal\Tests\blazy\Traits\BlazyManagerUnitTestTrait;

/**
 * @coversDefaultClass \Drupal\blazy\Blazy
 *
 * @group blazy
 */
class BlazyUnitTest extends UnitTestCase {

  use BlazyUnitTestTrait;
  use BlazyManagerUnitTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpVariables();
    $this->setUpUnitServices();
    $this->setUpUnitContainer();
    $this->setUpMockImage();
  }

  /**
   * Test \Drupal\blazy\Blazy\widthFromDescriptors.
   *
   * @param string $data
   *   The input data which can be string, or integer.
   * @param mixed|bool|int $expected
   *   The expected output.
   *
   * @covers ::widthFromDescriptors
   * @dataProvider providerTestWidthFromDescriptors
   */
  public function testWidthFromDescriptors($data, $expected) {
    $result = Blazy::widthFromDescriptors($data);
    $this->assertSame($result, $expected);
  }

  /**
   * Provide test cases for ::testWidthFromDescriptors().
   */
  public function providerTestWidthFromDescriptors() {
    return [
      [1024, 1024],
      ['1024', 1024],
      ['769w', 769],
      ['640w 2x', 640],
      ['2x 640w', 640],
      ['xYz123', FALSE],
    ];
  }

  /**
   * Tests \Drupal\blazy\Blazy\buildIframeAttributes.
   *
   * @param array $data
   *   The input data which can be string, or integer.
   * @param mixed|bool|int $expected
   *   The expected output.
   *
   * @covers ::buildIframeAttributes
   * @covers \Drupal\blazy\Dejavu\BlazyDefault::entitySettings
   * @dataProvider providerTestBuildIframeAttributes
   */
  public function testBuildIframeAttributes(array $data, $expected) {
    $variables = ['attributes' => [], 'image' => []];
    $settings  = BlazyDefault::entitySettings();

    $settings['embed_url'] = '//www.youtube.com/watch?v=E03HFA923kw';
    $settings['scheme']    = 'youtube';
    $settings['type']      = 'video';

    $this->assertArrayHasKey('iframe_lazy', $settings);

    $variables['settings'] = array_merge($settings, $data);
    Blazy::buildIframeAttributes($variables);

    $this->assertNotEmpty($variables[$expected]);
  }

  /**
   * Provide test cases for ::testBuildIframeAttributes().
   */
  public function providerTestBuildIframeAttributes() {
    return [
      [
        [
          'media_switch' => 'media',
          'ratio' => 'fluid',
        ],
        'iframe_attributes',
      ],
      [
        [
          'media_switch' => '',
          'ratio' => '',
          'width' => 640,
          'height' => 360,
        ],
        'iframe_attributes',
      ],
    ];
  }

  /**
   * Tests building Blazy attributes.
   *
   * @param array $settings
   *   The settings being tested.
   * @param object $item
   *   Whether to provide image item, or not.
   * @param bool $expected_image
   *   Whether to expect an image, or not.
   * @param bool $expected_iframe
   *   Whether to expect an iframe, or not.
   *
   * @covers \Drupal\blazy\Blazy::buildAttributes
   * @covers \Drupal\blazy\Blazy::buildBreakpointAttributes
   * @covers \Drupal\blazy\Blazy::buildUrl
   * @covers \Drupal\blazy\Dejavu\BlazyDefault::entitySettings
   * @dataProvider providerBuildAttributes
   */
  public function testBuildAttributes(array $settings, $item, $expected_image, $expected_iframe) {
    $content   = [];
    $variables = ['attributes' => []];
    $build     = $this->data;
    $settings  = array_merge($build['settings'], $settings);

    $settings['breakpoints']     = [];
    $settings['blazy']           = TRUE;
    $settings['lazy']            = 'blazy';
    $settings['image_style']     = '';
    $settings['thumbnail_style'] = '';

    if (!empty($settings['embed_url'])) {
      $settings = array_merge(BlazyDefault::entitySettings(), $settings);
    }

    $variables['element']['#item'] = $item == TRUE ? $this->testItem : NULL;
    $variables['element']['#settings'] = $settings;

    Blazy::buildAttributes($variables);

    $image = $expected_image == TRUE ? !empty($variables['image']) : empty($variables['image']);
    $iframe = $expected_iframe == TRUE ? !empty($variables['iframe_attributes']) : empty($variables['iframe_attributes']);

    $this->assertTrue($image);
    $this->assertTrue($iframe);

    $this->assertEquals($settings['blazy'], $variables['settings']['blazy']);
  }

  /**
   * Provider for ::testBuildAttributes.
   */
  public function providerBuildAttributes() {
    $breakpoints = $this->getDataBreakpoints();
    $uri = 'public://example.jpg';

    $data[] = [
      [
        'background' => FALSE,
        'uri' => '',
      ],
      TRUE,
      FALSE,
      FALSE,
    ];
    $data[] = [
      [
        'background' => FALSE,
        'responsive_image_style_id' => 'blazy_responsive_test',
        'uri' => $uri,
      ],
      TRUE,
      TRUE,
      FALSE,
    ];
    $data[] = [
      [
        'background' => TRUE,
        'uri' => $uri,
      ],
      TRUE,
      FALSE,
      FALSE,
    ];
    $data[] = [
      [
        'background' => FALSE,
        'ratio' => 'fluid',
        'sizes' => '100w',
        'width' => 640,
        'height' => 360,
        'uri' => $uri,
      ],
      TRUE,
      TRUE,
      FALSE,
    ];
    $data[] = [
      [
        'background' => FALSE,
        'embed_url' => '//www.youtube.com/watch?v=E03HFA923kw',
        'media_switch' => 'media',
        'ratio' => 'fluid',
        'sizes' => '100w',
        'scheme' => 'youtube',
        'type' => 'video',
        'uri' => $uri,
      ],
      TRUE,
      TRUE,
      TRUE,
    ];

    return $data;
  }

  /**
   * Tests BlazyManager image with lightbox support.
   *
   * This is here as we need file_create_url() for both Blazy and its lightbox.
   *
   * @param array $settings
   *   The settings being tested.
   *
   * @covers \Drupal\blazy\BlazyManager::preRenderImage
   * @covers \Drupal\blazy\BlazyLightbox::build
   * @covers \Drupal\blazy\BlazyLightbox::buildCaptions
   * @dataProvider providerTestPreRenderImageLightbox
   */
  public function testPreRenderImageLightbox(array $settings = []) {
    $build                       = $this->data;
    $settings['count']           = $this->maxItems;
    $settings['uri']             = $this->uri;
    $settings['box_style']       = '';
    $settings['box_media_style'] = '';
    $build['settings']           = array_merge($build['settings'], $settings);

    foreach (['caption', 'media', 'wrapper'] as $key) {
      $build['settings'][$key . '_attributes']['class'][] = $key . '-test';
    }

    $element = $this->doPreRenderImage($build);

    if ($settings['media_switch'] == 'content') {
      $this->assertEquals($settings['content_url'], $element['#url']);
      $this->assertArrayHasKey('#url', $element);
    }
    else {
      $this->assertArrayHasKey('data-' . $settings['media_switch'] . '-trigger', $element['#url_attributes']);
      $this->assertArrayHasKey('#url', $element);
    }
  }

  /**
   * Provide test cases for ::testPreRenderImageLightbox().
   *
   * @return array
   *   An array of tested data.
   */
  public function providerTestPreRenderImageLightbox() {
    $data[] = [
      [
        'box_caption' => '',
        'content_url' => 'node/1',
        'dimension' => '',
        'lightbox' => FALSE,
        'media_switch' => 'content',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'auto',
        'lightbox' => TRUE,
        'media_switch' => 'colorbox',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'alt',
        'lightbox' => TRUE,
        'media_switch' => 'colorbox',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'title',
        'lightbox' => TRUE,
        'media_switch' => 'photobox',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'alt_title',
        'lightbox' => TRUE,
        'media_switch' => 'colorbox',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'title_alt',
        'lightbox' => TRUE,
        'media_switch' => 'photobox',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'entity_title',
        'lightbox' => TRUE,
        'media_switch' => 'photobox',
        'type' => 'image',
      ],
    ];
    $data[] = [
      [
        'box_caption' => 'custom',
        'box_caption_custom' => '[node:field_text_multiple]',
        'dimension' => '640x360',
        'embed_url' => '//www.youtube.com/watch?v=E03HFA923kw',
        'lightbox' => TRUE,
        'media_switch' => 'photobox',
        'scheme' => 'youtube',
        'type' => 'video',
      ],
    ];

    return $data;
  }

}

namespace Drupal\blazy;

if (!function_exists('file_create_url')) {

  /**
   * Dummy function.
   */
  function file_create_url() {
    // Empty block to satisfy coder.
  }

}
