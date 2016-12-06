<?php namespace AdrisonLuz\SimpleFormSender\Components;

use Cms\Classes\ComponentBase;
use AdrisonLuz\SimpleFormSender\Models\FormsRegister;
use File;
use Schema;
use Mail;
use AdrisonLuz\SimpleFormSender\Models\Label;
use AdrisonLuz\SimpleFormSender\Models\Form;
use System\Models\MailTemplate;

class SimpleForm extends ComponentBase
{

    /**
     * The type of form
     * @var string
     */
    public $type;

    /**
     * The class of form
     * @var string
     */
    public $class;

    /**
     * The name of form
     * @var string
     */
    public $nameForm;

    /**
     * The label of form
     * @var string
     */
    public $labelForm;

    /**
     * The return function for submit form
     * @var string
     */
    public $successFunction;

    public function componentDetails()
    {
        return [
            'name'        => 'SimpleForm',
            'description' => 'A simple form to manager in Simple Form Sender plugin.'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title'       => 'Type',
                'description' => 'Type for form.',
                'type'        => 'string',
                'default'     => '',
                'validation'  => [
                    'required' => [
                        'message' => 'The type field is required.'
                    ]
                ]
            ],
            'successFunction' => [
                'title'       => 'Succes Function',
                'description' => 'The return function for submit form.',
                'type'        => 'string',
                'default'     => ''
            ],
           'class' => [
                'title'       => 'Class',
                'description' => 'The CSS class.',
                'type'        => 'string',
                'default'     => ''
            ]
        ];
    }

    public function onRun(){
        $this->type = $this->property('type');
        $this->class = $this->property('class');
        $this->successFunction = $this->page['successFunction'] = $this->property('successFunction');
    }

    public function onSendForm(){
        $this->type =post('type');
        $this->nameForm = str_replace('_', '', $this->type);
        $checkForm = FormsRegister::with('form')->where('type','=',$this->type)->get();

        $checkLabel = Label::where('name','=',$this->nameForm)->get();
        $this->labelForm = (count($checkLabel) > 0 ? $checkLabel->first()->label : $this->nameForm);

        if(count($checkForm) == 0 ){
            $formRegister = new FormsRegister();
            $formRegister->type = $this->type;
            $formRegister->label = $this->labelForm;
            $formRegister->table_form = 'adrisonluz_simpleformsender_form' . $this->type . 's';
            $formRegister->model = 'Form' . ucfirst($this->nameForm);
            $formSave = $formRegister->save();

            if($formSave){
                Schema::create('adrisonluz_simpleformsender_form' . $this->type . 's', function($table)
                {
                    $table->increments('id');
                    foreach (post() as $key => $value) {
                        if($key !== 'type' ){
                            switch($key){
                                case $key == 'msg':
                                case $key == 'texto':
                                case $key == 'feedback':
                                case $key == 'mensagem':
                                    $table->text($key);
                                    break;
                                default:
                                    $table->string($key);
                                    break;
                            }
                        }
                    }
                    $table->timestamps();
                });

                $controllerFolder = File::makeDirectory('plugins/adrisonluz/simpleformsender/controllers/form' . $this->nameForm, 0777, true, true);
                $this->setComponent();
                $this->setControllerComponent();
                $this->createController();

                $varsModel = array();
                foreach (post() as $key => $value) {
                    if($key !== 'type'){
                        $varsModel[] = $key;
                    }
                }
                $modelFolder = File::makeDirectory('plugins/adrisonluz/simpleformsender/models/form' . $this->nameForm, 0777, true, true);
                $this->createModel();
                $this->setModel($varsModel);

                shell_exec('composer dump-autoload');
                $checkForm = FormsRegister::with('form')->where('type','=',$this->type)->get();
            }else{
                echo 'Error to register the new form.';
                die();
            }
        }

        $model = '\AdrisonLuz\SimpleFormSender\Models\\' . $checkForm->first()->model;
        $send = new $model();
        $vars = array();

        foreach (post() as $key => $value) {
            if($key !== 'type'){
                $send->$key = $value;

                $labelMail = Label::where('name','=',$key)->get();
                if(count($labelMail) > 0)
                    $key = $labelMail->first()->label;

                $vars[] = ['key' => $key, 'val' => $value];
            }
        }
        $send->save();

        if(isset($checkForm->first()->form->mailto)){
            $form = $checkForm->first()->form;
            $file = (isset($_FILES['arquivo']) ? $_FILES['arquivo'] : '');

            $checkTemplate = MailTemplate::where('code','=',$form->type)->get();
            if(count($checkTemplate) > 0){
                $templete = $checkTemplate->first()->code;
                $subject = $checkTemplate->first()->subject;
            }else{
                $templete = 'default';
                $subject = 'Site | ' . $form->label;
            }

            Mail::send($templete, ['vars' => $vars], function ($m) use ($vars, $form, $file, $subject) {
                $m->to($form->mailto)->subject($subject);

                if($file !== '')
                    $m->attach($file);
            });
        }
    }

    public function setComponent(){
            $appYaml = '            form-' . $this->nameForm . ':' . "\n"
                . '             label: ' . ucfirst($this->labelForm) . "\n"
                . '             url: adrisonluz/simpleformsender/form' . $this->nameForm . "\n"
                . '             icon: icon-send-o' . "\n"
                . '             permissions:' . "\n"
                    . '                 - simpleformsender_manager' . "\n";

            $yaml = fopen("plugins/adrisonluz/simpleformsender/plugin.yaml","a");
            $yamlWrite = fwrite($yaml, $appYaml);
            fclose($yaml);
    }

    public function createController(){
        $controllerPHP = '<?php namespace AdrisonLuz\SimpleFormSender\Controllers;' . "\n"
            . ' ' . "\n"
        . 'use Backend\Classes\Controller;' . "\n"
        . 'use BackendMenu;' . "\n"
            . ' ' . "\n"
        . 'class Form' . ucfirst($this->nameForm) . ' extends Controller' . "\n"
        . '{' . "\n"
            . 'public $implement = [\'Backend\Behaviors\ListController\'];' . "\n"
            . ' ' . "\n"
            . 'public $listConfig = \'config_list.yaml\';' . "\n"
            . ' ' . "\n"
            . 'public $requiredPermissions = [' . "\n"
                . '\'simpleformsender_manager\'' . "\n"
            . '];' . "\n"
            . ' ' . "\n"
            . 'public function __construct()' . "\n"
            . '{' . "\n"
                . 'parent::__construct();' . "\n"
                . 'BackendMenu::setContext(\'AdrisonLuz.SimpleFormSender\', \'simple-form-sender\', \'form-' . $this->nameForm . '\');' . "\n"
            . '}' . "\n"
        . '}' . "\n";

        $controller = fopen("plugins/adrisonluz/simpleformsender/controllers/Form" . ucfirst($this->nameForm) . '.php',"a");
        $controllerWrite = fwrite($controller, $controllerPHP);
        fclose($controller);
    }

    public function setControllerComponent(){
            $controllerConfig =  'title: ' . ucfirst($this->labelForm) . "\n"
                    . 'modelClass: AdrisonLuz\SimpleFormSender\Models\Form' . ucfirst($this->nameForm) . "\n"
                    . 'list: $/adrisonluz/simpleformsender/models/form' . $this->nameForm .  '/columns.yaml' . "\n"
                    . 'recordUrl: adrisonluz/simpleformsender/form' . $this->nameForm . "\n"
                    . 'noRecordsMessage: No registers found' . "\n"
                    . 'showSetup: true' . "\n"
                    . 'showCheckboxes: true' . "\n"
                    . 'toolbar:' . "\n"
                    . '     buttons: list_toolbar' . "\n"
                    . '     search:' . "\n"
                    . '             prompt: \'backend::lang.list.search_prompt\'' . "\n";

            $config = fopen("plugins/adrisonluz/simpleformsender/controllers/form" . $this->nameForm .  "/config_list.yaml","a");
            $configWrite = fwrite($config, $controllerConfig);
            fclose($config);

            $list_toolbar =  '<div data-control="toolbar">' . "\n"
                                            . '<button' . "\n"
                                            . 'class="btn btn-default oc-icon-trash-o"' . "\n"
                                            . 'disabled="disabled"' . "\n"
                                            . 'onclick="$(this).data(\'request-data\', {' . "\n"
                                                . 'checked: $(\'.control-list\').listWidget(\'getChecked\')' . "\n"
                                            . '})"' . "\n"
                                            . 'data-request="onDelete"' . "\n"
                                            . 'data-request-confirm="<?= e(trans(\'backend::lang.list.delete_selected_confirm\')) ?>"' . "\n"
                                            . 'data-trigger-action="enable"' . "\n"
                                            . 'data-trigger=".control-list input[type=checkbox]"' . "\n"
                                            . 'data-trigger-condition="checked"' . "\n"
                                            . 'data-request-success="$(this).prop(\'disabled\', true)"' . "\n"
                                            . 'data-stripe-load-indicator>' . "\n"
                                            . '<?= e(trans(\'backend::lang.list.delete_selected\')) ?>' . "\n"
                                        . '</button>' . "\n"
                                    . '</div>' . "\n";

            $toolbar = fopen("plugins/adrisonluz/simpleformsender/controllers/form" . $this->nameForm .  "/_list_toolbar.htm","a");
            $toolbarWrite = fwrite($toolbar, $list_toolbar);
            fclose($toolbar);

            $index = fopen("plugins/adrisonluz/simpleformsender/controllers/form" . $this->nameForm .  "/index.htm","a");
            $indexWrite = fwrite($index, '<?= $this->listRender() ?>');
            fclose($index);
    }

    public function createModel(){
        $modelPHP =  '<?php namespace AdrisonLuz\SimpleFormSender\Models;' . "\n"
        . ' ' . "\n"
        . 'use Model;' . "\n"
        . ' ' . "\n"
        . '/**' . "\n"
         . '* Form' . ucfirst($this->nameForm) . ' Model' . "\n"
         . '*/' . "\n"
        . 'class Form' . ucfirst($this->nameForm) . ' extends Model' . "\n"
        . '{' . "\n"
        . ' ' . "\n"
            . '/**' . "\n"
             . '* @var string The database table used by the model.' . "\n"
             . '*/' . "\n"
            . 'public $table = \'adrisonluz_simpleformsender_form' . $this->type . 's\';' . "\n"
        . ' ' . "\n"
            . '/**' . "\n"
             . '* @var array Guarded fields' . "\n"
             . '*/' . "\n"
            . 'protected $guarded = [\'*\'];' . "\n"
        . ' ' . "\n"
            . '/**' . "\n"
             . '* @var array Fillable fields' . "\n"
             . '*/' . "\n"
            . 'protected $fillable = [];' . "\n"
        . ' ' . "\n"
        . '}' . "\n";

        $model = fopen("plugins/adrisonluz/simpleformsender/models/Form" . ucfirst($this->nameForm) . '.php',"a");
        $modelWrite = fwrite($model, $modelPHP);
        fclose($model);
    }

    public function setModel($varsModel){
            $modelStructure = 'columns:' . "\n";

            foreach ($varsModel as $column) {
                $checkColumn = Label::where('name','=',$column)->get();
               $labelColumn = (count($checkColumn) > 0 ? $checkColumn->first()->label : $column);

                $modelStructure .= ' ' . $column . ':' . "\n"
                                            . '     label: ' . $labelColumn .  "\n"
                                            . '     type: text' . "\n"
                                            . '     searchable: true' . "\n"
                                            . '     sortable: true' . "\n";
            }

            $model = fopen("plugins/adrisonluz/simpleformsender/models/form" . $this->nameForm .  "/columns.yaml","a");
            $modelWrite = fwrite($model, $modelStructure);
            fclose($model);
    }
}