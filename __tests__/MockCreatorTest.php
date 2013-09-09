<?php

class FBMock_MockCreatorTest extends FBMock_BaseTestCase {
  /**
   * @expectedException FBMock_MockObjectException
   * @expectedExceptionMessage mockSomething
   */
  public function testDisallowMethodStartingWithMock() {
    (new FBMock_MockCreator())->createMock('FBMock_TestClassWithInvalidMethod');
  }
}

class FBMock_TestClassWithInvalidMethod {
  public function mockSomething() {}
}
