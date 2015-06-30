<?php namespace Algolia\Core;

class TemplateHelper
{
    private $templates_dir;
    private $algolia_registry;

    public function __construct()
    {
        $this->templates_dir = plugin_dir_path(__DIR__).'templates/';
        $this->algolia_registry = Registry::getInstance();
    }

    public function available_templates()
    {
        $templates = array();

        foreach (scandir($this->templates_dir) as $dir)
        {
            if ($dir[0] != '.')
            {
                $template = new \stdClass();

                $configs = array();

                if (file_exists($this->templates_dir.$dir.'/config.php'))
                    $configs = include $this->templates_dir.$dir.'/config.php';

                $template->dir         = $dir;
                $template->name        = isset($configs['name']) ? $configs['name'] : $dir;

                $template->screenshot  = isset($configs['screenshot']) ? $configs['screenshot'] : 'screenshot.png';

                if (file_exists($this->templates_dir.$dir.'/'.$template->screenshot))
                    $template->screenshot = plugin_dir_url(__FILE__).'../templates/'.$dir.'/'.$template->screenshot;
                else
                    $template->screenshot = null;

                $template->screenshot_autocomplete  = isset($configs['screenshot-autocomplete']) ? $configs['screenshot-autocomplete'] : 'screenshot-autocomplete.png';

                if (file_exists($this->templates_dir.$dir.'/'.$template->screenshot_autocomplete))
                    $template->screenshot_autocomplete = plugin_dir_url(__FILE__).'../templates/'.$dir.'/'.$template->screenshot_autocomplete;
                else
                    $template->screenshot_autocomplete = null;

                $template->description = isset($configs['description']) ? $configs['description'] : '';

                $template->facet_types = isset($configs['facet_types']) ? $configs['facet_types'] : array();

                $templates[] = $template;
            }
        }

        return $templates;
    }

    public function get_current_template()
    {
        foreach ($this->available_templates() as $template)
            if ($template->dir == $this->algolia_registry->template)
                return $template;
    }
}