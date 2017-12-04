<?php
/**
 * @file
 * Contains \Drupal\app_user\Controller\UserController.
 */
namespace Drupal\app_user\Controller;

use Drupal\Core\Controller\ControllerBase;
class UserController extends APIBaseController {

  public function register() {
    $this->success($credentials);
  }

  /**
   * Logs in a user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response which contains the ID and CSRF token.
   */
  public function login(Request $request) {
    $format = $this->getRequestFormat($request);

    $content = $request->getContent();
    $credentials = $this->serializer->decode($content, $format);
    if (!isset($credentials['name']) && !isset($credentials['pass'])) {
      throw new BadRequestHttpException('Missing credentials.');
    }

    if (!isset($credentials['name'])) {
      throw new BadRequestHttpException('Missing credentials.name.');
    }
    if (!isset($credentials['pass'])) {
      throw new BadRequestHttpException('Missing credentials.pass.');
    }

    $this->floodControl($request, $credentials['name']);

    if ($this->userIsBlocked($credentials['name'])) {
      throw new BadRequestHttpException('The user has not been activated or is blocked.');
    }

    if ($uid = $this->userAuth->authenticate($credentials['name'], $credentials['pass'])) {
      $this->flood->clear('user.http_login', $this->getLoginFloodIdentifier($request, $credentials['name']));
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->userStorage->load($uid);
      $this->userLoginFinalize($user);

      // Send basic metadata about the logged in user.
      $response_data = [];
      if ($user->get('uid')->access('view', $user)) {
        $response_data['current_user']['uid'] = $user->id();
      }
      if ($user->get('roles')->access('view', $user)) {
        $response_data['current_user']['roles'] = $user->getRoles();
      }
      if ($user->get('name')->access('view', $user)) {
        $response_data['current_user']['name'] = $user->getAccountName();
      }
      $response_data['csrf_token'] = $this->csrfToken->get('rest');

      $logout_route = $this->routeProvider->getRouteByName('user.logout.http');
      // Trim '/' off path to match \Drupal\Core\Access\CsrfAccessCheck.
      $logout_path = ltrim($logout_route->getPath(), '/');
      $response_data['logout_token'] = $this->csrfToken->get($logout_path);

      $encoded_response_data = $this->serializer->encode($response_data, $format);
      return new Response($encoded_response_data);
    }

    $flood_config = $this->config('user.flood');
    if ($identifier = $this->getLoginFloodIdentifier($request, $credentials['name'])) {
      $this->flood->register('user.http_login', $flood_config->get('user_window'), $identifier);
    }
    // Always register an IP-based failed login event.
    $this->flood->register('user.failed_login_ip', $flood_config->get('ip_window'));
    throw new BadRequestHttpException('Sorry, unrecognized username or password.');
  }

  public function test11()
  {
    echo 322;
  }

    /**
     * verifymobile
     * 验证码
     * @access public
     * @return void
     */
    public function verifymobile() {
        $retMap = array(
            'success'=>false,
            'msg'=>'发送验证码失败!',
        );
        $session_authcode = $_SESSION['USER_AUTHCODE'];
        if(!empty($session_authcode)) {
            $_SESSION['USER_AUTHCODE'] = null;
            $_SESSION['USER_TMP_MOBILE'] = null;
        }

        $mobile = $_REQUEST['mobile'];
        if(empty($mobile)) {
            $retMap['msg'] = '手机号码不能为空!';
            echo json_encode($retMap);
            exit;
        }

        $type = $_REQUEST['type'];
        $smsType = 'SendTempletSmsAndCheckSource';//短信
        if($type && 2 == $type) {
            $smsType = 'CallVoiceVerificationCode';//语音
        }

        $mobile = trim($mobile);

        $authcode = rand(1000,9999);
        $_SESSION['USER_AUTHCODE'] = $authcode;
        $_SESSION['USER_TMP_MOBILE'] = trim($mobile);
        $_SESSION['USER_AUTHCODE_VALID'] = time() + 10 * 60;

        $disflag = $_SESSION['AREAFLAG'] ? : 'other';
        $send_res = send_template_sms(529, $mobile, array(1=>$authcode), $disflag, $smsType);//尊敬的用户，您申请查看订单详情的手机验证码：2524。该验证码有效期10分钟，如非本人操作，请忽略此条短信。如有疑问请联系客服400-660-7700咨询。
        $send_res = @json_decode(json_encode($send_res), true);
        //print_r($send_res);

        if($send_res && 1 == $send_res[$smsType.'Result']) {
            $retMap['success'] = true;
            $retMap['msg'] = '发送成功!';
        }else{
            $retMap['msg'] = '验证码获取失败,请稍后重试!';
            $retMap['result'] = $send_res;
        }
        echo json_encode($retMap);
    }

    /**
     * verify_order_act
     * 验证用户
     * @access public
     * @return void
     */
    public function verify_order_act()
    {
        $authcode = $_REQUEST['authcode'];
        $usermobile = $_REQUEST['mobile'];
        $retMap = array(
            'success' => false,
            'msg' =>'',
        );

        $sys_mark_code = $_SESSION['USER_AUTHCODE'];
        $tmp_mobile = $_SESSION['USER_TMP_MOBILE'];
        $valid_time = $_SESSION['USER_AUTHCODE_VALID'];//验证码有效期
        if($valid_time < time()) {
            $_SESSION['USER_AUTHCODE_VALID'] = 0;
            $retMap['msg'] = "您输入的验证码已过期，请重新获取验证码!";
            echo json_encode($retMap);
            exit;
        }
        if(!$authcode || $authcode != $sys_mark_code || trim($usermobile) != $tmp_mobile) { //如果没有验证成功继续验证页面
            $retMap['msg'] = "您输入的验证码有误,请重新输入!";
            echo json_encode($retMap);
            exit;
        }

        $_SESSION['USER_AUTHCODE'] = null;
        $_SESSION['USER_TMP_MOBILE'] = null;
        $_SESSION['VERIFY_ORDER_MOBILE'] = $usermobile;
        $_SESSION['VERIFY_END_DATETIME'] = time() + 60*30;//30分钟后过期
        $retMap['success'] = true;
        $retMap['msg'] = '恭喜您!登录成功!';
        echo json_encode($retMap);
    }
}

/*
 *发送短信
 */
function send_template_sms()
{
  return true;
}
