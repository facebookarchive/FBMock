<?php

class FBMock_MockCreator {
  public final function createMock($class_name, $extra_interfaces = array()) {
    FBMock_Utils::assertString($class_name);
    list($interface_names, $trait_names) =
      FBMock_Utils::getInterfacesAndTraits($extra_interfaces);
    $double = FBMock_Config::get()->getTestDoubleCreator()->createTestDoubleFor(
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
    FBMock_Utils::setDoubleImplementation(
      $double,
      FBMock_Config::get()->createMockImplementation($class_name)
    );

    $this->postCreateHandler($double);

    return $double;
  }

  public final function createStrictMock(
      $class_name,
      $extra_interfaces = array()) {
    FBMock_Utils::assertString($class_name);
    $mock = self::createMock($class_name, $extra_interfaces);
    FBMock_Utils::getDoubleImplementation($mock)->setStrictMock();
    return $mock;
  }

  // Override to add custom logic to mocks after they are created
  protected function postCreateHandler($double) { }
}
