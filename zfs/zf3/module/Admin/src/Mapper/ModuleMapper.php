<?php 


namespace Admin\Mapper;

use Zend\Db\Adapter\Adapter;



class ModuleMapper extends AbstractMapper
{
	protected $table = 'tbl_modules';

	public function __construct(Adapter $adapter)
	{
		parent::__construct( $adapter );
	}

	public function fetchAllModules()
	{
		try {
			
			$sql = $this->getSql();
			$select = $sql->select();
			$select->columns(array('id','parent_id','is_Active','module_name','route_name','controller','action'));
			$select->where('is_Active=1');
			$data = $this->selectwith($select)->toArray();
			return $data;
		} catch (Exception $e) {
			return false;	
		}

	}


	public function getCurrentResourceNamebyParams($Params)
	{
		if(isset($Params['controller']) && isset($Params['action']) && isset($Params['MatchedRoute'])  ) {

			try {

				$sql = $this->getSql();
				$select = $sql->select();
				$select->columns(array('module_name'))->where("controller ='{$Params['controller']}' and action ='{$Params['action']}' and route_name='{$Params['MatchedRoute']}'");
				$data = $this->selectwith($select)->current();
				return $data;

			} catch (Exception $e) {
				return false;
			}

		}
		else return false;

	}



}