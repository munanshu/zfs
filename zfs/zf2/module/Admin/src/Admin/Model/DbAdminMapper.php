<?php 

namespace Admin\Model;

class DbAdminMapper
{
	private $_dbadminmapper;

	function __construct(CommonDbMapper $cdm)
	{
		$this->_dbadminmapper = $cdm;
		return $this->_dbadminmapper;
	}
}