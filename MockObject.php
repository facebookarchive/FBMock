<?php

/**
 * Adds methods to a mock object for configuring its return values and spying
 * on method calls.
 *
 * All methods should be prefixed with "mock" to avoid collisions with other
 * methods on the object being mocked.
 *
 * See Mock interface for documentation.
 */
trait FBMock_MockObject { // implements Mock
  public function mockReturn(/* ... */) {
    $args = $this->mockAssertMultiArgs(func_get_args(), __METHOD__);

    if (count($args) == 1) {
      foreach ($args[0] as $method_name => $value) {
        FBMock_Utils::assertString($method_name);
        $this->__mockReturn($method_name, $value);
      }
      return $this;
    }

    return $this->__mockReturn($args[0], $args[1]);
  }

  private function mockAssertMultiArgs($args, $method_name) {
    FBMock_Utils::assertString($method_name);
    if (count($args) == 0 || count($args) > 2) {
      throw new FBMock_MockObjectException(
        "$method_name expects method name and return value or map of method ".
        "names to return values"
      );
    }

    return $args;
  }

  private function __mockReturn($method_name, $value) {
    FBMock_Utils::assertString($method_name);
    return $this->mockImplementation($method_name, function() use ($value) {
      return $value;
    });
  }

  public function mockImplementation($method_name, $callable) {
    FBMock_Utils::assertString($method_name);

    FBMock_Utils::getDoubleImplementation($this)
      ->setImplementation($this, $method_name, $callable);

    return $this;
  }

  public function mockReturnThis($method1 /** $method2, ... */) {
    $that = $this;
    foreach (func_get_args() as $method_name) {
      FBMock_Utils::assertString($method_name);
      $this->mockImplementation(
        $method_name,
        function() use ($that) {
          return $that;
        }
      );
    }
    return $this;
  }

  public function mockThrow($method_name, Exception $e) {
    FBMock_Utils::assertString($method_name);
    return $this->mockImplementation(
      $method_name,
      function() use ($e) {
        throw $e;
      }
    );
  }

  public function mockGetCalls($method_name) {
    FBMock_Utils::assertString($method_name);
    return FBMock_Utils::getDoubleImplementation($this)->getCalls($this, $method_name);
  }
}
