<?php

/**
 * Test that generated code for mock methods is correct.
 */
class FBMock_TestDoubleMethodGeneratorTestCase extends FBMock_BaseTestCase {
  private
    $refMethod;

  public static function setUpBeforeClass() {
    if (FBMock_Utils::isHHVM()) {
      // Float typehints cause fatal outside of HHVM so just eval class
      eval(<<<'EOD'
class FBMock_MethodGeneratorTestObjHHVM {
  public function typehintedPrimitives(int $uid, bool $is_test_user) {}

  public function typehintedAndDefaultedPrimitives(
    int $uid=0,
    bool $is_test_user=true,
    string $nickname="something_cool",
    Object $o=null) {}

  public function methodWithDecimalDefaults(
    float $f = 1.0,
    double $d = 2.0,
    float $f2 = 1.11,
    double $d2 = 2.22
  ) {}
}
EOD
      );
    }
  }

  public function testBasicMethodNoArguments() {
    $this->refMethod =
      new ReflectionMethod('FBMock_MethodGeneratorTestObj::testPublic');
    $this->assertCorrectHeader('public function testPublic()');
  }

  public function testProtectedMethod() {
    $this->refMethod =
      new ReflectionMethod('FBMock_MethodGeneratorTestObj::testProtected');
    $this->assertCorrectHeader('protected function testProtected()');
  }

  public function testPrivateMethod() {
    $this->refMethod =
      new ReflectionMethod('FBMock_MethodGeneratorTestObj::testPrivate');
    $this->assertCorrectHeader('private function testPrivate()');
  }

  public function testMethodWithArgumentsNoTypehints() {
    $this->refMethod =
      new ReflectionMethod('FBMock_MethodGeneratorTestObj::typehints1');
    $this->assertCorrectHeader(
      'public function typehints1($uid, $is_test_user)'
    );
  }

  public function testMethodWithTypehintedArguments() {
    $this->refMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObj::methodWithHintsAndDefaults'
    );

    if (FBMock_Utils::isHHVM()) {
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

  public function testMethodWithTypehintedPrimitives() {
    self::HHVMOnlyTest();

    $this->refMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObjHHVM::typehintedPrimitives'
    );
    $this->assertCorrectHeader(
      'public function typehintedPrimitives(int $uid, bool $is_test_user)'
    );
  }

  public function testMethodWithTypehintedAndDefaultedPrimitives() {
    self::HHVMOnlyTest();

    $this->refMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObjHHVM::typehintedAndDefaultedPrimitives'
    );
    $this->assertCorrectHeader(
      'public function typehintedAndDefaultedPrimitives('.
      'int $uid=0, bool $is_test_user=true, '.
      'string $nickname="something_cool", Object $o=NULL)'
    );
  }

  // Regression test for #2039889
  public function testDecimalDefaults() {
    self::HHVMOnlyTest();
    $this->refMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObjHHVM::methodWithDecimalDefaults'
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
    $this->refMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObj::testPublic'
    );
    $test_double_method_generator = new FBMock_TestDoubleMethodGenerator();
    self::assertEquals(
      'public function testPublic() {'."\n".
        '  return $this->__implementation'.
        '->processMethodCall($this, __FUNCTION__, func_get_args());'."\n}",
      $test_double_method_generator->generateCode($this->refMethod)
    );

    $this->refMethod = new ReflectionMethod(
      'FBMock_MethodGeneratorTestObj::testStatic'
    );
    self::assertEquals(<<<'EOD'
public static function testStatic() {
  throw new FBMock_TestDoubleException('Call to static method testStatic on FBMock_MethodGeneratorTestObj. Mocks and fakes don\'t support static methods');
}
EOD
      ,
      $test_double_method_generator->generateCode($this->refMethod)
    );
  }

  public function testMockMagicCall() {
    $this->refMethod =
      new ReflectionMethod('FBMock_MethodGeneratorTestObj::__call');
    $test_double_method_generator = new FBMock_TestDoubleMethodGenerator();
    self::assertEquals(
      'public function __call($method_name, $args) {'."\n".
      '  return $this->__implementation'.
      '->processMethodCall('.
      '$this, func_get_args()[0], func_get_args()[1]);'."\n}",
      $test_double_method_generator->generateCode($this->refMethod)
    );
  }

  // Helper methods
  private function assertCorrectHeader($expected) {
    $test_double_method_generator = new FBMock_TestDoubleMethodGenerator();
    self::assertEquals(
      $expected,
      $test_double_method_generator->getMethodHeader($this->refMethod)
    );
  }
}

class FBMock_MethodGeneratorTestObj {
  public function testPublic() {}
  protected function testProtected() {}
  private function testPrivate() {}
  public static function testStatic() {}

  public function typehints1($uid, $is_test_user) {}
  public function __call($method_name, $args) {}
  public function methodWithHintsAndDefaults(
    stdClass $o,
    array $a = array('asdf'),
    $n = NULL) { }
  public function methodWithBadTypehint(ClassThatDoesNotExist $a) {}
}
