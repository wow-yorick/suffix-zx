<?php

namespace Drupal\blazy\Form;

use Drupal\Core\Url;
use Drupal\Component\Utility\Unicode;

/**
 * A base for field formatter admin to have re-usable methods in one place.
 */
abstract class BlazyAdminFormatterBase extends BlazyAdminBase {

  /**
   * Returns re-usable image formatter form elements.
   */
  public function imageStyleForm(array &$form, $definition = []) {
    $is_responsive = function_exists('responsive_image_get_image_dimensions');

    if (empty($definition['no_image_style'])) {
      $form['image_style'] = $this->baseForm($definition)['image_style'];
    }

    if (!empty($definition['thumbnail_style'])) {
      $form['thumbnail_style'] = $this->baseForm($definition)['thumbnail_style'];
    }

    if ($is_responsive && !empty($definition['responsive_image'])) {
      $url = Url::fromRoute('entity.responsive_image_style.collection')->toString();
      $form['responsive_image_style'] = [
        '#type'        => 'select',
        '#title'       => $this->t('Responsive image'),
        '#options'     => $this->getResponsiveImageOptions(),
        '#description' => $this->t('Responsive image style for the main stage image is more reasonable for large images. Works with multi-serving IMG, or PICTURE element. Not compatible with breakpoints and aspect ratio, yet. Leave empty to disable. <a href=":url" target="_blank">Manage responsive image styles</a>.', [':url' => $url]),
        '#access'      => $this->getResponsiveImageOptions(),
        '#weight'      => -100,
      ];

      if (!empty($definition['background'])) {
        $form['background']['#states'] = $this->getState(static::STATE_RESPONSIVE_IMAGE_STYLE_DISABLED, $definition);
      }
    }

    if (!empty($definition['thumbnail_effect'])) {
      $form['thumbnail_effect'] = [
        '#type'    => 'select',
        '#title'   => $this->t('Thumbnail effect'),
        '#options' => isset($definition['thumbnail_effect']) ? $definition['thumbnail_effect'] : [],
        '#weight'  => -100,
      ];
    }
  }

  /**
   * Return the field formatter settings summary.
   *
   * @deprecated: To remove for self::getSettingsSummary() post full release so
   * to avoid unpredictable settings, and complication with form elements.
   */
  public function settingsSummary($plugin, $definition = []) {
    $definition = isset($definition) ? $definition : $plugin->getScopedFormElements();
    $definition['settings'] = isset($definition['settings']) ? $definition['settings'] : $plugin->getSettings();

    return $this->getSettingsSummary($definition);
  }

  /**
   * Return the field formatter settings summary.
   */
  public function getSettingsSummary($definition = []) {
    $summary = [];

    if (empty($definition['settings'])) {
      return $summary;
    }

    $this->getExcludedSettingsSummary($definition);

    $enforced = [
      'optionset',
      'cache',
      'skin',
      'view_mode',
      'override',
      'overridables',
      'style',
      'vanilla',
    ];

    $enforced    = isset($definition['enforced']) ? $definition['enforced'] : $enforced;
    $settings    = array_filter($definition['settings']);
    $breakpoints = isset($settings['breakpoints']) && is_array($settings['breakpoints']) ? array_filter($settings['breakpoints']) : [];

    foreach ($definition['settings'] as $key => $setting) {
      $title   = Unicode::ucfirst(str_replace('_', ' ', $key));
      $vanilla = !empty($settings['vanilla']);

      if ($key == 'breakpoints') {
        $widths = [];
        if ($breakpoints) {
          foreach ($breakpoints as $id => $breakpoint) {
            if (!empty($breakpoint['width'])) {
              $widths[] = $breakpoint['width'];
            }
          }
        }

        $title   = 'Breakpoints';
        $setting = $widths ? implode(', ', $widths) : 'none';
      }
      else {
        if ($vanilla && !in_array($key, $enforced)) {
          continue;
        }

        if ($key == 'override' && empty($setting)) {
          unset($settings['overridables']);
        }

        if (is_bool($setting) && $setting) {
          $setting = 'yes';
        }
        elseif (is_array($setting)) {
          $setting = array_filter($setting);
          if (!empty($setting)) {
            $setting = implode(', ', $setting);
          }
        }

        if ($key == 'cache') {
          $setting = $this->getCacheOptions()[$setting];
        }
      }

      if (empty($setting)) {
        continue;
      }

      if (isset($settings[$key]) && is_string($setting)) {
        $summary[] = $this->t('@title: <strong>@setting</strong>', [
          '@title'   => $title,
          '@setting' => $setting,
        ]);
      }
    }
    return $summary;
  }

