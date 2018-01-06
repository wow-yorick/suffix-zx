<?php

namespace Drupal\suffix_main;

/**
 * Class OrderStorage.
 */
class OrderStorage extends BaseStorage {

  public static $db = 'suffix_order';

  public static function insertOrderInfo(array $entry) {
      return self::insert(self::$db,$entry);
  }

  public static function updateOrderInfo($name,array $entry) {
    return self::update(self::$db,$name,$entry);
  }
    public static function orderList(array $entry = [],$fields=[],$sort=[],$page=1,$pageNum=20) {
        return self::load(self::$db,$entry,$fields,$sort,$page,$pageNum);
    }

  public static function getOrderInfo($entry=[]){
    return self::getOne(self::$db,$entry);
  }

}
