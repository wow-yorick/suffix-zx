<?php
/**
 * @file
 * Contains \Drupal\app_user\Controller\UserController.
 */
namespace Drupal\app_user\Controller;

class ProductController extends APIBaseController {

    //产品分类
    public function classify() {
        //调用默认规范验证(非必须)
        $ruleInfo = array(
//            'c_id' => array(
//                'rule' => 'require',
//                'declare' => '最美设备ID',
//            ),
        );
        $this->fieldVerify($ruleInfo);

        //业务逻辑start
        $retMap = array(
            'success' => false,
            'msg' =>'获取失败!',
            'result'=>array(),
        );
        //$dataInfo = $this->dataFormat($this->requestData, $ruleInfo);//过滤掉不在ruleInfo指定字段中的值
        $allProductType = \Drupal::entityTypeManager()->getStorage('commerce_product_type')->loadMultiple();
        if(!$allProductType){
            $this->error($retMap['msg']);
        }
        foreach ($allProductType as $key => $ptObj) {
            $tmp = $ptObj->toArray();
            array_push($retMap['result'], $tmp);
        }
        //dump($retMap);exit;
//        if(!$retMap['success']) {
//            $this->error($retMap['msg']);
//        }
        $retMap['success'] = true;
        $retMap['msg'] ="获取成功!";
        //业务逻辑end
        $this->success($retMap['result'], array('msg'=>$retMap['msg']));
    }

    public function productlist()
    {
        $retMap = array(
            'success' => false,
            'msg' =>'获取失败!',
            'result'=>array(),
        );

        //调用默认规范验证(非必须)
        $ruleInfo = array(
            'variation_type' => array(
                'rule' => 'require',
                'declare' => '产品类型',
            ),
        );
        $this->fieldVerify($ruleInfo);

        //业务逻辑start
        $dataInfo = $this->dataFormat($this->requestData, $ruleInfo);//过滤掉不在ruleInfo指定字段中的值

        $productList = \Drupal::entityTypeManager()->getStorage('commerce_product')->loadByProperties(['type'=>$dataInfo['variation_type']]);
        if(!$productList) {
            $this->error($retMap['msg']);
        }
        foreach ($productList as $proObj) {
            array_push($retMap['result'], $proObj->toArray());
        }
        //dump($productList);exit;
        $retMap['success'] = true;
        $retMap['msg'] ="获取成功!";
        //业务逻辑end
        $this->success($retMap['result']);
    }
}
