<?php

class Connection {
  private static $instance = null;
  private $dbh;

  public function __construct($config) {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    try {
      $this->dbh = new PDO($dsn, $config['username'], $config['password']);
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die("Connection failed: " . $e->getMessage());
    }
  }

  public static function getAvailableDrivers() {
    return PDO::getAvailableDrivers();
  }

  public static function getInstance($config) {
    if (self::$instance === null) {
      self::$instance = new self($config);
    }
    return self::$instance;
  }

  public function getConnection() {
    return $this->dbh;
  }
}