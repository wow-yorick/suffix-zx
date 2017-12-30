<?php

namespace Drupal\commerce_cnpay;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Define the Alipay client
 */
class AlipayClient {

  /**
   * API names.
   */
  const API_BILL_DOWNLOADURL_QUERY      = 'alipay.data.dataservice.bill.downloadurl.query';
  const API_APP_PAY                     = 'alipay.trade.app.pay';
  const API_CANCEL                      = 'alipay.trade.cancel';
  const API_CLOSE                       = 'alipay.trade.close';
  const API_CREATE                      = 'alipay.trade.create';
  const API_CUSTOMS_DECLARE             = 'alipay.trade.customs.declare';
  const API_CUSTOMS_QUERY               = 'alipay.trade.customs.query"';
  const API_FASTPAY_REFUND_QUERY        = 'alipay.trade.fastpay.refund.query';
  const API_ORDER_SETTLE                = 'alipay.trade.order.settle';
  const API_PAGE_PAY                    = 'alipay.trade.page.pay';
  const API_PAY                         = 'alipay.trade.pay';
  const API_PRECREATE                   = 'alipay.trade.precreate';
  const API_QUERY                       = 'alipay.trade.query';
  const API_REFUND                      = 'alipay.trade.refund';
  const API_VENDORPAY_DEVICEDATA_UPLOAD = 'alipay.trade.vendorpay.devicedata.upload';
  const API_WAP_PAY                     = 'alipay.trade.wap.pay';

  /**
   * API gateway urls.
   */
  const URL_LIVE    = 'https://openapi.alipay.com/gateway.do';
  const URL_SANDBOX = 'https://openapi.alipaydev.com/gateway.do';

  /**
   * Base url.
   *
   * @var string
   */
  protected $url;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Common configuration information passed into the client.
   *
   * @var array
   */
  protected $configuration;

  /**
   * App (merchant) private RSA key path.
   *
   * @var string
   */
  protected $appPrivateKeyPath;

  /**
   * Alipay public RSA key path.
   *
   * @var string
   */
  protected $alipayPublicKeyPath;

  /**
   * Constructs a new Alipay object.
   *
   * @param string $app_id
   *   The app id.
   * @param string $app_private_key_path
   *   The file path of app private RSA key.
   * @param string $alipay_public_key_path
   *   The file path of Alipay public RSA key.
   * @param bool $sandbox
   *   Whether the mode is sandbox.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   */
  public function __construct($app_id, $app_private_key_path, $alipay_public_key_path, $sandbox = FALSE, ClientInterface $http_client = NULL) {
    $this->url = $sandbox ? self::URL_SANDBOX : self::URL_LIVE;
    $this->appPrivateKeyPath = $app_private_key_path;
    $this->alipayPublicKeyPath = $alipay_public_key_path;
    // Create a pure client instead of the Drupal http_client for external
    // services.
    $this->httpClient = $http_client ?: new Client([
      'timeout' => 10,
    ]);

    // Common API request parameters.
    $this->configuration = [
      'app_id' => $app_id,
      // method
      'format' => 'JSON',
      // return_url
      'charset' => 'utf-8',
      'sign_type' => 'RSA2',
      // sign
      // timestamp
      'version' => '1.0',
      // notify_url,
      // biz_content
    ];
  }

  /**
   * Gets the API url.
   *
   * @return string
   *   The API url.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Builds request parameters.
   *
   * @param string $api
   *   The API name.
   * @param array $biz_content
   *   An array of biz data.
   * @param array $extra_params
   *   (optional) An array of extra parameters to add to or override the default
   *   parameters.
   *
   * @return array
   *   An array of signed parameters.
   */
  public function buildParams($api, array $biz_content, array $extra_params = NULL) {
    // Construct request parameters.
    $params = $this->configuration;
    $params['method'] = $api;
    $params['timestamp'] = date("Y-m-d H:i:s");
    $params['biz_content'] = json_encode($biz_content, JSON_UNESCAPED_UNICODE);
    if ($extra_params) {
      $params = array_merge($params, $extra_params);
    }
    $params['sign'] = $this->sign($params);
    return $params;
  }

