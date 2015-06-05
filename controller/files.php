<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 19.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


/**
 * Class Files
 *
 * Manipulate API for Data Entity
 *
 */
Class Files extends Controller {
    private $user_model = null;


    function __construct()
    {
        parent::__construct();
        $this->file_model = $this->loadModel('file_model');
        $this->logdata_model = $this->loadModel('logdata_model');
    }


    /**
     * default action for file
     *
     * @param null $action: string
     * @param null $first: mixed
     * @param null $second: mixed
     *
     */
    function index($action=null, $first=null, $second=null) {
        /*
         * action is not null,
         */
        if($action) {
            switch($action) {
                case 'uuid':
                    switch($_SERVER['REQUEST_METHOD']) {
                        /*
                         * update a data record
                         * param $uuid: int
                         */
                        case 'PUT':
                            $uuid = $first;
                            $this->updateData($uuid);
                            break;


                        /*
                         * delete a data record
                         * param $uuid: int
                         */
                        case 'DELETE':
                            $uuid = $first;
                            $this->deleteData($uuid);
                            break;


                        default:
                            $message = '허용되지 않은 메소드입니다.';
                            require 'views/error.php';
                            break;
                    }
                    break;


                case 'limit':
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $from = $first;
                            $to = $second;
                            $this->getDataByLimit($from, $to);
                            break;


                        default:
                            $message = '허용되지 않은 메소드입니다.';
                            require 'views/error.php';
                            break;
                    }
                    break;


                case 'date':
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $date = $first;
                            $this->getDataByDate($date);
                            break;


                        default:
                            $message = '허용되지 않은 메소드입니다.';
                            require 'views/error.php';
                            break;
                    }
                    break;


                case 'user':
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $user_id = $first;
                            $this->getDataByUserId($user_id);
                            break;


                        default:
                            $message = '허용되지 않은 메소드입니다.';
                            require 'views/error.php';
                            break;
                    }
                    break;
            }
        }
        /*
         * action is null.
         */
        else {
            switch($_SERVER['REQUEST_METHOD']) {
                /*
                 * get all files for logged users
                 * if no records, return empty array
                 */
                case 'GET':
                    $this->getFilesByUserId();
                    break;


                /*
                 * create a data record
                 * post params: data
                 */
                case 'POST':
                    $this->createFile();
                    break;


                default:
                    $message = '허용되지 않은 메소드입니다.';
                    require 'views/error.php';
                    break;
            }
        }
    }


    /**
     * get all files
     */
    function getFiles() {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $files = $this->file_model->getFiles();

        $response = new stdClass();

        $response->code = 200;
        $response->data = $files;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonString;
    }


    /**
     * get all files for an user
     *
     * @param $user_id: int
     */
    function getFilesByUserId($user_id=null) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        if($user_id) {
            $files = $this->file_model->getFilesByUserId($user_id);
        }
        else {
            $files = $this->file_model->getFilesByUserId($_SESSION['uid']);
        }

        $response = new stdClass();

        $response->code = 200;
        $response->data = $files;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonString;
    }


    /**
     * create a data record
     */
    function createFile() {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $file = null;
        $attach = 0;

        if(!isset($_POST['file'])) {
            $message = '파일이 전송되지 않았습니다.';
            require 'views/error.php';
            return;
        }

        if(isset($_POST['attach'])) {
            $attach = $_POST['attach'];
        }

        $file = $_POST['file'];
        $res = $this->file_model->createFile($_SESSION['uid'], $file, $attach);

        if(!$res) {
            $message = '파일 저장 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';
    }


    /**
     * update a data record
     *
     * @param $uuid: int
     */
    function updateData($uuid) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $this->file_model->getDataById($uuid);

        if(!$data) {
            $message = '존재하지 않는 데이터입니다.';
            require 'views/error.php';
            return;
        }

        if($data->user_id != $_SESSION['uid']) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $modifiedData = $_REQUEST['data'];

        if(!$modifiedData) {
            $message = '저장될 데이터가 없습니다.';
            require 'views/error.php';
            return;
        }

        $res = $this->file_model->updateData($uuid, $modifiedData);

        if(!$res) {
            $message = '데이터 수정 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';
    }


    /**
     * delete a data record
     *
     * @param $uuid: int
     */
    function deleteData($uuid) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $this->file_model->getDataById($uuid);

        if(!$data) {
            $message = '존재하지 않는 데이터입니다.';
            require 'views/error.php';
            return;
        }

        if($data->user_id != $_SESSION['uid']) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $res = $this->file_model->deleteData($uuid);

        if(!$res) {
            $message = '데이터 삭제 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';
    }


    /**
     * get data from to
     *
     * @param $from
     * @param $to
     */
    function getDataByLimit($from, $to) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $this->file_model->getDataByLimitWithUserId($from, $to, $_SESSION['uid']);

        $response = new stdClass();
        $response->code = 200;
        $response->data = $data;

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }


    /**
     * get data on date
     *
     * @param $date: date format string (yymmdd)
     */
    function getDataByDate($date) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $date = date ("Y-m-d H:i:s", strtotime('20'.$date));

        $data = $this->file_model->getDataByDateWithUserId($date, $_SESSION['uid']);

        $response = new stdClass();
        $response->code = 200;
        $response->data = $data;

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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