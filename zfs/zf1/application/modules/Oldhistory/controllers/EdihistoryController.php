<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Oldhistory_EdihistoryController extends Zend_Controller_Action

{

	public $Request = array();

    public $ModelObj = null;

	public $formObj  = NULL;

	

   /**

	* Initialize action controller

	* Function : init()

	* Auto create,loads and call the default object of model ,form and layout

	**/

    public function init()

    { 

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Oldhistory_Model_Oldhistorymanager($this->Request);

			// $this->formObj = new Accounting_Form_Heads($this->Request);

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request = $this->Request;

			$this->_helper->layout->setLayout('main');

    }



    public function getoldedihistoryAction()

    {
    	$this->view->edihistory = $this->ModelObj->getoledihistory();
    	$this->view->forwarders =  $this->ModelObj->getForwarderList();
    	
    }

    
   public function downloadedifileAction(){
   	$file = ROOT_PATH.'/private/EDI/'.$this->Request['file_name'];
   		if(file_exists($file))
	     commonfunction::readFile($file);
	 	else $this->_redirect($this->_request->getControllerName() . '/getoldedihistory');
	}
    
	 

}