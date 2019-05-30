<?php 


namespace Admin\Mapper;

use Zend\Db\Adapter\Adapter;
use Zend\Custom\Dbvars;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;

class UserMapper extends AbstractMapper
{
	protected $table = Dbvars::USERS;

	public function __construct(Adapter $adapter)
	{
		parent::__construct( $adapter );
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


	public function fetchRoles()
	{
		$select = new Select();
		$select->from(Dbvars::ROLES)
		->where('is_Active=1');
		$data = $this->ZendSql->prepareStatementForSqlObject($select)->execute();
		$data = $this->getResults($data);
		return $data;
	}

	public function fetchPermissions()
	{
		$select = new Select();
		$select->from(Dbvars::PERMISSION);
		$data = $this->ZendSql->prepareStatementForSqlObject($select)->execute();
		$data = $this->getResults($data);
		return $data;
	}

	public function fetchResources()
	{
		$select = new Select();
		$select->from(Dbvars::MODULES);
		$data = $this->ZendSql->prepareStatementForSqlObject($select)->execute();
		$data = $this->getResults($data);
		return $data;
	}

	public function getRolePermissions($role=false)
	{
		$select = $this->ZendSql->select()->from(array('RL'=>Dbvars::ROLES))->columns(array('role_name'));
		$select->join(array('RP'=>Dbvars::ROLE_PERMISSIONS),'RL.role_id=RP.role_id',array(),$select::JOIN_LEFT);
		$select->join(array('PR'=>Dbvars::PERMISSION),'PR.perm_id=RP.permission_id',array('perm_name'),$select::JOIN_LEFT);
		$select->join(array('MD'=>Dbvars::MODULES),'MD.id=RP.module_id',array('module_name'),$select::JOIN_LEFT);
		$data = $this->ZendSql->prepareStatementForSqlObject($select)->execute();
		$data = $this->getResults($data);
		return $data;
	}

	public function getUserRole($userId)
	{
		$select = $this->ZendSql->select()
			->from(array('UR'=>Dbvars::USER_ROLES))
			->where("user_id=$userId");
		$select ->join(array('RL'=>Dbvars::ROLES),'UR.role_id = RL.role_id',array('role_name'),$select::JOIN_LEFT);
		$data = $this->ZendSql->prepareStatementForSqlObject($select)->execute();	
		$data = $this->getResults($data);
		return $data;
	}

}