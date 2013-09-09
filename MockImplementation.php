<?php

class FBMock_MockImplementation {
  private
    $className,
    $isStrictMock = false,
    $methodsToStubs = array(),
    $methodsToCalls = array();

  public function __construct($class_name) {
    FBMock_Utils::assertString($class_name);
    $this->className = $class_name;
  }

  public function setImplementation($double, $method, $callable) {
    FBMock_Utils::assertString($method);
    $this->assertMethodExists($double, $method);

    // HHVM doesn't support Callable typehint yet
    FBMock_Utils::enforce(is_callable($callable), 'Must pass a callable');

    $this->methodsToStubs[strtolower($method)] = $callable;
    return $this;
  }

  public function getCalls($double, $method) {
    FBMock_Utils::assertString($method);
    $this->assertMethodExists($double, $method);
    if ($this->isStrictMock) {
      FBMock_Utils::enforce(
        $this->getStub($method),
        "Trying to fetch calls for unmocked method %s on strict mock of %s",
        $method,
        $this->className
      );
    }

    if (isset($this->methodsToCalls[strtolower($method)])) {
      return $this->methodsToCalls[strtolower($method)];
    }

    return array();
  }

  public function processMethodCall($double, $method, array $args = array()) {
    FBMock_Utils::assertString($method);
    $this->assertMethodExists($double, $method);
    $this->methodsToCalls[strtolower($method)][] = $args;

    $stub = $this->getStub($method);
    if ($stub) {
      return call_user_func_array($stub, $args);
    } else if ($this->isStrictMock) {
      throw new FBMock_MockObjectException(
        'Unmocked method %s called on strict mock of %s',
        $method,
        $this->className
      );
    }
    return null;
  }

  public function setStrictMock() {
    $this->isStrictMock = true;
    return $this;
  }

  protected function assertMethodExists($double, $method_name) {
    FBMock_Utils::assertString($method_name);

    // If they've implemented __call, we have no idea if this method is legit
    if (method_exists($double, '__call')) {
      return;
    }

    $method_exists = method_exists($double, $method_name);
    if ($method_exists) {
      $ref_method = new ReflectionMethod($double, $method_name);
      $real_name = $ref_method->getName();
      if ($real_name != $method_name) {
        throw new FBMock_MockObjectException(
          'Method "%s" does not exist for %s. Did you mean %s? The mocks '.
            'framework is case sensitive',
          $method_name,
          $this->className,
          $real_name
        );
      }
    }
    if (!$method_exists) {
      throw new FBMock_MockObjectException(
        'Method "%s" does not exist for %s',
        $method_name,
        $this->className
      );
    }
  }

  private function getStub($method_name) {
    FBMock_Utils::assertString($method_name);
    if (isset($this->methodsToStubs[strtolower($method_name)])) {
      return $this->methodsToStubs[strtolower($method_name)];
    }

    return null;
  }
}
