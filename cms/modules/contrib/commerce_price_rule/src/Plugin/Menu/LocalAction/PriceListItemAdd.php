<?php

namespace Drupal\commerce_price_rule\Plugin\Menu\LocalAction;

use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Modifies the 'Add a price list item' local action to add a destination.
 */
class PriceListItemAdd extends LocalActionDefault {

  /**
   * The currently active route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route_match;

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  private $redirectDestination;

  /**
   * Constructs a MenuLinkAdd object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The currently active route match object.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteProviderInterface $route_provider,
    RouteMatchInterface $route_match,
    RedirectDestinationInterface $redirect_destination
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $route_provider
    );

    $this->route_match = $route_match;
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('current_route_match'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    $parameters = parent::getRouteParameters($route_match);

    // We add the link to the View provided for managing list items. The list id
    // is not available in the normal parameters, we have to manually get it as
    // the 1st view argument.
    $price_list_id = $this->route_match->getParameter('arg_0');
    if (!isset($parameters['commerce_price_rule_list']) && $price_list_id) {
      $parameters['commerce_price_rule_list'] = $price_list_id;
    }

    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(RouteMatchInterface $route_match) {
    $options = parent::getOptions($route_match);

    // Append the current path as destination to the query string.
    $options['query']['destination'] = $this->redirectDestination->get();

    return $options;
  }

}
