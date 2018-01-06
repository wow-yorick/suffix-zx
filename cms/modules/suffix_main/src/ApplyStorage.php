<?php

namespace Drupal\suffix_main;

/**
 * Class ApplyStorage.
 */
class ApplyStorage extends BaseStorage {

  public static $db = 'suffix_apply';

  public static function insertApplyInfo(array $entry) {
    return self::insert(self::$db,$entry);
  }

  public static function applyList(array $entry = [],$fields=[],$sort=[],$page=1,$pageNum=10) {
    return self::load(self::$db,$entry,$fields,$sort,$page,$pageNum);
  }

  public static function getApplyInfo($entry=[]){
      return self::getOne(self::$db,$entry);
  }

}
