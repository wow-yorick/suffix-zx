<?php

namespace Drupal\slick_extras;

use Drupal\slick\SlickSkinInterface;

/**
 * Implements SlickSkinInterface as registered via hook_slick_skins_info().
 */
class SlickExtrasSkin implements SlickSkinInterface {

  /**
   * {@inheritdoc}
   */
  public function skins() {
    $slick = base_path() . drupal_get_path('module', 'slick');
    $path  = base_path() . drupal_get_path('module', 'slick_extras');
    $skins = [
      'd3-back' => [
        'name' => t('X 3d back'),
        'group' => 'main',
        'provider' => 'slick_extras',
        'css' => [
          'theme' => [
            $path . '/css/theme/slick.theme--d3-back.css' => [],
          ],
        ],
        'description' => t('Adds 3d view with focal point at back, works best with 3 slidesToShow, and caption below.'),
      ],
      'boxed' => [
        'name' => t('X Boxed'),
        'group' => 'main',
        'provider' => 'slick_extras',
        'description' => t('Adds margins to the sides of slick-list revealing arrows.'),
        'css' => [
          'theme' => [
            $path . '/css/theme/slick.theme--boxed.css' => [],
          ],
        ],
      ],
      'boxed-carousel' => [
        'name' => t('X Box carousel'),
        'group' => 'main',
        'provider' => 'slick_extras',
        'description' => t('Carousel that has margins, alternative to centerMode.'),
        'css' => [
          'theme' => [
            $path . '/css/theme/slick.theme--boxed.css' => [],
            $path . '/css/theme/slick.theme--boxed--carousel.css' => [],
          ],
        ],
      ],
      'boxed-split' => [
        'name' => t('X Box split'),
        'group' => 'main',
        'provider' => 'slick_extras',
        'description' => t('Adds margins and split caption and image.'),
        'css' => [
          'theme' => [
            $path . '/css/theme/slick.theme--boxed.css' => [],
            $slick . '/css/theme/slick.theme--split.css' => [],
          ],
        ],
      ],
      'rounded' => [
        'name' => t('X Rounded'),
        'group' => 'main',
        'provider' => 'slick_extras',
        'description' => t('Rounds the .slide__image, great for 3-5 visible-slides carousel.'),
        'css' => [
          'theme' => [
            $path . '/css/theme/slick.theme--rounded.css' => [],
          ],
        ],
      ],
      'vtabs' => [
        'name' => t('X VTabs'),
        'group' => 'thumbnail',
        'provider' => 'slick_extras',
        'description' => t('Adds a vertical tabs like thumbnail navigation.'),
        'css' => [
          'theme' => [
            $path . '/css/theme/slick.theme--vtabs.css' => [],
          ],
        ],
      ],
    ];

    return $skins;
  }

}
