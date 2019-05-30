<?php 


class CustomController extends Zend_Controller_Action
{

	public function init()
	{
		$this->mylibObj = new Zend_Mylib_Custom();
	}
	

	public function indexAction()
	{

		print_r($this->mylibObj->getData());
		 die;

	}		


}