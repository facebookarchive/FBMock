<?php

/**
 * Helper asserts for mocks. Add to your base PHPUnit\Framework\TestCase.
 */
trait FBMock_AssertionHelpers {
  /**
   * Assert that the method was called a certain number of times on a mock
   *
   * @param  $mock                a mock object
   * @param  $method_name         name of method to check
   * @param  $expected_num_calls  expected number of calls
   * @param  $msg                 message for assert (optional)
   */
  public static function assertNumCalls(
      FBMock_Mock $mock,
      $method_name,
      $expected_num_calls,
      $msg = '') {
    FBMock_Utils::assertString($method_name);
    FBMock_Utils::assertInt($expected_num_calls);
    $call_count = count($mock->mockGetCalls($method_name));
    PHPUnit\Framework\TestCase::assertEquals(
      $expected_num_calls,
      $call_count,
      $msg ?: "$method_name called wrong number of times"
    );
  }

  /**
   * Assert that the method was called once. If $args is given, check that it
   * matches the args $method_name was called with.
   *
   * @param  $mock         a mock object
   * @param  $method_name  name of method to check
   * @param  $args         expected arguments (optional)
   * @param  $msg          message for assert (optional)
   */
  public static function assertCalledOnce(
      FBMock_Mock $mock,
      $method_name,
      $args=null,
      $msg='') {
    FBMock_Utils::assertString($method_name);
    self::assertNumCalls($mock, $method_name, 1, $msg);

    if ($args !== null) {
      PHPUnit\Framework\TestCase::assertEquals(
        $args,
        $mock->mockGetCalls($method_name)[0],
        $msg ?: "$method_name args are not equal"
      );
    }
  }

  /**
   * Assert that the method was not called.
   *
   * @param  $mock         a mock object
   * @param  $method_name  name of method to check
   * @param  $msg          message for assert (optional)
   */
  public static function assertNotCalled(
      FBMock_Mock $mock,
      $method_name,
      $msg='') {
    FBMock_Utils::assertString($method_name);
    self::assertNumCalls($mock, $method_name, 0, $msg);
  }

  /**
   * Assert that the method calls match the array of calls.
   *
   * Example usage:
   *
   *    // Code under test calls method
   *    $mock->testMethod(1,2,3);
   *    $mock->testMethod('a', 'b', 'c');
   *
   *    // Test asserts calls
   *    self::assertCalls(
   *      $mock,
   *      'testMethod',
   *      array(1,2,3),
   *      array('a', 'b', 'c')
   *    );
   *
   * @param  $mock         a mock object
   * @param  $method_name  name of method to check
   * @param  ...           arrays of expected arguments for each call
   * @param  $msg          message for assert (optional)
   */
  public static function assertCalls(
      FBMock_Mock $mock,
      $method_name
      /* array $expected_first_call, array $expected_second_call, $msg = ''*/) {

    FBMock_Utils::assertString($method_name);
    $args = func_get_args();
    $msg = '';
    if (is_string(end($args))) {
      $msg = array_pop($args);
    }

    $expected_calls = array_slice($args, 2);
    self::assertNumCalls($mock, $method_name, count($expected_calls), $msg);

    $actual_calls = $mock->mockGetCalls($method_name);
    foreach ($expected_calls as $i => $call) {
      PHPUnit\Framework\TestCase::assertEquals(
        $call,
        $actual_calls[$i],
        $msg ?: "Call $i for method $method_name did not match expected call"
      );
    }
  }
}
