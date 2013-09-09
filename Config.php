<?php

class FBMock_Config {
  /**
   * Get object responsible for creating mock objects
   *
   * @return  FBMock_MockCreator or subclass
   */
  public function getMockCreator() {
    return new FBMock_MockCreator();
  }

  /**
   * Get object responsible for doing codegen for test doubles
   *
   * @return  FBMock_TestDoubleClassGenerator or subclass
   */
  public function getClassGenerator() {
    return new FBMock_TestDoubleClassGenerator();
  }

  public function getMethodGenerator() {
    return new FBMock_TestDoubleMethodGenerator();
  }

  public function getTestDoubleCreator() {
    return new FBMock_TestDoubleCreator();
  }

  /**
   * Get list of traits to add to mocks
   *
   * @return   array   names of traits
   */
  public function getMockTraits() {
    return array('FBMock_MockObject');
  }

  public function createMockImplementation($class_name) {
    return new FBMock_MockImplementation($class_name);
  }

  private static $config;

  public final static function get() {
    if (!self::$config) {
      $custom_config_path = __DIR__.'/CustomConfig.php';
      if (file_exists($custom_config_path)) {
        require_once $custom_config_path;
        self::$config = new FBMock_CustomConfig();
      } else {
        self::$config = new FBMock_Config();
      }
    }
    return self::$config;
  }

  public final static function setConfig(FBMock_Config $config) {
    self::$config = $config;
  }

  public final static function clearConfig() {
    self::$config = null;
  }
}
