<?php

// Loads mock and strict_mock functions
require_once __DIR__.'/Mock.php';

// Set up autoloader for mock framework classes
spl_autoload_register(function ($class) {
  if (strpos($class, 'FBMock') === 0) {
    require_once __DIR__.'/'.substr($class, strlen('FBMock_')).'.php';
  }
});
