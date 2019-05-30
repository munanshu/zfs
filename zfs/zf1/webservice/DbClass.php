<?php
class DBClass{
    public $db = NULL;
	public $dbQuery = NULL;
	public $db_prefix = 'parcel_';
	public function __construct(){
	  $configs = parse_ini_file("../application/configs/db.ini");
	  $this->db = new PDO('mysql:host='.$configs['resources.db.params.hostname'].';dbname='.$configs['resources.db.params.dbname'].'',$configs['resources.db.params.username'], $configs['resources.db.params.password']); 
	}
	public function PDOQuery($sql){
	    try {
			  $this->dbQuery = $this->db->prepare($sql);
			  $this->dbQuery->execute();
		} catch (PDOException $e) {
			 print "Error!: " . $e->getMessage() . "/r/n";
	      }
		return $this->dbQuery;  
	}
	public function PDOFetchAll(){
	 $records = $this->dbQuery->fetchAll(PDO::FETCH_ASSOC);
	 return $records;
	}
	public function PDOFetch(){
	     $record = $this->dbQuery->fetch(PDO::FETCH_ASSOC);
		 return $record;
	}
	public function PDOClose(){
	   $this->db = NULL;
	}
	public function compress($string){
	   return base64_encode(gzcompress($string));
	}
	public function decompress($string){
	   return gzuncompress(base64_encode($string));
	}
}
?>