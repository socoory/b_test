<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 19.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


/**
 * Class Data
 *
 * Manipulate API for Data Entity
 *
 */
Class Data extends Controller {
    private $user_model = null;


    function __construct()
    {
        parent::__construct();
        $this->data_model = $this->loadModel('data_model');
        $this->logdata_model = $this->loadModel('logdata_model');
    }


    /**
     * default action for data
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
                            $data = json_decode(file_get_contents('php://input'));

                            if($data != null) {
                                $_REQUEST = get_object_vars($data);
                            }
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
                 * get all data for logged users
                 * if no records, return empty array
                 */
                case 'GET':
                    $this->getDataByUserId();
                    break;


                /*
                 * create a data record
                 * post params: data
                 */
                case 'POST':
                    $data = json_decode(file_get_contents('php://input'));

                    if($data != null) {
                        $_POST = get_object_vars($data);
                    }
                    $this->createData();
                    break;


                default:
                    $message = '허용되지 않은 메소드입니다.';
                    require 'views/error.php';
                    break;
            }
        }
    }


    /**
     * get all data
     */
    function getData() {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $this->data_model->getData();

        $response = new stdClass();

        $response->code = 200;
        $response->data = $data;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonString;

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'get data',
            'attr' => null,
            'target_id' => null
        ));
    }


    /**
     * get all data for an user
     *
     * @param $user_id: int
     */
    function getDataByUserId($user_id=null) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        if($user_id) {
            $data = $this->data_model->getDataByUserId($user_id);
        }
        else {
            $data = $this->data_model->getDataByUserId($_SESSION['uid']);
        }

        $response = new stdClass();

        $response->code = 200;
        $response->data = $data;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonString;

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'get data by user id',
            'attr' => null,
            'target_id' => null
        ));
    }


    /**
     * create a data record
     */
    function createData() {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        if(!isset($_POST['data'])) {
            $message = '저장될 데이터가 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $_POST['data'];

        $res = $this->data_model->createData($_SESSION['uid'], $data);

        if(!$res) {
            $message = '데이터 저장 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';

        $target_id = $this->data_model->insertId();

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'create data',
            'attr' => null,
            'target_id' => $target_id
        ));
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

        $data = $this->data_model->getDataById($uuid);

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

        $res = $this->data_model->updateData($uuid, $modifiedData);

        if(!$res) {
            $message = '데이터 수정 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'update data',
            'attr' => null,
            'target_id' => $uuid
        ));
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

        $data = $this->data_model->getDataById($uuid);

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

        $res = $this->data_model->deleteData($uuid);

        if(!$res) {
            $message = '데이터 삭제 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'delete data',
            'attr' => null,
            'target_id' => $uuid
        ));
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

        $data = $this->data_model->getDataByLimitWithUserId($from, $to, $_SESSION['uid']);

        $response = new stdClass();
        $response->code = 200;
        $response->data = $data;

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'get data using limit',
            'attr' => null,
            'target_id' => null
        ));
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

        $data = $this->data_model->getDataByDateWithUserId($date, $_SESSION['uid']);

        $response = new stdClass();
        $response->code = 200;
        $response->data = $data;

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->logdata_model->log(array(
            'api_name' => 'data',
            'action' => 'get data using date',
            'attr' => null,
            'target_id' => null
        ));
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