  /**
   * Exclude the field formatter settings summary as required.
   */
  public function getExcludedSettingsSummary(array &$definition = []) {
    $settings     = &$definition['settings'];
    $excludes     = empty($definition['excludes']) ? [] : $definition['excludes'];
    $plugin_id    = isset($definition['plugin_id']) ? $definition['plugin_id'] : '';
    $blazy        = $plugin_id && strpos($plugin_id, 'blazy') !== FALSE;
    $image_styles = function_exists('image_style_options') ? image_style_options(TRUE) : [];
    $media_switch = empty($settings['media_switch']) ? '' : $settings['media_switch'];

    unset($image_styles['']);

    $excludes['current_view_mode'] = TRUE;

    if ($blazy) {
      $excludes['optionset'] = TRUE;
    }

    if ($media_switch != 'media') {
      $excludes['iframe_lazy'] = TRUE;
    }

    if (!empty($settings['responsive_image_style'])) {
      foreach (['ratio', 'breakpoints', 'background', 'sizes'] as $key) {
        $excludes[$key] = TRUE;
      }
    }

    if (empty($settings['grid'])) {
      foreach (['grid', 'grid_medium', 'grid_small', 'visible_items'] as $key) {
        $excludes[$key] = TRUE;
      }
    }

    // Remove exluded settings.
    foreach ($excludes as $key => $value) {
      if (isset($settings[$key])) {
        unset($settings[$key]);
      }
    }

    foreach ($settings as $key => $setting) {
      if ($key == 'style' || $key == 'responsive_image_style' || empty($settings[$key])) {
        continue;
      }
      if (strpos($key, 'style') !== FALSE && isset($image_styles[$settings[$key]])) {
        $settings[$key] = $image_styles[$settings[$key]];
      }
    }
  }

  /**
   * Returns available fields for select options.
   */
  public function getFieldOptions($target_bundles = [], $allowed_field_types = [], $entity_type = 'media', $target_type = '') {
    $options = [];
    $storage = $this->blazyManager()->getEntityTypeManager()->getStorage('field_config');

    // Fix for Views UI not recognizing Media bundles, unlike Formatters.
    if (empty($target_bundles)) {
      $bundle_service = \Drupal::service('entity_type.bundle.info');
      $target_bundles = $bundle_service->getBundleInfo($entity_type);
    }

    // Declutters options from less relevant options.
    $excludes = $this->getExcludedFieldOptions();

    foreach ($target_bundles as $bundle => $label) {
      if ($fields = $storage->loadByProperties(['entity_type' => $entity_type, 'bundle' => $bundle])) {
        foreach ((array) $fields as $field_name => $field) {
          if (in_array($field->getName(), $excludes)) {
            continue;
          }
          if (empty($allowed_field_types)) {
            $options[$field->getName()] = $field->getLabel();
          }
          elseif (in_array($field->getType(), $allowed_field_types)) {
            $options[$field->getName()] = $field->getLabel();
          }

          if (!empty($target_type) && ($field->getSetting('target_type') == $target_type)) {
            $options[$field->getName()] = $field->getLabel();
          }
        }
      }
    }

    return $options;
  }

  /**
   * Declutters options from less relevant options.
   */
  public function getExcludedFieldOptions() {
    $excludes = 'field_document_size field_id field_media_in_library field_mime_type field_source field_tweet_author field_tweet_id field_tweet_url field_media_video_embed_field field_instagram_shortcode field_instagram_url';
    $excludes = explode(' ', $excludes);
    $excludes = array_combine($excludes, $excludes);

    $this->blazyManager->getModuleHandler()->alter('blazy_excluded_field_options', $excludes);
    return $excludes;
  }

  /**
   * Returns Responsive image for select options.
   */
  public function getResponsiveImageOptions() {
    $options = [];
    if ($this->blazyManager()->getModuleHandler()->moduleExists('responsive_image')) {
      $image_styles = $this->blazyManager()->entityLoadMultiple('responsive_image_style');
      if (!empty($image_styles)) {
        foreach ($image_styles as $name => $image_style) {
          if ($image_style->hasImageStyleMappings()) {
            $options[$name] = strip_tags($image_style->label());
          }
        }
      }
    }
    return $options;
  }

}
