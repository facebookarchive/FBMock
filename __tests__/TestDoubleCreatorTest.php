<?php

/**
 * @emails mock-framework-tests@fb.com
 */
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
    $this->assertEquals(
      array('TestTrait1', 'TestTrait2'),
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
    $this->assertEquals(
      array('TestTrait1', 'TestTrait2'),
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
    mock('FinalTestObj');
  }
}

class TestObj {}
final class FinalTestObj {}
trait TestTrait1 {}
trait TestTrait2 {}
interface TestInterfaceA {}
interface TestInterfaceB {}
