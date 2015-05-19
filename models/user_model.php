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
	 * @author benimario
	 * @since 2015.05.18
	 *
	 * @param array(username, email, profile_image_path)
	 *
	 * @return bool
	 */


    function getUsers() {
        $sql = '
            SELECT
                *
            FROM
                users
        ';

        return $this->query_result($sql, null);
    }


	function createUser($data) {
		$sql = '
					INSERT INTO
					    users
					    (
                            regdate,
                            modified,
                            username,
                            email,
                            profile_image
                        )
                    VALUES (
                        NOW(), NOW(), ?, ?, ?
                    )
				';

		return $this->query_exec($sql, $data);
	}
}