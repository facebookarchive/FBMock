<?php

/**
 * Simple test to make sure creating a mock of interface works.
 */
class MockInterfaceTest extends FBMock_BaseTestCase {
  public function testCreateMockOfInterface() {
    self::assertEquals(
      'test',
      mock('MockFrameworkTestInferface')
        ->mockReturn('testPublicMethod', 'test')
        ->testPublicMethod()
    );
  }
}

interface MockFrameworkTestInferface {
  public function testPublicMethod();
}
