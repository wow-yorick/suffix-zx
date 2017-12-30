<?php

namespace Drupal\commerce_cnpay_test;

use EasyWeChat\Payment\Application;
use EasyWeChat\Payment\Order;

class WeChatTestOrderClient extends Order\Client {

  public function __construct() {
    parent::__construct(new Application());
  }

  public function unify(array $attributes) {
  }

  public function close(string $tradeNo) {
  }

}
