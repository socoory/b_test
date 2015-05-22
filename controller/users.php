<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 19.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


/**
 * Class Users
 *
 * Manipulate API for User Entity
 *
 */
Class Users extends Controller {
    private $user_model = null;


    function __construct()
    {
        parent::__construct();
        $this->user_model = $this->loadModel('user_model');
    }


    /**
     * default action for users
     *
     * @param null $uid: int
     * @param null $attr: string
     *
     */
    function index($uid=null, $attr=null) {
        /*
         * if uid is not null,
         * this function manipulates specific user record
         * attr is attribute of a user record
         */
        if($uid) {
            /*
             * if attr is not null,
             * manipulates specific attribute of an user record
             */
            if($attr) {
                switch($attr) {
                    /*
                     * edit user active status
                     * admin  only
                     */
                    case 'active':
                        break;


                    /*
                     * edit user level
                     * admin only
                     */
                    case 'level':
                        break;


                    default:
                        $message = '잘못된 요청입니다.';
                        require 'views/error.php';
                        break;
                }
            }
            /*
             * attr is null.
             * manipulates an user record.
             */
            else {
                switch($_SERVER['REQUEST_METHOD']) {
                    /*
                     * get user by uuid
                     * if no records, return empty json string
                     */
                    case 'GET':
                        $this->getUserById($uid);
                        break;


                    case 'POST':
                        $message = '허용되지 않은 메소드입니다.';
                        require 'views/error.php';
                        break;


                    /*
                     * update an user record
                     * admin or user self only
                     */
                    case 'PUT':
                        $this->updateUser($uid);
                        break;


                    /*
                     * delete an user record
                     * admin or userself only
                     */
                    case 'DELETE':
                        echo 'delete';
                        break;
                }
            }
        }
        /*
         * uid is null.
         */
        else {
            switch($_SERVER['REQUEST_METHOD']) {
                /*
                 * get all users
                 * if no records, return empty array
                 */
                case 'GET':
                    $this->getUsers();
                    break;


                /*
                 * create an user
                 * post params: name, email, profile_image
                 */
                case 'POST':
                    $this->createUser();
                    break;


                case 'PUT':
                    $message = '허용되지 않은 메소드입니다.';
                    require 'views/error.php';
                    break;


                case 'DELETE':
                    $message = '허용되지 않은 메소드입니다.';
                    require 'views/error.php';
                    break;
            }
        }
    }


    /**
     * get user by uuid
     */
    function getUserById($uid) {
        $user = $this->user_model->getUserById($uid);

        if($user) {
            $data = $user;
        }
        else {
            $data = '[]';
        }

        $response = new stdClass();
        $response->code = 200;
        $response->data = $data;

        $jsonString = json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo $jsonString;
    }


    /**
     * get all users
     */
    function getUsers() {
        $users = $this->user_model->getUsers();

        if($users) {
            $data = $users;
        }
        else {
            $data = '[]';
        }

        $response = new stdClass();
        $response->code = 200;
        $response->data = $data;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonString;
    }


    /**
     * create an user record.
     */
    function createUser() {
        // get post values
        if(isset($_POST['username'])) {
            $username = strip_tags($_POST['username']);
        }
        else {
            $message = '이름이 필요합니다.';
            require 'views/error.php';
            return;
        }
        if(isset($_POST['email'])) {
            $email = strip_tags($_POST['email']);
        }
        else {
            $message = '이메일이 필요합니다.';
            require 'views/error.php';
            return;
        }
        if(isset($_POST['profile_image'])) {
            $profile_image = strip_tags($_POST['profile_image']);
        }
        else {
            $profile_image = 'null';
        }
        if(isset($_POST['password'])) {
            $password = strip_tags($_POST['password']);
        }
        else {
            $message = '비밀번호가 필요합니다.';
            require 'views/error.php';
            return;
        }

        // email verification
        $verificationResult = $this->verifyEmail($email);

        // verification fail. return error response
        if($verificationResult != 'success') {
            $message = $verificationResult;
            require 'views/error.php';
            return;
        }

        $user = $this->user_model->getUserByEmail($email);
        if($user) {
            $message = '동일한 이메일 주소가 존재합니다.';
            require 'views/error.php';
            return;
        }

        // create an user record
        $result = $this->user_model->createUser(
            array(
                $username,
                $email,
                $profile_image
            )
        );

        if($result) {
            require 'views/success.php';
        }
        else {
            $message = '회원 등록 중 오류가 발생하였습니다.';
            require 'views/error.php';
        }

        $user = $this->user_model->getUserByEmail($email);

        // create an user_auth record
        $result = $this->user_model->createUserAuth(
            array(
                $user->uuid,
                password_hash($password, PASSWORD_BCRYPT)
            )
        );
    }


    /**
     * update an user record
     * admin or user self only
     *
     * @param $uid
     */
    function updateUser($uid) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        if(isset($_POST['userData'])) {
            $message = '잘못된 요청입니다.';
            require 'views/error.php';
            return;
        }

        $userData = json_decode($_REQUEST['userData']);

        // json format error
        if(!$userData || !$this->verifyUserData($userData)) {
            $message = '필드 수가 맞지 않습니다.';
            require 'views/error.php';
            return;
        }

        $user = $this->user_model->getUserById($uid);

        // user validation
        if(!$user) {
            $message = '회원 정보가 존재하지 않습니다.';
            require 'views/error.php';
            return;
        }

        // user verification
        if(!isset($_SESSION['uid']) || $_SESSION['uid'] != $uid) {
            $message = '사용자 정보가 일치하지 않습니다.';
            require 'views/error.php';
            return;
        }

        // email verification
        $verificationResult = $this->verifyEmail($userData->email);
        if($verificationResult != 'success') {
            $message = $verificationResult;
            require 'views/error.php';
            return;
        }

        $result = $this->user_model->updateUser(
            array(
                $userData->username,
                $userData->email,
                $userData->profile_image
            )
        );

        if($result) {
            require 'views/success.php';
        }
        else {
            $message = '회원 정보 수정 중 오류가 발생하였습니다.';
            require 'views/error.php';
        }
    }


    /**
     * verify email address
     *
     * @param $email
     * @return string
     */
    function verifyEmail($email) {

        return 'success';
    }


    /**
     * return true if logged in or admin user
     * else return false
     */
    function validatePermission() {
        return (isset($_SESSION['is_logged']) && $_SESSION['is_logged']) ||
               (isset($_SESSION['is_admin']) && $_SESSION['is_admin']);
    }


    /**
     * verify fields in userData
     * 
     * @param $userData
     * @return bool
     */
    function verifyUserData($userData) {
        return true;
    }
}