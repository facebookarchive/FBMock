<?php

/**
 * Simple test to make sure creating a mock of interface works.
 *
 * @emails mock-framework-tests@fb.com
 */
class MockInterfaceTest extends FBMock_BaseTestCase {
  public function testCreateMockOfInterface() {
    $this->assertEquals(
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
