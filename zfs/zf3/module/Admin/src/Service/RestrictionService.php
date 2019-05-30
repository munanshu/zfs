<?php

namespace Admin\Service;

use Admin\Mapper\UserMapper;
use Zend\Permissions\Acl\Acl as ZendAcl;

class RestrictionService extends ZendAcl
{
	protected $UserMapper;
	protected $Roles;
	protected $Resources;
	protected $RolePermission;

	public function __construct(UserMapper $UserMapper)
	{
		$this->UserMapper = $UserMapper;
	}
	
	public function initAcl()
	{
		$this->Roles = $this->UserMapper->fetchRoles();
		$this->Resources = $this->UserMapper->fetchResources();
		$this->RolePermission = $this->UserMapper->getRolePermissions();


		$this->_addAllRoles();
		$this->_addAllResources();
		$this->_addRoleResourcePerms();
		return $this;
	}

	public function _addAllRoles()
	{
		if(!empty($this->Roles)){
			foreach ($this->Roles as $key => $Role) {
				$this->addRole($Role['role_name']);
			}
		}
	}

	public function _addAllResources()
	{
		if(!empty($this->Resources)){
			foreach ($this->Resources as $key => $Resource) {
				$this->addResource($Resource['module_name']);
			}
		}
	}

	public function _addRoleResourcePerms()
	{
		if(!empty($this->RolePermission)){
			foreach ($this->RolePermission as $key => $RolePermission) {
				$this->allow($RolePermission['role_name'],$RolePermission['module_name'],$RolePermission['perm_name']);
			}
		}
	}



}


