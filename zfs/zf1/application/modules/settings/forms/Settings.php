<?php
class settings_Form_Settings extends Application_Form_CommonDecorator
{

    public $ModelObj;
	public $conmmonDecObj;
	public $StatusModelObj;
	public $forwarder_id;
	public $forwarder_namevalue;	
    public function init()
    {
        $this->ModelObj = new settings_Model_Settings();
		$this->StatusModelObj = new settings_Model_Statuscode();
		//$this = new settings_Form_CommonDecorator();
		
    }
	public function addCountryForm()
    { 
		
        $this->addElement('text', 'country_name', array(
            'label'      => 'Country Name:',
			'class'  => 'inputfield',
            'required'   => true
        ));
		$this->addElement($this->createElement('select', 'continent_id')
											->setLabel('Continent Name: ')
											->setAttrib("class","inputfield")
											//->addMultiOptions(array('0'=>'Select Continent'))
											//->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getContinentList(),array('continent_id','continent_name')))
											->addMultiOptions(array(''=>'Select Continent'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getContinentList(),array('continent_id','continent_name')))
											);
		$this->addElement('text', 'cncode', array(
            'label'      => '2 DIGIT CNCODE:',
			'class'  => 'inputfield',
			'maxlength' => '2',
			'minlength' => '2'
        ));
		
		$this->addElement('text', 'cncode3', array(
            'label'      => '3 DIGIT CNCODE:',
			'class'  => 'inputfield',
			'maxlength' => '3',
			'minlength' => '3'
        ));
		
		$this->addElement('text', 'iso_code', array(
            'label'      => '3 Digit ISO Numeric CODE:',
			'class'  => 'inputfield',
			'maxlength' => '3',
			'minlength' => '3'
        ));
		
 		$this->addElement('text', 'area_code', array(
            'label'      => 'Dialing Number:',
			'class'  => 'inputfield',
			'maxlength' => '4',
			'minlength' => '3'
			/*'validators' => array(
                array(
                    'validator'=>'between',
                    'options'=>array(
                        'min'=>8,
                        'max'=>10,
                        'messages'=>array(
                            Zend_Validate_Between::NOT_BETWEEN => 'This is for %min% to %max% years old.'
                        )
                    )
                ),
            ),*/
        ));
		$this->addElement('text', 'postcode_length', array(
            'label'      => 'Post Code Length:',
			'class'  => 'inputfield',
			'maxlength' => '2',
        ));
		  $this->addElement('text', 'no_of_cn23', array(
            'label'      => 'No. Of CN23 :',
		   'class'  => 'inputfield',
				));
		  $this->addElement('textarea', 'local_info_service', array(
					'label'      => 'Local Info :',
		   'class'  => 'inputfield',
		   'ROWS' =>'2',
				));
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;
    }
	
