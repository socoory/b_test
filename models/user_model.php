<?php
if (!defined("__BAAS_API__")) exit;

Class User_model extends Model {
	function __construct($db) {
		parent::__construct($db);
	}
	
	// --------------------------------------------------------------------
	/**
	 * create user
	 *
	 * @author Waldo
	 * @since 2015.02.28
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @param int
	 * @param string: url
	 *
	 * @return bool
	 */
	function createUser($email, $name, $password) {
		$sql = '
					INSERT INTO
						user
						(
							user_email,
							user_name,
							user_password,
							regdate
						)
					VALUE
						(
							?, ?, ?,
							now()
						)
				';
		return $this->query_exec($sql, array($email, $name, $password));
	}
}