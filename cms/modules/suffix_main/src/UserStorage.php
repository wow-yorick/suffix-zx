<?php

namespace Drupal\suffix_main;

/**
 * Class DbtngExampleStorage.
 */
class UserStorage extends BaseStorage {

    public static function getOneUserByMobile($mobile){
        return self::getOne('user__field_mobile',array('field_mobile_value'=>$mobile),array(array('entity_id','user_id')));
    }

    public static function getOneUserByUid($uid){
        return self::getOne('users',array('uid'=>$uid),array(array('uid','user_id')));
    }

    public static function getUserListByRole(){ //user__roles   users_field_data
        $dbs = array(
            array('user__roles','a'),
            array('users_field_data','b','a.entity_id = b.uid'),
            array('user__field_mobile','c','a.entity_id = c.entity_id'),
        );
        $entry = array(
            array('a.roles_target_id','shop_admin'),
            array('b.status',1),
        );
        $fields = array(
            array('b', 'uid','uid'),
            array('b', 'name','user_name'),
            array('c', 'field_mobile_value','mobile'),
        );
        return self::getList($dbs,$entry,$fields,array(),1,10);
    }

}
