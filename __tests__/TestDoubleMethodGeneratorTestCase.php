<?php
// Copyright 2004-present Facebook. All Rights Reserved.

/**
 * Test that generated code for mock methods is correct.
 */
class FBMock_TestDoubleMethodGeneratorTestCase extends FBMock_BaseTestCase {
  private
    $mockMethod;

  public function setUp() {
    FBMock_Config::setConfig(new FBMock_MethodGeneratorTestConfig());
    $this->mockMethod = mock('ReflectionMethod')->mockReturn(array(
      'getName' => 'test',
      'isPublic' => true,
      'getParameters' => array(),
    ));
  }

  public function testBasicMethodNoArguments() {
    $this->mockMethod->mockReturn('getParameters', array());
    $this->assertCorrectHeader('public function test()');
  }

  public function testProtectedMethod() {
    $this->mockMethod->mockReturn('isProtected', true);
    $this->assertCorrectHeader('protected function test()');
  }

  public function testPrivateMethod() {
    $this->mockMethod->mockReturn('isPrivate', true);
    $this->assertCorrectHeader('private function test()');
  }

  public function testMethodWithArgumentsNoTypehints() {
    $this->mockMethod->mockReturn(
      'getParameters',
      array(
        $this->createParam('uid'),
        $this->createParam('is_test_user'),
      )
    );

    $this->assertCorrectHeader('public function test($uid, $is_test_user)');
  }

  public function testMethodWithTypehintedArguments() {
    $this->mockMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObj::methodWithHintsAndDefaults'
    );

    if (FBMock_Utils::isHPHP()) {
        $expected = <<<'EOD'
public function methodWithHintsAndDefaults(stdClass $o, array $a=array (
  0 => "asdf",
), $n=NULL)
EOD;
      $this->assertCorrectHeader($expected);
    } else {
        $expected = <<<'EOD'
public function methodWithHintsAndDefaults(stdClass $o, array $a=array (
  0 => 'asdf',
), $n=NULL)
EOD;
      $this->assertCorrectHeader($expected);
    }
  }

  /**
   * @expectedException ReflectionException
   * @expectedExceptionMessage Class ClassThatDoesNotExist does not exist
   */
  public function testTypehintUndefinedClass() {
    $this->mockMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObj::methodWithBadTypehint'
    );

    $this->assertCorrectHeader('');
  }

  public function testMethodWithTypehintedPrimitives() {
    self::skipInZend();

    $this->mockMethod->mockReturn(
      'getParameters',
      array(
        $this->createParam('uid', 'int'),
        $this->createParam('is_test_user', 'bool'),
      )
    );

    $this->assertCorrectHeader(
      'public function test(int $uid, bool $is_test_user)'
    );
  }

  public function testMethodWithTypehintedAndDefaultedPrimitives() {
    self::skipInZend();

    $this->mockMethod->mockReturn(
      'getParameters',
      array(
        $this->createParam('uid', 'int', '0'),
        $this->createParam('is_test_user', 'bool', 'true'),
        $this->createParam('nickname', 'string', '"something_cool"'),
        $this->createParam('o', 'Object', 'null'),
      )
    );

    $this->assertCorrectHeader(
      'public function test('.
      'int $uid=0, bool $is_test_user=true, '.
      'string $nickname="something_cool", Object $o=null)'
    );
  }

  public function testDecimalDefaults() {
    self::skipInZend();

    // Float typehints cause fatal in zend so just eval class
    $class = <<<'EOD'
class FBMock_MethodGeneratorTestObj2 {
  public function methodWithDecimalDefaults(
    float $f = 1.0,
    double $d = 2.0,
    float $f2 = 1.11,
    double $d2 = 2.22
  ) {}
}
EOD;

    eval($class);
    $this->mockMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObj2::methodWithDecimalDefaults'
    );

    $expected = <<<'EOD'
public function methodWithDecimalDefaults(float $f=1.0, double $d=2.0, float $f2=1.11, double $d2=2.22)
EOD;
    $this->assertCorrectHeader($expected);
  }

  /**
   * Make sure body is also correct
   */
  public function testMethodWithBody() {
    $this->mockMethod->mockReturn(
      'getParameters',
      array($this->createParam('uid'))
    )->mockReturn('getDeclaringClass', 'TestClass');

    $test_double_method_generator = new FBMock_TestDoubleMethodGenerator();
    $this->assertEquals(
      'public function test($uid) {'."\n".
        '  return $this->__mockImplementation'.
        "->processMethodCall(__FUNCTION__, func_get_args());\n}",
      $test_double_method_generator->generateCode($this->mockMethod)
    );

    $this->assertEquals(
      'public static function test($uid) {'."\n".
      "  throw new FBMock_TestDoubleException('Call to static method test ".
      "on TestClass. Mocks and fakes don\'t support static methods');\n}",
      $test_double_method_generator->generateCode(
        $this->mockMethod->mockReturn('isStatic', true)
      )
    );

  }

  public function testMockMagicCall() {
    $this->mockMethod->mockReturn('getName', '__call');

    $test_double_method_generator = new FBMock_TestDoubleMethodGenerator();
    $this->assertEquals(
      'public function __call() {'."\n".
      '  return $this->__mockImplementation'.
      '->processMethodCall('.
      "func_get_args()[0], func_get_args()[1]);\n}",
      $test_double_method_generator->generateCode($this->mockMethod)
    );
  }

  // Helper methods
  private function assertCorrectHeader($expected) {
    $test_double_method_generator = new FBMock_TestDoubleMethodGenerator();
    $this->assertEquals(
      $expected,
      $test_double_method_generator->getMethodHeader($this->mockMethod)
    );
  }

  private function createParam(
      $name,
      $type = '',
      $default = '') {
    $mock = mock('ReflectionParameter')
      ->mockReturn('getName', $name)
      ->mockReturn('isDefaultValueAvailable', $default !== '');

    if (method_exists($mock, 'getTypehintText')) {
      $mock->mockReturn('getTypehintText', $type);
    }

    if (method_exists($mock, 'getDefaultValueText')) {
      $mock->mockReturn('getDefaultValueText', $default);
    }

    return $mock;
  }
}

class FBMock_MethodGeneratorTestObj {
  public function methodWithHintsAndDefaults(
    stdClass $o,
    array $a = array('asdf'),
    $n = NULL) { }
  public function methodWithBadTypehint(ClassThatDoesNotExist $a) {}
}

class FBMock_MethodGeneratorTestConfig extends FBMock_Config {
  public function getMethodGenerator() {
    return new FBMock_MethodGeneratorForTest();
  }
}

class FBMock_MethodGeneratorForTest extends FBMock_TestDoubleMethodGenerator {
  public function generateCode(ReflectionMethod $method) {
    // Can't get default value for last parameter to export because zend doesn't
    // allow you to get default values for built-ins and having a different
    // method signature than the method you are overriding causes an error in
    // strict standards mode. See note at bottom of
    // http://php.net/manual/en/reflectionparameter.getdefaultvalue.php
    if ($method->getName() == 'export' || $method->isFinal()) {
      return null;
    }
    return parent::generateCode($method);
  }
}
