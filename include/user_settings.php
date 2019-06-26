<?php
/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 26/6/2019
 * Time: 12:03 μμ
 */

class UserSettings
{

    private $default_settings;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
        // default user settings
        $this->default_settings = array(SETTING_FORUM_POST_VIEW => 0);
    }

    /**
     * @brief update user setting
     * @param $setting_id
     * @param $value
     */
    public function set($setting_id, $value) {
        Database::get()->query("REPLACE INTO user_settings(setting_id, `value`, user_id) 
                                           VALUES (?d, ?d, ?d)", $setting_id, $value, $this->user_id);
    }

    /**
     * brief update user settings with defaults
     */
    public function setDefault() {
        foreach ($this->default_settings as $setting => $value) {
            $this->set($setting, $value);
        }
    }

    /**
     * @brief get user settings
     * @param $setting_id
     * @return mixed
     */
    public function get($setting_id) {
    
      $result = Database::get()->querySingle("SELECT value FROM user_settings WHERE user_id = ?d", $this->user_id);
      
      if ($result) {
          return $result->value;
      } else {
          return $this->default_settings[$setting_id];
      }

    }

}