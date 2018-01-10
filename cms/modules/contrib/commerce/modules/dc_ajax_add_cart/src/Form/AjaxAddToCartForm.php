<?php

namespace Drupal\dc_ajax_add_cart\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_cart\Form\AddToCartForm;
use Drupal\commerce_cart\Form\AddToCartFormInterface;
use Drupal\Component\Utility\Html;
use Drupal\dc_ajax_add_cart\RefreshPageElementsHelper;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the order item ajax add to cart form.
 */
class AjaxAddToCartForm extends AddToCartForm implements AddToCartFormInterface {

  /**
   * RefreshPageElementsHelper service.
   *
   * @var \Drupal\dc_ajax_add_cart\RefreshPageElementsHelper
   */
  protected $refreshPageElementsHelper;

  /**
   * Constructs a new AjaxAddToCartForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_order\Resolver\OrderTypeResolverInterface $order_type_resolver
   *   The order type resolver.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   * @param \Drupal\commerce_price\Resolver\ChainPriceResolverInterface $chain_price_resolver
   *   The chain base price resolver.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\dc_ajax_add_cart\RefreshPageElementsHelper $refresh_page_elements_helper
   *   The RefreshPageElementsHelper service.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CurrentStoreInterface $current_store, ChainPriceResolverInterface $chain_price_resolver, AccountInterface $current_user, RefreshPageElementsHelper $refresh_page_elements_helper) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time, $cart_manager, $cart_provider, $order_type_resolver, $current_store, $chain_price_resolver, $current_user);

    $this->refreshPageElementsHelper = $refresh_page_elements_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_order.chain_order_type_resolver'),
      $container->get('commerce_store.current_store'),
      $container->get('commerce_price.chain_price_resolver'),
      $container->get('current_user'),
      $container->get('dc_ajax_add_cart.refresh_page_elements_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // @TODO Remove this once https://www.drupal.org/node/2897120 gets into
    // core.
    $form['#attached']['library'][] = 'core/jquery.form';
    $form['#attached']['library'][] = 'core/drupal.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $wrapper_id = Html::getUniqueId($this->getFormId() . '-ajax-add-cart-wrapper');

    $actions['submit'] = [
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#value' => $this->t('Add to cart'),
      '#submit' => ['::submitForm'],
      '#attributes' => [
        'class' => ['use-ajax-submit'],
      ],
      '#ajax' => [
        'callback' => '::refreshAddToCartForm',
        'wrapper' => $wrapper_id,
      ],
    ];

    return $actions;
  }

  /**
   * Refreshes the add to cart form.
   *
   * Fixes https://www.drupal.org/node/2905814
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The updated ajax response.
   */
  public function refreshAddToCartForm(array $form, FormStateInterface $form_state) {
    return $this->refreshPageElementsHelper
      ->updatePageElements($form)
      ->getResponse();
  }

}
