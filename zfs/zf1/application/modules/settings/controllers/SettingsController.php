<?php
class Settings_SettingsController extends Zend_Controller_Action

{



    public $Request = array();



    public $ModelObj = null;

	public $formObj  = NULL;



    public function init()

    { 

		try{	

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new settings_Model_Settings();

			$this->formObj = new settings_Form_Settings();

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->view->Request = $this->Request;

			$this->_helper->layout->setLayout('main');

	  }catch(Exception $e){

	    echo $e->getMessage();die;

	  }

    }



    public function indexAction()

    {

        // action body

    }

	/**

	 * General Setting Action 

	 * Function : action to set Language and Delevery Address

	 * View Post Code generalsetting page

	 **/

	public function generalsettingAction(){

		 $this->view->AllCountry = $this->ModelObj->getCountryList();

   		 $this->view->CountryDetail = $this->ModelObj->countryDetail();

	}

	/**

	* Function : paketshopAction()

	* Action to display packetshops

	* Date of creation 30/03/2015

	**/ 

	public function packetshopAction(){

		$this->view->paketshops = $this->ModelObj->packetshop($this->Request);

	}

	/**

	* Function : addpaketshopAction()

	* Action to add packetshops

	* Date of creation 20/10/2016

	**/ 

	public function addpacketshopAction(){

			 global $objSession; 

		 try{

		   $this->formObj->addPacketShopForm();

			if($this->_request->isPost()){

				 if($this->formObj->isValid($this->_request->getParams())){

				   $data = $this->_request->getParams();

				   $this->ModelObj->addpacketshop($data);

				   $objSession->successMsg = "New Packet Shop Added Successfully !!";

				   $this->_redirect($this->_request->getControllerName().'/packetshop');

				  }else{

						$this->formObj->populate($this->_request->getParams());
				  } 

			 }

		   }catch (Exception $e) {

			 $objSession->errorMsg = "Something is wrong !!";

		   }

		   $this->view->addPacketShop =  $this->formObj;

	  }	

	  		/**

		* Function : editpaketshopAction()

		* Action to edit packetshops

		* Date of creation 26/10/2016

		**/ 

	  	 public function editpacketshopAction(){

			global $objSession; 

			 try{

			   $this->Request['id'] = Zend_Encript_Encription:: decode($this->Request['id']);

               $this->formObj->addPacketShopForm();

			   $this->formObj->addPacketShopForm()->addpacketshop->setLabel('Update Packet Shop');

                if(isset($this->Request['id']) && $this->Request['id'] != '' && !$this->_request->isPost()){

					  $record = $this->ModelObj->packetshop($this->Request);

					  $record = $this->ModelObj->daysInJson($record[0]);

					  $this->formObj->populate($record);

				 }elseif($this->_request->isPost()){

				     if($this->formObj->isValid($this->Request)){

					   $this->ModelObj->updatepacketshop($this->Request);

					   $objSession->successMsg = "Packet Shop Updated Successfully !!";

					   $this->_redirect($this->_request->getControllerName().'/packetshop');

					  }else{

							$this->formObj->populate($this->Request);

					  } 

				 }

			   }catch (Zend_Exception $e) {

				 $objSession->errorMsg = "Something is wrong !!";

			   }

			   $this->view->editPacketShop =  $this->formObj;

		 }



		public function forwardersAction()

		{

		   $this->view->records = $this->ModelObj->ListForwarders();

		}

	 /**

	 * Function : forwardercountryAction()

	 * Action to displaylist of forwardercountry

	 * Date of creation 07/12/2016

	 **/  

	 public function forwardercountryAction()

		{ $this->_helper->layout->setLayout('popup');

	  $forwarder_id = Zend_Encript_Encription:: decode($this->Request['id']);

	  if($forwarder_id > 0 ){

			   $this->view->records = $this->ModelObj->forwarderCountry($forwarder_id);

		}

		}

	  /**

	  * country forwarder Service 

	  * Function : countryserviceAction()

	  * View the Applicaton Forwarders country service view

	  **/

