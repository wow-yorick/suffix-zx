<?php

namespace Drupal\blazy_test\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\blazy\BlazyManagerInterface;
use Drupal\blazy\Dejavu\BlazyVideoTrait;
use Drupal\blazy\Dejavu\BlazyEntityReferenceBase;
use Drupal\blazy\Dejavu\BlazyDefault;
use Drupal\blazy\Plugin\Field\FieldFormatter\BlazyFormatterBaseTrait;
use Drupal\blazy_test\BlazyFormatterTestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Blazy Entity Reference' formatter.
 *
 * @FieldFormatter(
 *   id = "blazy_entity_test",
 *   label = @Translation("Blazy Entity Reference Test"),
 *   field_types = {"entity_reference", "file"}
 * )
 */
class BlazyTestEntityReferenceFormatterTest extends BlazyEntityReferenceBase implements ContainerFactoryPluginInterface {

  use BlazyVideoTrait;
  use BlazyFormatterBaseTrait;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a SlickMediaFormatter instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, BlazyManagerInterface $blazy_manager, BlazyFormatterTestInterface $formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
    $this->blazyManager  = $blazy_manager;
    $this->formatter     = $formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('blazy.manager'),
      $container->get('blazy_test.formatter')
    );
  }

  /**
   * Returns the blazy formatter.
   */
  public function formatter() {
    return $this->formatter;
  }

  /**
   * Returns the blazy admin.
   */
  public function manager() {
    return $this->formatter;
  }

  /**
   * Returns the blazy_test admin service shortcut.
   */
  public function admin() {
    return \Drupal::service('blazy_test.admin');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return BlazyDefault::extendedSettings() + BlazyDefault::gridSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entities = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($entities)) {
      return [];
    }

    // Collects specific settings to this formatter.
    $settings = $this->buildSettings();
    $build = ['settings' => $settings];

    $this->formatter->buildSettings($build, $items);

    // Build the elements.
    $this->buildElements($build, $entities, $langcode);

    // Alternatively use grid: BlazyGrid::build($build['items'], $settings).
    $elements = $build['items'];
    $elements['#attached'] = $this->formatter->attach($settings);

    return $elements;
  }

  /**
   * Builds the settings.
   */
  public function buildSettings() {
    $settings              = $this->getSettings();
    $settings['blazy']     = TRUE;
    $settings['lazy']      = 'blazy';
    $settings['item_id']   = 'box';
    $settings['plugin_id'] = $this->getPluginId();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopedFormElements() {
    $admin       = $this->admin();
    $field       = $this->fieldDefinition;
    $entity_type = $field->getTargetEntityTypeId();
    $target_type = $this->getFieldSetting('target_type');
    $views_ui    = $this->getFieldSetting('handler') == 'default';
    $bundles     = $views_ui ? [] : $this->getFieldSetting('handler_settings')['target_bundles'];
    $node        = $admin->getFieldOptions($bundles, ['entity_reference'], $target_type, 'node');
    $stages      = $admin->getFieldOptions($bundles, ['image', 'video_embed_field'], $target_type);

    return [
      'namespace'  => 'blazy_test',
      'images'     => $stages,
      'overlays'   => $stages + $node,
      'thumbnails' => $stages,
      'optionsets' => ['default' => 'Default'],
    ] + parent::getScopedFormElements();
  }

}
