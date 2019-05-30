<?php


namespace Admin\Service;

use Admin\Mapper\UserMapper;

class UserService 
{
	private $UserMapper;

	public function __construct(UserMapper $UserMapper)
	{
		$this->UserMapper = $UserMapper;
	}

	public function fetchAllUsers()
	{
		return $this->UserMapper->fetchAll();
	}

	public function getUserRole($userID,$name=false,$id=false)
	{
		$UserRole = $this->UserMapper->getUserRole($userID);
		if(!empty($UserRole)){
			$UserRole = $UserRole[0];	
			if($name)
				return $UserRole['role_name'];
			if($id)
				return $UserRole['role_id'];
			
		}
	}

}