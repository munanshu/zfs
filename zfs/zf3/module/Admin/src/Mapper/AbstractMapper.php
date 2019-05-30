<?php 


namespace Admin\Mapper;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet; 
use Zend\Session\Container;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Sql;

class  AbstractMapper extends AbstractTableGateway
{
	protected $mapper;
	protected $ZendSql;

	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->initialize();
		$this->ZendSql = new Sql($this->adapter);
	}

	public function getResults($result='')
	{
		if($result instanceof ResultInterface && $result->isQueryResult() ){
             $resultSet = new ResultSet();
             $results = $resultSet->initialize($result);
             return $results->toArray();
     	}
	}

	public function insertQuery($table='',Array $data=array())
	{
		
		if(empty($data)){
			return array('error'=>true,'message'=>'no data provided to be inserted');
		}

		if(empty($table)){
			return array('error'=>true,'message'=>'no table provided to be inserted in');
		}

		if(empty($columns = array_keys($data))){
			return array('error'=>true,'message'=>'no columns are provided to be inserted in table');
		}
		try {
			$insert = $this->ZendSql->insert($table);	
			$insert->columns($columns);
			$insert->values($data);
			$ret = $this->ZendSql->prepareStatementForSqlObject($insert)->execute();
			if($ret)
				return $this->adapter->getDriver()->getLastGeneratedValue();
			else array('error'=>true,'message'=>'some internal error occurred while creation on customer');
		} catch (Exception $e) {

			return array('error'=>true,'message'=>$e->getMessage());

		}	
	}
	
	public function getCreatedBy($userData='')
	{
		$session = new Container('CurrentUser');
		$user_id = $this->DeCryptIdentity($session->data['user_id'],'userIdentity');
		$createdDatetime = date('Y-m-d h:i:s');
		return array('created_date'=>$createdDatetime,'created_by'=>$user_id);
	}


	public function EncryptIdentity($identity,$key)
	{
		try {

			@$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
			$blockCipher->setKey($key);
			$result = $blockCipher->encrypt($identity);

			return $result;

		} catch (Exception $e) {
			return false;
		}

	}

	public function DeCryptIdentity($identity,$key)
	{
		try {

			@$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
			$blockCipher->setKey($key);
			$result = $blockCipher->decrypt($identity);

			return $result;

		} catch (Exception $e) {

			return false;
		}

	}



}