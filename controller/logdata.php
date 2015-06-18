<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 22.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


/**
 * Class Logdata
 *
 * manipulates user authentication process, signout process
 *
 */
Class Logdata extends Controller {
    private $logdata_model = null;


    function __construct() {
        parent::__construct();
        $this->logdata_model = $this->loadModel('logdata_model');
    }


    function index($action, $first=null, $second=null) {
        switch($action) {
            case 'recent':
                if(!$second) {
                    $this->getRecentCalledApi($first);
                }
                else {
                    $this->getRecentLogsByApiName($first, $second);
                }
                break;
            case 'usage':
                $this->getRecentApiUsage($first);
                break;
            case 'frequency':
                $this->getApiCallFrequency($first);
                break;
        }
    }


    function getRecentCalledApi($limit) {
        $data = $this->logdata_model->getRecentCalledApi($limit);

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    function getRecentLogsByApiName($api_name, $limit) {
        $data = $this->logdata_model->getRecentLogsByApiName($api_name, $limit);

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    function getApiCallFrequency($days) {
        $data = $this->logdata_model->getApiCallFrequency($days);

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    function getRecentApiUsage($days) {
        $data = $this->logdata_model->getRecentApiUsage($days);

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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