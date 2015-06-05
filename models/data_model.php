<?php
if (!defined("__BAAS_API__")) exit;


/**
 * Class Data_model
 *
 * Manipulate CRUD for Data Entity
 *
 */
Class Data_model extends Model {
	function __construct($db) {
		parent::__construct($db);
	}


    /**
     * get all data
     *
     * @return data object array
     *
     */
    function getData() {
        $sql = '
            SELECT
                *
            FROM
                data
            ORDER BY
                uuid DESC
        ';

        return $this->query_result($sql, null);
    }


    /**
     * get data by uuid
     * @param $uuid: int
     * @return data object
     */
    function getDataById($uuid) {
        $sql = '
            SELECT
                *
            FROM
                data
            WHERE
                uuid = ?
            ORDER BY
                uuid DESC
        ';

        return $this->query_row($sql, array($uuid));
    }


    /**
     * get data by id
     *
     * @param $user_id: int
     * @return data object array
     */
    function getDataByUserId($user_id) {
        $sql = '
            SELECT
                *
            FROM
                data
            WHERE
                user_id = ?
            ORDER BY
                uuid DESC
        ';

        return $this->query_result($sql, array($user_id));
    }


    /**
     * create a data record
     *
     * @param $user_id: int
     * @param $data: string
     * @return boolean
     */
	function createData($user_id, $data) {
		$sql = '
            INSERT INTO
                data
                (
                    regdate,
                    modified,
                    data,
                    user_id
                )
            VALUES (
                NOW(), NOW(), ?, ?
            )
        ';

		return $this->query_exec($sql, array($data, $user_id));
	}


    /**
     * update an data record
     *
     * @param $uuid: int
     * @para $data: string
     * @return boolean
     */
    function updateData($uuid, $data) {
        $sql = '
            UPDATE
                data
            SET
                modified = NOW(),
                data = ?
            WHERE
                uuid = ?
        ';

        return $this->query_exec($sql, array($data, $uuid));
    }


    /**
     * delete an data record
     *
     * @param $uuid: int
     * @return boolean
     */
    function deleteData($uuid) {
        $sql = '
            DELETE FROM
                data
            WHERE
                uuid = ?
        ';

        return $this->query_exec($sql, array($uuid));
    }


    /**
     * get data from to
     *
     * @param $from: int
     * @param $to: int
     * @param $user_id: int
     * @return data object array
     */
    function getDataByLimitWithUserId($from, $to, $user_id) {
        $sql = '
            SELECT
                *
            FROM
                data
            WHERE
                user_id = ?
            ORDER BY
                uuid DESC
            LIMIT
                '.$from.', '.$to.'
        ';

        return $this->query_result($sql, array($user_id));
    }


    /**
     * get data on date
     *
     * @param $date: date format string (yymmdd)
     * @return data object array
     */
    function getDataByDateWithUserId($date, $user_id) {
        $sql = '
            SELECT
                *
            FROM
                data
            WHERE
                user_id = ? AND
                DATE(regdate) = DATE(?)
            ORDER BY
                uuid DESC
        ';

        return $this->query_result($sql, array($user_id, $date));
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