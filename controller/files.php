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
    private $file_model = null;
    private $logdata_model = null;


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
                         * update a file record
                         * param $uuid: int
                         */
                        case 'PUT':
                            $uuid = $first;
                            $this->updateFile($uuid);
                            break;


                        /*
                         * delete a file record
                         * param $uuid: int
                         */
                        case 'DELETE':
                            $uuid = $first;
                            $this->deleteFile($uuid);
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
                            if($first) {
                                $this->getFilesByUserId($first);
                            }
                            else {
                                $this->getFilesByUserId($_SESSION['uid']);
                            }
                            break;


                        default:
                            $message = '허용되지 않은 메소드입니다.';
                            require 'views/error.php';
                            break;
                    }
                    break;


                case 'data':
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $data_id = $first;
                            $this->getFilesByDataId($data_id);
                            break;


                        default:
                            $message = '허용되지 않은 메소드입니다.';
                            require 'views/error.php';
                            break;
                    }
                    break;


                default:
                    require 'views/404.php';
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

        $this->logdata_model->log(array(
            'api_name' => 'file',
            'action' => 'get files',
            'attr' => null,
            'target_id' => null
        ));
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

        $this->logdata_model->log(array(
            'api_name' => 'file',
            'action' => 'get files by user id',
            'attr' => null,
            'target_id' => null
        ));
    }


    /**
     * create a file record
     */
    function createFile() {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $file = null;
        $filename = null;
        $attach = 0;

        $data = json_decode(file_get_contents('php://input'));

        if($data != null) {
            $_POST = get_object_vars($data);
        }

        if(!isset($_POST['file'])) {
            $message = '파일이 전송되지 않았습니다.';
            require 'views/error.php';
            return;
        }

        if(isset($_POST['attach'])) {
            $attach = $_POST['attach'];
        }

        $file = $_POST['file'];

        $image = $file;
        $image = str_replace('data:image/', '', $image);
        $imageType = explode(';', $image);
        $image = str_replace($imageType[0].';base64','',$image);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);

        $uploaddir = 'uploads/';

        $username = '';
        if(isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
        }
        $fileName 	 = time().sha1($username);
        $fileName 	 = $fileName.'.'.$imageType[0];
        $uploadFile  = $uploaddir . $fileName;
        $thumb		 = null;

        if(file_put_contents($uploadFile, $image)) {
//            list($w,$h, $type) = getimagesize($uploadFile);
//
//            // jpeg orientation
//            if($type == 2) {
//                $exif = exif_read_data($uploadFile);
//                if(isset($exif['Orientation'])) {
//                    $img = imagecreatefromjpeg($uploadFile);
//                    switch($exif['Orientation']) {
//                        case 6: // rotate 90 degrees CW
//                            $img = imagerotate($img, -90, 0);
//                            break;
//                        case 8: // rotate 90 degrees CCW
//                            $img = imagerotate($img, 90, 0);
//                            break;
//                    }
//                    imagejpeg($img, $uploadFile);
//                }
//            }
//
//            list($w,$h, $type) = getimagesize($uploadFile);
//            if($w > 900) {
//                resizeImage($uploadFile);
//            }
//
//            list($w,$h, $type) = getimagesize($uploadFile);
//            if($w > 512 || $h > 512) {
//                $thumb = createThumb($uploaddir, $fileName, 512, round((512/$w)*$h), FALSE);
//                $uploadfile = $thumb;
//            }
//            else {
//                copy($uploadFile, $uploaddir.'thumb_512x512_'.$fileName);
//            }
        }
        else {
            $message = '파일 전송 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        $res = $this->file_model->createFile($_SESSION['uid'], $uploadFile, $attach);

        $target_id = $this->file_model->insertId();

        if(!$res) {
            $message = '파일 저장 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        $response = new stdClass();
        $response->code = 200;
        $response->message = 'success';
        $response->id = $target_id;
        $response->path = $uploadFile;

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->logdata_model->log(array(
            'api_name' => 'file',
            'action' => 'upload image',
            'attr' => null,
            'target_id' => $target_id
        ));
    }


    /**
     * update a file record
     *
     * @param $uuid: int
     */
    function updateFile($uuid) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $this->file_model->getFileById($uuid);

        if(!$data) {
            $message = '존재하지 않는 파일입니다.';
            require 'views/error.php';
            return;
        }

        if($data->user_id != $_SESSION['uid']) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = json_decode(file_get_contents('php://input'));

        if($data != null) {
            $_REQUEST = get_object_vars($data);
        }

        $modifiedData = $_REQUEST['file'];

        if(!$modifiedData) {
            $message = '저장될 파일이 없습니다.';
            require 'views/error.php';
            return;
        }

        $dir = $data->path;

        if(file_put_contents($dir, $modifiedData)) {
        }
        else {
            $message = '파일 수정 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';

        $this->logdata_model->log(array(
            'api_name' => 'file',
            'action' => 'update file',
            'attr' => null,
            'target_id' => $uuid
        ));
    }


    /**
     * delete a file record
     *
     * @param $uuid: int
     */
    function deleteFile($uuid) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $data = $this->file_model->getFileById($uuid);

        if(!$data) {
            $message = '존재하지 않는 파일입니다.';
            require 'views/error.php';
            return;
        }

        if($data->user_id != $_SESSION['uid']) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $res = $this->file_model->deleteFile($uuid);

        if(!$res) {
            $message = '데이터 삭제 중 오류가 발생하였습니다.';
            require 'views/error.php';
            return;
        }

        require 'views/success.php';

        $this->logdata_model->log(array(
            'api_name' => 'file',
            'action' => 'delete file',
            'attr' => null,
            'target_id' => $uuid
        ));
    }


    function getFilesByDataId($data_id) {
        if(!$this->validatePermission()) {
            $message = '권한이 없습니다.';
            require 'views/error.php';
            return;
        }

        $files = $this->file_model->getFilesByDataId($data_id);


        $response = new stdClass();

        $response->code = 200;
        $response->data = $files;

        $jsonString = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo $jsonString;

        $this->logdata_model->log(array(
            'api_name' => 'file',
            'action' => 'get files by data id',
            'attr' => null,
            'target_id' => $data_id
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