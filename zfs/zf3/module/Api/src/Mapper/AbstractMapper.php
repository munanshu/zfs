<?php 



namespace Api\Mapper;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet; 
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Stdlib\ArrayObject;

class AbstractMapper implements MapperInterface
{
	protected $adapter;	
	protected $ZendSql;	
	protected $paginationFactory;	
	
	public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->ZendSql = new Sql($this->adapter);
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


	public function getResults($result='')
	{
		if($result instanceof ResultInterface && $result->isQueryResult() ){
             $resultSet = new ResultSet();
             $results = $resultSet->initialize($result);
             return $results->toArray();
     	}
	}
	
	public function getPaginatedResults($table,$PageAdapter,$limit=null,$where=null,$currentPage=null)
	{

		$select = $this->ZendSql->select($table);
		if($where)	
			$select->where($where);

		$limit = $limit? ( ($limit>200)? 200 :  $limit ) : 50;
		$currentPage = $currentPage? $currentPage : 1;

		$PageAdapter = new DbSelect($select,$this->adapter, new ResultSet());
		$paginator = new Paginator($PageAdapter);
		$paginator->setItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($currentPage);

		$results = $paginator->getIterator();
		$results = \Zend\Stdlib\ArrayUtils::iteratorToArray($results);
		return $results;
	}




}