<?php
if (!defined("__BAAS_API__")) exit;


/**
 * Class User_model
 *
 * Manipulate CRUD for User Entity
 *
 */
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


    /**
     * get all users
     *
     * @return object array
     *
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


    /**
     * get user by id
     *
     * @param $uid
     * @return user object
     */
    function getUserById($uid) {
        $sql = '
            SELECT
                *
            FROM
                users
            WHERE
                uuid = ?
        ';

        return $this->query_row($sql, array($uid));
    }


    /**
     * get user by email
     *
     * @param $email
     * @return uesr object
     */
    function getUserByEmail($email) {
        $sql = '
            SELECT
                *
            FROM
                users
            WHERE
                email = ?
        ';

        return $this->query_row($sql, array($email));
    }


    /**
     * create an user record
     *
     * @param $data: array(name:string, email:string, profile_image:string)
     * @return boolean
     *
     */
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


    /**
     * update an user record
     *
     * @param $userData
     * @return boolean
     */
    function updateUser($userData) {
        $sql = '
            UPDATE
                users
            SET
                modified = NOW(),
                username = ?,
                email = ?,
                profile_image = ?
            WHERE
                uuid = ?
        ';

        return $this->query_exec($sql, $userData);
    }


    /**
     * update an user record (level)
     *
     * @param $uid: int
     * @param $level: int
     * @return boolean
     */
    function levelingUser($uid, $level) {
        $sql = '
            UPDATE
                users
            SET
                modified = NOW(),
                level = ?
            WHERE
                uuid = ?
        ';

        return $this->query_exec($sql, array($level, $uid));
    }


    /**
     * delete an user record
     *
     * @param $uid
     * @return boolean
     */
    function deleteUser($uid) {
        $sql = '
            DELETE FROM
                users
            WHERE uuid = ?
        ';

        return $this->query_exec($sql, array($uid));
    }


    /**
     * activate user record
     *
     * @param $uid
     * @param $state
     * @return boolean
     */
    function activateUser($uid, $state) {
        $sql = '
            UPDATE
                users
            SET
                active = ?
            WHERE
                uuid = ?
        ';

        return $this->query_exec($sql, array($state, $uid));
    }


    /**
     * create an user_auth record
     *
     * @param $data
     * @return boolean
     */
    function createUserAuth($data) {
        $sql = '
            INSERT INTO
                user_auth
            VALUES (
                ?, ?
            )
        ';

        return $this->query_exec($sql, $data);
    }


    /**
     * get user by user's uuid
     *
     * @param $uid: int
     */
    function getUserAuthById($uid) {
        $sql = '
            SELECT
                *
            FROM
                user_auth
            WHERE
                uid = ?
        ';

        return $this->query_row($sql, array($uid));
    }
}