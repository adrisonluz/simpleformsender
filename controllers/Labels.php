<?php namespace AdrisonLuz\SimpleFormSender\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Labels extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'simpleformsender_manager_admin'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('AdrisonLuz.SimpleFormSender', 'simple-form-sender', 'labels');
    }
}