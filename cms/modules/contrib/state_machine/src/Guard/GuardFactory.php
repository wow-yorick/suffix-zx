<?php

namespace Drupal\state_machine\Guard;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default implementation of the guard factory.
 */
class GuardFactory implements GuardFactoryInterface {

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The guard service ids, grouped by workflow group id.
   *
   * @var string[]
   */
  protected $guardServiceIds;

  /**
   * Constructs a new GuardFactory object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param string[] $guard_service_ids
   *   The guard service ids, grouped by workflow group id
   */
  public function __construct(ContainerInterface $container, array $guard_service_ids) {
    $this->container = $container;
    $this->guardServiceIds = $guard_service_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function get($group_id) {
    if (!isset($this->guardServiceIds[$group_id])) {
      return [];
    }

    $guards = [];
    foreach ($this->guardServiceIds[$group_id] as $service_id) {
      $guards[] = $this->container->get($service_id);
    }

    return $guards;
  }

}
