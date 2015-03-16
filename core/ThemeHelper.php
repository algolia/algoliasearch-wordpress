<?php namespace Algolia\Core;

class ThemeHelper
{
    private $themes_dir;

    public function __construct()
    {
        $this->themes_dir = plugin_dir_path(__DIR__).'themes/';
    }

    public function available_themes()
    {
        $themes = array();

        foreach (scandir($this->themes_dir) as $dir)
        {
            if ($dir[0] != '.')
            {
                $theme = new \stdClass();

                $configs = array();

                if (file_exists($this->themes_dir.$dir.'/config.php'))
                    $configs = include $this->themes_dir.$dir.'/config.php';

                $theme->dir         = $dir;
                $theme->name        = isset($configs['name']) ? $configs['name'] : $dir;

                $theme->screenshot  = isset($configs['screenshot']) ? $configs['screenshot'] : 'screenshot.png';

                if (file_exists($this->themes_dir.$dir.'/'.$theme->screenshot))
                    $theme->screenshot = plugin_dir_url(__FILE__).'../themes/'.$dir.'/'.$theme->screenshot;
                else
                    $theme->screenshot = null;

                $theme->description = isset($configs['description']) ? $configs['description'] : '';

                $themes[] = $theme;
            }
        }

        return $themes;
    }
}