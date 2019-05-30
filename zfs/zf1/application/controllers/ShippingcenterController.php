<?php

    /**

     * Controll the Translation module

     * @Auth : SJM softech Pvt. Ltd.

     * @Created Date: 29-Nov-2012

     * @Description : Controll the functionality related to Translation

     **/
//ini_set('display_erros','1');
    class ShippingcenterController extends Zend_Rest_Controller

    {
        public $ObjModel;
		public $response=NULL;
        public function init(){
            $this->ObjModel 	= new Application_Model_Shippingcenter();
			$this->_helper->layout->disableLayout();
			$this->ObjModel->setDataToIndex($this->_request->getParams());

        }

		
	 public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }
	public function getAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }
	public function headAction(){
	     $this->_helper->viewRenderer->setNoRender(true);
	}

    public function postAction()

    {  
		if($this->ObjModel->getData['mode'] == 'check_service'){
		   $this->response = $this->ObjModel->getCountryService();//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'Check_Price'){

				//print_r("price");die;

		   $this->response = $this->ObjModel->getPrice();//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'Change_service'){

				//print_r("price");die;

		   $this->response = $this->ObjModel->getAdditionalService($this->ObjModel->getData);//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'country_list'){

				//print_r("price");die;

		   $this->response = $this->ObjModel->getServiceCountryList($this->ObjModel->getData);//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'weight_range'){

				//print_r("price");die;

		   $this->response = $this->ObjModel->getWaightRange($this->ObjModel->getData);//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'Service_List'){

				//print_r("price");die;

		   $this->response = $this->ObjModel->getServiceList($this->ObjModel->getData);//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'Address'){

				//print_r("price");die;

		   $this->response = $this->ObjModel->getNetherlandsAddress($this->ObjModel->getData);//print_r($this->response);die;

		}elseif($this->ObjModel->getData['mode'] == 'AddSenderAddress'){

		   $this->response = $this->ObjModel->AddSenderaddress($_POST);//print_r($this->response);die;

		}

      

	  $this->getResponse ()->setHeader ( 'Content-Type', 'text/xml' );

	  print $this->_handleStruct($this->response);exit;

	  $this->_helper->viewRenderer->setNoRender(true);

    }



    public function putAction()

    {

        $this->response = $this->ObjModel->UpdatedServiceCountryList();

	    $this->getResponse ()->setHeader ( 'Content-Type', 'text/xml' );

	    print $this->_handleStruct($this->response);exit;

		$this->_helper->viewRenderer->setNoRender(true);

    }



    public function deleteAction()

    {

        $this->_helper->viewRenderer->setNoRender(true);

    }

	public function ResponseAction(){

	     //print_r($this->response);

	   $this->_helper->viewRenderer->setNoRender(true);

	}

	/**

  * Handle an array or object result

  *

  * @param array|object $struct Result Value

  * @return string XML Response

  */

 protected function _handleStruct($struct) {

 

  $dom = new DOMDocument ( '1.0', 'UTF-8' );

  $root = $dom->createElement ( "xml" );

  $method = $root;

   

 // $root->setAttribute ( 'generator', 'Yiyu Blog' );

  //$root->setAttribute ( 'version', '1.0' );

  $dom->appendChild ( $root );

   

  $this->_structValue ( $struct, $dom, $method );

   

  $struct = ( array ) $struct;

   //$status = $dom->createElement ( 'status', 'success' );

   //$method->appendChild ( $status );

   //$dom->save('simple_eng.xml');

   return $dom->saveXML ();

 }

  

 /**

  * Recursively iterate through a struct

  *

  * Recursively iterates through an associative array or object's properties

  * to build XML response.

  *

  * @param mixed $struct

  * @param DOMDocument $dom

  * @param DOMElement $parent

  * @return void

  */

 protected function _structValue($struct, DOMDocument $dom, DOMElement $parent) {

  $struct = ( array ) $struct;

   

  foreach ( $struct as $key => $value ) {

   if ($value === false) {

    $value = 0;

   } elseif ($value === true) {

    $value = 1;

   }

    

   if (ctype_digit ( ( string ) $key )) {

    $key = 'key_' . $key;

   }

    

   if (is_array ( $value ) || is_object ( $value )) {

    $element = $dom->createElement (str_replace(range(0,9),'',$key));

    $this->_structValue ( $value, $dom, $element );

   } else {

    $element = $dom->createElement (str_replace(range(0,9),'',$key));

    $element->appendChild ( $dom->createTextNode ( $value ) );

   }

    

   $parent->appendChild ( $element );

  }

 }

}

?>

