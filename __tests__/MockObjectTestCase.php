<?php

class MockObjectTestCase extends FBMock_BaseTestCase {
  const MOCK_RETURN_VALUE = 'mock return value';

  public function testMockReturnValue() {
    self::assertEquals(
      self::MOCK_RETURN_VALUE,
      mock('TestObject')
        ->mockReturn('testMethod', self::MOCK_RETURN_VALUE)
        ->testMethod()
    );
  }

  public function testMockReturnMultipleValues() {
    $mock = mock('TestObject')->mockReturn(array(
      'testMethod' => 1,
      'testMethod2' => 2,
    ));

    self::assertEquals(1, $mock->testMethod());
    self::assertEquals(2, $mock->testMethod2());
  }

  public function mockTestMethod($test_value) {
    return $test_value;
  }

  public static function mockTestStaticMethod($test_value) {
    return $test_value;
  }

  public function testMockImplementation() {
    // Test with a closure
    self::assertEquals(
      self::MOCK_RETURN_VALUE,
      mock('TestObject')->mockImplementation(
        'testMethod',
        function ($test_value) {
          return $test_value;
        }
      )->testMethod(self::MOCK_RETURN_VALUE)
    );

    // Test with an array(obj,method) callable
    self::assertEquals(
      self::MOCK_RETURN_VALUE,
      mock('TestObject')->mockImplementation(
        'testMethod',
        array($this, 'mockTestMethod')
      )->testMethod(self::MOCK_RETURN_VALUE)
    );

    // Test with a string callable
    self::assertEquals(
      self::MOCK_RETURN_VALUE,
      mock('TestObject')->mockImplementation(
        'testMethod',
        'MockObjectTestCase::mockTestStaticMethod'
      )->testMethod(self::MOCK_RETURN_VALUE)
    );
  }

  public function testMockReturnThis() {
    $this->assertInstanceOf(
      'TestObject',
      mock('TestObject')
        ->mockReturnThis('testMethod', 'testMethod2')
        ->testMethod()
        ->testMethod2()
    );
  }

  public function testMockImplementsInterface() {
    $mock = mock('TestObject');
    $this->assertTrue($mock instanceof TestObject);
    $this->assertFalse($mock instanceof TestImplementClass);
    $this->assertFalse($mock instanceof TestMockObjectInterface);
    $mock = mock('TestImplementClass', 'TestMockObjectInterface');
    $this->assertFalse($mock instanceof TestObject);
    $this->assertTrue($mock instanceof TestImplementClass);
    $this->assertTrue($mock instanceof TestMockObjectInterface);

    $mock = strict_mock('TestObject');
    $this->assertTrue($mock instanceof TestObject);
    $this->assertFalse($mock instanceof TestImplementClass);
    $this->assertFalse($mock instanceof TestMockObjectInterface);
    $mock = strict_mock('TestImplementClass', 'TestMockObjectInterface');
    $this->assertFalse($mock instanceof TestObject);
    $this->assertTrue($mock instanceof TestImplementClass);
    $this->assertTrue($mock instanceof TestMockObjectInterface);
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Test Exception
   */
  public function testMockException() {
    mock('TestObject')->mockThrow(
      'testMethod',
      new Exception('Test Exception')
    )->testMethod();
  }

  /**
   * @expectedException FBMock_MockObjectException
   * @expectedExceptionMessage Unmocked method testMethod called on strict mock
   */
  public function testUnmockedMethodThrowsForStrictMock() {
    strict_mock('TestObject')->testMethod();
  }

  public function testUnmockedMethodReturnsNull() {
    $this->assertNull(mock('TestObject')->testMethod());
  }

  /**
   * @expectedException FBMock_MockObjectException
   */
  public function testMockNonExistentMethod() {
    mock('TestObject')->mockReturn('nonExistentMethod', 1);
  }

  /**
   * @expectedException FBMock_MockObjectException
   */
  public function testMockGetCallsUndefinedMethod() {
    mock('TestObject')->mockGetCalls('nonExistentMethod');
  }

  /**
   * @expectedException Exception
   */
  public function testMockGetCallsUnmockedMethodStrictMock() {
    // TODO: use FBMock_MockObjectException here, see #1913833
    strict_mock('TestObject')->mockGetCalls('testMethod');
  }

  public function testMockGetCallsMockedMethodStrictMock() {
    strict_mock('TestObject')
      ->mockReturn('testMethod', 1)->mockGetCalls('testMethod');
  }

  public function testMockObjectWithCall() {
    self::assertEquals(
      self::MOCK_RETURN_VALUE,
      mock('ObjectWithCall')
        ->mockReturn('nonExistentMethod', self::MOCK_RETURN_VALUE)
        ->nonExistentMethod()
    );
  }

  /**
   * @expectedException FBMock_TestDoubleException
   */
  public function testCallStaticMethod() {
    mock('TestObject')->testStaticMethod();
  }

  /**
   * @expectedException FBMock_MockObjectException
   * @expectedExceptionMessage mockReturn
   */
  public function testBadMockReturnNoArgs() {
    mock('TestObject')->mockReturn();
  }

  public function testMockWithWakeup() {
    $this->assertTrue(mock('ObjectWithWakeup') instanceof ObjectWithWakeup);
  }

  /**
   * @expectedException FBMock_TestDoubleException
   * @expectedExceptionMessage Trying to mock PHP internal class DateTime. Mocking of internal classes is only supported in HHVM.
   */
  public function testMockInternalClassNonHHVM() {
    self::skipInHHVM();
    mock('DateTime');
  }

  public function testMockInternalClassHHVM() {
    self::HHVMOnlyTest();
    mock('DateTime');
  }
}

class TestObject {
  public function testMethod() {}
  public function testMethod2() {}
  public static function testStaticMethod() {}
}

class ObjectWithCall {
  public function __call($method_name, $arg2) { }
}

class ObjectWithWakeup {
  public function __wakeup() {}
}

class TestImplementClass implements TestMockObjectInterface {
  public function testMethodSignature() {}
}

interface TestMockObjectInterface {
  public function testMethodSignature();
}
