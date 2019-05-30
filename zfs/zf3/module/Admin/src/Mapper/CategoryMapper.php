<?php


namespace Admin\Mapper;

use Zend\Db\Adapter\Adapter;
use Zend\Custom\Dbvars;

class CategoryMapper extends AbstractMapper
{
	protected $table = 'tbl_categories';
	
	public function __construct(Adapter $adapter)
	{
		parent::__construct($adapter);
	}


	public function fetchAll()
	{
		try {
			
			$sql = $this->getSql();
			$select = $sql->select()->columns(array('category_id','category_image','parent_id','category_desc','category_name','is_Active','created_by','created_date' ));
			$select->join(Dbvars::MEDIA,'tbl_categories.category_image = tbl_media.media_id',array('path_storage'),$select::JOIN_LEFT);
			$select->join(array('US'=>Dbvars::USERS),'US.created_by = US.user_id',array('username'),$select::JOIN_LEFT);
			$data = $this->selectwith($select)->toArray();
			return $data;
		} catch (Exception $e) {
			return array('error'=>1,'message'=>$e->getMessage());
		}
	}

	public function addCategory(array $data)
	{
		try {
			
			$createdByData = $this->getCreatedBy();
			$InsertData = array_merge($data,$createdByData);
			
			$res = $this->insert($InsertData); 
			if($res)
				return array('error'=>true,'message'=>'some internal error occurred');
			else return $this->getLastInsertValue();

		} catch (Exception $e) {
			return array('error'=>true,'message'=>$e->getMessage());
		}	
			 
	}

}