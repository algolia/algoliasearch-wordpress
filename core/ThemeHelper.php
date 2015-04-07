<?php namespace Algolia\Core;

class ThemeHelper
{
    private $themes_dir;
    private $algolia_registry;

    public function __construct()
    {
        $this->themes_dir = plugin_dir_path(__DIR__).'themes/';
        $this->algolia_registry = Registry::getInstance();
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

                $theme->screenshot_autocomplete  = isset($configs['screenshot-autocomplete']) ? $configs['screenshot-autocomplete'] : 'screenshot-autocomplete.png';

                if (file_exists($this->themes_dir.$dir.'/'.$theme->screenshot_autocomplete))
                    $theme->screenshot_autocomplete = plugin_dir_url(__FILE__).'../themes/'.$dir.'/'.$theme->screenshot_autocomplete;
                else
                    $theme->screenshot_autocomplete = null;

                $theme->description = isset($configs['description']) ? $configs['description'] : '';

                $theme->facet_types = isset($configs['facet_types']) ? $configs['facet_types'] : array();

                $themes[] = $theme;
            }
        }

        return $themes;
    }

    public function get_current_theme()
    {
        foreach ($this->available_themes() as $theme)
            if ($theme->dir == $this->algolia_registry->theme)
                return $theme;
    }
}