<?php
// Copyright 2004-present Facebook. All Rights Reserved.

class FBMock_MockImplementation {
  private
    $className,
    $isStrictMock = false,
    $methodsToStubs = array(),
    $methodsToCalls = array(),
    $methodsWithDupeParams = array();

  public function __construct($class_name) {
    FBMock_Utils::assertString($class_name);
    $this->className = $class_name;
  }

  public function setImplementation($method, $callable) {
    FBMock_Utils::assertString($method);

    // HHVM doesn't support Callable typehint yet
    FBMock_Utils::enforce(is_callable($callable), 'Must pass a callable');

    $this->methodsToStubs[strtolower($method)] = $callable;
    return $this;
  }

  public function getCalls($method) {
    FBMock_Utils::assertString($method);
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

  public function processMethodCall($method, array $args = array()) {
    FBMock_Utils::assertString($method);
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

  private function getStub($method_name) {
    FBMock_Utils::assertString($method_name);
    if (isset($this->methodsToStubs[strtolower($method_name)])) {
      return $this->methodsToStubs[strtolower($method_name)];
    }

    return null;
  }
}
