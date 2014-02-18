# FBMock

FBMock is a PHP mocking framework designed to be simple and easy to use.

Unlike other mocking frameworks, FBMock is basically just stubs with spies. Instead of using a custom DSL and relying on opaque tear-down verification, FBMock encourages developers to program simple return behaviors and only use spies when appropriate.

## Requirements

- HHVM or PHP 5.4+

Note: the framework's tests use PHPUnit but PHPUnit is not necessary for using FBMock.

## Usage

Include init.php which sets up the autoloader

```php
require_once YOUR_FBMOCKS_DIR.'/init.php'
```

### Install using Composer (optional)

To install this package via composer, just add the package to `require` and start using it.

```json
{
    "require": {
        "facebook/fbmock": "*@dev"
    }
}
```

### Creating a mock

```php
mock('Foo')
```

Also, you can mock an interface in the same manner:

```php
mock('IFoo')
```

### Stubbing

By default, all methods return null. Helper methods for configuring return values are prefixed with 'mock'.

```php
mock('Foo')->mockReturn('bar', 'return value here');
```

Sometimes, you need more control than mockReturn:

```php
mock('Foo')->mockImplementation(
    'bar',
    function ($bar_type) {
        return $bar_type == 1;
    }
);
```

For other helpers (mockThrow, mockReturnThis, etc.) see Mock.php

### Spying

```php
$mock_foo = mock('Foo');
$mock->mockReturn('bar', 1);

$code_under_test->doSomething($mock_foo);

// Returns array of calls
$mock_foo->mockGetCalls('bar');
```

If you are using PHPUnit, you can add FBMock_AssertionHelpers to your base PHPUnit_TestCase to get some spying helpers:

```php
$this->assertCalledOnce($mock_foo, 'bar');
$this->assertCalledOnce($mock_foo, 'bar', $expected_params_as_array); // param assertion is optional
$this->assertCalls($mock_foo, 'bar', $expected_params_for_first_call, $expected_params_for_second_call, ...);
```

### Tips

#### Use multiset capabilities to improve legibility

```php
mock('Foo')->mockReturn(array(
    'bar' => 1,
    'get' => 'data',
    'run' => true,
))->mockReturnThis('set', 'addID', 'removeID');
```

#### Utilize the fluent interface for concise mock setup.
```
mock('Foo')
    ->mockReturn('bar', 1)
    ->mockThrow('other_method')
    ->mockImplementation('another_method', function () { /* ... */ })
    ->mockReturnThis('setData');
```

#### Use strict mocks when it's critical a method isn't called

If any unmocked method is called, the mock will throw.
```php
$db_conn = strict_mock('DBConnection')->mockReturn('read', $data);

// ...in code under test
$db_conn->write($some_data); // Throws FBMock_MockFrameworkException
```

### Customizing

See CustomConfig-sample.php for instructions on customizing FBMock for your needs.

### HHVM-only features

- Mock final classes and classes with final methods
- Mock internal classes

### Community

We have a [mailing list](http://groups.google.com/group/fbmock). If you're using FBMock, send us an email to say hi!
