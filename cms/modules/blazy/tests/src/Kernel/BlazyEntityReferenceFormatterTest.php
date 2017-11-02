<?php

namespace Drupal\Tests\blazy\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\blazy\Traits\BlazyKernelTestTrait;

/**
 * Tests the Blazy entity reference file formatter.
 *
 * @coversDefaultClass \Drupal\blazy_test\Plugin\Field\FieldFormatter\BlazyTestEntityReferenceFormatterTest
 * @group blazy
 */
class BlazyEntityReferenceFormatterTest extends KernelTestBase {

  use BlazyKernelTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'field',
    'field_ui',
    'filter',
    'entity_test',
    'node',
    'file',
    'image',
    'breakpoint',
    'responsive_image',
    'link',
    'text',
    'options',
    'blazy',
    'blazy_ui',
    'blazy_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpVariables();
    $this->setUpKernelInstall();
    $this->setUpKernelManager();

    $this->blazyAdminTest  = $this->container->get('blazy_test.admin');
    $this->entityFieldName = 'field_entity_test';
    $this->entityPluginId  = 'blazy_entity_test';
    $this->targetBundle    = 'bundle_target_test';
    $this->targetBundles   = [$this->targetBundle];

    $settings['image_settings'] = [
      'iframe_lazy'  => TRUE,
      'lazy'         => 'blazy',
      'media_switch' => '',
      'ratio'        => 'fluid',
      'view_mode'    => 'default',
    ];

    $settings['entity_field_name'] = $this->entityFieldName;
    $settings['entity_plugin_id']  = $this->entityPluginId;

    $settings['entity_settings'] = [
      'grid'      => 4,
      'optionset' => '',
    ] + $this->getFormatterSettings();

