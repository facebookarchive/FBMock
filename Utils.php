<?php
// Copyright 2004-present Facebook. All Rights Reserved.

class FBMock_Utils {
  public static function mockClassNameFor(
      $class_name,
      array $interfaces,
      array $traits) {
    self::assertString($class_name);

    sort($interfaces);
    sort($traits);

    return sprintf(
      'FBMockFramework_%s_%s_%s',
      $class_name,
      implode('_', (array)str_replace('_', '__', $interfaces)),
      implode('_', (array)str_replace('_', '__', $traits))
    );
  }

  public static function isHPHP() {
    return isset($_ENV['HPHP']);
  }

  public static function isHPHPc() {
    return self::isHPHP() &&
      !isset($_ENV['HPHP_INTERPRETER']) &&
      !isset($_ENV['HHVM']);
  }

  public static function getInterfacesAndTraits(array $interfaces = array()) {
    $interfaces[] = 'FBMock_Mock';
    return array(
      $interfaces,
      array_merge(
        array('FBMock_MockObject'),
        FBMock_Config::get()->getExtraMockTraits()
      )
    );
  }

  public static function enforce($invariant, $msg /* args */) {
    if (!$invariant) {
      self::assertString($msg);

      // TODO (#1913833): use FBMock_MockObjectException
      throw new Exception(vsprintf($msg, array_slice(func_get_args(), 2)));
    }
  }

  public static function assertString($str) {
    if (!is_string($str)) {
      throw new InvalidArgumentException(
        "String argument expected, ".gettype($str)." given"
      );
    }
  }

  public static function assertInt($int) {
    if (!is_int($int)) {
      throw new InvalidArgumentException(
        "Integer argument expected, ".gettype($int)." given"
      );
    }
  }
}
