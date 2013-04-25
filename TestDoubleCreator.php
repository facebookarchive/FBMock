<?php

class FBMock_TestDoubleCreator {
  public final function createTestDoubleFor(
      $class_name,
      array $interfaces = array(),
      array $traits = array(),
      $method_checker = null) {

    static $class_name_to_mock_count;
    FBMock_Utils::assertString($class_name);
    if (!class_exists($class_name) && !interface_exists($class_name)) {
      throw new FBMock_TestDoubleException(
        "Attempting to mock $class_name but $class_name isn't loaded."
      );
    }

    if (!isset($class_name_to_mock_count[$class_name])) {
      $class_name_to_mock_count[$class_name] = 0;
    }

    $mock_class_name = FBMock_Utils::mockClassNameFor(
      $class_name,
      $interfaces,
      $traits,
      $class_name_to_mock_count[$class_name]++
    );

    $class_generator_class = FBMock_Config::get()->getClassGenerator();
    $class_generator = new $class_generator_class();
    $code = $class_generator->generateCode(
      new ReflectionClass($class_name),
      $mock_class_name,
      $interfaces,
      $traits,
      $method_checker
    );
    eval($code);

    $use_serialize = false;
    $hierarchy = class_parents($class_name);
    $hierarchy[$class_name] = $class_name;

    foreach ($hierarchy as $class) {
      if ((new ReflectionClass($class))->isInternal()) {
        $use_serialize = true;
        break;
      }
    }

    if ($use_serialize) {
      $mock_object = unserialize(
        sprintf('O:%d:"%s":0:{}', strlen($mock_class_name), $mock_class_name)
      );
    } else {
      $mock_object = (new ReflectionClass($mock_class_name))
        ->newInstanceWithoutConstructor();
    }

    $mock_object->__mockImplementation =
      new FBMock_MockImplementation($class_name);

    $this->postCreateHandler($mock_object);
    return $mock_object;
  }

  // Override to add custom logic to mocks after they are created
  protected function postCreateHandler($double) { }
}
