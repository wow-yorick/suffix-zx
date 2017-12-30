<?php

namespace Drupal\commerce_cnpay_test;

use EasyWeChat\Payment\Application;

/**
 * Used to mock objects for magic __call() method.
 */
class WeChatTestClient extends Application {

  public function __construct(WeChatTestOrderClient $order_client) {
    parent::__construct();
    $this['order'] = function ($app) use ($order_client) {
      return $order_client;
    };
  }

}
