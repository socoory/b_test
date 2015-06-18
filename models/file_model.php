<?php
if (!defined("__BAAS_API__")) exit;


/**
 * Class File_model
 *
 * Manipulate CRUD for File Entity
 *
 */
Class File_model extends Model {
	function __construct($db) {
		parent::__construct($db);
	}


    /**
     * get all data
     *
     * @return data object array
     *
     */
    function getFiles() {
        $sql = '
            SELECT
                *
            FROM
                files
            ORDER BY
                uuid DESC
        ';

        return $this->query_result($sql, null);
    }


    /**
     * get files by uuid
     * @param $uuid: int
     * return file object
     */
    function getFileById($uuid) {
        $sql = '
            SELECT
                *
            FROM
                files
            WHERE
                uuid = ?
            ORDER BY
                uuid DESC
        ';

        return $this->query_row($sql, array($uuid));
    }


    /**
     * get files by id
     *
     * @param $user_id: int
     * @return file object array
     */
    function getFilesByUserId($user_id) {
        $sql = '
            SELECT
                *
            FROM
                files
            WHERE
                user_id = ?
            ORDER BY
                uuid DESC
        ';

        return $this->query_result($sql, array($user_id));
    }


    /**
     * create a file record
     *
     * @param $user_id: int
     * @param $path: string
     * @return boolean
     *
     */
	function createFile($user_id, $path) {
		$sql = '
            INSERT INTO
                files
                (
                    regdate,
                    path,
                    user_id
                )
            VALUES (
                NOW(), ?, ?
            )
        ';

		return $this->query_exec($sql, array($path, $user_id));
	}


    /**
     * delete an file record
     *
     * @param $uuid: int
     * @return boolean
     */
    function deleteFile($uuid) {
        $sql = '
            DELETE FROM
                files
            WHERE
                uuid = ?
        ';

        return $this->query_exec($sql, array($uuid));
    }


    /**
     * get files by data id
     *
     * @param $data_id
     * @return file object array
     */
    function getFilesByDataId($data_id) {
        $sql = '
            SELECT
                *
            FROM
                files
            WHERE
                data_id = ?
            ORDER BY
                uuid DESC
        ';

        return $this->query_result($sql, array($data_id));
    }
}