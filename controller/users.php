<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 19.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


Class Users extends Controller
{
    private $user_model = null;

    function __construct()
    {
        parent::__construct();
        $this->user_model = $this->loadModel('user_model');
    }

    function index() {
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $users = $this->user_model->getUsers();

                if($users) {
                    $response = json_encode($users, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    echo $response;
                }
                else {
                    echo '{}';
                }
                break;
            case 'POST':
                $result = $this->user_model->createUser(
                    array(
                        'test'.rand(0,1000),
                        'test'.rand(0,1000),
                        'test'.rand(0,1000)
                    )
                );
                if($result) {
                    require 'views/success.php';
                }
                break;
            case 'PUT':
                echo 'put';
                break;
            case 'DELETE':
                echo 'delete';
                break;
        }
    }
}