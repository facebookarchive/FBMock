<?php

class FBMock_TestDoubleCreator {
  public final function createTestDoubleFor(
      $class_name,
      array $interfaces = array(),
      array $traits = array(),
      $method_checker = null) {

    FBMock_Utils::assertString($class_name);
    $this->assertAllowed();

    if (!class_exists($class_name) && !interface_exists($class_name)) {
      throw new FBMock_TestDoubleException(
        "Attempting to mock $class_name but $class_name isn't loaded."
      );
    }

    $mock_class_name = FBMock_Utils::mockClassNameFor(
      $class_name,
      $interfaces,
      $traits
    );

    $ref_class = new ReflectionClass($class_name);

    if ($ref_class->isInternal() && !FBMock_Utils::isHHVM()) {
      throw new FBMock_TestDoubleException(
        "Trying to mock PHP internal class $class_name. Mocking of internal ".
        "classes is only supported in HHVM."
      );
    }

    if (!class_exists($mock_class_name, $autoload = false)) {
      $class_generator_class = FBMock_Config::get()->getClassGenerator();
      $class_generator = new $class_generator_class();
      $code = $class_generator->generateCode(
        $ref_class,
        $mock_class_name,
        $interfaces,
        $traits,
        $method_checker
      );

      eval($code);
    }

    $mock_object = (new ReflectionClass($mock_class_name))
      ->newInstanceWithoutConstructor();

    return $mock_object;
  }

  // Hook to disallow doubles in certain cases (such as prod)
  protected function assertAllowed() {}
}
