<?php

namespace Drupal\suffix_main;

/**
 * Class NeedStorage.
 */
class NeedStorage extends BaseStorage {

  public static $db = 'suffix_need';

  public static function insertNeedInfo(array $entry) {
    return self::insert(self::$db,$entry);
  }

  public static function updateNeedInfo($name,array $entry) {
    return self::update(self::$db,$name,$entry);
  }

  public static function needList(array $entry = [],$fields=[],$sort=[],$page=1,$pageNum=10) {
      return self::load(self::$db,$entry,$fields,$sort,$page,$pageNum);
  }

  public static function getNeedInfo($entry=[]){
    return self::getOne(self::$db,$entry);
  }

  function sqlSearch($sql){
      return self::sqlQuery($sql);
  }

}
