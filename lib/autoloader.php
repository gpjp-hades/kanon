<?php

namespace lib;

final class autoloader {

  static $log = [];

  public const LIBS = ["lib"];
  public const TEMPLATES = "templates";
  public const ROOT = "/Kanon-old";

  function __construct() {
    $final = [];
    foreach ($this::LIBS as $path) {
      $final = \array_merge($final, glob($_SERVER['DOCUMENT_ROOT'] . \lib\autoloader::ROOT . "/$path/*.{php}", GLOB_BRACE));
    }

    foreach ($final as $file) {
      try {
        $this->require($file);
      } catch (\Exception $e) {
        \lib\autoloader::log("autoloader", $e);
      }
    }
  }

  public static function getTemplate(string $name) {
    $fname = $_SERVER['DOCUMENT_ROOT'] . \lib\autoloader::ROOT . "/" . \lib\autoloader::TEMPLATES . "/" . $name . ".phtml";
    if (!is_file($fname))
      return false;
    \lib\csrf::wipe();
    return \lib\autoloader::require($fname);
  }

  static function log(string $source, string $event) {
    return array_push(\lib\autoloader::log, [$source, $event]);
  }

  static function getLog() {
    return \lib\autoloader::log;
  }

  private static function require(string $fname) {
    if (!is_file($fname))
      return false;

    return require_once $fname;
  }
}

new \lib\autoloader;