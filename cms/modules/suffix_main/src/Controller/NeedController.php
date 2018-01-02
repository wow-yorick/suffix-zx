<?php
/**
 * @file
 * Contains \Drupal\suffix_main\Controller\NeedController.
 */
namespace Drupal\suffix_main\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\suffix_main\Controller\OrderController;
use Drupal\suffix_main\ApplyStorage;
use Drupal\suffix_main\ProductStorage;
use Drupal\suffix_main\NeedStorage;
use Drupal\suffix_main\UserStorage;
use Drupal\suffix_main\OrderStorage;

class NeedController extends APIBaseController {

    public function __construct()
    {
    }

    public function add_need(){
        $queryData = $this->getRequest();
        $validateRule = array(
            'apply_id'=>array(
                'rule'=>'require',
                'declare'=>'报名号'
            ),
            'product_id'=>array(
                'rule'=>'all|number',
                'declare'=>'产品ID'
            ),
            'add_name'=>array(
                'rule'=>'all',
                'declare'=>'操作人'
            ),
        );
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        $insertData = $this->getDefaultValue();
        $insertData['apply_id'] = intval($queryData['apply_id']);
        $insertData['product_id'] = $queryData['product_id']?intval($queryData['product_id']):null;
        $applyInfo = ApplyStorage::get_one(array('apply_id'=>intval($queryData['apply_id'])));
        if(!$applyInfo){
            $this->error('预约信息错误');
        }
        $applyInfo = $this->json_deal($applyInfo);
        $insertData['user_id'] = $applyInfo['uid'];
        $insertData['province'] = $applyInfo['province'];
        $insertData['city'] = $applyInfo['city'];
        $insertData['district'] = $applyInfo['district'];
        $insertData['address'] = $applyInfo['address'];
        $insertData['longitude'] = $applyInfo['longitude'];
        $insertData['latitude'] = $applyInfo['latitude'];
        $insertData['remark'] = $applyInfo['remark'];
        if($queryData['product_id']) {
            $productInfo = ProductStorage::get_one(intval($queryData['product_id']));
            if (!$productInfo) {
                $this->error('产品信息错误');
            }
        }
        if($queryData['add_name']){
            $insertData['create_user'] = $queryData['create_user'];
            $insertData['modify_user'] = $queryData['modify_user'];
        }
        $res = NeedStorage::insert($insertData);
        $return = array('need_id'=>$res);
        $orderId = $this->assignOrder(2); //自动分单逻辑
        if($orderId){
            $return['order_id'] = $orderId;
            //改变需求状态逻辑
            NeedStorage::update(array('need_id'=>intval($res),'status'=>2));
        }
        $this->success($return);
    }

    public function getProductList(){
        $queryData = $this->getRequest();
        $validateRule = array(
            'type'=>array(
                'rule'=>'require',
                'declare'=>'分类'
            ),
            'online'=>array(
                'rule'=>'all|number',
                'declare'=>'是否上架',
                'default'=>0
            ),
        );
        $this->fieldVerify($validateRule,$queryData);
        $queryData = self::dataFormat($queryData,$validateRule);
        $list = ProductStorage::get_product_list(array('type'=>$queryData['type'],'deleted'=>intval($queryData['online'])));
        $this->success($list);
    }

    public function edit_need(){
        $this->success('edit_need_ok');
    }

    private function getDefaultValue(){//补全插入数据字段
        return array(
            'user_id' => null,
            'apply_id' => null,
            'product_id' => null,
            'province' => '',
            'city' => '',
            'district' => '',
            'address' => '',
            'longitude' => '',
            'latitude' => '',
            'remark' => '',
            'status' => 1,
            'create_time' => time(),
            'create_user' => '',
            'modify_time' => time(),
            'modify_user' => '',
        );
    }

    private function validateRule(){
        return array();
    }

    private function assignOrder($need_id){
        $return  = null;
        $shopList = UserStorage::get_user_list_by_role();
        if($shopList){
            $shopList = $this->json_deal($shopList);
            $shopId = $shopList[0]['uid'];
            $return  = $this->auto_add_order($need_id,$shopId);
        }
        return $return;
    }

    public function auto_add_order($need_id,$shopId){
        $insertData = OrderController::getDefaultValue();
        $needInfo = NeedStorage::get_one(array('need_id'=>intval($need_id)));
        if(!$needInfo){
            $this->error('需求信息有误');
        }
        $shopInfo = UserStorage::get_one(array('uid'=>intval($shopId)));
        if(!$shopInfo){
            $this->error('商家信息有误');
        }
        $needInfo = $this->json_deal($needInfo);
        $insertData['user_id'] = $needInfo['user_id'];
        $insertData['accept_id'] = intval($shopId);
        $insertData['apply_id'] = $needInfo['apply_id'];
        $insertData['need_id'] = $needInfo['need_id'];
        $insertData['province'] = $needInfo['province'];
        $insertData['city'] = $needInfo['city'];
        $insertData['district'] = $needInfo['district'];
        $insertData['remark'] = $needInfo['remark'];
        $orderId = OrderStorage::insert($insertData);
        return $orderId?:null;
    }

}