  /**
   * Query.
   *
   * @param array $biz_content
   *   An array of biz data.
   *
   * @return array
   *   An array of response data.
   */
  public function query(array $biz_content) {
    return $this->post(self::API_QUERY, $biz_content);
  }

  /**
   * Refund.
   *
   * @param array $biz_content
   *   An array of biz data.
   *
   * @return array
   *   An array of response data.
   */
  public function refund(array $biz_content) {
    return $this->post(self::API_REFUND, $biz_content);
  }

  /**
   * Close.
   *
   * @param array $biz_content
   *   An array of biz data.
   * @param string $notify_url
   *   The notify url.
   *
   * @return array
   *   An array of response data.
   */
  public function close(array $biz_content, $notify_url = NULL) {
    $extra_params = [];
    if ($notify_url) {
      $extra_params['notify_url'] = $notify_url;
    }
    return $this->post(self::API_CLOSE, $biz_content, $extra_params);
  }

  /**
   * Executes http post.
   *
   * @param string $api
   *   The API name.
   * @param array $biz_content
   *   An array of biz data.
   * @param array $extra_params
   *   (optional) An array of extra parameters to add to or override the default
   *   parameters.
   *
   * @return array
   *   The response array.
   */
  protected function post($api, array $biz_content, array $extra_params = NULL) {
    // Do post request.
    $response = $this->httpClient->post($this->url, [
      'form_params' => $this->buildParams($api, $biz_content, $extra_params),
    ]);

    try {
      // Response headers:
      //   * Content-Type: text/html;charset=GBK
      $content = \GuzzleHttp\json_decode($response->getBody(), TRUE);
    }
    catch (\InvalidArgumentException $e) {
      // The decode error happens to sub_msg with GBK charset when a failed
      // response is returned.
      $body = (string) $response->getBody();
      $body = iconv('GBK', 'utf-8', $body);
      $content = \GuzzleHttp\json_decode($body, TRUE);
    }

    // Extract response data array and verify its signature.
    $data_key = str_replace('.', '_', $api) . '_response';
    // Do not need to verify signature of the direct API call response.
    return $content[$data_key];
  }

  /**
   * Signs the given array of parameters.
   *
   * @param array $params
   *   An array of parameters.
   *
   * @return string
   *   The signature.
   *
   * @throws \Exception
   *   Throws if cannot get the private RSA key.
   */
  public function sign(array $params) {
    $data = $this->getSignString($params);

    $key = 'file://' . $this->appPrivateKeyPath;
    if (($priv_key_id = openssl_get_privatekey($key)) === FALSE) {
      throw new \Exception('Invalid app private key.');
    }
    $signature_alg = $this->configuration['sign_type'] === 'RSA2' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;
    openssl_sign($data, $signature, $priv_key_id, $signature_alg);
    openssl_free_key($priv_key_id);

    return base64_encode($signature);
  }

  /**
   * Verifies the signature of the given array of parameters.
   *
   * @param array $params
   *   The parameter array to verify.
   * @param string $signature
   *   The parameter signature.
   *
   * @return bool
   *   TRUE if the signature is correct, FALSE otherwise.
   *
   * @throws \Exception
   *   Throws if cannot get the public RSA key.
   */
  public function verify(array $params, $signature) {
    unset($params['sign_type'], $params['sign']);
    $data = $this->getSignString($params);

    $key = 'file://' . $this->alipayPublicKeyPath;
    if (($pub_key_id = openssl_get_publickey($key)) === FALSE) {
      throw new \Exception('Invalid Alipay public key.');
    }
    $signature_alg = $this->configuration['sign_type'] === 'RSA2' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;
    $result = openssl_verify($data, base64_decode($signature), $pub_key_id, $signature_alg) === 1;
    openssl_free_key($pub_key_id);

    return $result;
  }

  /**
   * Concatenates the given array of parameters to sign.
   *
   * @param array $params
   *   The parameter array.
   *
   * @return string
   *   The concatenated data string.
   */
  protected function getSignString(array $params) {
    ksort($params);
    $pairs = [];
    foreach ($params as $k => $v) {
      if ($v !== NULL && $v !== '') {
        $pairs[] = "$k=$v";
      }
    }
    return implode('&', $pairs);
  }

}
