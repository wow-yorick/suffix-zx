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
        $needInfo = NeedStorage::getNeedInfo(array('need_id'=>intval($queryData['need_id'])));
        if(!$needInfo){
            $this->error('需求信息有误');
        }
        $shopInfo = UserStorage::getOneUserByUid(intval($queryData['accept_id']));
        if(!$shopInfo){
            $this->error('商家信息有误');
        }
        $needInfo = $this->json_deal($needInfo);
        $insertData['user_id'] = intval($needInfo['user_id']);
        $insertData['accept_id'] = intval($queryData['accept_id']);
        $insertData['apply_id'] = intval($needInfo['apply_id']);
        $insertData['need_id'] = intval($needInfo['need_id']);
        $insertData['province'] = $needInfo['province'];
        $insertData['city'] = $needInfo['city'];
        $insertData['district'] = $needInfo['district'];
        $insertData['remark'] = $needInfo['remark'];
        if($queryData['add_name']){
            $insertData['create_user'] = $queryData['add_name'];
            $insertData['modify_user'] = $queryData['add_name'];
        }
        $orderId = OrderStorage::insertOrderInfo($insertData);
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
        $param = array();
        if($queryData['need_id']){
            $param['need_id'] = $queryData['need_id'];
        }
        if($queryData['accept_id']){
            $param['accept_id'] = $queryData['accept_id'];
        }
        $list = OrderStorage::orderList($param);
        $this->success($list);
    }

    public function edit_order(){
        $queryData = $this->getRequest();
        $validateRule = array(
            'order_id'=>array(
                'rule'=>'require',
                'declare'=>'订单ID'
            ),
            'status'=>array(
                'rule'=>'all|number',
                'declare'=>'订单状态'
            ),
            'order_status'=>array(
                'rule'=>'all|number',
                'declare'=>'订单处理状态'
            ),
            'modify_user'=>array(
                'rule'=>'all',
                'declare'=>'操作人'
            ),
        );
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        if(!$queryData['status']&&!$queryData['order_status']){
            $this->error('状态不能为空');
        }
        $data = array(
            'order_id' => intval($queryData['order_id']),
        );
        if($queryData['status']){
            $data['status'] = $queryData['status'];
        }
        if($queryData['order_status']){
            $data['order_status'] = $queryData['order_status'];
        }
        if($queryData['modify_user']){
            $data['modify_user'] = $queryData['modify_user'];
        }
        OrderStorage::updateOrderInfo('order_id',$data);
        $this->success('edit_order_ok');
    }

    public function getDefaultValue(){
        return array(
            'user_id' => 0,
            'accept_id' => 0,
            'apply_id' => 0,
            'need_id' => 0,
            'province' => '',
            'city' => '',
            'district' => '',
            'remark' => '',
            'status' => 1,
            'order_status' => 0,
            'create_time' => time(),
            'create_user' => '',
            'modify_time' => time(),
            'modify_user' => '',
        );
    }



}


