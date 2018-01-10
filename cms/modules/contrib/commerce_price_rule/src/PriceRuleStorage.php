<?php

namespace Drupal\commerce_price_rule;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines the price rule storage.
 */
class PriceRuleStorage extends CommerceContentEntityStorage implements PriceRuleStorageInterface {

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new PriceRuleStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    Connection $database,
    EntityManagerInterface $entity_manager,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    EventDispatcherInterface $event_dispatcher,
    TimeInterface $time
  ) {
    parent::__construct(
      $entity_type,
      $database,
      $entity_manager,
      $cache,
      $language_manager,
      $event_dispatcher
    );

    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('event_dispatcher'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadAvailable(StoreInterface $store) {
    $today = gmdate('Y-m-d', $this->time->getRequestTime());
    $query = $this->getQuery()
      ->condition('stores', [$store->id()], 'IN')
      ->condition('status', TRUE);

    // Start and end dates.
    $start_condition = $query->orConditionGroup()
      ->condition('start_date', NULL, 'IS NULL')
      ->condition('start_date', $today, '<=');
    $end_condition = $query->orConditionGroup()
      ->condition('end_date', NULL, 'IS NULL')
      ->condition('end_date', $today, '>=');
    $query->condition($start_condition)
      ->condition($end_condition);

    $result = $query->execute();
    if (empty($result)) {
      return [];
    }

    // Load and sort the price rules.
    $price_rules = $this->loadMultiple($result);
    uasort($price_rules, [$this->entityType->getClass(), 'sort']);

    return $price_rules;
  }

}
