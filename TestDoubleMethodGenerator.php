<?php

/**
 * Generate code for a single method on a mock object
 */
class FBMock_TestDoubleMethodGenerator {
  public function generateCode(ReflectionMethod $method) {
    $func_name = '__FUNCTION__';
    $args = 'func_get_args()';

    if ($method->getName() == '__call') {
      $func_name = 'func_get_args()[0]';
      $args = 'func_get_args()[1]';
    }

    if ($method->isStatic()) {
      $method_body = sprintf(
        "throw new FBMock_TestDoubleException('Call to static method %s on ".
        "%s. Mocks and fakes don\'t support static methods');",
        $method->getName(),
        $method->getDeclaringClass()->getName()
      );
    } else {
      $method_body = sprintf(
        'return $this->__implementation->processMethodCall($this, %s, %s);',
        $func_name,
        $args
      );
    }

    $code = sprintf(
      "%s {\n  %s\n}",
      $this->getMethodHeader($method),
      $method_body
    );

    return $code;
  }

  // public for testing
  public final function getMethodHeader(ReflectionMethod $method) {
    $param_sources = array();
    foreach ($method->getParameters() as $param) {
      $param_sources[] = $this->generateParameterCode($param);
    }

    if ($method->isProtected()) {
      $modifier = 'protected';
    } else if ($method->isPrivate()) {
      $modifier = 'private';
    } else {
      $modifier = 'public';
    }

    return sprintf(
      '%s %sfunction %s(%s)',
      $modifier,
      $method->isStatic() ? 'static ' : '',
      $method->getName(),
      implode(', ', $param_sources)
    );
  }

  public final function generateParameterCode(ReflectionParameter $param) {
    $code = '';

    $typehint_type = $this->getParameterTypehint($param);
    if ($typehint_type) {
      $code .= $typehint_type.' ';
    }
    if($param->isPassedByReference()) {
      $code .= '&$'.$param->getName();  
    } else {
      $code .= '$'.$param->getName();  
    }
    

    if ($param->isDefaultValueAvailable()) {
      $code .= '='.$this->getDefaultParameterValue($param);
    }

    return $code;
  }

  private function getParameterNames(ReflectionMethod $method) {
    $param_names = array();
    foreach ($method->getParameters() as $param) {
      $param_names[] = "'".$param->getName()."'";
    }
    return 'array('.implode(', ', $param_names).')';
  }

  private function getDefaultParameterValue(ReflectionParameter $param) {
    if (method_exists($param, 'getDefaultValueText')) {  // HHVM-only
      return $param->getDefaultValueText();
    }

    return var_export($param->getDefaultValue(), true);
  }

  protected function getParameterTypehint(ReflectionParameter $param) {
    // HHVM-only (primitive typehints)
    if (method_exists($param, 'getTypehintText')) {
      return $param->getTypehintText();
    }

    if ($param->getClass()) {
      return $param->getClass()->getName();
    }

    if ($param->isArray()) {
      return 'array';
    }

    return '';
  }
}
