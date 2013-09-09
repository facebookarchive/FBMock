<?php

/**
 * Create a mock object of type $class_name.
 *
 * All unmocked methods will return null.
 */
function mock($class_name /* string interface_name, i_n2 ... */) {
  FBMock_Utils::assertString($class_name);
  $interface_names = func_get_args();
  array_shift($interface_names);
  return FBMock_Config::get()
    ->getMockCreator()
    ->createMock($class_name, $interface_names);
}

/**
 * Create a strict mock object of type $class_name.
 *
 * "Strict" means that if any unmocked method is called, an exception will be
 * thrown.
 */
function strict_mock($class_name /* string interface_name, i_n2 ... */) {
  FBMock_Utils::assertString($class_name);
  $interface_names = func_get_args();
  array_shift($interface_names);
  return FBMock_Config::get()
    ->getMockCreator()
    ->createStrictMock($class_name, $interface_names);
}

/**
 * Public API for configuring mock objects.
 */
interface FBMock_Mock {
  /**
   * Force the given method(s) to always return a value
   *
   * @param  $method_name  Name of method
   * @param  $value        Value to return
   *
   *   OR
   *
   * @param  $method_names_to_values   map of method names to their return
   *                                   values
   * @return $this
   */
  public function mockReturn(/*...*/);

  /**
   * Replace the implementation of a method with a closure
   *
   * @param  $method_name  Name of method to replace
   * @param  $callable     New implementation of method.  Can be a Closure,
   *                       string, array(obj, method), etc.
   * @return $this
   */
  public function mockImplementation($method_name, $callable);

  /**
   * Force the given method(s) to return $this to allow chaining
   *
   * @param  ...   method names
   * @return $this
   */
  public function mockReturnThis($method1 /** $method2, ... */);

  /**
   * Throw exception when method is called
   *
   * @param  $method_name  name of method
   * @param  $e            exception to throw
   * @return $this
   */
  public function mockThrow($method_name, Exception $e);

  /**
   * Returns an array representing the invocations of $method_name in the
   * following form:
   *
   * array(
   *   array($param1, $param2, $param3 ...) // first invocation
   *   array($param1, $param2, $param3 ...) // second invocation
   * )
   *
   * Note: see MockAssertionHelpers which has helper methods for asserting
   * calls in tests.
   *
   * @param   $method_name   name of method to get calls for
   * @return  array of invocations
   */
  public function mockGetCalls($method_name);
}
