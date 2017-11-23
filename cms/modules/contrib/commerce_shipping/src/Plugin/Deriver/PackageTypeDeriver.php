<?php

namespace Drupal\commerce_shipping\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exposes each package type config entity as a package type plugin.
 */
class PackageTypeDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PackageTypeDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $plugin_definitions = [];
    $package_type_entities = $this->entityTypeManager->getStorage('commerce_package_type')->loadMultiple();
    /** @var \Drupal\commerce_shipping\Entity\PackageTypeInterface $package_type */
    foreach ($package_type_entities as $package_type) {
      $plugin_definitions[$package_type->uuid()] = [
        'id' => 'commerce_package_type:' . $package_type->uuid(),
        'remote_id' => 'custom',
        'label' => $package_type->label(),
        'dimensions' => $package_type->getDimensions(),
        'weight' => $package_type->getWeight(),
      ];
    }

    return $plugin_definitions;
  }

}
