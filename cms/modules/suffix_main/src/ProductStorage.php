<?php

namespace Drupal\suffix_main;
use Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight\B;

/**
 * Class ProductStorage.
 */
class ProductStorage extends BaseStorage {

  public static function getProductList(array $entry = [],$sort=[],$page=1,$pageNum=100) {
      $dbs = array(
          array('commerce_product_field_data','a'),
          array('commerce_product__stores', 'd', 'a.product_id = d.entity_id'),
          array('commerce_product__field_fengmianjiage', 'u', 'a.product_id = u.entity_id'),
      );
      $fields = array(
          array('a', 'product_id','product_id'),
          array('a', 'type','type'),
          array('a', 'title','title'),
          array('d', 'deleted','online'),
          array('u', 'field_fengmianjiage_number','price'),
      );
      $param = array();
      foreach ($entry as $field => $value) {
        if($field == 'deleted'){
            $db = 'd';
        }else{
            $db = 'a';
        }
        if(is_array($value)){
            $param[] = array($db.'.'.$field, $value[0],$value[1]);
        }else {
            $param[] = array($db.'.'.$field, $value);
        }
      }
      return self::getList($dbs,$param,$fields,$sort,$page,$pageNum);
  }
//commerce_product  commerce_product__field_fengmianjiage  commerce_product_field_data commerce_product__stores
  public static function getProductInfo($product_id){
    $select = db_select('commerce_product_field_data','a');
      $select->join('commerce_product__stores', 'd', 'a.product_id = d.entity_id');
      $select->join('commerce_product__field_fengmianjiage', 'u', 'a.product_id = u.entity_id');
//    $select->fields('a'); //取所有字段
      $select->addField('a', 'product_id','product_id');//revision_id
      $select->addField('a', 'type','type');
      $select->addField('a', 'title','title');
      $select->addField('d', 'deleted','online');
      $select->addField('u', 'field_fengmianjiage_number','price');//revision_id
      $select->condition('a.product_id', $product_id);

    // Return the result in object format.
    return $select->execute()->fetch();
  }

  public static function getProductKind(){
      $select = db_select('commerce_product','a');
      $select->addField('a', 'type','type');
      $select->distinct();
      $types = $select->execute()->fetchAll();
//      return $types->condition('type',array(),'');
      return $types;
  }

}
