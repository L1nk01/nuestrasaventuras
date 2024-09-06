<?php
class PageController {
  public function home() {
    require_once(dirname(__DIR__, 1) . '/public/views/home.php');
  }

  public function gallery() {
    require_once(dirname(__DIR__, 1) . '/public/views/gallery.php');
  }

  public function no_file_selected() {
    require_once(dirname(__DIR__, 1) . '/public/views/no_file_selected.php');
  }

  public function not_supported_file_type() {
    require_once(dirname(__DIR__, 1) . '/public/views/not_supported_file_type.php');
  }

  public function file_too_large() {
    require_once(dirname(__DIR__, 1) . '/public/views/file_too_large.php');
  }
}