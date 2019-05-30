<?php


namespace Api\Model;

use Zend\Db\TableGateway\AbstractTableGateway; 
use Zend\Db\Adapter\Adapter; 
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;

class UserTable extends AbstractTableGateway
{
	
	protected $table ='tbl_users';

	 public function __construct(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->initialize();
	}

	public function fetchAll($orderby='user_id',$sort='desc',$limit=20)
	{
	   try {
	   		
			$sql = $this->getSql();
			$select = $sql->select();
			$select->columns(array('username','email','user_id','is_Active','created_date'));
			$select->order($orderby." ".$sort);
			$data = $this->selectwith($select)->toArray();
			return array('data'=> $data);

	   	} catch (Exception $e) {
			return array('error'=> true,'message'=>$e->getMessage() );
	   	}	
	}

	public function fetchUser($userid='')
	{
		try {
			$sql = $this->getSql();
			$select = $sql->select()->columns(array('user_id','email'))->where("user_id=$userid");
			$data = $this->selectwith($select)->current();
			if(!empty($data))
				return array('user'=>$data);
			else return false;
		} catch (Exception $e) {
			return false;	
		}
	}

	public function saveToken($data)
	{
		try {
		
			$insert = new Insert('tbl_user_tokens');
			$insert->columns(array('user_id','token','type','created_on'));
			$insert->values($data);
			$sql = $this->getSql();
			$ret = $sql->prepareStatementForSqlObject($insert)->execute();

			if($ret)
				return $ret->getgeneratedValue();
			else return false;

		} catch (Exception $e) {
			 return false;			
		}

	}
	public function fetchTokenbyUser($refreshToken='',$userid)
	{
		try {

			$select = new Select('tbl_user_tokens');
			$select->where("user_id=$userid && token='$refreshToken' && type=2");
			$data = $this->selectwith($select)->current();
			return $data;
		} catch (Exception $e) {
			return false;
		}
	}

	public function deleteExistingTokens($userid='')
	{
		try {
			
			$deleteObj = new \Zend\Db\Sql\Delete;
			$deleteObj->from('tbl_user_tokens')->where("user_id=$userid && type=2");

			$ret = $this->getSql()->prepareStatementForSqlObject($deleteObj)->execute();
			if($ret)
				return true;
			else return false;

		} catch (Exception $e) {
			return false;			
		}

	}

}