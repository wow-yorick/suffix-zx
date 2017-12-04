<?php
/**
 * APIBaseController
 * api接口基类
 * @package
 * @version $id$
 * @copyright 2015-2016 The JIA Group
 * @author yorick <v@5zyx.com>
 * @license Copyright © 2005-2015 www.jia.com All rights reserved
 */
namespace Drupal\app_user\Controller;

use Drupal\Core\Controller\ControllerBase;
class APIBaseController extends ControllerBase {

    protected $requestData;

    const TIME_EXCEED = 90;//签名有效期

    //APPID 限制
    public static $appidArr = array(
        160 => "前端请求",
    );

    //验签公钥
    const SIGN_KEY = 'suffix-zx';

    public function __construct() {
        $this->requestData = self::getRequest();//设置请求
        $this->safetyVerification($this->requestData);//安全检查
        $this->checkSignature();//验证签名
    }

    /**
     * getRequest
     * 获取请求数据
     * @access public
     * @return void
     */
    public static function getRequest()
    {
        $requestDataArr = array();

        $post_data = file_get_contents("php://input");
        if ($post_data) {
            $requestDataArr = json_decode($post_data, true);
        } else {
            $requestDataArr = $_REQUEST;
        }
        $requestDataArr['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        //Log::write('['.MODULE_NAME.'/'.ACTION_NAME.'] REQUEST:'.json_encode($requestDataArr,JSON_UNESCAPED_UNICODE),'NOTIC');
        return $requestDataArr;
    }

    /**
     * responseStructBase
     * 响应数据结构
     * @param mixed $code
     * @access public
     * @return void
     */
    protected function responseStruct($code, $customArr = array())
    {
        $codeMap = array(
            '0000'=> '请求成功!',
            '4001' => '未知错误!',
            '4002' => '非法访问!',
            '4004' => '输入不合法!',
        );

        $retMap = array(
            'statusCode' => $code,
            'msg' => $codeMap[$code] ? : '',
            'costTime' =>  '0.122s',
            'result' => '',
        );
        $retMap = array_merge($retMap, $customArr);
        return $retMap;
    }

    /**
     * success
     * 成功响应封装
     * @param mixed $result
     * @access protected
     * @return void
     */
    protected function success($result = '',$responseExt = array())
    {
        $retMap = $this->responseStruct('0000', $responseExt);
        $retMap['result'] = $result;
        //Log::write('['.MODULE_NAME.'/'.ACTION_NAME.'] RESPONCE:'.json_encode($retMap,JSON_UNESCAPED_UNICODE),'NOTIC');
        $this->responseData($retMap);
    }

    /**
     * error
     * 错误响应封装
     * @param string $msg
     * @access protected
     * @return void
     */
    protected function error($msg = '', $statusCode = 4004)
    {
        $retMap = $this->responseStruct($statusCode);
        if(!empty($msg)) {
            $retMap['msg'] = $msg;
        }
        //Log::write('['.MODULE_NAME.'/'.ACTION_NAME.'] RESPONCE:'.json_encode($retMap, JSON_UNESCAPED_UNICODE),'NOTIC');
        $this->responseData($retMap);
    }




    /**
     * responseData
     * 接口响应
     * @param mixed $data
     * @access public
     * @return void
     */
    protected function responseData($data = array())
    {
        $requestData = $this->requestData;
        ob_flush();
        if (isset($requestData['callback']) && $requestData['callback'])
            echo  $requestData['callback'] . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ')';
        else
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die;
    }


    /**
     * safetyVerification
     * 接口安全验证
     * @param mixed $requestData
     * @access public
     * @return void
     */
    protected function safetyVerification($requestData)
    {
        if(!PRODUCT) {
            return true;
        }
        if (isset($requestData['mobile']) && $requestData['mobile'] == '13200000001' || empty($requestData['HTTP_REFERER'])) {
            return true;
        }
        //来源url限制
        if (empty($requestData['HTTP_REFERER']) || (false === strpos($requestData['HTTP_REFERER'], 'jia.com') && false === strpos($requestData['HTTP_REFERER'], 'cms.tg.com.cn')))
        {
            $retArr = $this->responseStruct(4004, array('msg'=>'不安全的来源!'));
            $this->responseData($retArr);
        }

        //ip安全限制
        /*$ip = $_SERVER["REMOTE_ADDR"];
        //echo $ip;
        $preg = '/^(192\.168|10\.10|127\.0\.0\.1)/';
        preg_match($preg,$ip,$match);
        //var_dump($match);
        if(empty($match)){
            $retArr = $this->responseStruct(4004, array('msg'=>'没有权限!'));
            $this->responseData($retArr);
        }*/

        //ip黑名单限制
        $result = getUrl("http://10.10.21.164/api/sys/ip/check", 'POST', json_encode(array("ip" => real_ip())));//132.123.222.222
        $result_json = json_decode($result, true);
        //需要封掉的ip地址
        if ($result_json['result']['type'] == "black") {
            $retArr = $this->responseStruct(4004);
            $retArr['msg'] = "IP 地址限制";
            $this->responseData($retArr);
        }

        return true;
    }

    /**
     * checkSignature
     * 验证签名
     * @access protected
     * @return void
     */
    protected function checkSignature()
    {
        $appid = isset($this->requestData['APPID']) ? $this->requestData['APPID'] : '';
        $questSignature = isset($this->requestData['signature']) ? $this->requestData['signature'] : '';

        $appidArr = array_keys(self::$appidArr);
        if(!in_array($appid, $appidArr)) {
            $this->error('无效的APPID!');
        }
        //前端请求加密不方便
        if(160 == $appid) {
            $questSignature = strtolower(md5($appid.self::SIGN_KEY));
        }
        $currentSign = strtolower(md5($appid.self::SIGN_KEY));
        if($questSignature != $currentSign) {
            $this->error('签名错误!');
        }
        return true;
    }

    /**
     * trimStr
     * 去除两端空格
     * @param mixed $qre
     * @access private
     * @return void
     */
    private function trimStr($qre)
    {
        if(is_array($qre)) {
            foreach($qre as $k => $v) {
                $qre[$k] = trim($v);
            }
            return $qre;
        } else {
            return trim($qre);
        }
    }


    /**
     * fieldVerify
     * 字段验证
     * @access public
     * @return void
     */
    protected function fieldVerify(Array $validateRule, $requestData = array())
    {
        if(empty($requestData)) {
            $requestData = $this->requestData;//获取请求数据
        }
        foreach ($validateRule as $field => $rule) {
            $requestData[$field] = $this->trimStr($requestData[$field]);
            switch($rule['rule']) {
                case 'require' :
                    if (empty($requestData[$field])) {
                        $retArr = $this->responseStruct(4004);
                        $retArr['msg'] = $rule['declare'].'不能为空!';
                        $this->responseData($retArr);
                    }
                break;
                case 'mobile' :
                    if (!is_mobile($requestData[$field])) {
                        $retArr = $this->responseStruct(4004);
                        $retArr['msg'] = $rule['declare'].'格式错误!';
                        $this->responseData($retArr);
                    }
                break;
                case 'ypt_mobile' :
                    $mobile = public_authcode($requestData[$field],'DECODE');
                    if (!is_mobile($mobile)) {
                        $retArr = $this->responseStruct(4004);
                        $retArr['msg'] = $rule['declare'].'格式错误!';
                        $this->responseData($retArr);
                    }
                break;
                case 'number' :
                case 'floatnumber' :
                    if (!is_numeric($requestData[$field])) {
                        $retArr = $this->responseStruct(4004);
                        $retArr['msg'] = $rule['declare'].'需要是数字!';
                        $this->responseData($retArr);
                    }
                    $requestData[$field] = intval($requestData[$field]);
                break;
                case 'in' :
                case 'in|number' :
                    if (!in_array($requestData[$field], $rule['list'])) {
                        $this->error($rule['declare'].'不符合取值范围!');
                    }
                break;
                case 'all|in' :
                    if (null != $requestData[$field] && !in_array($requestData[$field], $rule['list'])) {
                        $this->error($rule['declare'].'不符合取值范围!');
                    }
                break;
                case 'all|number' :
                case 'all|floatnumber' :
                    if (null != $requestData[$field] && !is_numeric($requestData[$field])) {
                        $this->error($rule['declare'].'如果不为空时必须为数字!');
                    }
                break;
                case 'time' :
                    if (!is_numeric($requestData[$field])) {
                        $this->error($rule['declare'].'必须为时间戳!');
                    }
                break;
                case 'all|time' :
                    if (!empty($requestData[$field]) && !is_numeric($requestData[$field])) {
                        $this->error($rule['declare'].'如果不为空时必须为时间戳!');
                    }
                break;
                case 'datestr' :
                    if (!strtotime($requestData[$field])) {
                        $this->error($rule['declare'].'必须为规范化日期串,如(2016-11-11)!');
                    }
                break;
            }
        }
    }

    /**
     * dataFormat
     * 数据根据规则格式化
     * @param mixed $requestData 验证通过后的请求字段
     * @param mixed $validateRule 字段验证规则
     * @access public
     * @return void
     */
    public static function dataFormat($requestData, $validateRule)
    {
        $data = array(
        );

        foreach ($validateRule as $field => $rule) {
            switch($rule['rule']) {
                case 'in|number' :
                case 'number' :
                    $data[$field] = intval($requestData[$field]);
                break;
                case 'floatnumber' :
                    $data[$field] = floatval(sprintf('%.2f',$requestData[$field]));
                break;
                case 'ypt_mobile' :
                    $data[$field] = $requestData[$field];
                break;
                case 'time' :
                    $data[$field] = intval($requestData[$field]);
                    $data[$field.'_format'] = date('Y-m-d G:i:s', $data[$field]);
                break;
                case 'all|number' :
                    //if('0' !== $requestData[$field] . '0') {
                    if(isset($requestData[$field])) {
                        $data[$field] = intval($requestData[$field]);
                    } else {
                        //有默认值用默认值 没有忽略
                        $defaultval = isset($rule['defaultval']) ? intval($rule['defaultval']) : '';
                        if('' !== $defaultval) {
                            $data[$field] = $defaultval;
                        }
                    }
                break;
                case 'all|floatnumber' :
                    if(isset($requestData[$field])) {
                        $data[$field] = floatval(sprintf('%.2f',$requestData[$field]));
                    } else {
                        //有默认值用默认值 没有忽略
                        $defaultval = isset($rule['defaultval']) ? floatval(sprintf('%.2f',$rule['defaultval'])) : 0.00;
                        if(0.00 !== $defaultval) {
                            $data[$field] = $defaultval;
                        }
                    }
                break;
                case 'all|time' :
                    if(!empty($requestData[$field])) {
                        $data[$field] = intval($requestData[$field]);
                        $data[$field.'_format'] = date('Y-m-d G:i:s', $data[$field]);
                    } else {
                        $defaultval = isset($rule['defaultval']) ? intval($rule['defaultval']) : 0;
                        $data[$field] = $defaultval;
                    }
                break;
                default:
                    $defaultval = isset($rule['defaultval']) ? $rule['defaultval'] : '';
                    $data[$field] = isset($requestData[$field]) ? $requestData[$field] : $defaultval;
            }
        }

        return $data;
    }


    /**
     * outputFieldsDoc
     * 导出接口参数列表
     * @access public
     * @return void
     */
    public static function outputFieldsDoc(Array $validateRule)
    {
        if(empty($validateRule)) {
            echo "未定义字段规则";
            return false;
        }
        $retMap = array();
        foreach($validateRule as $key => $valid) {
            $charType = 'String';
            $require = '必须';
            switch($valid['rule']) {
            case 'number':
            case 'floatnumber':
                $charType = 'Number';
                $require = '必须';
                break;
            case 'all' :
                $charType = 'String';
                $require = '选填';
                break;
            case 'all|number' :
            case 'all|floatnumber' :
                $charType = 'Number';
                $require = '选填';
                break;
            case 'all|time' :
                $charType = '时间戳';
                $require = '选填';
                break;
            case 'time' :
                $charType = '时间戳';
                $require = '必须';
                break;
            case 'ypt_mobile' :
                $charType = 'String(手机号加密)';
                $require = '必须';
                break;
            case 'mobile' :
                $charType = 'Number';
                $require = '必须';
                break;
            case 'datestr' :
                $charType = 'Date(Y-m-d)';
                $require = '必须';
                break;
            default :
                $charType = 'String';
                $require = '必须';
            }
            $mark = isset($valid['mark']) ? "[{$valid['mark']}]":'';
            if(isset($valid['list'])) {
                $inStr = implode(',', $valid['list']);
                $inStr = '{'.$inStr.'}';
                $mark .= $inStr;
            }
            $tmp = array();
            $tmp['字段名'] = $key;
            $tmp['类型'] = $charType;
            $tmp['字段说明'] = $require;
            $tmp['备注'] = $valid['declare'].$mark;
            if('signature_2' == $key) {
                $tmp['备注'] = '请求参数除了signature_2以外对所有参数的关联数组按照键名进行升序排序然后{key}{value}{key}{balue}...组成字符串进行md5加密({xx}为变量)';
            }
            $tmp['默认值'] = isset($valid['defaultval']) ? $valid['defaultval']: '无';
            array_push($retMap, $tmp);
        }

        //表头
        $firstRow = current($retMap);
        echo '|';
        foreach($firstRow as $key => $val) {
            echo $key;
            echo '|';
        }
        echo PHP_EOL.'<br>';
        //表头end
        foreach($retMap as $arr) {
            foreach($arr as $dt) {
                echo '|';
                echo $dt;
            }
            echo '|';
            echo PHP_EOL.'<br>';
        }

        echo PHP_EOL.'<br>';
        echo PHP_EOL.'<br>';
        $retJsonArr = array();
        $retJsonArr['APPID'] = 140;
        $retJsonArr['signature'] = strtolower(md5($retJsonArr['APPID'].self::SIGN_KEY));
        foreach($retMap as $subArr) {
            if(empty($subArr['默认值']) || '无' != $subArr['默认值']) {
                $retJsonArr[$subArr['字段名']] = $subArr['默认值'];
                continue;
            }
            switch($subArr['类型']) {
            case 'String':
                $retJsonArr[$subArr['字段名']] = 'aabb';
                break;
            case 'Number':
                $retJsonArr[$subArr['字段名']] = 117824;
                break;
            case 'String(手机号加密)':
                $retJsonArr[$subArr['字段名']] = 13200000001;
                break;
            case '时间戳':
                $retJsonArr[$subArr['字段名']] = time();
                break;
            default:
                $retJsonArr[$subArr['字段名']] = 'aabbdefault';
            }
            if(isset($validateRule[$subArr['字段名']]['list'])) {
                $retJsonArr[$subArr['字段名']] = current($validateRule[$subArr['字段名']]['list']);
            }


            $rang = rand(10000, 99999);
            $mobile = "132000{$rang}";
            if('mobile' == $validateRule[$subArr['字段名']]['rule']) {
                $retJsonArr[$subArr['字段名']] = $mobile;
            }
            if('ypt_mobile' == $validateRule[$subArr['字段名']]['rule']) {
                $retJsonArr[$subArr['字段名']] = public_authcode($mobile, 'ENCODE');
            }
        }
        if($retJsonArr['signature_2']) {
            unset($retJsonArr['signature_2']);
            $retJsonArr['signature_2'] = self::strongSign($retJsonArr);
        }
        //print_r($retJsonArr);exit;

        echo json_encode($retJsonArr, JSON_UNESCAPED_UNICODE);

        echo PHP_EOL.'<br>';
        echo PHP_EOL.'<br>';
    }

    /**
     * outDoc
     * 导出字段说明
     * @access public
     * @return void
     */
    public function outDoc($validateRule = array())
    {
        if(empty($validateRule) && method_exists($this,'validateRule')){
            $validateRule = $this->validateRule();
        }
        self::outputFieldsDoc($validateRule);
    }

    /**
     * mogoQueryShowFields
     * mongo查询根据指定字段显示
     * @param array $validateRule
     * @static
     * @access public
     * @return void
     */
    public static function mogoQueryShowFields($validateRule = array())
    {
        $fieldArr = array();
        foreach($validateRule as $field => $rule) {
            $fieldArr[$field] = 1;
        }
        return $fieldArr;
    }

    /**
     * queryBuilder
     * 查询构建
     * @param Array $query
     * @param Array $rule
     * @param Array $baseQuery
     * @static
     * @access public
     * @return void
     */
    public static function queryBuilder(Array $query, Array $rule, Array $baseQuery = array())
    {
        foreach($query as $field => $val) {
            if(!isset($query[$field]) || empty($val) || !isset($rule[$field]['query'])) {
                continue;
            }

            switch($rule[$field]['query']) {
            case 'eq':
                $baseQuery[$field] = $val;
                break;
            case 'time_gte':
                $timeField = explode('_', $field);
                $baseQuery[$timeField[1]]['$gte'] = strtotime($val);
                break;
            case 'time_lte':
                $timeField = explode('_', $field);
                $baseQuery[$timeField[1]]['$lte'] = strtotime($val);
                break;
            }
        }

        return $baseQuery;
    }

    /**
     * getRealImg
     * 获取有效图片
     * @param string $img_link  图片链接
     * @return img_link
    */
    static function getRealImg($img_link =NULL){
        if($img_link) {
            if(strpos($img_link, "/.") !== false ||
                strpos($img_link, '/view') !== false){
                return 'http://tgi12.jia.com/114/998/14998516.jpg';
            }
        } else {
            return 'http://tgi12.jia.com/114/998/14998516.jpg';
        }

        return $img_link;
    }

    /**
     * memcache获取锁
     */
    public function getLock($key, $expire = 60){
        $memObj = get_instance_of('CacheMemcache');
        //echo $memObj->options['prefix'].$key;
        while(!$memObj->handler->add($memObj->options['prefix'].$key, 1, false, $expire) ){
            usleep(1000);
            @$i++;
            if($i > 5){//尝试等待N次
                return false;
                break;
            }
        }

        return true;

    }

    /**
     * memcache解锁
     */
    public function unLock($key){
        $memObj = get_instance_of('CacheMemcache');
        return $memObj->handler->delete($memObj->options['prefix'].$key, 0);
    }
    /**
     * 获取过滤敏感词汇
     */
    public function getForbidonWords($content){
        $res_res = array();
        $res  = getURL('http://10.10.21.31:10006/sensitive/detail','post',json_encode(array('content'=>$content)));
        $res = json_decode($res,true);
        if($res['result']){
            $result  = trim($res['result'],'[');
            $result  = trim($result,']');
            $res_res = explode(',',$result);
            foreach ($res_res as $kkk=> $vvv){
                $res_res[$kkk] = trim($vvv);
            }
        }
        return $res_res;
    }

    /**
     * checkStrongSign
     * 二次签名认证
     * @access public
     * @return void
     */
    public function checkStrongSign()
    {
        $questSignature = $this->requestData['signature_2'];
        if(empty($questSignature)) {
            $this->error("需要二次签名!");
        }

        $data = $this->requestData;
        unset($data['signature_2']);
        unset($data['HTTP_REFERER']);
        unset($data['_URL_']);
        $currentSign = self::strongSign($data);
        if($questSignature != $currentSign) {
            $this->error('二次签名认证失败!');
        }
        if(time()-$data['timestamp'] > self::TIME_EXCEED) {
            $this->error('二次签名已过期!');
        }
        return true;
    }


    /**
     * 二次签名方法
     * @param $data
     * @return string
     */
    protected static function strongSign($data,$authtype='md5', $needAddSignKey = false)
    {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . $v;
        }
        if($needAddSignKey) {
            $str .= self::SIGN_KEY;
        }
        return $authtype($str);
    }

    public static function strongSignValidateRule()
    {
        return $ruleInfo = array(
            'signature_2'  => array(
                'rule'    => 'require',
                'declare' => '二次签名'
            ),
            'timestamp' =>array(
                'rule'    => 'time',
                'declare' => '服务请求端时间戳'
            ),

        );
    }

}
