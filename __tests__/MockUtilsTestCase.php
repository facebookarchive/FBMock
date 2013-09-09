<?php

class FBMock_UtilsTestCase extends FBMock_BaseTestCase {
  public function testMockClassName() {
    self::assertEquals(
      'FBMockFramework_classname_interface__a_interface__b_trait__a_trait__b',
      FBMock_Utils::mockClassNameFor(
        'classname',
        array('interface_a', 'interface_b'),
        array('trait_a', 'trait_b')
      )
    );

    self::assertEquals(
      'FBMockFramework_classname__',
      FBMock_Utils::mockClassNameFor(
        'classname',
        array(),
        array()
      )
    );
  }

  public function testMockGetInterfacesAndTraits() {
    FBMock_Config::setConfig(new MockUtilsTestCase_TestConfig());

    self::assertEquals(
      array(
        array('FBMock_Mock'),
        array('TestCustomTrait1', 'TestCustomTrait2')
      ),
      FBMock_Utils::getInterfacesAndTraits()
    );

    FBMock_Config::clearConfig();
  }

  public function testAssertStringPass() {
    FBMock_Utils::assertString('test');
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage String argument expected, array given
   */
  public function testAssertStringFail() {
    FBMock_Utils::assertString(array());
  }

  public function testEnforcePass() {
    FBMock_Utils::enforce(true, 'no message');

    // Allow truthy things
    FBMock_Utils::enforce('a', 'no message');
  }

  public function testAssertIntPass() {
    FBMock_Utils::assertInt(3);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Integer argument expected, string given
   */
  public function testAssertIntFail() {
    FBMock_Utils::assertInt("3");
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Error message with 2 args
   */
  public function testEnforceFail() {
    FBMock_Utils::enforce(array(), 'Error message with %d %s', 2, 'args');
  }
}

class MockUtilsTestCase_TestConfig extends FBMock_Config {
  public function getMockTraits() {
    return array('TestCustomTrait1', 'TestCustomTrait2');
  }
}
