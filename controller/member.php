<?php
if (!defined("__BAAS_API__")) exit;

Class Member extends Controller {
	function __construct() {
		parent::__construct();
		$this->user_model = $this->loadModel('user_model');
	}

	//*********************************************************************//
	//                              sign up                                //
	//*********************************************************************//
	
	// ----------------------------------------------------------------------
	/**
	 * Sign up 
	 * 
	 * @author waldo
	 * @since 2015.05.04
	 * 
	 */
	function signup_process() {
        $email = $_REQUEST["email"];
        $checked_email 	= $this->user_model->getUserByEmail($email);

		if(!empty($checked_email)) {	//check overlapping e-mail
			echo '중복된 이메일입니다.';
		}
		else {
            $name           = $_REQUEST["name"];
			$password	 	= password_hash($_REQUEST["password"], PASSWORD_BCRYPT);	//bcrypt for hashing password
			$res  = $this->user_model->createUser($email, $name, $password);

			if($res) {
				echo 'Sign Up Complete';
			}
			else {
				echo 'Sign Up Fail';
			}
		}
	}

    //*********************************************************************//
	//                                Login                                //
	//*********************************************************************//
	
	// ----------------------------------------------------------------------
	/**
	 * Login 
	 * 
	 * @author waldo
	 * @since 2015.05.04
	 * 
	 */
	function login_process() {
		$email		= $_REQUEST["email"];
		$res 		= $this->user_model->getUserByEmail($email);
		 
		if($res == false) {
            echo '가입된 이메일이 없습니다.';
        }
		else {
            $password	= $_REQUEST["password"];
			if(password_verify($password, $res->user_password)) {			//check password's verify
				$_SESSION['is_logged'] 			= true;
				$_SESSION['user_id'] 			= $res->user_id;
				$_SESSION['user_name'] 			= $res->user_name;
				$_SESSION['user_email'] 		= $res->user_email;
                echo '로그인 성공';
			}
			else {
				echo '패스워드가 맞지 않습니다.';
			}
		}
	}
	
	//*********************************************************************//
	//                                 Edit                                //
	//*********************************************************************//
	
	// ----------------------------------------------------------------------
	/**
	 * Edit user's information 
	 * 
	 * @author waldo
	 * @since 2015.02
	 * 
	 */
	function edit_process() {
		
		require 'libs/functions.php';
		
		$email_info = $_POST['user_email'];
		$checked_email = $this->user_model->getUserByEmail($email_info);
		
		if((empty($checked_email) == FALSE) && ($_SESSION['user_email'] != $_POST['user_email'])) { //check overlapping e-mail
			$this->redirect("중복된 이메일이 있습니다.", "member", "edit");
			return;
		}
		else {
			$temp_res = $this->user_model->getUserById($_SESSION['user_id']); 
			$old_password = $_POST['user_old_password'];
			
			if(password_verify($old_password, $temp_res->password) == FALSE) { //check old password
				$this->redirect("이전 패스워드가 맞지 않습니다.", "member", "edit");
				return;
			}
			else {
				if(isset($_POST['check_delete_image'])) {
					$uploadfile = 'images/no-profile.png';
				}
				else {
					$user_profile_image = $_FILES['user_profile_image'];
					$fileType 			= explode('/', $user_profile_image['type']);
					$fileName 			= time().$user_profile_image['name'];
					if($user_profile_image['name'] == null) {
						$uploadfile = $_SESSION['user_profile_image'];
					}
					else {
						if($fileType[0] != 'image') {	// check that file type is image
							$this->redirect("이미지 파일이 아닙니다.", "member", "edit");
							return;
						}
						else {
							$uploaddir = 'upload/profile/'.$_SESSION['user_id'];				
							if(file_exists($uploaddir) == false) {
								mkdir("$uploaddir", 0777); 
							}
							$fileName = sha1($fileName).'.'.$fileType[1];
							$uploadfile = $uploaddir.'/'.$fileName;
							if(move_uploaded_file($user_profile_image['tmp_name'], $uploadfile))
							{
								$thumb = createThumb($uploaddir, $fileName, 100, 100);
								$uploadfile = $thumb;
							}
						}
					}
				}

				$options 		= array('cost' => 11);
				$email      	= $_POST['user_email'];
				$name     		= $_POST['user_name'];
				
				if($_POST['user_new_password'] == "") {
					$password = $temp_res->password;
				}
				else {
					$password = password_hash($_POST['user_new_password'], PASSWORD_BCRYPT, $options);
				}
				
				$group_id		= $_POST['user_group_id'];
				$user_id 		= $_SESSION['user_id'];
				$profile_image	= $uploadfile;
				$info = array($email, $name, $password, $group_id, $profile_image, $user_id);
				$res = $this->user_model->updateUserInfo($info);
				
				if($res == TRUE) {
					$_SESSION['user_id'] 			= $res->id;
					$_SESSION['is_logged'] 			= true;
					$_SESSION['user_name'] 			= $res->name;
					$_SESSION['user_email'] 		= $res->email;
					$_SESSION['user_group_id'] 		= $res->group_id;
					$_SESSION['user_profile_image']	= $res->profile_image;
					header('Location: '. URL);
				}
				else {
					echo '<script>
					alert("Edit Fail!");
					location.replace("'.URL.'/member/edit");
					</script>';	
				}
			}
		}
	}
	
	//*********************************************************************//
	//                                Logout                               //
	//*********************************************************************//
	
	// ----------------------------------------------------------------------
	/**
	 * Logout
	 * 
	 * @author waldo
	 * @since 2015.02
	 * 
	 */
	function logout() {
		session_destroy();
		echo '<script>
			alert("Logout Success!");
			location.replace("'.URL.'");
			</script>';
	}
}