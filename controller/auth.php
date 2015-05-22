<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 22.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


/**
 * Class Auth
 *
 * manipulates user authentication process, signout process
 *
 */
Class Auth extends Controller {
    private $user_model = null;


    function __construct() {
        parent::__construct();
        $this->user_model = $this->loadModel('user_model');
    }


    /**
     * login / logout process
     *
     * @param $action: string
     */
    function index($action=null) {
        switch($action) {
            case 'signin':
                break;
            case 'signout':
                session_destroy();
                break;
        }
    }
}