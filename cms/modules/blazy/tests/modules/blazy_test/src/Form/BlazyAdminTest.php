<?php

namespace Drupal\blazy_test\Form;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\blazy\Form\BlazyAdminInterface;
use Drupal\blazy\BlazyManagerInterface;

/**
 * Provides resusable admin functions or form elements.
 */
class BlazyAdminTest implements BlazyAdminTestInterface {

  use StringTranslationTrait;

  /**
   * The blazy admin service.
   *
   * @var \Drupal\blazy\Form\BlazyAdminInterface
   */
  protected $blazyAdmin;

  /**
   * The blazy_test manager service.
   *
   * @var \Drupal\blazy_test\BlazyManagerInterface
   */
  protected $manager;

  /**
   * Static cache for the skin definition.
   *
   * @var array
   */
  protected $skinDefinition;

  /**
   * Static cache for the skin options.
   *
   * @var array
   */
  protected $skinOptions;

  /**
   * Constructs a GridStackAdmin object.
   */
  public function __construct(BlazyAdminInterface $blazy_admin, BlazyManagerInterface $manager) {
    $this->blazyAdmin = $blazy_admin;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('blazy.admin.extended'), $container->get('blazy.manager'));
  }

  /**
   * Returns the blazy admin.
   */
  public function blazyAdmin() {
    return $this->blazyAdmin;
  }

  /**
   * Returns the blazy_test manager.
   */
  public function manager() {
    return $this->manager;
  }

  /**
   * Returns defined skins as registered via hook_blazy_test_skins_info().
   */
  public function getSkins() {
    if (!isset($this->skinDefinition)) {
      $this->skinDefinition = $this->manager->buildSkins('blazy_test', '\Drupal\blazy_test\BlazyTestSkin');
    }

    return $this->skinDefinition;
  }

  /**
   * Returns all settings form elements.
   */
  public function buildSettingsForm(array &$form, $definition = []) {
    $definition += [
      'namespace'  => 'blazy',
      'optionsets' => [],
      'skins'      => $this->getSkinOptions(),
      'grid_form'  => TRUE,
      'style'      => TRUE,
    ];

    foreach (['background', 'caches', 'fieldable_form', 'id', 'vanilla'] as $key) {
      $definition[$key] = TRUE;
    }

    $definition['layouts'] = isset($definition['layouts']) ? array_merge($this->getLayoutOptions(), $definition['layouts']) : $this->getLayoutOptions();

    $this->openingForm($form, $definition);
    $this->mainForm($form, $definition);
    $this->closingForm($form, $definition);
  }

  /**
   * Returns the opening form elements.
   */
  public function openingForm(array &$form, $definition = []) {
    $this->blazyAdmin->openingForm($form, $definition);
  }

  /**
   * Returns the main form elements.
   */
  public function mainForm(array &$form, $definition = []) {
    if (!empty($definition['image_style_form'])) {
      $this->blazyAdmin->imageStyleForm($form, $definition);
    }

    if (!empty($definition['media_switch_form'])) {
      $this->blazyAdmin->mediaSwitchForm($form, $definition);
    }

    if (!empty($definition['grid_form'])) {
      $this->blazyAdmin->gridForm($form, $definition);
    }

    if (!empty($definition['fieldable_form'])) {
      $this->blazyAdmin->fieldableForm($form, $definition);
    }

    if (!empty($definition['breakpoints'])) {
      $this->blazyAdmin->breakpointsForm($form, $definition);
    }
  }

  /**
   * Returns the closing form elements.
   */
  public function closingForm(array &$form, $definition = []) {
    $this->blazyAdmin->closingForm($form, $definition);
  }

  /**
   * Returns available blazy_test skins for select options.
   */
  public function getSkinOptions() {
    if (!isset($this->skinOptions)) {
      $this->skinOptions = [];
      foreach ($this->getSkins() as $skin => $properties) {
        $this->skinOptions[$skin] = Html::escape($properties['name']);
      }
    }

    return $this->skinOptions;
  }

  /**
   * Returns default layout options for the core Image, or Views.
   */
  public function getLayoutOptions() {
    return [
      'bottom' => $this->t('Caption bottom'),
      'center' => $this->t('Caption center'),
      'top'    => $this->t('Caption top'),
    ];
  }

  /**
   * Return the field formatter settings summary.
   *
   * @deprecated: Removed for self::getSettingsSummary().
   */
  public function settingsSummary($plugin, $definition = []) {
    return $this->blazyAdmin->settingsSummary($plugin, $definition);
  }

  /**
   * Return the field formatter settings summary.
   *
   * @todo: Remove second param $plugin for post-release for Blazy RC2+.
   */
  public function getSettingsSummary(array $definition = [], $plugin = NULL) {
    // @todo: Remove condition for Blazy RC2+.
    if (!method_exists($this->blazyAdmin, 'getSettingsSummary')) {
      return $this->blazyAdmin->settingsSummary($plugin, $definition);
    }

    return $this->blazyAdmin->getSettingsSummary($definition);
  }

  /**
   * Returns available fields for select options.
   */
  public function getFieldOptions($target_bundles = [], $allowed_field_types = [], $entity_type_id = 'media', $target_type = '') {
    return $this->blazyAdmin->getFieldOptions($target_bundles, $allowed_field_types, $entity_type_id, $target_type);
  }

  /**
   * Returns re-usable logic, styling and assets across fields and Views.
   */
  public function finalizeForm(array &$form, $definition = []) {
    $this->blazyAdmin->finalizeForm($form, $definition);
  }

}
