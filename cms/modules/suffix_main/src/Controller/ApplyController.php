<?php
/**
 * @file
 * Contains \Drupal\suffix_main\Controller\ApplyController.
 */
namespace Drupal\suffix_main\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\suffix_main\UserStorage;
use Drupal\suffix_main\ApplyStorage;
class ApplyController extends APIBaseController {
    public function __construct()
    {
//        $this->checkSignature();
    }

    public function apply(){
        $queryData = $this->getRequest();
        $validateRule = $this->validateRule();
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        $applyInfo = ApplyStorage::get_one(array('mobile'=>$queryData['mobile'],'create_time'=>array(0=>1514788801,1=>'>')));
        $insertData = $this->getDefaultValue();
        $insertData['username'] = $queryData['username'];
        $insertData['mobile'] = $queryData['mobile'];
        $oldUser = UserStorage::get_one_user_by_mobile($queryData['mobile']);
        if(!empty($oldUser)){
            $oldUser = $this->json_deal($oldUser);
            $insertData['uid'] = $oldUser['user_id'];
        }
        if($queryData['longitude'] && $queryData['latitude']){//31.255923,121.462056  根据经纬度 获取预约所在省市区
            $cityInfo = $this->getCityInfo($queryData['latitude'],$queryData['longitude']);
            $preg = '/renderReverse&&renderReverse\((.*)\)/';
            preg_match($preg,$cityInfo,$match);
            $cityInfo = json_decode($match[1],true);
            $cityInfoRes = $cityInfo['result'];
            $insertData['province'] = $cityInfoRes['addressComponent']['province'];
            $insertData['city'] = $cityInfoRes['addressComponent']['city'];
            $insertData['district'] = $cityInfoRes['addressComponent']['district'];
            $insertData['latitude'] = $queryData['latitude'];
            $insertData['longitude'] = $queryData['longitude'];
        }

        if($queryData['product_id']){
            //购物车预约逻辑
        }
        $res = ApplyStorage::insert($insertData);
        $this->success($res);
    }

    public function getApplyList(){
        $queryData = $this->getRequest();
        $validateRule = array(
            'city'=>array(
                'rule'=>'all',
                'declare'=>'城市'
            ),
        );
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        $param = array();
        if($queryData['city']){
            $param['city'] = $queryData['city'];
        }
        $list = ApplyStorage::load($param,array('field'=>'create_time','order'=>'DESC'));
        $this->success($list);
    }

    private function getDefaultValue(){//补全插入数据字段
        return array(
            'username' => '',
            'mobile' => '',
            'uid'=>null,
            'gender' => 0,
            'product_id'=>null,
            'province' => '',
            'city' => '',
            'district' => '',
            'address' => '',
            'longitude' => '',
            'latitude' => '',
            'remark' => '',
            'create_time' => time()
        );
    }

    private function validateRule(){
        return array(
            'username'=>array(
                'rule'=>'require',
                'declare'=>'用户名'
            ),
            'mobile'=>array(
                'rule'=>'require',
                'declare'=>'手机号'
            ),
            'product_id'=>array(
                'rule'=>'all|number',
                'declare'=>'产品ID'
            ),
            'gender'=>array(
                'rule'=>'all|number',
                'declare'=>'性别'
            ),
            'longitude'=>array(
                'rule'=>'all',
                'declare'=>'经度'
            ),
            'latitude'=>array(
                'rule'=>'all',
                'declare'=>'纬度'
            ),
        );
    }

}


