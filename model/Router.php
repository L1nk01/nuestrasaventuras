<?php

class Router {
  private $controller;
  private $method;
  private $config;

  public function __construct($config = null) {
    global $database;
    $this->config = $database;
    $this->matchRoute();
  }

  public function matchRoute() {
    $url = explode('/', URL);

    $this->controller = !empty($url[0]) ? $url[0] :'page';
    $this->method = !empty($url[1]) ? $url[1] : 'home';

    $this->controller = $this->controller . 'Controller';

    require_once(dirname(__DIR__. 1) . '/controllers/' . $this->controller . '.php');
  }

  public function run() {
    $controllerName = $this->controller;

    $reflection = new ReflectionClass($controllerName);
    $constructor = $reflection->getConstructor();

    if ($constructor && $constructor->getNumberOfParameters() > 0) {
      $controller = new $controllerName($this->config);
    } else {
      $controller = new $controllerName();
    }

    $method = $this->method;
    $controller->$method();
  }
}