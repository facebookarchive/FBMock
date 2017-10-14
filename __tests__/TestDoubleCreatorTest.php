<?php
namespace { // Global code

class FBMock_TestDoubleCreatorTest extends FBMock_BaseTestCase {
  public function testNoInterfacesOrTraits() {
    $obj_creator = new FBMock_TestDoubleCreator();
    $obj = $obj_creator->createTestDoubleFor('TestObj');

    $this->assertInstanceof('TestObj', $obj);
  }

  public function testWithInterfacesAndTraits() {
    $obj_creator = new FBMock_TestDoubleCreator();
    $obj = $obj_creator->createTestDoubleFor(
      'TestObj',
      array('TestInterfaceA', 'TestInterfaceB'),
      array('TestTrait1', 'TestTrait2')
    );

    $this->assertInstanceof('TestObj', $obj);
    $this->assertInstanceof('TestInterfaceA', $obj);
    $this->assertInstanceof('TestInterfaceB', $obj);

    $ref_class = new ReflectionClass($obj);
    self::assertEquals(
      array('TestTrait1', 'TestTrait2', 'FBMock_TestDoubleObject'),
      array_keys($ref_class->getTraits())
    );
  }

  public function testDoubleOfInterface() {
    $obj_creator = new FBMock_TestDoubleCreator();
    $obj = $obj_creator->createTestDoubleFor(
      'TestInterfaceA',
      array('TestInterfaceB'),
      array('TestTrait1', 'TestTrait2')
    );

    $this->assertInstanceof('TestInterfaceA', $obj);
    $this->assertInstanceof('TestInterfaceB', $obj);

    $ref_class = new ReflectionClass($obj);
    self::assertEquals(
      array('TestTrait1', 'TestTrait2', 'FBMock_TestDoubleObject'),
      array_keys($ref_class->getTraits())
    );
  }

  /**
   * @expectedException Exception
   */
  public function testMockNonExistentClass() {
    mock("ClassThatDoesn'tExist");
  }

  /**
   * @expectedException FBMock_TestDoubleException
   */
  public function testMockFinalTestObj() {
    self::skipInHHVM();
    mock('FinalTestObj');
  }

  public function testMockFinalHHVM() {
    self::HHVMOnlyTest();
    self::assertEquals(
      1,
      mock('FinalMethodObj')->mockReturn('foo', 1)->foo()
    );
  }

  public function testMockNamespacedClass() {
    $obj_creator = new FBMock_TestDoubleCreator();
    $obj = $obj_creator->createTestDoubleFor('\Hello\World');

    self::assertInstanceOf('\Hello\World', $obj);
  }

  public function testMockNestedNamespacedClass() {
    $obj_creator = new FBMock_TestDoubleCreator();
    $obj = $obj_creator->createTestDoubleFor('\Foo\Bar\Baz');

    self::assertInstanceOf('\Foo\Bar\Baz', $obj);
  }
}

class TestObj {}
final class FinalTestObj {}
class FinalMethodObj {
  public final function foo() {}
}
trait TestTrait1 {}
trait TestTrait2 {}
interface TestInterfaceA {}
interface TestInterfaceB {}
}

namespace Hello {
  class World {}
}

namespace Foo\Bar {
  class Baz {}
}
