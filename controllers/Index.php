<?php namespace AdrisonLuz\SimpleFormSender\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Index extends Controller
{
    public $implement = ['Backend\Behaviors\ListController'];

    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = [
        'simpleformsender_manager'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('AdrisonLuz.SimpleFormSender', 'simple-form-sender', 'index');
    }
}