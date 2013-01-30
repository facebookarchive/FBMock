<?php

class FBMock_BaseTestCase extends PHPUnit_Framework_TestCase {
  use FBMock_AssertionHelpers;

  public static function setUpBeforeClass() {
    // In case these tests are being run somewhere a custom config is defined
    FBMock_Config::setConfig(new FBMock_Config());
  }

  public static function skipInZend() {
    if (!FBMock_Utils::isHPHP()) {
      self::markTestSkipped('Test is HPHP-only');
    }
  }

  public static function skipInHPHP() {
    if (FBMock_Utils::isHPHP()) {
      self::markTestSkipped('Test is Zend-only');
    }
  }
}