	public function printSettingForm()
    {     
		$this->addElement('radio', 'label_position', array(
            'label'      => 'Label Print Option:',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
            'required'   => true,
			'multiOptions'=>array(
                'a1' => 'A4-1Position',
				'a4' => 'A4-4Position',
				'a6' => 'A6-Format'
            ),
			'value' => $this->ModelObj->Useconfig['label_position'],
        ));
		 $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Update',
			'class'  => 'btn btn-danger btn-block',
        ));
		 return $this;
    }
     /**
     * Function : addPacketShopForm()
	 * Params : NULL
     * Setting PostCode Length of country
     * */
    public function addPacketShopForm(){
		$this->setName('addpacketshop');
		$this->setAttrib('class', 'inputbox');
        $this->setAction('');
		$this->setAttrib('id', 'addpacketshop');
        $this->setMethod('post');
		$this->setDecorators(
			$this->form12Dec
		 );
        $CountryList = $this->createElement('select', 'country_id')
											->setLabel('Country: ')
											->setAttrib("class","inputfield")
											->addMultiOptions(array(''=>'Select Country'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getCountryList(),array('country_id','country_name')))
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		
        $shopname = $this->createElement('text', 'shope_name')
											->setLabel('Shop Name :')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$city = $this->createElement('text', 'city')
											->setLabel('City :')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$postalcode = $this->createElement('text', 'postal_code')
											->setLabel('Postal Code:')
											->setAttrib("class","inputfield")
											->setAttrib("onblur","getLatlong(this.value)")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$street = $this->createElement('text', 'street')
											->setLabel('Street:')
											->setAttrib ( 'placeholder', 'Street' )
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$streetno = $this->createElement('text', 'streetno')
											->setLabel('Sreet No :')
											->setAttrib("class","inputfield")
											->setAttrib ( 'placeholder', 'Street No' )
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$address = $this->createElement('text', 'address')
											->setLabel('Address :')
											->setAttrib ( 'placeholder', 'Address' )
											->setAttrib("class","inputfield")
											->setDecorators($this->element3Dec);
		$clearfix = $this->createElement('text','clearfix',$this->clearfix)->setAttrib('readonly', 'readonly');

      	 $days = 		 $this->createElement('hidden', 'days')
											->setLabel('Weekdays -:')
											->setAttrib("class","inputfield")
											->setDecorators($this->element12Dec);
	    $this->addElements(array($CountryList,$shopname,$city,$postalcode,$street,$streetno,$address,$clearfix,$days));
				$days =array('0'=>'Sunday','1'=>'Monday','2'=>'Tuesday','3'=>'Wednesday','4'=>'Thursday','5'=>'Friday','6'=>'Saturday');
		for($i=0;$i<count($days);$i++){
			$this->addElements(array($this->createElement('hidden', 'weekday_'.$days[$i])
											->setLabel($days[$i].':')
											->setAttrib("class","inputfield")
											->setAttrib ( 'placeholder', 'Start Time' )
											->setValue($days[$i])
											->setDecorators($this->element2Dec),
			$this->createElement('text', 'start_time_'.$days[$i])
											->setLabel(' ')
											->setAttrib("class","inputfield")
											->setAttrib ( 'placeholder', 'Start Time' )
											->setAttrib ( 'onmouseover', 'clickTimePicker(this.id)' )
											->setDecorators($this->element2Dec),
			$this->createElement('text', 'end_time_'.$days[$i])
											->setLabel(' ')
											->setAttrib ( 'placeholder', 'End Time' )
											->setAttrib ( 'onmouseover', 'clickTimePicker(this.id)' )
											->setAttrib("class","inputfield")
											->setDecorators($this->element2Dec)));
			
		 }
		 
		 $clearfix1 = $this->createElement('text','clearfix1',$this->clearfix)
		 									->setAttrib('readonly', 'readonly');
		 $this->addElement("hidden", "latitude");
		 $this->addElement("hidden", "longitude");
		 $Update = $this->createElement('submit', 'addpacketshop')
										->setAttrib("class","btn btn-danger btn-round")
										->setLabel('New Packet Shop')
										->setIgnore(true)
										->setDecorators($this->submit3Dec);
		 $this->addElements(array($clearfix1,$Update));
        	return $this;
    }
	
	 /**
     * Function : addPacketShopForm()
	 * Params : NULL
     * Setting PostCode Length of country
     * */
    public function addWeightScalForm(){
		$this->setName('addweightscallers');
		$this->setAttrib('class', 'inputbox');
        $this->setMethod('post');
		$this->setDecorators(
			$this->form12Dec
		 );
		
        $machineurl = $this->createElement('text', 'machine_url')
											->setLabel('IP Address')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$serialno = $this->createElement('text', 'serial_no')
											->setLabel(' Serial number')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);
		$machinename = $this->createElement('text', 'machine_name')
											->setLabel('Machine Name')
											->setAttrib("class","inputfield")
											->setAttrib("onblur","getLatlong(this.value)")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);

		 $submit = $this->createElement('submit', 'submit')
										->setAttrib("class","btn btn-danger btn-round")
										->setLabel('Add Scaler')
										->setIgnore(true)
										->setDecorators($this->submit3Dec);
		 $this->addElements(array($machineurl,$serialno,$machinename,$submit));
        	return $this;
    }

	/**
     * Function : addPacketShopForm()
	 * Params : NULL
     * Setting PostCode Length of country
     * */
    public function addEditVehicleSettingForm(){
		$this->setName('addeditvehiclesetting');
		$this->setAttrib('class', 'inputbox');
        $this->setMethod('post');
		$this->setDecorators(
			$this->form9Dec
		 );
		 
        $vehicle_name = $this->createElement('text', 'vehicle_name')
											->setLabel('Vehicle Name')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element6Dec);
		$vehicle_number = $this->createElement('text', 'vehicle_number')
											->setLabel('Liceplat No.')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element6Dec);
		$vehicle_distance = $this->createElement('text', 'vehicle_distance')
											->setLabel('Killometer on Techo')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element6Dec);

		 $submit = $this->createElement('submit', 'submit')
										->setAttrib("class","btn btn-danger btn-round")
										->setLabel('Add Vehicle')
										->setIgnore(true)
										->setDecorators($this->submit3Dec);
		 $this->addElements(array($vehicle_name,$vehicle_number,$vehicle_distance,$submit));
        	return $this;
    }


	/**
     * Function : addPortForm()
	 * Params : NULL
     * Setting PostCode Length of country
	 * Date : 11/11/2016
     * */
    public function addPortSettingForm(){
		 
        $CountryList = $this->createElement('select', 'country_id')
											->setLabel('Select Country')
											->setAttrib("class","inputfield")
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getCountryList(),array('country_id','country_name')))
											->setRequired(true)
											->addValidator('NotEmpty');
		$port_name = $this->createElement('text', 'port_name')
											->setLabel('Port Name')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty');
		$port_code = $this->createElement('text', 'port_code')
											->setLabel('Port Code')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty');

		 $submit = $this->createElement('submit', 'submit')
										->setAttrib("class","btn btn-danger btn-round")
										->setLabel('Add Port')
										->setIgnore(true);
		 $this->addElements(array($CountryList,$port_name,$port_code,$submit));
        	return $this;
    }
	
		 /**

     * Function : emailnotification()

     * Email Format Setting for Notification

     **/

        public function emailnotification($data) {
		   $customer=array();
		   $country=array();
				 $mailtext = (isset($data['notification_id']))?$this->ModelObj->getdynamicfield($data['notification_id']):array();
			  $notification_id=(isset($data['notification_id']))?$data['notification_id']:null;
		   $emailnotification = $this->createElement('select', 'notification_id')
				   ->setLabel('Notification Type: ')
				   ->setAttrib("class","inputfield")
				   ->setAttrib("onchange","this.form.submit()")
				   ->addMultiOptions(array(''=>'Select Notification'))
				   ->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->notificationtype(),array('notification_id','notification_name')))
				   ->setDecorators($this->element6Dec);
				   
							
		  if($notification_id!='1' && $notification_id!='4' && $this->ModelObj->Useconfig['level_id']!='5'){
			  if($this->ModelObj->Useconfig['level_id']=='1' && $notification_id=='3'){
			$customer = $this->createElement('select', 'user_id')
					 ->setLabel('Depot: ')
					 ->setAttrib("class","inputfield")
					 ->setAttrib("onchange","this.form.submit()")
					 ->addMultiOptions(array(''=>'Select Depot'))
					 ->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getDepotList(),array('user_id','username')))
					 ->setDecorators($this->element6Dec);
		   }
			
		   else{         
			$customer = $this->createElement('select', 'user_id')
					 ->setLabel('Customer: ')
					 ->setAttrib("class","inputfield")
					 ->setAttrib("onchange","this.form.submit()")
					 ->addMultiOptions(array(''=>'Select Customer'))
					 ->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getCustomerList(),array('user_id','username')))
					 ->setDecorators($this->element6Dec);
		   }
				   
		  }
					   
		  if($notification_id!='3'){
		   $country = $this->createElement('select', 'country_id')
					->setLabel('Country: ')
					->setAttrib("class","inputfield")
					->setAttrib("onchange","this.form.submit()")
					->addMultiOptions(array(''=>'Select Country'))
					->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getCountryList(),array('country_id','country_name')))
					->setDecorators($this->element6Dec);
		  }
		  
		  
		   
		   $dynamicfield = $this->createElement('select', 'dynamic_field')
				   ->setLabel('Dynamic Field: ')
				   ->setAttrib("class","inputfield")
				   ->setAttrib("onchange","AssignTofck()")
				   ->addMultiOptions($this->ModelObj->getdyanmicfielddata($mailtext))
				   ->setDecorators($this->element6Dec);
			   
				   
			$this->addElements(array($emailnotification,$customer,$country,$dynamicfield));
			$this->addElement('text', 'subject', array(
					'label'      => 'Subject:',
		   'class'  => 'inputfield',
		   'decorators' => $this->element12Dec
				));
		   
		   return $this;
		   
		  }
	/**
     * Function : blockipForm()
	 * Params : NULL
     * block ip form 
     * */
    public function blockipForm(){
		$this->setName('blockipform');
		$this->setAttrib('class', 'inputbox');
        $this->setMethod('post');
		$this->setDecorators(
			$this->form12Dec
		 );
		$blockip = $this->createElement('text', 'blockip')
											->setLabel('Block Ip')
											->setAttrib("class","input__field input__field--kaede")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setDecorators($this->element3Dec);

		 $submit = $this->createElement('submit', 'submit')
										->setAttrib("class","btn btn-danger btn-round")
										->setLabel('Add Block Ip')
										->setIgnore(true)
										->setDecorators($this->submit3Dec);
		 $this->addElements(array($blockip,$submit));
        	return $this;
    }
	 /**
     * Function : languageForm()
  * Params : NULL
     * Setting language form in setting
  * Date : 23/12/2016
     * */
    public function languageForm(){
		$this->setName('languageform');
		$this->setAttrib('class', 'inputbox');
		$this->setMethod('post');
		$this->setDecorators(
				$this->form12Dec
		);
	  $language_name = $this->createElement('text', 'language_name')
			   ->setLabel('Language Name')
			   ->setAttrib("class","inputfield")
			   ->setRequired(true)
			   ->addValidator('NotEmpty')
			   ->setDecorators($this->element6Dec);
	  $language_code = $this->createElement('text', 'language_code')
			   ->setLabel('Language Code')
			   ->setAttrib("class","inputfield")
			   ->setRequired(true)
			   ->addValidator('NotEmpty')
			   ->setDecorators($this->element6Dec);
	
	   $submit = $this->createElement('submit', 'submit')
			  ->setAttrib("class","btn btn-danger btn-round")
			  ->setLabel('Add Language')
			  ->setIgnore(true)
			  ->setDecorators($this->submit3Dec);
	   $this->addElements(array($language_name,$language_code,$submit));
			 return $this;
    }
	/**
     * Function : serviceForm()
  * Params : NULL
     * block ip form 
     * */
   public function serviceForm(){
	  $this->setName('serviceform');
	  $this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
	  $this->setDecorators(
	   $this->form12Dec
	   );
	  $service_name = $this->createElement('text', 'service_name')
			   ->setLabel('Service Name')
			   ->setAttrib("class","inputfield")
			   ->setRequired(true)
			   ->addValidator('NotEmpty')
			   ->setDecorators($this->element6Dec);
	  $internalcode = $this->createElement('text', 'internal_code')
			   ->setLabel('Internal Code')
			   ->setAttrib("class","inputfield")
			   ->setRequired(true)
			   ->addValidator('NotEmpty')
			   ->setDecorators($this->element6Dec);
	  $service_icon = $this->createElement('text', 'service_icon')
			   ->setLabel('Service Icon')
			   ->setAttrib("class","inputfield")
			   ->setDecorators($this->element6Dec);
	  $this->addElements(array($service_name,$internalcode,$service_icon));
	  $this->addElement('radio', 'signature', array(
				   'label'      => 'Signature',
				   'label_class' => 'control control--radio',
				   'decorators' => $this->radio12Dec,
				   'separator' =>'',
				   'class'  => 'printlabel',
				   'required'   => true,
				   'multiOptions'=>array(
				   '0'=>'No','1'=>'Yes'
				   ),
				   'value' => '0',
			   ));
	 $this->addElement('radio', 'tracking', array(
				   'label'      => 'Tracking',
				   'label_class' => 'control control--radio',
				   'decorators' => $this->radio12Dec,
				   'separator' =>'',
				   'class'  => 'printlabel',
				   'required'   => true,
				   'multiOptions'=>array(
				   '0'=>'No','1'=>'Yes'
				   ),
				   'value' => '0',
			   ));
			   
	  $description = $this->createElement('textarea', 'description')
			   ->setLabel('Description')
			   ->setAttrib('COLS', '25')
			   ->setAttrib('ROWS', '2')
			   ->setAttrib("class","inputfield")
			   ->setDecorators($this->element6Dec);
	   $submit = $this->createElement('submit', 'submit')
			  ->setAttrib("class","btn btn-danger btn-round")
			  ->setLabel('Add Service')
			  ->setIgnore(true)
			  ->setDecorators($this->submit3Dec);
   $this->addElements(array($description,$submit));
         return $this;
    }
	
	/**
     * Function : addeditstatuscodeForm()
	 * Params : NULL
     * Setting add edit status code form in setting
	 * Date : 23/12/2016
     * */
    public function addeditstatuscodeForm(){
			$this->setName('addeditstatuscode');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators(
			$this->form12Dec
			);
			$code_numeric = $this->createElement('text', 'code_numeric')
					->setLabel('Code Numeric :')
					->setAttrib("class","inputfield")
					->setAttrib('readonly', 'true')
					->setDecorators($this->element4Dec);
			$status_name = $this->createElement('textarea', 'status_name')
					->setLabel('Status Name English:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);
			$status_name_french = $this->createElement('textarea', 'status_name_french')
					->setLabel('Status Name French:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					// ->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);
			$status_name_german = $this->createElement('textarea', 'status_name_german')
					->setLabel('Status Name German:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					// ->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);
			$status_name_nederlands = $this->createElement('textarea', 'status_name_nederlands')
					->setLabel('Status Name Nederlands:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					// ->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);
			$status_name_pools = $this->createElement('textarea', 'status_name_pools')
					->setLabel('Status Name Pools:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					// ->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);

			$status_name_swedish = $this->createElement('textarea', 'status_name_swedish')
					->setLabel('Status Name Swedish:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					// ->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);

			$status_name_italy = $this->createElement('textarea', 'status_name_italy')
					->setLabel('Status Name Italy:')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					// ->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);		
															
			$display_reason = $this->createElement('textarea', 'display_reason')
					->setLabel('Display Reason :')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);

			$this->addElements(array($code_numeric,$status_name,$status_name,$status_name_french,$status_name_german,$status_name_nederlands,$status_name_pools,$status_name_swedish,$status_name_italy,$display_reason));

			$this->addElement('radio', 'error_type', array(
			'label'      => 'Error Type :',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
			'required'   => true,
			'multiOptions'=>array(
			'0'=>'ERROR','1'=>'DELIVERED','2'=>'INFORMATION'
			),
			'value' => '0',
			));
			$this->addElement('radio', 'status', array(
			'label'      => 'Status Mode :',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
			'required'   => true,
			'multiOptions'=>array(
			'0'=>'In-Active','1'=>'Active'
			),
			'value' => '1',
			));
			$this->addElement('radio', 'emailstatus', array(
			'label'      => 'Email Notification Option :',
			'label_class' => 'control control--radio',
			'decorators' => array('ViewHelper','Errors','Description',
			array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
			'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-12 col_paddingtop'))),
			'separator' =>'',
			'class'  => 'printlabel',
			'multiOptions'=>array(
			'1'=>'Used Existing Notification','0'=>'Create New Notification'
			)
			));
			$CountryList = $this->createElement('select', 'notification_id')
					->setLabel('Exist Notification List: ')
					->setAttrib("class","inputfield")
					->addMultiOptions(array(''=>'Select Notification'))
					->addMultiOptions(commonfunction::scalarToAssociative($this->StatusModelObj->getNotifications(array('templatecategory_id'=>'3')),array('ID','Name')))
					->setDecorators(array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
			array('HtmlTag',array('tag' => 'div','id'=>'exist_notification', 'class' => 'col-sm-4 col_paddingtop'))));
			$notification_name = $this->createElement('text', 'new_notification_name')
					->setLabel('New Notification Name :')
					->setAttrib("class","inputfield")
					->setDecorators(array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
			array('HtmlTag',array('tag' => 'div', 'id'=>'new_notification', 'class' => 'col-sm-4 col_paddingtop'))));
			$this->addElements(array($CountryList,$notification_name));
			$this->addElement('radio', 'notification_sta', array(
			'label'      => 'Notification Status :',
			'label_class' => 'control control--radio',
			'decorators' => array('ViewHelper','Errors','Description',
			array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
			'label',array('HtmlTag', array('tag' => 'div','id'=>'notification_status', 'class' => 'col-sm-12 col_paddingtop'))),
			'separator' =>'',
			'class'  => 'printlabel',
			'multiOptions'=>array(
			'1'=>'ON','0'=>'OFF'
			),
			'value' => '0',
			));
			$submit = $this->createElement('submit', 'submit')
				->setAttrib("class","btn btn-danger btn-round")
				->setLabel('Add Status')
				->setIgnore(true)
				->setDecorators($this->submit3Dec);
			$this->addElements(array($submit));
			return $this;
    }
	
	
	/**
     * Function : associateforwarderForm()
	 * Params : NULL
     * Setting associate forwarder
	 * Date : 28/12/2016
     * */
    public function associateforwarderForm($data){
			$this->setName('associateforwarder');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators(
			$this->form12Dec
			);
			$this->addElement ('multiCheckbox', 'error_id',array (
				'multiOptions' => commonfunction::scalarToAssociative($this->StatusModelObj->getForwarderStatusCodeList($data),array('error_id','associateForwarderDeatil')),
				'style' => 'margin:10px'
				)
				
			);

			$submit = $this->createElement('submit', 'submit')
				->setAttrib("class","btn btn-danger btn-round")
				->setLabel('Associate Status')
				->setIgnore(true)
				->setDecorators($this->submit3Dec);
			$this->addElements(array($submit));
			return $this;
    }
	/**
     * Function : transitdetailForm()
  * Params : NULL
     * Setting transit detail
  * Date : 03/01/2017
     * */
    public function transitdetailForm(){
		   $this->setName('transitdetail');
		   $this->setAttrib('class', 'inputbox');
		   $this->setMethod('post');
		   $this->setDecorators(
		   $this->form12Dec
		   );
		   $country_info = $this->createElement('textarea', 'local_info_service')
			 ->setLabel('Country Local Info :')
			 ->setAttrib('ROWS', '3')
			 ->setAttrib('placeholder', 'Enter Local Info')
			 ->setAttrib("class","inputfield")
			 ->setDecorators($this->element12Dec);
		   $invoiceheader = new Zend_Form_Element_Note('header');
		   $invoiceheader->setvalue('Service Transit Time')
				->setDecorators($this->headerdecorator);
		   $this->addElements(array($country_info,$invoiceheader));
		   $services = $this->ModelObj->getAllServices();
		   foreach($services as $service){
		   
			if($service['parent_service']=='0'){
			 $mainservice = '';
			 }else{
				$parent = $this->ModelObj->getAllServices('0','',$service['parent_service']);
			 if(count($parent)>0){
			 $mainservice = $parent[0]['service_name'].'--';
			}
			 }
			 
			 $this->addElement('text', $service['service_id'], array(
			'label'      => $mainservice.$service['service_name'].' :',
			'class'  => 'inputfield',
			'placeholder' => 'Enter no. of Days ',
			'decorators' =>$this->element4Dec
			));
		   }
		   $submit = $this->createElement('submit', 'submit')
			->setAttrib("class","btn btn-danger btn-round")
			->setLabel('Save Datail')
			->setIgnore(true)
			->setDecorators($this->submit3Dec);
		   $this->addElements(array($submit));
		   return $this;
    }
		/**
     * Function : countrysettingForm()
	 * Params : NULL
     * Setting
	 * Date : 23/12/2016
     * */
    public function countrysettingForm(){
			$this->setName('countrysetting');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators(
			$this->form12Dec
			);
			$this->addElement('radio', 'postcode_validation', array(
			'label'      => 'Postcode Validation :',
			'label_class' => 'control control--radio',
			'decorators' => $this->decorator,
			'separator' =>'',
			'class'  => 'printlabel',
			'multiOptions'=>array(
			'1'=>'ON','0'=>'OFF'
			),
			'value' => '1',
			));
			$notification_name = $this->createElement('text', 'website_link')
					->setLabel('Website Link :')
					->setAttrib("class","inputfield")
					->setDecorators($this->decorator);
			$submit = $this->createElement('submit', 'submit')
				->setAttrib("class","btn btn-danger btn-round")
				->setLabel('Country Setting')
				->setIgnore(true)
				->setDecorators($this->submit3Dec);
			$this->addElements(array($notification_name,$submit));
			return $this;
    }
	public function editForwarderForm(){
		
        $this->setMethod('post');
   		$this->setName('editforwarder');
 	 	$this->setAttrib('class', 'inputbox');
		$forwarderHeader = new Zend_Form_Element_Note('detailheader');
		$forwarderHeader->setvalue('Forwarder Detail')
					  ->setDecorators($this->headerdecorator);	
		$forwarder_name = new Zend_Form_Element_Text('forwarder_name');
		$forwarder_name->setLabel("Forwarder Name:")
						->setAttrib('disable', 'disable')
						->setAttrib('style', 'font-weight:bold')
						->setDecorators($this->decorator);
		$tracking_start = new Zend_Form_Element_Text('tracking_start');
        $tracking_start->setLabel('Tracking Range Start:')
					  ->addFilter('StringTrim')
					  ->setAttrib('class', 'inputfield')
					  ->setDecorators($this->decorator);
		
		$tracking_end = new Zend_Form_Element_Text('tracking_end');
        $tracking_end->setLabel('Tracking Range End:')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->addValidator('NotEmpty')
				  ->setDecorators($this->decorator);

		$last_tracking = new Zend_Form_Element_Text('last_tracking');
        $last_tracking->setLabel('Last Tracking:')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->addValidator('NotEmpty')
				  ->setDecorators($this->decorator);
		
		$sender_address = new Zend_Form_Element_Textarea('sender_address');
        $sender_address->setLabel('Sender Address:')
				  ->addFilter('StripTags')
				  ->setAttrib('ROWS', '3')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$depot_address= new Zend_Form_Element_Textarea('depot_address');
        $depot_address->setLabel('Depot Address:')
				  ->addFilter('StripTags')
				  ->setAttrib('ROWS', '3')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);

		$return_address= new Zend_Form_Element_Textarea('return_address');
        $return_address->setLabel('Return Address:')
				  ->addFilter('StripTags')
				  ->setAttrib('ROWS', '3')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$errorFTPheader = new Zend_Form_Element_Note('errorFTPheader');
		$errorFTPheader->setvalue('Error Parcel FTP Information')
					  ->setDecorators($this->headerdecorator);
					  
		$errorHostAddressFTP = new Zend_Form_Element_Text('error_hostname');
        $errorHostAddressFTP->setLabel('FTP Host Address (Error Parcel):')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$errorUsernameFTP = new Zend_Form_Element_Text('error_username');
        $errorUsernameFTP->setLabel('FTP Username (Error Parcel):')
					  ->addFilter('StringTrim')
					  ->setAttrib('class', 'inputfield')
					  ->setDecorators($this->decorator);
		$errorPasswordFTP = new Zend_Form_Element_Text('error_password');
        $errorPasswordFTP->setLabel('FTP Password (Error Parcel) :')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$errorPortFTP = new Zend_Form_Element_Text('error_port');
        $errorPortFTP->setLabel('FTP Port (Error Parcel) :')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);

		$FTPheader = new Zend_Form_Element_Note('errorheader');
		$FTPheader->setvalue('EDI FTP Details')
					  ->setDecorators($this->headerdecorator);
					  
		$HostAddressFTP = new Zend_Form_Element_Text('host_name');
        $HostAddressFTP->setLabel('FTP Host Name:')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$UsernameFTP = new Zend_Form_Element_Text('host_username');
        $UsernameFTP->setLabel('FTP Host User Name:')
					  ->addFilter('StringTrim')
					  ->setAttrib('class', 'inputfield')
					  ->setDecorators($this->decorator);
		$PasswordFTP = new Zend_Form_Element_Text('host_password');
        $PasswordFTP->setLabel('FTP Host Password:')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$PortFTP = new Zend_Form_Element_Text('host_port');
        $PortFTP->setLabel('FTP Port Number:')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);

		$shippngMenifestInfoHeader = new Zend_Form_Element_Note('shippngMenifestInfoHeader');
		$shippngMenifestInfoHeader->setvalue('Shipping Manifest Information')
								  ->setDecorators($this->headerdecorator);
					  
		$manifest_customer_name = new Zend_Form_Element_Text('manifest_customer_name');
        $manifest_customer_name->setLabel('Contract Holder:')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$manifest_customer_number = new Zend_Form_Element_Text('manifest_customer_number');
        $manifest_customer_number->setLabel('Customer Number :')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		
		$emailIdForParcelWithErrorHeader = new Zend_Form_Element_Note('emailIdForParcelWithErrorHeader');
		$emailIdForParcelWithErrorHeader->setvalue($this->forwarder_namevalue.' Guy Email ID For Parcel with Error')
								  ->setDecorators($this->headerdecorator);
					  
		$forwarder_email = new Zend_Form_Element_Text('forwarder_email');
        $forwarder_email->setLabel('Email ID of '.$this->forwarder_namevalue.' Guy :')
				  ->addFilter('StringTrim')
				  ->setAttrib('class', 'inputfield')
                  ->setDecorators($this->decorator);
		$additionalheader = new Zend_Form_Element_Note('additionalheader');
		$additionalheader->setvalue('Additional Detail')
					  ->setDecorators($this->headerdecorator);  
		$this->addElements(array($forwarderHeader,$forwarder_name,$tracking_start,$tracking_end,$last_tracking,$sender_address,$depot_address,$return_address,
		$errorFTPheader,$errorHostAddressFTP,$errorUsernameFTP,$errorPasswordFTP,$errorPortFTP,$FTPheader,$HostAddressFTP,$UsernameFTP,$PasswordFTP,$PortFTP,
		$shippngMenifestInfoHeader,$manifest_customer_number,$manifest_customer_name,$emailIdForParcelWithErrorHeader,$forwarder_email,$additionalheader));
		if($this->forwarder_id=='1' ||$this->forwarder_id=='4' ||$this->forwarder_id=='5' ||$this->forwarder_id=='6' ||$this->forwarder_id=='20' ||$this->forwarder_id=='23' ||$this->forwarder_id=='26' ||$this->forwarder_id=='32' ||$this->forwarder_id=='54')
		{
		  $this->depot_number();
		}
		if($this->forwarder_id=='4' ||$this->forwarder_id=='5' ||$this->forwarder_id=='6' ||$this->forwarder_id=='7' ||$this->forwarder_id=='11' ||$this->forwarder_id=='14' ||$this->forwarder_id=='20' ||$this->forwarder_id=='24' ||$this->forwarder_id=='25'
		||$this->forwarder_id=='26'	||$this->forwarder_id=='34'	||$this->forwarder_id=='37'	||$this->forwarder_id=='49'	||$this->forwarder_id=='51'){
			$this->customer_number();
		}
		if($this->forwarder_id=='4' ||$this->forwarder_id=='14')
		{
		  $this->shipping_depot_no();		
		}
		if($this->forwarder_id=='1' ||$this->forwarder_id=='23' ||$this->forwarder_id=='26' ||$this->forwarder_id=='32' ||$this->forwarder_id=='54')
		{
		  $this->delis_user_id();		
		}
		if($this->forwarder_id=='1' ||$this->forwarder_id=='7' ||$this->forwarder_id=='14' ||$this->forwarder_id=='17' ||$this->forwarder_id=='23' ||$this->forwarder_id=='26' ||$this->forwarder_id=='32' ||$this->forwarder_id=='54')
		{
		  $this->version_number();		
		}
		if($this->forwarder_id=='4' ||$this->forwarder_id=='5' ||$this->forwarder_id=='6' ||$this->forwarder_id=='17')
		{
		  $this->SAP_number();		
		}
		if($this->forwarder_id=='14' ||$this->forwarder_id=='17')
		{
		  $this->class_of_service();		
		}
		if($this->forwarder_id=='4' ||$this->forwarder_id=='5' ||$this->forwarder_id=='6' ||$this->forwarder_id=='7' ||$this->forwarder_id=='11' ||$this->forwarder_id=='14' ||$this->forwarder_id=='24' ||$this->forwarder_id=='25'|| $this->forwarder_id=='30' ||$this->forwarder_id=='37'||$this->forwarder_id=='45'){
			$this->contract_number();
		}
		if($this->forwarder_id=='7' ||$this->forwarder_id=='14' ||$this->forwarder_id=='6' ||$this->forwarder_id=='17')
		{
		  $this->sub_contract_number();		
		}
		if($this->forwarder_id=='11' ||$this->forwarder_id=='14' ||$this->forwarder_id=='17' ||$this->forwarder_id=='20' ||$this->forwarder_id=='24' ||$this->forwarder_id=='25' ||$this->forwarder_id=='27' ||$this->forwarder_id=='37'){
			$this->service_type();
		}
		if($this->forwarder_id=='17' ||$this->forwarder_id=='24' ||$this->forwarder_id=='25')
		{
		  $this->service_indicator();		
		}
		if($this->forwarder_id=='17')
		{
		  $this->service_icon();		
		}
		if($this->forwarder_id=='1' ||$this->forwarder_id=='23' ||$this->forwarder_id=='26' ||$this->forwarder_id=='32' ||$this->forwarder_id=='54')
		{
		  $this->service_code_kp();
		  $this->service_code_np();
		}
		if($this->forwarder_id=='4' ||$this->forwarder_id=='5' ||$this->forwarder_id=='6' ||$this->forwarder_id=='41')
		{
		  $this->primary_port();
		  $this->primary_socket();		  
		}
		if($this->forwarder_id=='5' ||$this->forwarder_id=='41')
		{
		  $this->secondry_socket();
		  $this->secondry_port();			  
		}
		if($this->forwarder_id=='11' ||$this->forwarder_id=='14' ||$this->forwarder_id=='15' ||$this->forwarder_id=='17' ||$this->forwarder_id=='22' ||$this->forwarder_id=='24' ||$this->forwarder_id=='25' ||$this->forwarder_id=='27' ||$this->forwarder_id=='28'
		||$this->forwarder_id=='33'	||$this->forwarder_id=='37'	||$this->forwarder_id=='42'	||$this->forwarder_id=='43'	||$this->forwarder_id=='44'	||$this->forwarder_id=='46'	||$this->forwarder_id=='48'	||$this->forwarder_id=='49'	||$this->forwarder_id=='50'	||$this->forwarder_id=='51'	||$this->forwarder_id=='53'){
			$this->barcode_prefix();
		}
		$this->addElement($this->createElement('submit', 'submit')
				->setAttrib("class","btn btn-danger btn-round")
				->setLabel('Edit Forwarder')
				->setAttrib('style', 'margin-left:auto;margin-right:auto;display:block;')
				->setIgnore(true));
		return $this;		  			  
	}
	
	public function customer_number()
	{	if($this->forwarder_id=='4'){
			$customer_number = 'GLS Reference Number:';
		}else{
			$customer_number = 'Customer Number:';
		}
		$this->addElement('text', 'customer_number', array(
            'label'      => $customer_number,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}

	public function depot_number()
	{	if($this->forwarder_id=='20'){
			$depot_number = 'Injection HUB Number:';
		}else{
			$depot_number = 'Depot Number:';
		}
		$this->addElement('text', 'depot_number', array(
            'label'      => $depot_number,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}	

	public function shipping_depot_no()
	{	if($this->forwarder_id=='14'){
			$shipping_depot_no = 'Delivery Location Code [A230] :';
		}else{
			$shipping_depot_no = 'Shipping Depot No.:';
		}
		$this->addElement('text', 'shipping_depot_no', array(
            'label'      => $shipping_depot_no,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function delis_user_id()
	{	if($this->forwarder_id=='1'){
			$delis_user_id = 'Delis User Id:';
		}else{
			$delis_user_id = 'Delis User Id:';
		}
		$this->addElement('text', 'delis_user_id', array(
            'label'      => $delis_user_id,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function version_number()
	{	if($this->forwarder_id=='14'){
			$version_number = 'Version Number of Pre-Advice File [A020] :';
		}elseif($this->forwarder_id=='17'){
			$version_number = 'UPS URC Code:';
		}else{
			$version_number = 'Version Number:';
		}
		$this->addElement('text', 'version_number', array(
            'label'      => $version_number,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function SAP_number()
	{	if($this->forwarder_id=='17'){
			$SAP_number = 'UPS URC Date:';
		}else{
			$SAP_number = 'SAP Number:';
		}
		$this->addElement('text', 'SAP_number', array(
            'label'      => $SAP_number,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function class_of_service()
	{	if($this->forwarder_id=='14'){
			$class_of_service = 'Customer Code (Party Code):';
		}else{
			$class_of_service = 'Class Of Service:';
		}
		$this->addElement('text', 'class_of_service', array(
            'label'      => $class_of_service,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function contract_number()
	{	if($this->forwarder_id=='17'){
			$contract_number= 'UPS Account Number:';
		}elseif($this->forwarder_id=='14'){
			$sub_contract_number = 'Prisma Code [V040] :';
		}elseif($this->forwarder_id=='24' || $this->forwarder_id=='25'){
			$sub_contract_number = 'Base Number:';
		}elseif($this->forwarder_id=='30' || $this->forwarder_id=='45'){
			$sub_contract_number = 'Client Code :';
		}else{
			$contract_number = 'Contract Number:';
		}
		$this->addElement('text', 'contract_number', array(
            'label'      => $contract_number,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function sub_contract_number()
	{	if($this->forwarder_id=='7'){
			$sub_contract_number = 'Subtract Number:';
		}elseif($this->forwarder_id=='14'){
			$sub_contract_number = 'Franking Mark [V051]:';
		}elseif($this->forwarder_id=='17'){
			$sub_contract_number = 'UPS Sub Account Number:';
		}else{
			$sub_contract_number = 'Sub Contract Number:';
		}
		$this->addElement('text', 'sub_contract_number', array(
            'label'      => $sub_contract_number,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}

	public function service_type()
	{	if($this->forwarder_id=='14'){
			$service_type = 'Version Number of Pre-Advice File [A020] :';
		}elseif($this->forwarder_id=='27'){
			$service_type = 'Marchant:';
		}else{
			$service_type = 'Service Type:';
		}
		$this->addElement('text', 'service_type', array(
            'label'      => $service_type,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function service_indicator()
	{	if($this->forwarder_id=='24' || $this->forwarder_id=='25'){
			$service_indicator = 'Participation:';
		}else{
			$service_indicator = 'Service Indicator:';
		}
		$this->addElement('text', 'service_indicator', array(
            'label'      => $service_indicator,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function service_icon()
	{	if($this->forwarder_id=='1'){
			$service_icon = 'Service Icon:';
		}else{
			$service_icon = 'Service Icon:';
		}
		$this->addElement('text', 'service_icon', array(
            'label'      => $service_icon,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function service_code_kp()
	{	if($this->forwarder_id=='1'){
			$service_code_kp = 'Service Code Kp:';
		}else{
			$service_code_kp = 'Service Code Kp:';
		}
		$this->addElement('text', 'service_code_kp', array(
            'label'      => $service_code_kp,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function service_code_np()
	{	if($this->forwarder_id=='1'){
			$service_code_np = 'Service Code Np:';
		}else{
			$service_code_np = 'Service Code Np:';
		}
		$this->addElement('text', 'service_code_np', array(
            'label'      => $service_code_np,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}

	public function primary_socket()
	{	if($this->forwarder_id=='41'){
			$primary_socket = 'Live API Secret:';
		}else{
			$primary_socket = 'Primary Socket:';
		}
		$this->addElement('text', 'primary_socket', array(
            'label'      => $primary_socket,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function primary_port()
	{	if($this->forwarder_id=='41'){
			$primary_port = 'API ID:';
		}else{
			$primary_port = 'Primary Port:';
		}
		$this->addElement('text', 'primary_port', array(
            'label'      => $primary_port,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	public function secondry_socket()
	{	if($this->forwarder_id=='41'){
			$secondry_socket = 'Test API Secret:';
		}else{
			$secondry_socket = 'Secondry Socket:';
		}
		$this->addElement('text', 'secondry_socket', array(
            'label'      => $secondry_socket,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}

	public function secondry_port()
	{
		if($this->forwarder_id=='41')
		{
			$secondry_port = 'Secondry API ID:';
		}else{
			$secondry_port = 'Secondry Port:';
		}
		$this->addElement('text', 'secondry_port', array(
            'label'      => $secondry_port,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}

	 public function barcode_prefix()
	{
		if($this->forwarder_id=='14')
		{
			$barcode_prefix = 'Franking License:';
		}elseif($this->forwarder_id=='17')
		{
			$barcode_prefix = 'Service Level:';
		}elseif($this->forwarder_id=='24' || $this->forwarder_id=='25')
		{
			$barcode_prefix = 'Application Identifier:';
		}elseif($this->forwarder_id=='27')
		{
			$barcode_prefix = 'Origin:';
		}elseif($this->forwarder_id=='28' || $this->forwarder_id=='37')
		{
			$barcode_prefix = 'Parcel Number Prefix:';
		}else{
			$barcode_prefix = 'Barcode Prefix:';
		}
		$this->addElement('text', 'barcode_prefix', array(
            'label'      => $barcode_prefix,
			'class'  => 'inputfield',
			'decorators' => $this->decorator,
        ));
		return $this;		
	}
	
	/**
     * Function :  addeditForwarderErrorForm()
	 * Params : NULL
     * Setting add edit Forwarder Error form in setting
	 * Date : 10/03/2017
     * */
    public function addeditForwarderErrorForm(){
			$this->setName('addeditForwarderError');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators(
			$this->form12Dec
			);
			$ForwarderList =  $this->createElement('select', 'country_id')
								->setLabel('Select Forwarder')
								->setAttrib("class","inputfield")
								->addMultiOptions(array(''=>'Select Forwarder'))
								->addMultiOptions(commonfunction::scalarToAssociative($this->StatusModelObj->getForwarderList(),array('forwarder_id','forwarder_name')))
								->setRequired(true)
								->addValidator('NotEmpty')
								->setDecorators($this->element6Dec);
			$error_alpha = $this->createElement('text', 'error_alpha')
					->setLabel('Status Alpha Code:')
					->setAttrib("class","inputfield")
					->setRequired(true)
					->addValidator('NotEmpty')
					->setDecorators($this->element6Dec);
			$error_numeric = $this->createElement('text', 'error_numeric')
					->setLabel('Status Numeric Code:')
					->setAttrib("class","inputfield")
					->setRequired(true)
					->addValidator('NotEmpty')
					->setDecorators($this->element6Dec);
			$statusDescription = $this->createElement('textarea', 'error_desc')
					->setLabel('Status Description:')
					->setAttrib('ROWS', '1')
					->setAttrib("class","inputfield")
					->setDecorators($this->element6Dec);
			$display_reason = $this->createElement('textarea', 'display_reason')
					->setLabel('Display Reason :')
					->setAttrib('ROWS', '1')
					->setAttrib("class","inputfield")
					->setDecorators($this->element6Dec);

			$this->addElements(array($ForwarderList,$error_alpha,$error_numeric,$statusDescription,$display_reason));

			$this->addElement('radio', 'error_type', array(
			'label'      => 'Error Type :',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
			'required'   => true,
			'multiOptions'=>array(
			'0'=>'ERROR','1'=>'DELIVERED','2'=>'INFORMATION'
			),
			'value' => '0',
			));
			$this->addElement('radio', 'error_status', array(
			'label'      => 'Status Mode :',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
			'required'   => true,
			'multiOptions'=>array(
			'0'=>'In-Active','1'=>'Active'
			),
			'value' => '1',
			));
			$masterList =  $this->createElement('select', 'master_id')
								->setLabel('Select Master Status :')
								->setAttrib("class","inputfield")
								->addMultiOptions(array(''=>'--Master Status--'))
								->addMultiOptions(commonfunction::scalarToAssociative($this->StatusModelObj->getmastererror(),array('master_id','status_name')))
								->setDecorators($this->element6Dec);
			$submit = $this->createElement('submit', 'submit')
				->setAttrib("class","btn btn-danger btn-round")
				->setLabel('Add Status')
				->setIgnore(true)
				->setDecorators($this->submit3Dec);
			$this->addElements(array($masterList,$submit));
			return $this;
    }
	public function changeLanguageForm()
    { 
        $this->addElement($this->createElement('select', 'language_id')
											->setLabel('Select Language')
											->setAttrib("class","inputfield")
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->ListLanguage(),array('language_id','language_name')))
											->setRequired(true)
											->addValidator('NotEmpty')
											->setValue($this->ModelObj->Useconfig['language_id']));
		 $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Update',
			'class'  => 'btn btn-danger btn-block',
        ));
		 return $this;
    }


    public function AddSmsTextForm()
    {
    	$masterStatusList = $this->createElement('select', 'master_id')
    			->setLabel('Select Master Status')
				->setAttrib("class","inputfield")
				->setAttrib("onchange",'getSmsTexts(this.value)')
				->addMultiOptions(array(''=>'Select Master Status'))
				->addMultiOptions(commonfunction::scalarToAssociative($this->StatusModelObj->getStatusMasterList(),array('master_id','status_name')))
				->setRequired(true)
				->setDecorators($this->element4Dec)
				->addValidator('NotEmpty');
		$this->addElement($masterStatusList);

		$languages = $this->ModelObj->ListLanguage();	
		
		unset($this->element4Dec[3][1]['requiredSuffix']);

		foreach ($languages as $key => $value) {

			$ele = $this->createElement('textarea', $value['language_id'])
					->setLabel('Status Name '.$value['language_name'].':')
					->setAttrib('COLS', '20')
					->setAttrib('ROWS', '1')
					->setRequired(true)
					->addValidator('NotEmpty')
					->setAttrib("class","inputfield")
					->setDecorators($this->element4Dec);
			$this->addElement($ele);		
		}
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
			'class'  => 'btn btn-danger btn-round',
			'decorators' => $this->submit2Dec
        ));

		return $this;


		 
											

    }

}

