<?php

namespace Drupal\Tests\state_machine\Unit;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\state_machine\WorkflowGroupManager;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \Drupal\state_machine\WorkflowGroupManager
 * @group Workflow
 */
class WorkflowGroupManagerTest extends UnitTestCase {

  /**
   * The cache backend to use.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The plugin discovery.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface
   */
  protected $discovery;

  /**
   * The group manager under test.
   *
   * @var \Drupal\Tests\state_machine\Unit\TestWorkflowGroupManager
   */
  protected $groupManager;

  /**
   * The expected definitions array.
   *
   * @var array
   */
  protected $expectedDefinitions = [
    'entity_test' => [
      'id' => 'entity_test',
      'label' => 'Entity Test',
      'entity_type' => 'entity_test',
      'class' => 'Drupal\state_machine\Plugin\WorkflowGroup\WorkflowGroup',
      'workflow_class' => '\Drupal\state_machine\Plugin\Workflow\Workflow',
      'provider' => 'state_machine_test',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Prepare the default constructor arguments required by
    // WorkflowGroupManager.
    $this->cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $this->moduleHandler->moduleExists('state_machine_test')->willReturn(TRUE);
    $this->groupManager = new TestWorkflowGroupManager($this->moduleHandler->reveal(), $this->cache);
  }

  /**
   * Provide a set of incomplete config workflow groups to test the process
   * definitions.
   */
  public function configWorkflowGroups() {
    return [
      [['workflow_group_1' => [
        'entity_type' => 'entity_test',
      ]]],
      [['workflow_group_2' => [
        'label' => 'Entity Test',
      ]]],
    ];
  }

  /**
   * Tests the processDefinition method with missing keys.
   *
   * @param $group_config array
   *  Workflow group configuration that will be translated into YAML.
   *
   * @covers ::processDefinition
   * @dataProvider configWorkflowGroups
   */
  public function testProcessIncompleteDefinitions($group_config) {
    vfsStream::setup('root');
    $file = Yaml::encode($group_config);
    vfsStream::create([
      'state_machine_test' => [
        'state_machine_test.workflow_groups.yml' => $file,
      ]]
    );

    $discovery = new YamlDiscovery('workflow_groups', ['state_machine_test' => vfsStream::url('root/state_machine_test')]);
    $this->groupManager->setDiscovery($discovery);
    $required_properties = ['label', 'entity_type'];

    $definition = $discovery->getDefinitions();
    $missing_properties = array_diff($required_properties, array_keys($group_config));
    $this->setExpectedException('Drupal\Component\Plugin\Exception\PluginException',
      sprintf('The workflow_group %s must define the %s property.', key($definition), reset($missing_properties)));
    $this->groupManager->processDefinition($definition, key($definition));
  }

  /**
   * @covers: getDefinitionsByEntityType
   */
  public function testGetDefinitionsByEntityType() {
    vfsStream::setup('root');
    $group_config = [
      'entity_test' => [
        'label' => 'Entity Test',
        'entity_type' => 'entity_test',
      ],
    ];
    $file = Yaml::encode($group_config);
    vfsStream::create([
      'state_machine_test' => [
        'state_machine_test.workflow_groups.yml' => $file,
      ],
    ]);

    $discovery = new YamlDiscovery('workflow_groups', ['state_machine_test' => vfsStream::url('root/state_machine_test')]);
    $this->groupManager->setDiscovery($discovery);
    $this->assertEquals($this->expectedDefinitions, $this->groupManager->getDefinitionsByEntityType('entity_test'), 'Workflow group definition matches the expectations');
  }

  /**
   * Tests that the workflow group manager returns the right object.
   */
  public function testProcessValidDefinition() {
    vfsStream::setup('root');
    $group_config = [
      'order' => [
        'label' => 'Entity Test',
        'entity_type' => 'entity_test',
      ],
    ];
    $file = Yaml::encode($group_config);
    vfsStream::create([
        'state_machine_test' => [
          'state_machine_test.workflow_groups.yml' => $file,
        ],
      ]
    );

    $discovery = new YamlDiscovery('workflow_groups', ['state_machine_test' => vfsStream::url('root/state_machine_test')]);
    $this->groupManager->setDiscovery($discovery);

    /** @var $workflow_group \Drupal\state_machine\Plugin\WorkflowGroup\WorkflowGroup */
    $workflow_group = $this->groupManager->createInstance('order');
    $this->assertEquals('Entity Test', $workflow_group->getLabel(), 'Workflow group label matches the expected one');
    $this->assertEquals('entity_test', $workflow_group->getEntityTypeId(), 'Workflow group entity type id matches the expected one');
    $this->assertEquals('\Drupal\state_machine\Plugin\Workflow\Workflow', $workflow_group->getWorkflowClass(), 'Workflow group class matches the expected one');
  }

}

/**
 * Provides a testing version of WorkflowGroupManager with an empty constructor.
 */
class TestWorkflowGroupManager extends WorkflowGroupManager {
  /**
   * Sets the discovery for the manager.
   *
   * @param \Drupal\Component\Plugin\Discovery\DiscoveryInterface $discovery
   *   The discovery object.
   */
  public function setDiscovery(DiscoveryInterface $discovery) {
    $this->discovery = $discovery;
  }

}
