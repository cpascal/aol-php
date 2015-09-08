<?php
namespace aol\components\config\errors;

class Config_Error extends \Exception {
  const LOAD_FAILED = 1;
  const SAVE_FAILED = 2;
  const PUSH_FAILED = 3;
  const GET_FAILED = 4;

  public function __construct($message, $code) {
    parent::__construct('Config_Error::' . $message, $code);
  }

}
