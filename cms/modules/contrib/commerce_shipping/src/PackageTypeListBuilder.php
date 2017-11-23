<?php

namespace Drupal\commerce_shipping;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\physical\NumberFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of package types.
 */
class PackageTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * The number formatter.
   *
   * @var \Drupal\physical\NumberFormatterInterface
   */
  protected $numberFormatter;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('physical.number_formatter')
    );
  }

  /**
   * Constructs a new PackageTypeListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\physical\NumberFormatterInterface $number_formatter
   *   The number formatter.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, NumberFormatterInterface $number_formatter) {
    parent::__construct($entity_type, $storage);

    $this->numberFormatter = $number_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Package type');
    $header['dimensions'] = $this->t('Dimensions');
    $header['weight'] = $this->t('Weight');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_shipping\Entity\PackageTypeInterface $entity */
    $row['label'] = $entity->label();

    $dimensions = $entity->getDimensions();
    $dimension_list = [
      $this->numberFormatter->format($dimensions['length']),
      $this->numberFormatter->format($dimensions['width']),
      $this->numberFormatter->format($dimensions['height']),
    ];
    $row['dimensions'] = implode(' × ', $dimension_list) . ' ' . $dimensions['unit'];

    $weight = $entity->getWeight();
    $row['weight'] = $this->numberFormatter->format($weight['number']) . ' ' . $weight['unit'];

    return $row + parent::buildRow($entity);
  }

}
