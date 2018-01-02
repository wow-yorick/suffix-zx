<?php
/**
 * @file
 * Contains \Drupal\suffix_main\Controller\OrderController.
 */
namespace Drupal\suffix_main\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\suffix_main\NeedStorage;
use Drupal\suffix_main\OrderStorage;
use Drupal\suffix_main\UserStorage;

class OrderController extends APIBaseController {

    public function __construct()
    {

    }

    public function add_order(){
        $queryData = $this->getRequest();
        $validateRule = array(
            'need_id'=>array(
                'rule'=>'require',
                'declare'=>'需求ID'
            ),
            'accept_id'=>array(
                'rule'=>'all|number',
                'declare'=>'商家ID'
            ),
            'add_name'=>array(
                'rule'=>'all',
                'declare'=>'操作人'
            ),
        );
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        $insertData = $this->getDefaultValue();
        $needInfo = NeedStorage::get_one(array('need_id'=>intval($queryData['need_id'])));
        if(!$needInfo){
            $this->error('需求信息有误');
        }
        $shopInfo = UserStorage::get_one(array('uid'=>intval($queryData['accept_id'])));
        if(!$shopInfo){
            $this->error('商家信息有误');
        }
        $needInfo = $this->json_deal($needInfo);
        $insertData['user_id'] = $needInfo['user_id'];
        $insertData['accept_id'] = intval($queryData['accept_id']);
        $insertData['apply_id'] = $needInfo['apply_id'];
        $insertData['need_id'] = $needInfo['need_id'];
        $insertData['province'] = $needInfo['province'];
        $insertData['city'] = $needInfo['city'];
        $insertData['district'] = $needInfo['district'];
        $insertData['remark'] = $needInfo['remark'];
        if($queryData['add_name']){
            $insertData['create_user'] = $queryData['add_name'];
            $insertData['modify_user'] = $queryData['add_name'];
        }
        $orderId = OrderStorage::insert($insertData);
        $this->success($orderId);

    }

    public function orderList(){
        $queryData = $this->getRequest();
        $validateRule = array(
            'need_id'=>array(
                'rule'=>'all',
                'declare'=>'需求ID'
            ),
            'accept_id'=>array(
                'rule'=>'all|number',
                'declare'=>'商家ID'
            ),
        );
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        $list = OrderStorage::load($queryData);
        $this->success($list);
    }

    public function edit_order(){

        $this->success('edit_order_ok');
    }

    public function getDefaultValue(){
        return array(
            'user_id' => null,
            'accept_id' => null,
            'apply_id' => null,
            'need_id' => null,
            'province' => '',
            'city' => '',
            'district' => '',
            'remark' => '',
            'status' => 1,
            'create_time' => time(),
            'create_user' => '',
            'modify_time' => time(),
            'modify_user' => '',
        );
    }



}


