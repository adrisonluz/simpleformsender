<?php namespace AdrisonLuz\SimpleFormSender;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'AdrisonLuz\SimpleFormSender\Components\SimpleForm'       => 'simpleForm'
        ];
    }

    public function registerSettings()
    {
    }
}
