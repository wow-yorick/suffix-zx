<?php

namespace Drupal\state_machine\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StateTransitionForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\FieldableEntityInterface
   */
  protected $entity;

  /**
   * Constructs a new StateTransitionForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *    The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'state_machine_state_transition_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $field_name = $form_state->get('field_name');
    $entity_type = $form_state->get('entity_type');
    $entity_id = $form_state->get('entity_id');
    $this->entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
    /** @var \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface $state_item */
    $state_item = $this->entity->get($field_name)->first();

    $form['actions'] = [
      '#type' => 'container',
    ];
    foreach ($state_item->getTransitions() as $transition_id => $transition) {
      $form['actions'][$transition_id] = [
        '#type' => 'submit',
        '#value' => $transition->getLabel(),
        '#submit' => ['::submitForm'],
        '#transition' => $transition,
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    /** @var \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface $state_item */
    $state_item = $this->entity->get($form_state->get('field_name'))->first();
    $state_item->applyTransition($triggering_element['#transition']);
    $this->entity->save();
  }

}
