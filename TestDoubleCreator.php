<?php

class FBMock_TestDoubleCreator {
  public final function createTestDoubleFor(
      $class_name,
      array $interfaces = array(),
      array $traits = array(),
      $method_checker = null) {

    FBMock_Utils::assertString($class_name);
    if (!class_exists($class_name) && !interface_exists($class_name)) {
      throw new FBMock_TestDoubleException(
        "Attempting to mock $class_name but $class_name isn't loaded."
      );
    }

    $mock_class_name =
      FBMock_Utils::mockClassNameFor($class_name, $interfaces, $traits);
    if (!class_exists($mock_class_name, /* autoload */ false)) {
      if (FBMock_Utils::isHPHPc()) {
        $this->loadMockForHPHPc($class_name, $interfaces, $traits);
      } else {
        $class_generator_class = FBMock_Config::get()->getClassGenerator();
        $class_generator = new $class_generator_class();
        $code = $class_generator->generateCode(
          new ReflectionClass($class_name),
          $interfaces,
          $traits,
          $method_checker
        );
        eval($code);
      }
    }

    // Hack to avoid calling constructor
    $mock_object = unserialize(
      sprintf('O:%d:"%s":0:{}', strlen($mock_class_name), $mock_class_name)
    );

    $mock_object->__mockImplementation =
      new FBMock_MockImplementation($class_name);

    $this->postCreateHandler($mock_object);
    return $mock_object;
  }

  protected function loadMockForHPHPc($class_name, $interfaces, $traits) {
    throw new FBMock_MockObjectException('HPHPc is not supported');
  }

  // Override to add custom logic to mocks after they are created
  protected function postCreateHandler($double) { }
}
