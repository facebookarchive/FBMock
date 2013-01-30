<?php

class FBMock_TestDoubleException extends Exception {
  public function __construct($format_str /* $arg1, $arg2 */) {
    FBMock_Utils::assertString($format_str);
    $args = array_slice(func_get_args(), 1);
    parent::__construct(vsprintf($format_str, $args));
  }
}
