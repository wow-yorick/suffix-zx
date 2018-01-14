<?php
/**
 * @file
 * Contains \Drupal\app_user\Controller\CartController.
 */
namespace Drupal\app_user\Controller;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\app_user\Utility\DescriptionTemplateTrait;
use Drupal\views\Views;
use Drupal\Core\Link;

class CartController extends ControllerBase {
    use DescriptionTemplateTrait;

    /**
     * The cart provider.
     *
     * @var \Drupal\commerce_cart\CartProviderInterface
     */
    protected $cartProvider;

    /**
     * Constructs a new CartController object.
     *
     * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
     *   The cart provider.
     */
    public function __construct(CartProviderInterface $cart_provider) {
        $this->cartProvider = $cart_provider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getModuleName() {
        return 'app_user';
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('commerce_cart.cart_provider')
        );
    }

    /**
     * Outputs a cart view for each non-empty cart belonging to the current user.
     *
     * @return array
     *   A render array.
     */
    public function confirm() {
        $build = [
        ];
        $cacheable_metadata = new CacheableMetadata();
        $cacheable_metadata->addCacheContexts(['user', 'session']);

        $carts = $this->cartProvider->getCarts();
        $carts = array_filter($carts, function ($cart) {
            /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
            return $cart->hasItems();
        });
        if (!empty($carts)) {
            $cart_views = $this->getCartViews($carts);
            foreach ($carts as $cart_id => $cart) {
                $build[$cart_id] = [
                    '#prefix' => '',
                    '#suffix' => '',
                    '#type' => 'view',
                    '#name' => $cart_views[$cart_id],
                    '#arguments' => [$cart_id],
                    '#embed' => TRUE,
                ];
                $cacheable_metadata->addCacheableDependency($cart);
            }
        }
        else {
            $build['empty'] = [
                '#theme' => 'commerce_cart_empty_page',
            ];
        }
        $build['#cache'] = [
            'contexts' => $cacheable_metadata->getCacheContexts(),
            'tags' => $cacheable_metadata->getCacheTags(),
            'max-age' => $cacheable_metadata->getCacheMaxAge(),
        ];
        //$view = Views::getView("commerce_cart_form");
//        dump($view);
        //dump($build);exit;
        $permissions_admin_link = Link::createFromRoute($this->t('the permissions admin page'), 'user.admin_permissions')->toString();
        $build['#theme'] = 'page__cart__confirm';
        $build['#type'] ="page";
        $build['#variables'] = $permissions_admin_link;
        return $build;
    }

    /**
     * Gets the cart views for each cart.
     *
     * @param \Drupal\commerce_order\Entity\OrderInterface[] $carts
     *   The cart orders.
     *
     * @return array
     *   An array of view ids keyed by cart order ID.
     */
    protected function getCartViews(array $carts) {
        $order_type_ids = array_map(function ($cart) {
            /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
            return $cart->bundle();
        }, $carts);
        $order_type_storage = $this->entityTypeManager()->getStorage('commerce_order_type');
        $order_types = $order_type_storage->loadMultiple(array_unique($order_type_ids));
        $cart_views = [];
        foreach ($order_type_ids as $cart_id => $order_type_id) {
            /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
            $order_type = $order_types[$order_type_id];
            $cart_views[$cart_id] = $order_type->getThirdPartySetting('commerce_cart', 'cart_form_view', 'commerce_cart_form');
        }

        return $cart_views;
    }


}