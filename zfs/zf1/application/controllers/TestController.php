<?php
class TestController extends Zend_Controller_Action
{
	public $ModelObj;
    public function init()
    {
       $this->ModelObj = new Application_Model_Test();
    }

    public function indexAction()
    {
	   // $this->ModelObj->sendEmailforYesterday();
	   ob_clean();
	   ob_start();
       print_r('Here1');die;
	   ob_end_flush();
	   die;
    }
}

?>