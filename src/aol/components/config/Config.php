<?php
namespace aol\components\config;

use aol\core\Atom_Object;
use aol\components\config\errors\Config_Error;

/**
 * @class Config
 *
 * @brief json 파일로부터 설정을 로드하는 객체. 
 * @author Lee, Hyeon-gi
 */
class Config extends Atom_Object {
  public function __construct() {
    $this->configs = array();
  }

  protected function select_path($config_path) {
    $this->config_path = $config_path;
  }

  public function get_config_path() {
    return $this->config_path;
  }

  /**
   * 설정 정보를 변경 한다. 변경하기 위해서는 정보가 미리 로드되어 있어야 한다.
   * 
   * 시스템이 구동되면서 동적으로 설정을 변경하고자 할떄는 persistent 옵션을 false로 설정
   * 변경된 설정 정보가 파일에 반영되길 원한다면 persistenent 옵션을 true로 설정
   *
   * @param path string 설정 패스 
   * @param value object 설정 데이터
   * @param persistent bool 파일에 반영 여부
   */
  public function set($path, $value, $persistent = false) {
    $tokens = explode('.', $path);
    $config_name = $tokens[0];
    if (!isset($this->configs[$config_name]))
      throw new Config_Error('Not loaded config:' . $path);
    $current = &$this->configs[$config_name];
    for ($i = 1; $i < count($tokens); $i++) {
      $name = $tokens[$i]; 
      if ($i == (count($tokens) - 1))
        $current->$name = $value;
      else { 
        if (!isset($current->$name))
          $current->$name = new \stdClass();
        $current = &$current->$name;
      }
    }

    if ($persistent)
      $this->save($config_name);
  }

  public function push($path, $value, $persistent = false) {
    $tokens = explode('.', $path);
    $config_name = $tokens[0];
    if (!isset($this->configs[$config_name]))
      throw new Config_Error('Not loaded config:' . $path);
    $current = &$this->configs[$config_name];
    for ($i = 1; $i < count($tokens); $i++) {
      $name = $tokens[$i]; 
      if ($i == (count($tokens) - 1)) {
        if (!is_array($current->$name))
          throw new Config_Error("Target path is not array", Config_Error::PUSH_FAILED);
        array_push($current->$name, $value);
      }
      else { 
        if (!isset($current->$name))
          $current->$name = new \stdClass();
        $current = &$current->$name;
      }
    }
    if ($persistent)
      $this->save($config_name);
  }

  public function clear($config_name, $persistent = false) {
    unset($this->configs[$config_name]);
    if ($persistent)
      $this->delete($config_name);
  }

  private function delete($config_name) {
    $env = $this->get_enviroment();
    $file_path = "$this->config_path/$config_name.json";
    if (file_exists($file_path))
      unlink($file_path);
  }

  /**
   * 설정 정보를 리턴한다. 
   *
   * @param path string 설정 패스 
   * @return object 설정 값
   */
  public function get($path) {
    $tokens = explode('.', $path);
    $config_name = $tokens[0];
    if (!isset($this->configs[$config_name]))
      $this->load($config_name);

    $current = $this->configs[$config_name];
    for ($i = 1; $i < count($tokens); $i++) {
      $name = $tokens[$i];
      if (!isset($current->$name))
        throw new Config_Error('Not exist path:' + $path, Config_Error::GET_FAILED);
      $current = $current->$name;
    }
    return $current;
  }

  private function load($config_name) {
    try {
      $file_path = "$this->config_path/$config_name.json";
      $contents = file_get_contents($file_path);
      if (!$contents)
        throw new Config_Error("Read faield. path is $file_path", File_Error::LOAD_FAILED);

      $parse_data = json_decode($contents);
      switch (json_last_error()) {
        case JSON_ERROR_NONE:
          break;
        case JSON_ERROR_DEPTH:
          throw new \Exception('Maximum stack depth exceeded');
          break;
        case JSON_ERROR_STATE_MISMATCH:
          throw new \Exception('Underflow or the modes mismatch');
          break;
        case JSON_ERROR_CTRL_CHAR:
          throw new \Exception('Unexpected control character found');
          break;
        case JSON_ERROR_SYNTAX:
          throw new \Exception('Syntax error, malformed JSON');
          break;
        case JSON_ERROR_UTF8:
          throw new \Exception('Malformed UTF-8 characters, possibly incorrectly encoded');
          break;
        default:
          throw new \Exception('Unknown error');
          break;
      }
      $this->configs[$config_name] = $parse_data;
    }
    catch (\Exception $e) {
      throw new Config_Error($e->getMessage(), Config_Error::LOAD_FAILED);
    }
  }

  private function save($config_name) {
    try {
      $env = $this->get_enviroment();
      $file_path = "$this->config_path/$config_name.json";
      $encode_data = json_encode($this->configs[$config_name], JSON_PRETTY_PRINT);
      if (FALSE === file_put_contents($file_path, $encode_data, LOCK_EX))
        throw new Config_Error("Save faield. path is $file_path", Config_Error::SAVE_FAILED);
    }
    catch (\Exception $e) {
      throw new Config_Error($e->getMessage(), Config_Error::SAVE_FAILED);
    }
  }

  private $configs;
  private $config_path;
}
