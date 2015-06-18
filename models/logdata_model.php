<?php
if (!defined("__BAAS_API__")) exit;

Class Logdata_model extends Model {
	function __construct($db) {
		parent::__construct($db);
	}


    /**
     * logging api usage
     *
     * @param $info: array(
     *                   api_name: string
     *                   action: string
     *                   attr: string
     *                   target_id: int
     *               )
     * @return boolean
     */
    function log($info) {
        $sql = '
            INSERT INTO
                logdata (
                    api_name,
                    action,
                    attr,
                    regdate,
                    target_id
                )
            VALUES (
                ?, ?, ?, NOW(), ?
            )
        ';

        return $this->query_exec($sql, array(
            $info['api_name'],
            $info['action'],
            $info['attr'],
            $info['target_id']
        ));
    }


    function getRecentCalledApi($limit) {
        $sql = '
            SELECT
                *
            FROM
                logdata
            ORDER BY
                regdate DESC
            LIMIT
                0, '.$limit.'
        ';

        return $this->query_result($sql, null);
    }


    /**
     * get recent {$limit} logs by table name
     *
     * @param $table_name
     * @param $limit
     * @return objecdt array
     */
    function getRecentLogsByApiName($api_name, $limit) {
        switch($api_name) {
            case 'user':
                $api = 'users';
                $columns = 'uuid as uid, username, email, profile_image, level, active';
                break;
            case 'file':
                $api = 'files';
                $columns = 'uuid as uid, path, user_id';
                break;
            case 'data':
                $api = 'data';
                $columns = 'uuid as uid, data, user_id';
                break;
        }

        $sql = '
            SELECT
                *
            FROM
                logdata as log
            LEFT JOIN
                (
                SELECT
                    '.$columns.'
                FROM '.$api.'
                ) as api
            ON target_id = api.uid
            WHERE
                api_name = ?
            ORDER BY
                log.uuid DESC
            LIMIT
                0, '.$limit.'
        ';

        return $this->query_result($sql, array($api_name));
    }


    function getApiCallFrequency($days) {
        $sql = '
            SELECT
                regdate, api_name, count(api_name) as count
            FROM
                (
                SELECT
                    DATE_FORMAT(regdate, \'%Y%m%d\') as regdate, api_name
                FROM
                    logdata
                WHERE
                    regdate > DATE_SUB(NOW(), INTERVAL '.$days.' DAY)
                ORDER BY
                    regdate DESC
                )
                as l
            GROUP BY
                regdate, api_name
            ORDER BY
                regdate DESC
        ';

        return $this->query_result($sql, null);
    }


    function getRecentApiUsage($days) {
        $sql = '
            SELECT
                api_name, COUNT(*) as count
            FROM
                logdata
            WHERE
                regdate > DATE_SUB(NOW(), INTERVAL '.$days.' DAY)
            GROUP BY
                api_name
            ORDER BY
                api_name DESC
        ';

        return $this->query_result($sql, null);
    }
}