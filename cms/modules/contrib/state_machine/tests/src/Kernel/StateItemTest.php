<?php

namespace Drupal\Tests\state_machine\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\state_machine\Plugin\Field\FieldType\StateItem
 * @group state_machine
 */
class StateItemTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_test', 'state_machine', 'field', 'user', 'state_machine_test'];

  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_state',
      'entity_type' => 'entity_test',
      'type' => 'state',
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'test_state',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'settings' => [
        'workflow' => 'default',
      ],
    ]);
    $field->save();
  }

  /**
   * @dataProvider providerTestField
   */
  public function testField($initial_state, $allowed_transitions, $invalid_new_state, $valid_transition, $expected_new_state) {
    $entity = EntityTest::create(['test_state' => ['value' => $initial_state]]);
    // Ensure that the first state of a workflow is chosen automatically.
    $this->assertEquals($initial_state, $entity->test_state->value);
    $this->assertFalse($entity->test_state->isEmpty());

    $result = $entity->test_state->first()->getTransitions();
    $this->assertCount(count($allowed_transitions), $result);
    $this->assertEquals($allowed_transitions, array_keys($result));

    if ($invalid_new_state) {
      $entity->test_state->value = $invalid_new_state;
      $this->assertFalse($entity->test_state->first()->isValid());
    }

    /** @var \Drupal\state_machine\WorkflowManagerInterface $workflow_manager */
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    /** @var \Drupal\state_machine\Plugin\Workflow\Workflow $workflow */
    $workflow = $workflow_manager->createInstance('default');
    $transition = $workflow->getTransition($valid_transition);
    $entity->test_state->first()->applyTransition($transition);
    $this->assertEquals($expected_new_state, $entity->test_state->value);
  }

  public function providerTestField() {
    $data = [];
    $data['new->fulfillment'] = ['new', ['create', 'cancel'], 'completed', 'create', 'fulfillment'];
    $data['new->canceled'] = ['new', ['create', 'cancel'], 'completed', 'cancel', 'canceled'];
    $data['fulfillment->completed'] = ['fulfillment', ['fulfill', 'cancel'], 'new', 'fulfill', 'completed'];
    // A transition to canceled is forbidden by the FulfillmentGuard.

    return $data;
  }

  /**
   * @dataProvider providerSettableOptions
   */
  public function testSettableOptions($initial_state, $available_options) {
    $entity = EntityTest::create(['test_state' => ['value' => $initial_state]]);
    $this->assertEquals($initial_state, $entity->test_state->value);
    // An invalid state should not have any settable options.
    $this->assertEquals($available_options, $entity->test_state->get(0)->getSettableOptions());
  }

  public function providerSettableOptions() {
    $data = [];
    $data['new'] = ['new', ['canceled' => 'Canceled', 'fulfillment' => 'Fulfilment', 'new' => 'New']];
    $data['invalid'] = ['invalid', []];

    return $data;
  }

}
