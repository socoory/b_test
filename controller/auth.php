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
        $data = json_decode(file_get_contents('php://input'));

        if($data != null) {
            $_POST = get_object_vars($data);
        }
        
        switch($action) {
            case 'get':
                $this->getUserSession();
                break;
            case 'signin':
                $this->signin();
                break;
            case 'signout':
                $this->signout();
                break;
        }
    }


    /**
     * signin
     *
     * @postparam email, password
     */
    function signin() {
        if(isset($_POST['email'])) {
            $email = strip_tags($_POST['email']);
        }
        else {
            $message = '이메일이 필요합니다.';
            require 'views/error.php';
            return;
        }

        if(isset($_POST['password'])) {
            $password = strip_tags($_POST['password']);
        }
        else {
            $message = '비밀번호가 필요합니다.';
            require 'views/error.php';
            return;
        }

        $user = $this->user_model->getUserByEmail($email);

        if(!$user) {
            $message = '사용자 정보가 존재하지 않습니다.';
            require 'views/error.php';
            return;
        }

        $userAuth = $this->user_model->getUserAuthById($user->uuid);

        if(!$userAuth) {
            $message = '사용자 인증 정보가 존재하지 않습니다.';
        }

        if(password_verify($password, $userAuth->password)) {
            $_SESSION['is_logged'] = true;
            $_SESSION['uid'] = $user->uuid;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['profile_image'] = $user->profile_image;

            require 'views/success.php';
        }
        else {
            $message = '인증 정보가 일치하지 않습니다.';
            require 'views/error.php';
        }
    }


    /**
     * signout
     */
    function signout() {
        $_SESSION['is_logged'] = false;
        session_destroy();

        require 'views/success.php';
    }


    /**
     * get user session
     */
    function getUserSession() {
        $userData = $_SESSION;
        unset($userData['is_logged']);
        $response = new stdClass();
        $response->code = 200;
        $response->data = $userData;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo $jsonString;
    }
}