<?php

namespace Drupal\suffix_main;

/**
 * Class DbtngExampleStorage.
 */
class UserStorage {

  public static function insert(array $entry) {
    $return_value = NULL;
    try {
      $return_value = db_insert('app_user')
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
      $count = db_update('user__field_mobile')
        ->fields($entry)
        ->condition('id', $entry['id'])
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


    public static function load(array $entry = []) {
        // Read all fields from the dbtng_example table.
        $select = db_select('app_user','a');
        $select->fields('a');

        // Add each field and value as a condition to this query.
        foreach ($entry as $field => $value) {
            $select->condition($field, $value);
        }
        // Return the result in object format.
        return $select->execute()->fetchAll();
    }

    public static function get_one_user_by_mobile($mobile){
        $select = db_select('user__field_mobile','a');
//        $select->fields('a'); //取所有字段
        $select->addField('a', 'entity_id','user_id');//revision_id
        $select->condition('field_mobile_value', $mobile);
        // Return the result in object format.
        return $select->execute()->fetch();
    }

    public static function get_one($entry){
        $select = db_select('users_field_data','a');
        $select->fields('a'); //取所有字段
        foreach ($entry as $field => $value) {
            $select->condition($field, $value);
        }
        // Return the result in object format.
        return $select->execute()->fetch();
    }

    public static function get_user_list_by_role(){ //user__roles   users_field_data
        $select = db_select('user__roles','a');
        $select->join('users_field_data', 'd', 'a.entity_id = d.uid');
        $select->join('user__field_mobile', 'm', 'a.entity_id = m.entity_id');
//        $select->fields('a'); //取所有字段
        $select->addField('d', 'uid','uid');
        $select->addField('d', 'name','user_name');
        $select->addField('m', 'field_mobile_value','mobile');
        $select->condition('a.roles_target_id', 'shop_admin');
        $select->condition('d.status', 1);
//        $select->orderBy('chenged', 'DESC');  //排序
        // Return the result in object format.
        return $select->execute()->fetchAll();
    }

}
