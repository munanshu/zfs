<?php

namespace Admin\Model;


class CommonDbMapper
{
	private $db;

	function __construct()
	{
		$this->db = "munanshu";
		return $this->db;
	}

}