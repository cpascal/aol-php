<?php
namespace aol\components\language;

use aol\components\config\Config;
use aol\core\Atomic_Object;
use aol\components\language\dto\Message_DTO;

class Language extends Config {
  const MIN_STRING_TOKEN_COUNT = 2;

  public function __construct() {
    $this->messages = array();
  }

  public function select_language($application_path, $country_code) {
    $this->country_code = $country_code;
    $this->select_path($application_path . "/db/lang/" . $country_code);
  }

  public function load($message_name) {
    $this->messages[$message_name] = $this->get_message_dtos($message_name);
  }

  private function get_message_dtos($message_name) {
    $message_dtos = array();
    foreach ($this->get("$message_name") as $message) {
      $dto = new Message_DTO();
      $dto->key = $message->Key;
      $dto->value = $message->Value;
      $message_dtos[$dto->key] = $dto;
    }
    return $message_dtos;
  }

  public function line($path, $tag_to_plain_text = false) {
    $tokens = explode('.', $path);
    if (self::MIN_STRING_TOKEN_COUNT > count($tokens))
      return '';
    $message_name = $tokens[0];
    $string_key = '';
    $first_key = key($tokens);
    end($tokens);
    $last_key = key($tokens);
    foreach ($tokens as $key=>$value) {
      if ($key != $first_key) {
        $string_key .= $value;
        if ($key != $last_key)
          $string_key .= '.';
      }
    }
    if (!isset($this->messages[$message_name]))
      return '';
    if (!isset($this->messages[$message_name][$string_key])) {
      return '';
    }
    if ($tag_to_plain_text) {
      $replace = $this->messages[$message_name][$string_key]->value;
      $replace = str_replace('<', '&lt', $replace);
      $replace = str_replace('>', '&gt', $replace);
      return $replace;
    }
    else
      return $this->messages[$message_name][$string_key]->value;
  }

  private $mssages;
  private $country_code;
}