	public function countryserviceAction(){

		  $this->_helper->layout->setLayout('popup');

		  global $objSession;

		  $forwarder_id = Zend_Encript_Encription:: decode($this->Request['token1']);

		  if($this->_request->isPost() && !empty($this->Request['submit'])){

			$response =  $this->ModelObj->UpdateForwarderService();

			if($response){

			   $objSession->successMsg = "Country Detail Updated successfully!!";

			   echo '<script type="text/javascript">parent.window.location.reload();

				parent.jQuery.fancybox.close();</script>';

				exit(); 

			}else{

			   $objSession->errorMsg = "Something is wrong !!";

			}

		  }

		  $this->view->ForwarderCountry = $this->ModelObj->getForwarderCountry($forwarder_id);

		  $this->view->ForwarderName = $this->ModelObj->ForwarderName($forwarder_id);

		  $this->view->CountryDetail = $this->ModelObj->getCountryList();

   }

 

	 /**

	  * close fancybox and redirect

	  * Function : closeredirectAction()

	  * 19/12/2016

	  **/

	 public function closeredirectAction(){

	  echo '<script type="text/javascript">

	  window.top.location.href = "'.BASE_URL.'/Settings/countryservice?token1='.$this->Request['token1'].'&token2='.$this->Request['token2'].'";

	  parent.jQuery.fancybox.close();</script>';

			  exit(); 

	  

	 }

 

	 /**

	  * ADD COUNTRY FOR FORWARDER 

	  * Function : addservicecountryAction()

	  * 19/12/2016

	  **/

	 public function addservicecountryAction(){

		 global $objSession; 

	  $this->_helper->layout->setLayout('popup');

	  $this->Request['token1'] = Zend_Encript_Encription:: decode($this->Request['token1']);

		 if(isset($this->Request['country_id'])){

	  $this->Request['country_id'] = Zend_Encript_Encription:: decode($this->Request['country_id']);

	  }

	  if($this->_request->isPost() && isset($this->Request['submit']) &&!empty($this->Request['submit'])){

		$response =  $this->ModelObj->forwarderaddcountry($this->Request);

		if($response){

		 $objSession->successMsg = "Country added successfully!!";

		}else{

		   $objSession->errorMsg = "Country is aready added !!";

		}

		echo '<script type="text/javascript">parent.window.location.reload();

			parent.jQuery.fancybox.close();</script>';

			exit();  

	  }

	  $ForwarderName =  $this->ModelObj->getForwarderList($this->Request['token1']);

	  $this->view->ForwarderName = $ForwarderName[0]['forwarder_name'];

	  $this->view->allcountry = $this->ModelObj->getCountryList();

	 }



    public function languagesAction()

    {

         $this->view->records = $this->ModelObj->ListLanguage();

    }



    public function servicesAction()

    {

        $this->view->records = $this->ModelObj->ListServices();

    }

	public function printsettingAction(){

		$this->formObj->printSettingForm();

		global $objSession; 

    	 try{

			if($this->getRequest()->isPost()){

			 if($this->formObj->isValid($this->Request)){

			  $result =  $this->ModelObj->UpdatePrintOption($this->_request->getParams('label_position'));

			   $objSession->successMsg = "Print Setting Option Updated successfully!!";

			   $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());

			 }else{

			  $this->formObj->populate($this->Request);

			 }

		  }

		 }catch (Exception $e) {

		  $objSession->errorMsg = "Something is wrong !!";

		}

