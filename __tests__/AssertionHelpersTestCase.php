<?php

class FBMock_AssertionHelpersTestCase extends FBMock_BaseTestCase {
  public function setUp() {
    $this->mock = mock('TestObject');
    $this->mock->testMethod(1);
    $this->mock->testMethod(2);
  }

  public function testCorrectAssertionsDontFail() {
    $this->assertCalls($this->mock, 'testMethod', array(1), array(2));
    $this->assertNumCalls($this->mock, 'testMethod', 2);

    $m = mock('TestObject');
    $this->assertNotCalled($m, 'testMethod');
    $this->assertNotCalled($m, 'testMethod', 'custom msg');

    $m->testMethod(1);
    $this->assertCalledOnce($m, 'testMethod');
    $this->assertCalledOnce($m, 'testMethod', array(1));
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   */
  public function testAssertCallsFails() {
    $this->assertCalls($this->mock, 'testMethod', array(2));
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   * @expectedExceptionMessage custom msg
   */
  public function testAssertCallsFailsWithCustomMsg() {
    $this->assertCalls(
      $this->mock,
      'testMethod',
      array(1),
      array(3),
      'custom msg'
    );
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   */
  public function testAssertNumCallsFails() {
    $this->assertNumCalls($this->mock, 'testMethod', 1);
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   * @expectedExceptionMessage custom msg
   */
  public function testAssertNumCallsFailsWithCustomMsg() {
    $this->assertNumCalls($this->mock, 'testMethod', 1, 'custom msg');
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   */
  public function testAssertCalledOnceFails() {
    $this->assertCalledOnce($this->mock, 'testMethod');
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   * @expectedExceptionMessage custom msg
   */
  public function testAssertCalledOnceFailsWithCustomMsg() {
    $this->assertCalledOnce($this->mock, 'testMethod', null, 'custom msg');
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   */
  public function testAssertNotCalledFails() {
    $this->assertNotCalled($this->mock, 'testMethod');
  }

  /**
   * @expectedException PHPUnit_Framework_ExpectationFailedException
   * @expectedExceptionMessage custom msg
   */
  public function testAssertNotCalledFailsWithCustomMsg() {
    $this->assertNotCalled($this->mock, 'testMethod', 'custom msg');
  }
}
