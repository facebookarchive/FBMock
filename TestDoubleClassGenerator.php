<?php

/**
 * Generate code for a mock version of a class
 */
class FBMock_TestDoubleClassGenerator {
  public final function generateCode(
      ReflectionClass $class,
      $test_double_class_name,
      array $interfaces = array(),
      array $traits = array(),
      $method_checker = null) {

    FBMock_Utils::assertString($test_double_class_name);
    if ($class->isFinal() && !$this->canOverrideFinals()) {
      throw new FBMock_TestDoubleException(
        "Cannot mock final class %s",
        $class->getName()
      );
    }

    $code = $this->getMockClassHeader(
      $class,
      $test_double_class_name,
      $interfaces,
      $traits
    ) . "\n";

    $method_sources = array();

    foreach ($class->getMethods() as $method) {
      $method_checker && $method_checker($class, $method);

      if ($method->isFinal() && !$this->canOverrideFinals()) {
        continue;
      }

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
      $test_double_class_name,
      array $interfaces,
      array $traits) {

    $extends = '';
    if ($class->isInterface()) {
      $interfaces[] = $class->getName();
    } else {
      $extends = 'extends '.$class->getName();
    }

    $interfaces_str =
      $interfaces ? 'implements '.implode(', ', $interfaces) : '';

    $traits []= 'FBMock_TestDoubleObject';
    $traits_str = 'use '.implode(', ', $traits).';';

    $doc_block = '';
    if (FBMock_Utils::isHHVM()) {
      // Allows mocking final classes and methods.
      // CAUTION: this implementation is subject to change, avoid using outside
      // of FBMock
      $doc_block = '<< __MockClass >>';
    }

    return sprintf(<<<'EOT'
%s
class %s %s %s {
  %s
EOT
      ,
      $doc_block,
      $test_double_class_name,
      $extends,
      $interfaces_str,
      $traits_str
    );
  }

  protected function assertNotFinal(ReflectionClass $c) {
    if ($c->isFinal()) {
      throw new FBMock_TestDoubleException(
        "Cannot mock final class %s",
        $c->getName()
      );
    }
  }

  private function canOverrideFinals() {
    return FBMock_Utils::isHHVM();
  }
}

// Hack - if we include this property directly on the class, it'll show up
// if someone foreach's a mock but it doesn't if we put it in a trait
trait FBMock_TestDoubleObject {
  public $__implementation;
}
