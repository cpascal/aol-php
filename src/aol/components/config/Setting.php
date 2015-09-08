<?php
namespace aol\components\config;

use aol\core\Atom_Object;
use aol\components\config\errors\Config_Error;

/**
 * @class Setting
 *
 * @author Lee, Hyeon-gi
 */
class Setting extends Config {
  public function __construct() {
  }

  public function select_application($application_path, $environment) {
    $this->application_path = $application_path;
    $this->environment = $environment;
    $this->select_path($application_path . "/db/config/" . $environment);
  }

  public function get_application_path() {
    return $this->application_path;
  }

  public function get_enviroment() {
    return $this->environment;
  }
}
