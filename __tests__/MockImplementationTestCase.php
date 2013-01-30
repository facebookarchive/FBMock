<?php

/**
 * Test of MockImplementation's ability to store method return values and
 * implementations and also be able to record method call arguments.
 *
 * @emails mock-framework-tests@fb.com
 */
class FBMock_MockImplementationTestCase extends FBMock_BaseTestCase {
  const MOCK_RETURN_VALUE = 'mock return value';

  private
    $mockImplementation;

  public function setUp() {
    $this->mockImplementation = new FBMock_MockImplementation('TestClass');
  }

  public function testMockImplementation() {
    $this->assertEquals(
      self::MOCK_RETURN_VALUE,
      $this->mockImplementation
        ->setImplementation(
          'testMethod',
          function ($test_value) {
            return $test_value;
          }
        )->processMethodCall('testMethod', array(self::MOCK_RETURN_VALUE))
    );
  }

  public function testMockGetCallsMultipleCalls() {
    $this->mockImplementation->processMethodCall('testMethod');

    // Method call with an array as the argument
    $this->mockImplementation
      ->processMethodCall('testMethod', array(array(1, 2, 3)));

    // Method call with three parameters
    $this->mockImplementation
      ->processMethodCall('testMethod', array('a', 'b', 'c'));

    $this->assertEquals(
      array(
        array(),
        array(
          array(1, 2, 3),
        ),
        array(
          'a', 'b', 'c',
        )
      ),
      $this->mockImplementation->getCalls('testMethod')
    );
  }

  public function testMockGetCallsNoCalls() {
    $this->assertEquals(
      array(),
      $this->mockImplementation->getCalls('testMethod')
    );
  }
}
