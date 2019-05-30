<?php


namespace Admin\Service;

use Admin\Mapper\MediaMapper;

class MediaService extends FileConstants
{
	protected $MediaMapper;
	protected $AllowedFormats = ['txt','docx','pdf','PDF','csv','png','jpg','jpeg','gif','php'];
	protected $AllowedSize = 40000;

	public function __construct(MediaMapper $MediaMapper)
	{
		$this->MediaMapper = $MediaMapper;
	}

	 

	public function uploadFiles($fieldname,$uploadArr,$code,$fileDir){

		$fileinfo = NULL;
		$error = false;
		$adapter = new \Zend\File\Transfer\Adapter\Http();

		$files = $adapter->getFileInfo();

		if(!isset($uploadArr[$fieldname]))
			return false;

		if(!isset($uploadArr[$fieldname][0])){
			$newuploadArr = $uploadArr;
			unset($uploadArr);
			$uploadArr[$fieldname][] = $newuploadArr[$fieldname];
		}

			foreach ($uploadArr[$fieldname] as $key => $file) {
				$time = time();
		 
				$fileName = $time."_".$file['name'];
				$filefullPath = $fileDir.DIRECTORY_SEPARATOR.$fileName;
				try {
				
					$adapter->addValidator('Extension', false , $this->AllowedFormats);
					$adapter->addValidator('FileSize',false,$this->AllowedSize);

					if($adapter->isValid()){

		                $adapter->setDestination($fileDir);
		                $adapter->addFilter('Rename', array('target' => $adapter->getDestination() . DIRECTORY_SEPARATOR . $fileName, 'overwrite' => true));
		                if (!$adapter->receive()) {
		                	$error = 1 ;
		                    $messages = $adapter->getMessages();
		                    break;
		                }

					}else {
		                $error = 1 ;
						$messages = $adapter->getMessages();
		                    break;

					}

				} catch (Exception $e) {
		            $error = 1 ;
					$messages =  $e->getMessage();
		            break;

				}
				$uploadedFiles[] = $filefullPath;

			}		
				
				if($error)				 
					return array('error'=>true,'message'=>$messages);
				else return array('success'=>true,'files'=>$uploadedFiles);

 

	}

	public function saveFiles($fieldname,$file,$code)
	{

		if($media_code_id = $this->MediaMapper->MediaCodeExist($code)){

			$uploadedResp = $this->uploadFiles($fieldname,$file,$code,self::Category_File_Dir);

			if($uploadedResp and !isset($uploadedResp['error'])){


				$AddedResp = $this->MediaMapper->addMedia($uploadedResp['files'],$media_code_id);

				 return $AddedResp;

			}else return $uploadedResp;

		}
		else return array('error'=>true,'message'=>"code $code doesn't exists");
			
	}

}