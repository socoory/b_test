<?php
if (!defined("__BAAS_API__")) exit;

Class Logdata_model extends Model {
	function __construct($db) {
		parent::__construct($db);
	}


    /**
     * logging api usage
     *
     * @param $api_name: string
     * @param null $action: string
     * @param null $attr: string
     * @return boolean
     */
    function log($api_name, $action=null, $attr=null) {
        $sql = '
            INSERT INTO
                logdata (
                    api_name,
                    action,
                    attr
                )
            VALUES (
                ?, ?, ?
            )
        ';

        return $this->query_exec($sql, array(
            $api_name,
            $action,
            $attr
        ));
    }
}