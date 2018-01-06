<?php

namespace Drupal\suffix_main;

/**
 * Class BaseStorage.
 */
class BaseStorage {
    /**
     * Save an entry in the database.
     *
     * The underlying DBTNG function is db_insert().
     *
     * Exception handling is shown in this example. It could be simplified
     * without the try/catch blocks, but since an insert will throw an exception
     * and terminate your application if the exception is not handled, it is best
     * to employ try/catch.
     *
     * @param array $entry
     *   An array containing all the fields of the database record.
     *
     * @return int
     *   The number of updated rows.
     *
     * @throws \Exception
     *   When the database insert fails.
     *
     * @see db_insert()
     */

   protected function insert($db,array $entry) {
    $return_value = NULL;
    try {
      $return_value = db_insert($db)
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

  protected function update($db,$name='id',array $entry) {
    $entry['modify_time'] = time();
    try {
      // db_update()...->execute() returns the number of rows updated.
      $count = db_update($db)
        ->fields($entry)
        ->condition($name, $entry[$name])
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

  protected function delete($db,$name='id',array $entry) {
    db_delete($db)
      ->condition($name, $entry[$name])
      ->execute();
  }

  protected function load($db,array $entry = [],$fields=[],$sort=[],$page=1,$pageNum=10) {
    // Read all fields from the dbtng_example table.
    $select = db_select($db,'a');
    if(empty($fields)) {
        $select->fields('a');
    }else{
        foreach($fields as $val){
            if(is_array($val)){
                $select->addField('a', $val[0], $val[1]);
            }else {
                $select->addField('a', $val);
            }
        }
    }
//    // Add each field and value as a condition to this query.
    foreach ($entry as $field => $value) {
        if(is_array($value)){
            $select->condition($field, $value[0],$value[1]);
        }else {
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

  protected function getOne($db,$entry=[],$fields=[]){
    $select = db_select($db,'a');
      if(!$fields) {
          $select->fields('a');
      }else{
          foreach($fields as $val){
              if(is_array($val)){
                  $select->addField('a', $val[0], $val[1]);
              }else {
                  $select->addField('a', $val);
              }
          }
      }
    foreach ($entry as $field => $value) {
        if(is_array($value)){
            $select->condition($field, $value[0],$value[1]);
        }else {
            $select->condition($field, $value);
        }
    }
    // Return the result in object format.
    return $select->execute()->fetch();
  }


  protected function getList($dbs,array $entry,array $fields,$sort=[],$page=1,$pageNum=10) {
        foreach ($dbs as $key => $val){
          if($key == 0){
              $select = db_select($val[0],$val[1]);
          }else{
              $select->join($val[0], $val[1], $val[2]);
          }
        }

        foreach($fields as $item){
            if(is_array($item)){
                $select->addField($item[0], $item[1],$item[2]);
            }else {
                exit('字段格式错误');
            }
        }
        foreach ($entry as $field => $value) { //这里条件需与表别名对应
            if(is_array($value)){
                if(count($value) == 3){
                    $select->condition($value[0], $value[1],$value[2]);
                }else {
                    $select->condition($value[0], $value[1]);
                }
            }else {
                exit('条件格式错误');
            }
        }
        $skip = ($page-1)*$pageNum;
        $select->range($skip, $pageNum);
        if($sort) {//这里条件需与表别名对应
            $select->orderBy($sort['field'], $sort['order']);
        }
        $return =  $select->execute()->fetchAll();
        return $return;
    }

    protected function sqlQuery($sql){
        return db_query($sql);
    }

}
