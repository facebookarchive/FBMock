<?php

final class FBMock_MockCreator {
  public static function createMock($class_name, $extra_interfaces = array()) {
    FBMock_Utils::assertString($class_name);
    list($interface_names, $trait_names) =
      FBMock_Utils::getInterfacesAndTraits($extra_interfaces);
    return FBMock_Config::get()->getDoubleCreator()->createTestDoubleFor(
      $class_name,
      $interface_names,
      $trait_names,
      function (ReflectionClass $class, ReflectionMethod $method) {
        if (strpos($method->getName(), 'mock') === 0) {
          throw new FBMock_MockObjectException(
            '%s cannot be mocked because it has a method name that starts '.
            'with "mock": %s. Methods named mock____ are reserved for '.
            'configuring mock objects.',
            $class->getName(),
            $method->getName()
          );
        }
      }
    );
  }

  public static function createStrictMock(
      $class_name,
      $extra_interfaces = array()) {
    FBMock_Utils::assertString($class_name);
    $mock = self::createMock($class_name, $extra_interfaces);
    $mock->__mockImplementation->setStrictMock();
    return $mock;
  }
}
