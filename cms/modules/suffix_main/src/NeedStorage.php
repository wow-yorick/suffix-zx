<?php

namespace Drupal\suffix_main;

/**
 * Class NeedStorage.
 */
class NeedStorage {
  public static function insert(array $entry) {
//    $return_value = NULL;
    try {
      $return_value = db_insert('suffix_need')
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      drupal_set_message(t('db_insert failed. Message = %message, query= %query', [
        '%message' => $e->getMessage(),
        '%query' => $e->query_string,
      ]
      ), 'error');
    }
    return $return_value;
  }

  public static function update(array $entry) {
    try {
      // db_update()...->execute() returns the number of rows updated.
      $count = db_update('suffix_need')
        ->fields($entry)
        ->condition('need_id', $entry['need_id'])
        ->execute();
    }
    catch (\Exception $e) {
      drupal_set_message(t('db_update failed. Message = %message, query= %query', [
        '%message' => $e->getMessage(),
        '%query' => $e->query_string,
      ]
      ), 'error');
    }
    return $count;
  }

  public static function delete(array $entry) {
    db_delete('suffix_need')
      ->condition('need_id', $entry['need_id'])
      ->execute();
  }

  public static function load(array $entry = [],$sort=[],$page=1,$pageNum=10) {
    // Read all fields from the dbtng_example table.
    $select = db_select('suffix_need','a');
    $select->fields('a');

    // Add each field and value as a condition to this query.
    foreach ($entry as $field => $value) {
        if(is_array($value)){
            $select->condition($field, $value[0],$value[1]);
        }else{
            $select->condition($field, $value);
        }
    }
    $skip = ($page-1)*$pageNum;
    $select->range($skip, $pageNum);
    if($sort) {
        $select->orderBy($sort['field'], $sort['order']);
    }
    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

  public static function get_one($entry=[]){
    $select = db_select('suffix_need','a');
    $select->fields('a'); //取所有字段
//        $select->addField('a', 'entity_id','user_id');//revision_id
    foreach ($entry as $field => $value) {
        if(is_array($value)){
            $select->condition($field, (string)$value[0],$value[1]);
        }else {
            $select->condition($field, $value);
        }
    }
    // Return the result in object format.
    return $select->execute()->fetch();
  }

}
