<?php

namespace Drupal\state_machine\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds the context provider service IDs to the context manager.
 */
class GuardsPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   *
   * Passes the grouped service IDs of guards to the guard factory.
   */
  public function process(ContainerBuilder $container) {
    $guards = [];
    foreach ($container->findTaggedServiceIds('state_machine.guard') as $id => $attributes) {
      if (empty($attributes[0]['group'])) {
        continue;
      }

      $guards[$attributes[0]['group']][] = $id;
    }

    $definition = $container->getDefinition('state_machine.guard_factory');
    $definition->addArgument($guards);
  }

}
