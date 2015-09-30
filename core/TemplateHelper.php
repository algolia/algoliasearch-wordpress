<?php namespace Algolia\Core;

class TemplateHelper
{
    private $templates_dir;
    private $templates;

    public function __construct()
    {
        $this->templates_dir = plugin_dir_path(__DIR__).'templates/';
        $this->templates = null;
    }

    public function availableTemplates()
    {
        if ($this->templates !== null)
            return $this->templates;

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
                $template->attributes  = isset($configs['attributes']) ? $configs['attributes'] : array();

                if (file_exists($this->templates_dir.$dir.'/'.$template->screenshot))
                    $template->screenshot = plugin_dir_url(__FILE__).'../templates/'.$dir.'/'.$template->screenshot;
                else
                    $template->screenshot = null;

                $template->description = isset($configs['description']) ? $configs['description'] : '';

                $templates[] = $template;
            }
        }

        $this->templates = $templates;

        return $templates;
    }

    public function getTemplate($template)
    {
        foreach ($this->availableTemplates() as $template_elt)
            if ($template_elt->dir == $template)
                return $template_elt;

        return null;
    }
}