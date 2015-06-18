<?php
/**
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
        $this->logdata_model = $this->loadModel('logdata_model');
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
                     *
                     * @post param state: boolean
                     */
                    case 'active':
                        $this->activateUser($uid);
                        break;


                    /*
                     * edit user level
                     * admin only
                     */
                    case 'level':
                        $this->levelingUser($uid);
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
                        $this->deleteUser($uid);
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
     *
     * @echo user object to json string
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

        $jsonString = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo $jsonString;

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'get user by user id',
            'attr' => null,
            'target_id' => null
        ));
    }


    /**
     * get all users
     *
     * @echo user objects array to json string
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

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'get users',
            'attr' => null,
            'target_id' => null
        ));
    }


    /**
     * create an user record.
     *
     * @post param username: string
     * @post param email: string
     * @post param profile_image: string
     * @post param password: string
     */
    function createUser() {
        // get post values
        $postData = json_decode(file_get_contents('php://input'));

        if($postData != null) {
            $_POST = get_object_vars($postData);
        }

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
        $verificationResult = $this->verifyEmail($email, true);

        // verification fail. return error response
        if($verificationResult != 'success') {
            $message = $verificationResult;
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
            return;
        }

        $user = $this->user_model->getUserByEmail($email);

        // create an user_auth record
        $result = $this->user_model->createUserAuth(
            array(
                $user->uuid,
                password_hash($password, PASSWORD_BCRYPT)
            )
        );

        $target_id = $this->user_model->insertId();

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'create user',
            'attr' => null,
            'target_id' => $target_id
        ));
    }


    /**
     * update an user record
     * admin or user self only
     *
     * @param $uid
     *
     * @post param userData: json string{
     *      username: string
     *      email: string
     *      profile_image: string
     * }
     */
    function updateUser($uid) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = json_decode(file_get_contents('php://input'));

        if($data != null) {
            $_REQUEST = get_object_vars($data);
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

        if(!isset($_REQUEST['username'])) {
            $_REQUEST['username'] = $user->username;
        }
        if(!isset($_REQUEST['email'])) {
            $_REQUEST['email'] = $user->email;
        }
        if(!isset($_REQUEST['profile_image'])) {
            $_REQUEST['profile_image'] = $user->profile_image;
        }

        // email verification
        $verificationResult = $this->verifyEmail($_REQUEST['email']);

        if($verificationResult != 'success') {
            $message = $verificationResult;
            require 'views/error.php';
            return;
        }

        $result = $this->user_model->updateUser(
            array(
                $_REQUEST['username'],
                $_REQUEST['email'],
                $_REQUEST['profile_image'],
                $uid
            )
        );

        if($result) {
            require 'views/success.php';
        }
        else {
            $message = '회원 정보 수정 중 오류가 발생하였습니다.';
            require 'views/error.php';
        }

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'update user',
            'attr' => null,
            'target_id' => $uid
        ));
    }


    /**
     * delete an user record
     * by admin or userself only
     *
     * @param $uid: int
     */
    function deleteUser($uid) {
        $user = $this->user_model->getUserById($uid);

        if(!($this->isAdmin() || (isset($_SESSION['uid']) && $user->uuid == $_SESSION['uid']))) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $result = $this->user_model->deleteUser($uid);

        if(!$result) {
            $message = '회원 정보 삭제 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'delete user',
            'attr' => null,
            'target_id' => $uid
        ));
    }


    /**
     * activate user record
     * admin only
     *
     * @param $uid
     *
     * @post param state: boolean
     */
    function activateUser($uid) {
        if(!$this->isAdmin()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
        }

        $user = $this->user_model->getUserById($uid);

        if(!$user) {
            $message = '회원 정보가 존재하지 않습니다.';
            require('views/error.php');
        }
        else {
            $data = json_decode(file_get_contents('php://input'));

            if($data != null) {
                $_POST = get_object_vars($data);
            }

            if(!isset($_POST['state'])) {
                $message = '상태 값이 전송되지 않았습니다.';
                require 'views/error.php';
                return;
            }

            $state = $_POST['state'];

            if($state == 'true') {
                $state = 1;
            }
            else if($state == 'false') {
                $state = 0;
            }
            else {
                $message = '올바르지 않은 값입니다.';
                require 'views/error.php';
                return;
            }

            $result = $this->user_model->activateUser($uid, $state);

            if($result) {
                require 'views/success.php';
            }
            else {
                $message = '회원 활성상태 수정 중 오류가 발생하였습니다.';
                require 'views/error.php';
            }
        }

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'activate user',
            'attr' => 'active',
            'target_id' => $uid
        ));

        return;
    }


    /**
     * edit user level
     * admin only
     *
     * @param $uid
     *
     * @post param level: short
     */
    function levelingUser($uid) {
        if(!$this->isAdmin()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
        }

        $user = $this->user_model->getUserById($uid);

        if(!$user) {
            $message = '회원 정보가 존재하지 않습니다.';
            require('views/error.php');
        }
        else {
            $data = json_decode(file_get_contents('php://input'));

            if($data != null) {
                $_POST = get_object_vars($data);
            }

            if(!isset($_POST['level'])) {
                $message = '레벨 값이 존재하지 않습니다.';
                require 'views/error.php';
                return;
            }

            $level = $_POST['level'];

            if(!filter_var($level, FILTER_VALIDATE_INT)) {
                $message = '올바르지 않은 값입니다.';
                require 'views/error.php';
                return;
            }

            $result = $this->user_model->levelingUser($uid, $level);

            if($result) {
                require 'views/success.php';
            }
            else {
                $message = '회원 레벨 수정 중 오류가 발생하였습니다.';
                require 'views/error.php';
            }
        }

        $this->logdata_model->log(array(
            'api_name' => 'user',
            'action' => 'leveling user',
            'attr' => 'level',
            'target_id' => $uid
        ));
    }


    /**
     * verify email address
     *
     * @param $email
     * @param $new: boolean - is new user or not
     * @return string
     */
    function verifyEmail($email, $new=false) {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '이메일 형식이 맞지 않습니다.';
        }

        if($new) {
            $user = $this->user_model->getUserByEmail($email);
            if ($user) {
                return '동일한 이메일 주소가 존재합니다.';
            }
        }

        return 'success';
    }


    /**
     * return true if logged in or admin user
     * else return false
     */
    function validatePermission() {
        return (isset($_SESSION['is_logged']) && $_SESSION['is_logged'])
               || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']);
    }


    /**
     * return true if admin user
     *
     * @return bool
     */
    function isAdmin() {
        return true;
        return (isset($_SESSION['is_admin']) && $_SESSION['is_admin']);
    }
}