<?php





interface QueryPathExtension {
  public function __construct(QueryPath $qp);
}


class QueryPathExtensionRegistry {
  
  public static $useRegistry = TRUE;
  
  protected static $extensionRegistry = array();
  protected static $extensionMethodRegistry = array();
  
  public static function extend($classname) {
    self::$extensionRegistry[] = $classname;
    $class = new ReflectionClass($classname);
    $methods = $class->getMethods();
    foreach ($methods as $method) {
      self::$extensionMethodRegistry[$method->getName()] = $classname;
    }
  }
  
  
  public static function hasMethod($name) {
    return isset(self::$extensionMethodRegistry[$name]);
  }
  
  
  public static function hasExtension($name) {
    return in_array($name, self::$extensionRegistry);
  }
  
  
  public static function getMethodClass($name) {
    return self::$extensionMethodRegistry[$name];
  }
  
  
  public static function getExtensions(QueryPath $qp) {
    $extInstances = array();
    foreach (self::$extensionRegistry as $ext) {
      $extInstances[$ext] = new $ext($qp);
    }
    return $extInstances;
  }
  
  
  public static function autoloadExtensions($boolean = TRUE) {
    self::$useRegistry = $boolean;
  }
}