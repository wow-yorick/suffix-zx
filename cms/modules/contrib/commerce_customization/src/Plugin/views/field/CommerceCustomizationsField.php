<?php

namespace Drupal\commerce_customization\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("commerce_customizations_field")
 */
class CommerceCustomizationsField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $order_item = $values->_entity;
    $data = $order_item->getData('commerce_customization');

    // @todo unhardcode the field name.
    $plugin_manager = \Drupal::service('plugin.manager.commerce_customization');
    $definitions = $plugin_manager->getDefinitions();

    $render = [];
    foreach ($data as $key => $customization) {

      // Ignore data that is not for plugins.
      if (!isset($customization['__plugin'])) {
        continue;
      }

      // Ignore if plugin doesn't.
      $plugin = $customization['__plugin'];
      if (!isset($definitions[$plugin])) {
        continue;
      }

      // Get a render for that plugin.
      $instance = $plugin_manager->createInstance($plugin);
      if ($plugin_render = $instance->render($customization)) {
        $render[] = $plugin_render;
      }
    }
    return $render;
  }

}