     $this->view->PrintSettingForm = $this->formObj;

	}

	public function weightscallersAction(){

	    global $objSession;

	    $this->formObj->addWeightScalForm();

		if($this->_request->isPost() && !empty($this->Request['submit'])){

		 if($this->formObj->isValid($this->Request)){

		  $this->ModelObj->AddWeightScalerDetail($this->Request);

		  $objSession->successMsg = "Weight Scaler Added Successfully !!";

		  $this->_redirect($this->_request->getControllerName().'/weightscallers');

		 }else{

		  $this->formObj->populate($this->Request);

		 }

		}

		if(isset($this->Request['mode']) && $this->Request['mode']=='delete' && $this->Request['id']>0){

		 $result = $this->ModelObj->deletescaler($this->Request);

		  if($result){$objSession->successMsg = "Weight Scaler Deleted Successfully !!";

			echo '<script type="text/javascript">window.location.reload();</script>';

			 exit();

		   }

		}

	   $this->view->scallers = $this->ModelObj->getWeightScallers($this->Request);

	   $this->view->scallerForm = $this->formObj;

	}

	/**

	* Call Email Notification Page 

	* Function : generalsettingsAction()

	* View the Email Notification Settings page

	**/

	public function emailnotificationAction(){

     global $objSession; 

			try{

		  $this->formObj->emailnotification($this->Request);

		  if($this->_request->isPost() && !empty($this->Request['emailtemplae'])){

			$response = $this->ModelObj->UpdateEmailTemplate($this->Request);

			$objSession->successMsg = $response;

			//$this->_redirect($this->_request->getControllerName().'/emailnotification');

		  }

		  if(isset($this->Request['notification_id']) && $this->Request['notification_id']!=''){

		  $template = $this->ModelObj->fetchemailtemplate($this->Request);

		  $this->Request['subject'] = $template['subject'];

		  $this->view->Template = $template;

		  }

		  $this->formObj->populate($this->Request);

		  $this->view->Notification = $this->formObj;  

			}catch(Exception $e) { 

		   $objSession->errorMsg = "Something is wrong !!";

			}

     }

	public function vehiclesettingAction(){

	   $this->view->vehicledetail = $this->ModelObj->Vehiclelistdetail();

	}

	 /**

	  * Function : addportAction()

	  * This function is for add port in country

	  * Return reload parent window

	  * Date of creation 11/11/2016

	  **/ 

	 public function addeditportAction(){

	  try{

	  global $objSession;

		 $this->_helper->layout->setLayout('popup');

	  $this->formObj->addPortSettingForm();

	  if($this->Request['mode'] == 'add'){

	  if($this->_request->isPost() && !empty($this->Request['submit'])){

		 if($this->formObj->isValid($this->Request)){

		  $this->ModelObj->AddPort($this->Request);

		  $objSession->successMsg = "Port Added Successfully !!";

		  echo '<script type="text/javascript">parent.window.location.reload();

			  parent.jQuery.fancybox.close();</script>';

			  exit(); 

		 }

		}

			$this->Request['country_id']  = (!isset($this->Request['country_id']))? $this->Request['id']:$this->Request['country_id'];

			$this->formObj->populate($this->Request);

	   }elseif($this->Request['mode'] == 'edit'){

		$this->formObj->addPortSettingForm()->submit->setLabel('Update');

		if($this->_request->isPost() && !empty($this->Request['submit'])){

		 if($this->formObj->isValid($this->Request)){

		  $this->ModelObj->UpdatePort();

		  $this->_redirect($this->_request->getControllerName().'/portlist?id='.$this->Request['country_id']);

		  

		 }else{

		 }

		}

		$port =  $this->ModelObj->portlist($this->Request);

	    $this->formObj->populate($port[0]);

	   }	

	   $this->view->AddPortForm = $this->formObj;

	  }catch(Exception $e) {

	   $objSession->errorMsg = "Something is wrong !!";

	  }

  }

  	/**

     * Action to add new country detail

     * Function : addcountryAction()

     * There are popup layout to add new country detail

     **/

	public function addcountryAction()

    { 	

		$this->_helper->layout->setLayout('popup');

		$this->formObj->addCountryForm();

		 try {

				if($this->getRequest()->isPost()){

				if($this->formObj->isValid($this->Request)){

					$result = $this->ModelObj->addcountrydetail($this->Request);

					 global $objSession; 

					 $objSession->successMsg = "Country Added successfully!!";

							echo '<script type="text/javascript">parent.window.location.reload();

								parent.jQuery.fancybox.close();</script>';

								exit();	

				}else{

					

					$this->formObj->populate($this->Request);

				}

			}

		}catch (Exception $e) {

				 global $objSession; 

				 $objSession->errorMsg = "Something is wrong !!";

		}

		$this->view->AddCountryForm =  $this->formObj; 	

    }

	/**

	* Action to edit country detail

	* Function : editcountryAction()

	* There are popup layout to edit country detail

	**/

   public function editcountryAction()

    { 

    try{

      global $objSession; 

      $this->_helper->layout->setLayout('popup');

      $this->formObj->addCountryForm();

      $this->formObj->addCountryForm()->submit->setLabel('Update');

      if(isset($this->Request['id']) && $this->_request->isPost()){

       if($this->formObj->isValid($this->Request)){

        $result = $this->ModelObj->upadtecountrydetail($this->Request);

        $objSession->successMsg = "Country Detail Updated successfully!!";

         echo '<script type="text/javascript">parent.window.location.reload();

          parent.jQuery.fancybox.close();</script>';

          exit(); 

       }else{

       

        $this->formObj->populate($this->Request);

       }

     }else{

       $result = $this->ModelObj->getCountryDetail($this->Request['id']);

      $result['postcode_length'] = ($result['postcode_length'] == 0) ? '' : $result['postcode_length'];

      //echo "<pre>";print_r($result); die;

       $this->formObj->populate($result);

     }

     }catch (Exception $e) {

     $objSession->errorMsg = "Something is wrong !!";

    } 

    $this->view->EditCountryForm =  $this->formObj;

    }

	

	/**

	* Function : addeditvehiclesettingAction()

	* Action to add edit vehicle detail

	* Date of creation 10/11/2016

	**/  

  public function addeditvehiclesettingAction(){ 

   $this->_helper->layout->setLayout('popup');

   global $objSession;

   $this->formObj->addEditVehicleSettingForm();

   if($this->Request['mode'] == 'add' && !isset($this->Request['id'])){

      $this->view->title = 'Add Vehicle Setting';

      if($this->_request->isPost() && !empty($this->Request['submit'])){

     if($this->formObj->isValid($this->Request)){

      $this->ModelObj->AddVehicleSetting($this->Request);

      $objSession->successMsg = "Vehicle Setting Added Successfully !!";

      echo '<script type="text/javascript">parent.window.location.reload();

          parent.jQuery.fancybox.close();</script>';

          exit(); 

     }else{

      $this->formObj->populate($this->Request);

     }

    }

      

   }elseif($this->Request['mode'] == 'edit' && isset($this->Request['id'])){

    $this->view->title = 'Edit Vehicle Setting';

    $this->formObj->addEditVehicleSettingForm()->submit->setLabel('Update');

    if($this->_request->isPost() && !empty($this->Request['submit'])){

     if($this->formObj->isValid($this->Request)){

      $this->ModelObj->EditVehicleSetting($this->Request);

      $objSession->successMsg = "Vehicle Setting Updated Successfully !!";

      echo '<script type="text/javascript">parent.window.location.reload();

          parent.jQuery.fancybox.close();</script>';

          exit(); 

     }else{

      $this->formObj->populate($this->Request);

     }

    }else{

     $fatchRowData = $this->ModelObj->Vehiclelistdetail($this->Request['id']);

     $this->formObj->populate($fatchRowData[0]);

    }

   }

   $this->view->addeditvehicle = $this->formObj;

  }

  	/**

	* Function : portlistAction()

	* Action to displaylist of port based on country

	* Date of creation 25/11/2016

	**/  

		public function portlistAction(){

		  $this->_helper->layout->setLayout('popup');

			 $this->view->portdata = $this->ModelObj->portlist($this->Request);

		}

		

	/**

	* Function : shopdaysAction()

	* Action to displaylist of shopdays based on country

	* Date of creation 30/11/2016

	**/  

		public function shopdaysAction(){

		  $this->_helper->layout->setLayout('popup');

		  $this->Request['id'] = Zend_Encript_Encription:: decode($this->Request['id']);

			 $record = $this->ModelObj->packetshop($this->Request);

			 $this->view->shopdays = $record[0]['workinghours'];

		}

	/**

	* Function : blockiplistAction()

	* Action to displaylist of blockiplist

	* Date of creation 30/11/2016

	**/  

  	public function blockiplistAction(){

	

		if($this->_request->isPost() && !empty($this->Request['submit'])){

		  $this->ModelObj->addBlockIp($this->Request);

		  $this->_redirect($this->_request->getControllerName().'/blockiplist');

		 

		}

	   $this->view->blockipList = $this->ModelObj->getBlockIp();

	   $this->view->blockipForm = $this->formObj;

	}

	 /**

  * call All Services List Page 

  * Function : addeditlanguageAction()

  * View for add, edit language

  * Date : 23/12/2016

 **/

 

 public function addeditlanguageAction(){

  $this->_helper->layout->setLayout('popup');

  global $objSession;

  $this->formObj->languageForm(); 

   if($this->Request['mode'] == 'add'){

     $this->view->title = 'Add New Language';

     if($this->_request->isPost() && !empty($this->Request['submit'])){

    if($this->formObj->isValid($this->Request)){

     $this->ModelObj->addLanguage();

     $objSession->successMsg = "Language Added Successfully !!";

     echo '<script type="text/javascript">parent.window.location.reload();

      parent.jQuery.fancybox.close();</script>';

      exit(); 

    }else{

     $this->formObj->populate($this->Request);

    }

   }

     

  }elseif($this->Request['mode'] == 'edit' && isset($this->Request['id'])){

   $this->view->title = 'Update Language';

   $this->formObj->languageForm()->submit->setLabel('Update Language');

   if($this->_request->isPost() && !empty($this->Request['submit'])){

    if($this->formObj->isValid($this->Request)){

     $this->ModelObj->updateLanguage();

     $objSession->successMsg = "Service Updated Successfully !!";

     echo '<script type="text/javascript">parent.window.location.reload();

      parent.jQuery.fancybox.close();</script>';

      exit(); 

    }else{

     $this->formObj->populate($this->Request);

    }

   }else{

    $fatchRowData = $this->ModelObj->ListLanguage();

    $this->formObj->populate($fatchRowData[0]);

   }

     }

     

     $this->view->addeditlanguage = $this->formObj;

   }

 public function addeditserviceAction(){

	  $this->_helper->layout->setLayout('popup');

	  global $objSession;

	  $this->formObj->serviceForm(); 

	   if($this->Request['mode'] == 'addMain' || $this->Request['mode'] == 'addsub'){

		 $this->view->title = 'Create New Service';

		 if($this->_request->isPost() && !empty($this->Request['submit'])){

		if($this->formObj->isValid($this->Request)){

		 $this->ModelObj->addService();

		 $objSession->successMsg = "Service Added Successfully !!";

		 echo '<script type="text/javascript">parent.window.location.reload();

		  parent.jQuery.fancybox.close();</script>';

		  exit(); 

		}else{

		 $this->formObj->populate($this->Request);

		}

	   }

		 

	  }elseif(($this->Request['mode'] == 'editMain' || $this->Request['mode'] == 'editsub') && isset($this->Request['id'])){

	   $this->view->title = 'Update Service';

	   $this->formObj->serviceForm()->submit->setLabel('Update Service');

	   if($this->_request->isPost() && !empty($this->Request['submit'])){

		if($this->formObj->isValid($this->Request)){

		 $this->ModelObj->updateService();

		 $objSession->successMsg = "Service Updated Successfully !!";

		 echo '<script type="text/javascript">parent.window.location.reload();

		  parent.jQuery.fancybox.close();</script>';

		  exit(); 

		}else{

		 $this->formObj->populate($this->Request);

		}

	   }else{

		$fatchRowData = $this->ModelObj->getAllServices('','',Zend_Encript_Encription:: decode($this->Request['id']));

		$this->formObj->populate($fatchRowData[0]);

	   }

     }

     

     $this->view->addeditservice = $this->formObj;

   }

  /**

  * call additional Services List Page 

  * Function : additionalserviceAction()

  * View the All the additional service List page

  * Date : 22/12/2016

 **/

  public function additionalserviceAction()

    { 

        $this->view->records = $this->ModelObj->getAllServices(Zend_Encript_Encription:: decode($this->Request['id']));

    }

  /**

  * Set transit deatail for all services 

  * Function : transitdetailAction()

  * Date : 03/01/2017

     **/

 public function transitdetailAction(){

  $this->_helper->layout->setLayout('popup');

  global $objSession;

     if(isset($this->Request['id']) && $this->Request['id'] !=''){

   if($this->_request->isPost() && !empty($this->Request['submit'])){

     $result = $this->ModelObj->UpdateLocalInfoTransitTime();

     if($result){

    $objSession->successMsg = "Transit Info Updated Successfully !!";

     }else{

       $objSession->successMsg = "Something is wrong !!";

     }

     echo '<script type="text/javascript">parent.window.location.reload();

      parent.jQuery.fancybox.close();</script>';

      exit(); 

   }else{

    $this->view->transitData = $this->ModelObj->getTransitDetail();

    $this->view->allcountry = $this->ModelObj->getCountryList();

   }

     }

    }



	/**

	 * setting for country  

	 * Function : countrysettingAction()

	 * Date : 14/01/2017

     **/

	public function countrysettingAction(){

		$this->_helper->layout->setLayout('popup');

		global $objSession;

		$this->formObj->countrysettingForm(); 

	   if(isset($this->Request['country_id']) && $this->Request['country_id'] !=''){

			if($this->_request->isPost() && !empty($this->Request['submit'])){

			  $result = $this->ModelObj->addeditcountrysetting();

			  if($result){

				$objSession->successMsg = "Country Setting Upadated Successfully !!";

			  }else{

			    $objSession->errorMsg = "Something is wrong !!";

			  }

			  echo '<script type="text/javascript">parent.window.location.reload();

				  parent.jQuery.fancybox.close();</script>';

				  exit(); 

			}else{

			     $populateData = $this->ModelObj->getCountrySettingDetail(Zend_Encript_Encription:: decode($this->Request['country_id']));

				 $this->formObj->populate($populateData);

			}

		   }

		$this->view->countrysetting = $this->formObj;

    }  

	

	/**

	 * setting for Edit Forwarder  

	 * Function : editforwarderAction()

	 * Date : 06/03/2017

     **/

	public function editforwarderAction(){

		global $objSession; 

	   if(isset($this->Request['forwarder_id']) && $this->Request['forwarder_id'] !=''){

	        $this->Request['forwarder_id'] = Zend_Encript_Encription:: decode($this->Request['forwarder_id']);

			$this->formObj->forwarder_id = $this->Request['forwarder_id'];

			$this->formObj->forwarder_namevalue = $this->ModelObj->ForwarderName($this->Request['forwarder_id']);

			$this->formObj->editForwarderForm();

			if($this->_request->isPost() && !empty($this->Request['submit'])){

			  if(isset($this->Request['last_tracking']) && ($this->Request['last_tracking'] > $this->Request['tracking_start'] && $this->Request['last_tracking'] < $this->Request['tracking_end'])   )
			  {

				  $result = $this->ModelObj->updateForwarderDetail();

				  if($result){

					$objSession->successMsg = "Forwarder Upadated Successfully !!";

				  }else{

				    $objSession->errorMsg = "Something is wrong !!";

				  }

			  	$this->_redirect($this->_request->getControllerName().'/forwarders');
				  
			  }else {

				 $objSession->errorMsg = "Last Tracking should lie somewhere between start and end tracking";

				 $this->formObj->populate($this->Request);

			  }	


			}else{

			     $populateData1 = $this->ModelObj->ForwarderName($this->Request['forwarder_id'],true);

				 $populateData2 = $this->ModelObj->ForwarderFTPDetail($this->Request['forwarder_id']);

				 $populateData = $populateData1+$populateData2;

				 $this->formObj = $this->ModelObj->RemoveEmptyFields($populateData,$this->formObj);
				 // echo "<pre>";print_r($this->formObj); die;

				 $this->formObj->populate($populateData);

			}

			  $this->view->editForwarder = $this->formObj;

		   }

    } 	

	

	/**

	 * setting for Chage Language  

	 * Function : changelanguageAction()

	 * Date : 23/03/2017

     **/

	public function changelanguageAction(){

		$this->formObj->changeLanguageForm();

		global $objSession; 

    	 try{

			if($this->getRequest()->isPost()){

			 if($this->formObj->isValid($this->Request)){

			  $result =  $this->ModelObj->UpdateLanguageOption();

			   $objSession->successMsg = "Language Setting Option Updated successfully!!";

			   $this->_redirect($this->_request->getControllerName().'/'.$this->_request->getActionName());

			 }else{

			  $this->formObj->populate($this->Request);

			 }

		  }

		 }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

     $this->view->changeLanguageSettingForm = $this->formObj;

	}	

}















