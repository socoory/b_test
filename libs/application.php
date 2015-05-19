<?php
if (!defined("__BAAS_API__")) exit;

class Application
{
	private $controller = null;
	private $action = null;

	public function __construct() {
		$canControll = false;
		$url = "";
		
		if(isset($_GET['url'])) {
			$url = rtrim($_GET['url'], '/');
			$url = filter_var($url, FILTER_SANITIZE_URL);
		}
		
		$params = explode('/', $url);
		$counts = count($params);
		$this->controller = "default";
		
		if(isset($params[0])) {
			if($params[0]) $this->controller = $params[0];
		}

		if(file_exists('./controller/' . $this->controller . '.php')) {
			require './controller/' . $this->controller . '.php';
			
			$this->controller = new $this->controller();
			$this->action = "index";
			
			if(isset($params[1])) {
				if($params[1]) {
					$this->action = $params[1];
				}
			}
			
			if(method_exists($this->controller, $this->action)) {
				$canControll = true;
				
				switch($counts){
					case '0':
					case '1':
					case '2':
						$this->controller->{$this->action}();
						break;						
					case '3':
						$this->controller->{$this->action}($params[2]);
						break;
					case '4':
						$this->controller->{$this->action}($params[2], $params[3]);
						break;
					case '5':
						$this->controller->{$this->action}($params[2], $params[3], $params[4]);
						break;
					case '6':
						$this->controller->{$this->action}($params[2], $params[3], $params[4], $params[5]);
						break;
					case '7':
						$this->controller->{$this->action}($params[2], $params[3], $params[4], $params[5], $params[6]);
						break;
					case '8':
						$this->controller->{$this->action}($params[2], $params[3], $params[4], $params[5], $params[6], $params[7]);
						break;
					case '9':
						$this->controller->{$this->action}($params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8]);
						break;
					case '10':
						$this->controller->{$this->action}($params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9]);
						break;
				}
			}
		}

		if($canControll == false) {
			require './views/404.php';
		}
		
		if(isset($_SESSION['user_ip'])) {
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			    $ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
			    $ip = $_SERVER['REMOTE_ADDR'];
			}
			
			if($_SESSION['user_ip'] != $ip) {
//				session_destroy();
//				echo '<script>
//					alert("IP가 변경되었습니다.!");
//					location.replace("'.URL.'");
//					</script>';
			}
		}
	}
}
