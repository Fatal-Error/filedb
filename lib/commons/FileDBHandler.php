<?php

class FileDBHandler {

  /**
   * @param $filepath
   * @return mixed
   * @throws Exception
   */
  public static function readFile($filepath) {
    $content = &self::_static(__FUNCTION__ . '_' . $filepath);

    if (!isset($content)) {
      if (!self::fileExists($filepath)) {
        throw new \Exception(sprintf('Cannot read from file "%s". It doesn\'t exists.', $filepath));
      }

      $settings = FileDB::getSettings();

      switch ($settings['compression']['type']) {
        case 'gzip':
          ob_start();
          readgzfile($filepath);
          $data = ob_get_clean();
          break;

        default:
          $data = file_get_contents($filepath);
      }

      $content = unserialize($data);
    }

    return $content;
  }

  /**
   * @param $filepath
   * @return mixed
   */
  public static function fileExists($filepath) {
    $files = & self::_static(__FUNCTION__);

    if (!isset($files[$filepath])) {
      $files[$filepath] = file_exists($filepath);
    }

    return $files[$filepath];
  }

  /**
   * @param $filename
   * @param $data
   * @param bool $ignoreExists
   * @return int The number of bytes written to the file, or FALSE on failure.
   * @throws Exception
   */
  public static function writeFile($filename, $data, $ignoreExists = FALSE) {
    if (!$ignoreExists && !self::fileExists($filename)) {
      throw new \Exception(sprintf('Cannot write into file "%s". It doesn\'t exists.', $filename));
    }

    $settings = FileDB::getSettings();
    $data = serialize($data);

    if ($settings['compression']['type'] == 'gzip') {
      $data = gzencode($data, $settings['compression']['level']);
    }

    return file_put_contents($filename, $data, LOCK_EX);
  }

  /**
   * @param string $dir
   * @param string $mask
   * @param string $nomask
   * @return array
   */
  public static function scanDir($dir, $mask = '/\.php$/', $nomask = '/(\.\.?|CVS)$/') {
    $files = array();

    if (is_dir($dir) && $handle = opendir($dir)) {
      while (FALSE !== ($filename = readdir($handle))) {
        if (!preg_match($nomask, $filename) && $filename[0] != '.' && preg_match($mask, $filename)) {
          $files[] = $filename;
        }
      }

      closedir($handle);
    }

    natsort($files);

    return $files;
  }

  /**
   * @global array $settings
   * @return string
   */
  public static function getDBFolder() {
    $folder = & self::_static(__FUNCTION__);

    if (empty($folder)) {
      $settings = FileDB::getSettings();
      $folder = $settings['db']['folder'];
    }

    return $folder;
  }

  /**
   * @param string $tableName
   * @return string
   */
  public static function getTableFile($tableName) {
    return self::getDBFolder() . '/' . $tableName . '.' . FileDBTable::EXTENSION;
  }

  public static function &_static($name, $default_value = NULL, $reset = FALSE) {
    static $data = array(), $default = array();

    if (isset($data[$name])) {
      if ($reset) {
        $data[$name] = $default_value;
      }

      return $data[$name];
    }

    if ($reset) {
      return $data;
    }

    $default[$name] = $data[$name] = $default_value;

    return $data[$name];
  }

  public static function _static_reset($name) {
    //error_log(print_r('RESET STATIC ' . $name, true));
    self::_static($name, NULL, TRUE);
  }
}