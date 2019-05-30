<?php


namespace Api\Service;




class AbstractService 
{
	
	public $ServiceParams;
	
	public function setApiParams($params)
	{
		$this->ServiceParams = $params;
	}

	public function getApiParams()
	{
		return $this->ServiceParams;
	}
	 
}