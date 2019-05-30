<?php 


namespace Admin\Mapper;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;


class MediaMapper extends AbstractMapper
{
	protected $table = 'tbl_media';

	public function __construct(Adapter $adapter)
	{
		parent::__construct( $adapter );
	}

	public function addMedia(array $files,$media_code_id)
	{
		$error = false;
		try {
			
			foreach ($files as $key => $file) {

				$CreatedBy = $this->getCreatedBy();
				$FileInsData['user_id'] = $CreatedBy['created_by'];
				$FileInsData['code_id'] = $media_code_id;
				$FileInsData['media_type'] = filetype($file);
				$FileInsData['media_file_name'] = basename($file);
				$FileInsData['path_storage'] = $file;
				$Insdata = array_merge($FileInsData,$CreatedBy);

				$ret = $this->insert($Insdata);
				if(!$ret)
					$error = true;
			}
				if($error)
					return array('error'=>true,'message'=>'some internal error occurred');
				else return $this->getLastInsertValue();

		} catch (Exception $e) {
			return array('error'=>true,'message'=>$e->getMessage());
		}

	} 

	public function MediaCodeExist($code)
	{
		$select = new Select();
		$select->from('tbl_media_codes')->where("code_name='$code'"); 
		$data = $this->selectwith($select)->current();
		if(!empty($data))
			return $data['code_id'];
		else return false;
	}


}