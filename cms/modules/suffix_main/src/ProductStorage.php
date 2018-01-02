<?php

namespace Drupal\suffix_main;

/**
 * Class ProductStorage.
 */
class ProductStorage {

  public static function get_product_list(array $entry = [],$sort=[],$page=1,$pageNum=10) {
    // Read all fields from the dbtng_example table.
      $select = db_select('commerce_product_field_data','a');
      $select->join('commerce_product__stores', 'd', 'a.product_id = d.entity_id');//revision_id
      $select->join('commerce_product__field_fengmianjiage', 'u', 'a.product_id = u.entity_id');
//    $select->fields('a'); //取所有字段
      $select->addField('a', 'product_id','product_id');
      $select->addField('a', 'type','type');
      $select->addField('a', 'title','title');
      $select->addField('d', 'deleted','online');
      $select->addField('u', 'field_fengmianjiage_number','price');//revision_id
    // Add each field and value as a condition to this query.
    foreach ($entry as $field => $value) {
        if($field == 'deleted'){
            $db = 'd';
        }else{
            $db = 'a';
        }
        if(is_array($value)){
            $select->condition($db.'.'.$field, (string)$value[0],$value[1]);
        }else {
            $select->condition($db.'.'.$field, $value);
        }
    }
    $skip = ($page-1)*$pageNum;
    $select->range($skip, $pageNum);
    if($sort) {
        $select->orderBy($sort['field'], $sort['order']);
    }
    // Return the result in object format.
    $return =  $select->execute()->fetchAll();
    return $return;
  }
//commerce_product  commerce_product__field_fengmianjiage  commerce_product_field_data commerce_product__stores
  public static function get_one($product_id){
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

}