    $this->setUpContentWithEntityReference($settings);
    $this->formatterInstance = $this->getFormatterInstance($this->entityPluginId, $this->entityFieldName);
  }

  /**
   * Tests the Blazy formatter display.
   *
   * @todo: Useful assertions.
   */
  public function testFormatterDisplay() {
    $bundle     = $this->bundle;
    $field_name = $this->entityFieldName;
    $plugin_id  = $this->entityPluginId;
    $formatter  = $this->formatterInstance;
    $definition = array_merge($formatter->getScopedFormElements(), $this->getFormatterDefinition());
    $settings   = array_merge($definition['settings'], $this->getDefaultFields(TRUE)) + $formatter::defaultSettings();

    $settings['grid']    = 4;
    $settings['style']   = 'grid';
    $settings['overlay'] = 'field_image';
    $settings['image']   = $this->testFieldName;

    foreach (['field_title', 'field_teaser', 'field_link'] as $key) {
      $settings['caption'][$key] = $key;
    }

    $this->referencingDisplay->setComponent($this->entityFieldName, [
      'type'     => $this->entityPluginId,
      'settings' => $settings,
      'label'    => 'hidden',
    ]);

    $this->referencingDisplay->save();

    // Create referencing entity.
    $this->referencingEntity = $this->createReferencingEntity();

    // Verify the un-accessible item still exists.
    $this->assertEquals($this->referencingEntity->{$field_name}->target_id, $this->referencedEntity->id(), format_string('The un-accessible item still exists after @name formatter was executed.', ['@name' => $plugin_id]));

    $entity_type_id = $this->referencingEntity->getEntityTypeId();
    $component = $this->referencingDisplay->getComponent($this->entityFieldName);
    $this->assertEquals($this->entityPluginId, $component['type']);

    $array = $this->referencingEntity->get($this->entityFieldName);
    $value = $array->getValue();

    $list = $this->fieldTypePluginManager->createFieldItemList($this->referencingEntity, $this->entityFieldName, $value);
    $entities = $list->referencedEntities();

    $elements['settings'] = $settings;
    $formatter->buildElements($elements, $entities, NULL);
    $this->assertArrayHasKey('items', $elements);

    $build = $this->referencingDisplay->build($this->referencingEntity);

    $render = $this->blazyManager->getRenderer()->renderRoot($build);
    $this->assertNotEmpty($render);

    $string = $formatter->getFieldString($this->referencedEntity, '', NULL);
    $this->assertEmpty($string);

    $data['settings'] = $settings;
    $data['settings']['delta'] = 0;
    $data['settings']['vanilla'] = TRUE;

    $formatter->buildElement($data, $this->referencedEntity, NULL);
    $this->assertArrayHasKey('items', $data);

    $data['settings'] = $settings;
    $data['settings']['delta'] = 0;
    $data['settings']['vanilla'] = FALSE;
    $data['settings']['image'] = $this->testFieldName;
    $data['settings']['media_switch'] = 'rendered';
    $data['settings']['nav'] = TRUE;
    $data['settings']['thumbnail_style'] = 'thumbnail';
    $data['settings']['thumbnail_caption'] = 'field_text';

    $formatter->buildElement($data, $entities[0], NULL);
    $this->assertArrayHasKey('items', $data);
  }

  /**
   * Tests Blazy preview.
   *
   * @param array $settings
   *   The settings being tested.
   * @param bool $is_entity
   *   Tests againts entity or image.
   * @param bool $is_item
   *   Tests againts empty image.
   * @param mixed|bool|array $expected
   *   The expected output.
   *
   * @dataProvider providerTestBuildPreview
   * @depends testFormatterDisplay
   */
  public function testBuildPreview(array $settings, $is_entity, $is_item, $expected) {
    $formatter  = $this->formatterInstance;
    $definition = array_merge($formatter->getScopedFormElements(), $this->getFormatterDefinition());
    $settings   = array_merge($definition['settings'], $settings) + $this->getDefaultFields(TRUE);

    $settings['delta'] = 0;

    $item   = $is_item ? $this->referencedEntity->get($this->testFieldName) : NULL;
    $entity = $is_entity ? $this->referencedEntity : $this->testItem;
    $data   = [
      'item' => $item,
      'settings' => $settings,
    ];

    $preview = $formatter->buildPreview($data, $entity, '');
    $result = $is_entity ? !empty($preview) : $preview;

    $this->assertEquals($expected, $result);
  }

  /**
   * Provide test cases for ::testBuildPreview().
   *
   * @return array
   *   An array of tested data.
   */
  public function providerTestBuildPreview() {
    $data[] = [
      [],
      FALSE,
      FALSE,
      [],
    ];
    $data[] = [
      [
        '_basic' => FALSE,
        '_detached' => FALSE,
      ],
      TRUE,
      TRUE,
      TRUE,
    ];
    $data[] = [
      [
        '_basic' => TRUE,
        '_detached' => TRUE,
      ],
      TRUE,
      TRUE,
      TRUE,
    ];
    $data[] = [
      [],
      TRUE,
      FALSE,
      TRUE,
    ];
    return $data;
  }

  /**
   * Tests the Blazy formatter settings form.
   */
  public function testFormatterSettingsForm() {
    $formatter  = $this->formatterInstance;
    $definition = array_merge($formatter->getScopedFormElements(), $this->getFormatterDefinition());

    $definition['settings'] = array_merge($definition['settings'], $this->getDefaultFields(TRUE));

    // Check for setttings form.
    $form = [];
    $form_state = new FormState();

    // Verify global option current_view_mode is available.
    $form['overlay']['#description'] = '';
    $definition['_views'] = TRUE;
    $form['disabled_access'] = [
      '#type'   => 'hidden',
      '#access' => FALSE,
    ];

    // Check for formatter buildSettingsForm.
    $this->blazyAdminFormatter->buildSettingsForm($form, $definition);
    $this->assertArrayHasKey('current_view_mode', $form);

    // Check for setttings form.
    $elements = $formatter->settingsForm($form, $form_state);
    $this->assertArrayHasKey('closing', $elements);

    $default_settings = $formatter::defaultSettings();
    $this->assertArrayHasKey('image_style', $default_settings);

    $data['settings'] = $definition['settings'];

    // Tests the Blazy admin formatters.
    $this->assertArrayHasKey('fieldable_form', $definition);

    // Verify summary is working.
    $summary = $formatter->settingsSummary();
    foreach ($summary as $markup) {
      $arguments = $markup->getArguments();
      $this->assertArrayHasKey('@title', $arguments);
    }

    $formatter_settings = $formatter->buildSettings();
    $this->assertArrayHasKey('plugin_id', $formatter_settings);
  }

}
