<?php
/**
 * Created by PhpStorm.
 * User: benimario
 * Date: 15. 5. 19.
 * Time: 오후 10:02
 */
if (!defined("__BAAS_API__")) exit;


/**
 * Class Test
 *
 * Manipulate API for Data Entity
 *
 */
Class Test extends Controller {

    function __construct() {
        parent::__construct();
        $this->file_model = $this->loadModel('file_model');
        $this->logdata_model = $this->loadModel('logdata_model');
    }


    function index() {
        require 'views/test.php';
    }
}