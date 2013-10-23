<?php

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
      self::classNameForMock($class_name),
      implode('_', (array)str_replace('_', '__', $interfaces)),
      implode('_', (array)str_replace('_', '__', $traits))
    );
  }

  public static function isHHVM() {
    return defined('HPHP_VERSION');
  }

  public static function getInterfacesAndTraits(array $interfaces = array()) {
    $interfaces[] = 'FBMock_Mock';
    return array(
      $interfaces,
      FBMock_Config::get()->getMockTraits(),
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

  public static function setDoubleImplementation($double, $impl) {
    $double->__implementation = $impl;
  }

  public static function getDoubleImplementation($double) {
    return $double->__implementation;
  }

  public static function classNameForMock($class_name) {
    // Remove namespace separators from class name as mocks are not namespaced.
    return str_replace('\\', '__NS__', $class_name);
  }
}
