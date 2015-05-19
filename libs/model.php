<?php
class Model
{
	public $db = null;	
	function __construct($db){
		try{
			$this->db = $db;
		}
		catch(PDOException $e){
			exit('데이터베이스 연결에 오류가 발생했습니다.');
		}
	}
	
	function __destruct(){
	}

	function query_result($sql, $array){
		$query = $this->db->prepare($sql);
		$query->execute($array);
		return $query->fetchAll();
	}
	
	function query_row($sql, $array){
		$query = $this->db->prepare($sql);
		$query->execute($array);
		return $query->fetch();
	}
	
	function query_exec($sql, $array) {
		$query = $this->db->prepare($sql);
		return $query->execute($array);
	}
	
	// ----------------------------------------------------------------------
	/**
	 * get last inserted id attr
	 * 
	 * @author Benimario
	 * @since 2015.02
	 * 
	 * @return int
	 */
	function insertId() {
		$sql = '
					SELECT
						LAST_INSERT_ID() as id
				';
				
		return $this->query_row($sql, null)->id;
	}
}