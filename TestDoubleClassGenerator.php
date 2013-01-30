<?php

/**
 * Generate code for a mock version of a class
 */
class FBMock_TestDoubleClassGenerator {
  public final function generateCode(
      ReflectionClass $class,
      array $interfaces = array(),
      array $traits = array(),
      $method_checker = null) {
    $code = $this->getMockClassHeader($class, $interfaces, $traits) . "\n";

    $method_sources = array();

    foreach ($class->getMethods() as $method) {
      $method_checker && $method_checker($class, $method);

      // #1137433
      if (!$class->isInterface()) {
        $method = new ReflectionMethod($class->getName(), $method->getName());
      }
      $test_double_method_generator =
        FBMock_Config::get()->getMethodGenerator();
      $method_source = $test_double_method_generator->generateCode($method);
      if ($method_source) {
        $method_sources[] = $method_source;
      }
    }

    $code .= implode("\n\n", $method_sources);
    $code .= "\n}"; // close class

    return $code;
  }

  public final function getMockClassHeader(
      ReflectionClass $class,
      array $interfaces,
      array $traits) {

    $mock_class_name =
      FBMock_Utils::mockClassNameFor($class->getName(), $interfaces, $traits);

    $extends = '';
    if ($class->isInterface()) {
      $interfaces[] = $class->getName();
    } else {
      $extends = 'extends '.$class->getName();
    }

    $interfaces_str =
      $interfaces ? 'implements '.implode(', ', $interfaces) : '';

    $traits_str =
      $traits ? 'use '.implode(', ', $traits).';' : '';

    // << __MockClass >> is a user attribute for extending HPHP. It allows us to
    // override final methods and subclass final classes.
    return sprintf(<<<EOD
%s
class %s %s %s {
  %s
EOD
      ,
      $this->getDocBlock(),
      $mock_class_name,
      $extends,
      $interfaces_str,
      $traits_str
    );
  }

  // Override this to add a custom doc block at the tops of classes
  public function getDocBlock() {
    return '';
  }
}
