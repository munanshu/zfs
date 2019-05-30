<?php


namespace Api\Service;

use Api\Model\UserTable;
 
class UserService 
{
	protected $usertable;
	public function __construct(UserTable $usertable)
	{
		$this->usertable = $usertable;
	}

	public function getAllUsers()
	{
		return $this->usertable->fetchAll();
	}

	public function getUser($userid)
	{	
		return $this->usertable->fetchUser($userid);
	}

}
