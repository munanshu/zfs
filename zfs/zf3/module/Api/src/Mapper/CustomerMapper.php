<?php


namespace Api\Mapper;

use Zend\Db\Adapter\AdapterInterface;


class CustomerMapper extends AbstractMapper implements MapperInterface 
{
	
	protected $mapper = 'tbl_customers';

	public function __construct( AdapterInterface $dbadapter)
	{
		parent::__construct($dbadapter,$this->mapper);
	}

	public function addCustomer($data='')
	{

		if(is_array($data)){
			$result = $this->insertQuery($this->mapper,$data);
			if(isset($result['error']))
				return array('error'=>true,'message'=>$result['message']);
			else return $result;
		}
		else return array('error'=>true,'message'=>'invalid data should be in array');
		
	}

	public function getCustomers($id='',$page=1,$limit=50)
	{
		try {
			
			 $page = !empty($page)? $page : 1 ;
			 $limit = !empty($limit)? $limit : 50 ;
			 $id = !empty($id)? $id : null ;
			$select = $this->ZendSql->select($this->mapper);

			if(!empty($id) && is_numeric($id)){
				$select->where( array("customer_id"=>$id));
				$stmt = $this->ZendSql->prepareStatementForSqlObject($select)->execute();
				$results = $stmt->current();

				if(!empty($results))
					return $results;
				else return array('error'=>true,'message'=>"Customer doesn't exists with id $id");

			}
			else {
				
				// $stmt = $this->ZendSql->prepareStatementForSqlObject($select)->execute();
				// $results = $this->getResults($stmt);
				$results = $this->getPaginatedResults($this->mapper,$this->adapter,$limit,'',$page); 
				
			}

			if($results)
				return $results;
			else return array('error'=>true,'message'=>'some internal error occurred while fetching customer');

		} catch (Exception $e) {
			return array('error'=>true,'message'=>$e->getMessage());
		}	
						
	}


}