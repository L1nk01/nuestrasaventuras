<?php

class Router
{
  private $controller;
  private $method;
  private $config;
  private $params = [];

  public function __construct($config = null)
  {
    global $database;
    $this->config = $database;
    $this->matchRoute();
  }

  public function matchRoute()
  {
    $url = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

    $this->controller = !empty($url[0]) ? $url[0] : 'page';
    $this->method = !empty($url[1]) ? $url[1] : 'home';

    $this->controller = $this->controller . 'Controller';

    if (count($url) > 2) {
      $this->params = array_slice($url, 2);
    }

    require_once(dirname(__DIR__ . 1) . '/controllers/' . $this->controller . '.php');
  }

  public function run()
  {
    $controllerName = $this->controller;

    $reflection = new ReflectionClass($controllerName);
    $constructor = $reflection->getConstructor();

    if ($constructor && $constructor->getNumberOfParameters() > 0) {
      $controller = new $controllerName($this->config);
    } else {
      $controller = new $controllerName();
    }

    $method = $this->method;

    if ($reflection->hasMethod($method)) {
      $controller->$method();
    } else {
      echo "Method $method not found in $controllerName";
    }
  }
}
