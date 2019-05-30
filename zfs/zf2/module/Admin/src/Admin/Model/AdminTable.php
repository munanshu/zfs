<?php 


namespace Admin\Model;


use Zend\Db\TableGateway\TableGateway;

class AdminTable {

	protected $tablegateway;

	public function __construct( TableGateway  $tablegateway)
	{

		$this->tablegateway = $tablegateway;

	}


	public function fetchSingle($id)
	{

		$sql = $this->tablegateway->getSql();
		$select = $sql->select();
		$select->columns(array('id','username'));
		$select->where(array('id'=>$id));
		$statement = $sql->prepareStatementForSqlObject($select);
		$res = $statement->execute();
		$res = $res->getResource()->fetch(\PDO::FETCH_ASSOC);;
		return $res;
	}


}