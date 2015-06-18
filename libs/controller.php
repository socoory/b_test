<?php

class Controller
{
	public $db = null;

	function __construct()
	{
		$this->dbConnect();

        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        }
	}

	private function dbConnect()
	{
		$options = array(
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, 
				PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
		$this->db = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS, $options);
	}

	public function loadModel($model_name)
	{
		if(!class_exists($model_name))
			require 'models/' . strtolower($model_name) . '.php';
		return new $model_name($this->db);
	}
	
	public function redirect($message, $controller, $action) {
		echo '<script>alert("'.$message.'"); location.replace("'.URL.'/'.$controller.'/'.$action.'");</script>';
	}

	function mapSession() {
		$object = new stdClass();
		
		foreach($_SESSION as $key => $val) {
			$object->$key = $val;
		}
		
		return $object;
	}

	function mapPost() {
		$object = new stdClass();
		
		foreach($_POST as $key => $val) {
			$object->$key = $val;
		}
		
		return $object;
	}
}