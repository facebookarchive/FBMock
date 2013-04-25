<?php

class FBMock_Utils {
  public static function mockClassNameFor(
      $class_name,
      array $interfaces,
      array $traits,
      $instance_number) {

    self::assertString($class_name);
    self::assertInt($instance_number);

    sort($interfaces);
    sort($traits);

    return sprintf(
      'FBMockFramework_%s_%s_%s_%d',
      $class_name,
      implode('_', (array)str_replace('_', '__', $interfaces)),
      implode('_', (array)str_replace('_', '__', $traits)),
      $instance_number
    );
  }

  public static function isHPHP() {
    return isset($_ENV['HPHP']);
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
}
