<?php

class MockRegressionTests extends FBMock_BaseTestCase {

  // #1137433
  public function testTraitOverrideVisibility() {
    mock('TestChild');
  }

  /**
   * @expectedException FBMock_MockObjectException
   * @expectedMessage did you mean
   */
  public function testEnforceStubbingCaseSensitivity() {
    mock('TestParent')->mockReturn('TestMethod', 'asdf');
  }

  /**
   * @expectedException FBMock_MockObjectException
   * @expectedMessage did you mean
   */
  public function testEnforceGetCallsCaseSensitivity() {
    mock('TestParent')->mockGetCalls('TestMethod');
  }

  public function testStoredCallsCaseInsensitivity() {
    // Though we make you respect the case of methods when using mocks, we can't
    // really enforce that calling code does so it makes sense for calls to
    // 'TestMethod' to show up when you check calls to 'testMethod'
    $mock = mock('TestChild');
    $mock->TestMethod();
    $this->assertCalledOnce($mock, 'testMethod');
  }
}

class TestChild extends TestParent {
  use TestTrait;
}

class TestParent {
  private function testMethod() {}
}

trait TestTrait {
  public function testMethod() {}
}
