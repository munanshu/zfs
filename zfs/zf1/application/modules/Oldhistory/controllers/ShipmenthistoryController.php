<?php
/**

 * Controll the Account module

 * @Auth : SJM softech Pvt. Ltd.

 * @Created Date: 03rd-October-2016

 * @Description : Controll the functionality related to Account

 **/

class Oldhistory_ShipmenthistoryController extends Zend_Controller_Action

{

	public $Request = array();

    public $ModelObj = null;

	public $formObj  = NULL;

	

    

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

    public function getshipmenthistoryAction()
    {   
        $this->view->yearlyInvoiceDetails =  $this->ModelObj->getShipmenthistoryDetails() ;

    }
   

	

}