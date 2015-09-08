<?php
namespace aol\core;

class Object_Converter extends Atomic_Object {
  public static function to_array($target) {
    if (is_object($target)) {
      $target = get_object_vars($target);
    }

    if (is_array($target)) {
      return array_map('aol\core\Object_Converter::to_array', $target);
    }
    else
      return $target;
  }

  public static function to_object($target) {
    if (is_array($target)) {
      return (object)array_map('aol\core\Object_Converter::to_object', $target);
    }
    else
      return $target;
  }
}
