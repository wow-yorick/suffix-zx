<?php

namespace Drupal\Tests\commerce_cnpay\Kernel;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\Tests\commerce_cart\Kernel\CartManagerTestTrait;

/**
 * Tests cn payments.
 */
class CnpayTestBase extends CommerceKernelTestBase {

  use CartManagerTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'path',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_checkout',
    'commerce_payment',
    'commerce_cnpay',
    'commerce_cnpay_test',
  ];

  /**
   * The http kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManager
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProvider
   */
  protected $cartProvider;

  /**
   * The sample variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation1;

  /**
   * The sample variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation2;

  /**
   * The sample variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $variation3;

  /**
   * The sample customer.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $customer;

  /**
   * The cart order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $cart;

  /**
   * The payment storage.
   *
   * @var \Drupal\commerce_payment\PaymentStorageInterface
   */
  protected $paymentStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('system');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_payment');
    $this->installConfig([
      'commerce_product',
      'commerce_order',
      'user',
    ]);

    // It's important to install modules before using the container, because
    // installing modules will lead container to rebuild,
    $this->installCommerceCart();
    // We have to get services from the rebuilded container again, otherwise
    // for example $this->entityManager->getStorage() does not work for
    // resetting cache or deleting entities because $this->entityManager is from
    // old container.
    // See tryTestContainerRebuild().
    $this->entityManager = $this->container->get('entity.manager');
    $this->state = $this->container->get('state');
    $this->httpKernel = $this->container->get('http_kernel');

    // Import 'CNY' currency.
    $currency_importer = $this->container->get('commerce_price.currency_importer');
    $currency_importer->import('CNY');

    // Create variations.
    $this->variation1 = $this->createVariation('1.00', $this->store);
    $this->variation2 = $this->createVariation('2.00', $this->store);
    $this->variation3 = $this->createVariation('3.00', $this->store);

    $this->createUser(); // A dummy user with uid 1.
    $this->customer = $this->createUser();

    // Create a cart order.
    $this->cart = $this->cartProvider->createCart('default', $this->store, $this->customer);
    $this->cartManager->addEntity($this->cart, $this->variation1, 1);

    $this->paymentStorage = $this->entityManager->getStorage('commerce_payment');
  }

//  /**
//   * Tests custom simple cache.
//   */
//  public function testCustomCache() {
//    $cache = $this->nativeGatewayPlugin->getClient()->getCache();
//    $this->assertTrue($cache instanceof SimpleCache, 'Custom cache is set.');
//  }

  /**
   * Create a new variation and save it.
   *
   * @param string $price_number
   *   The price number.
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store the product belongs to.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected function createVariation($price_number, $store) {
    $variation = ProductVariation::create([
      'type' => 'default',
      'sku' => $this->randomMachineName(),
      'title' => $this->randomString(),
      'price' => new Price($price_number, 'CNY'),
      'status' => 1,
    ]);
    $variation->save();

    // We need a product too otherwise tests complain about the missing
    // backreference.
    $product = Product::create([
      'type' => 'default',
      'uid' => $store->getOwnerId(),
      'title' => $this->randomString(),
      'status' => 1,
      'stores' => [$store],
      'variations' => [$variation],
    ]);
    $product->save();

    return $variation;
  }

}
