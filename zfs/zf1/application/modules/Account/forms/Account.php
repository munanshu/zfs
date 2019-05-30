<?php



class Account_Form_Account extends Application_Form_CommonDecorator

{

	public $listCountry = array();

	public $listForwarders = array();

	public $listServices = array();

	public $listScheduledRoutings = array();

	public $AdminLevelId = '';

	public $LevelId = '';

	public $privilege = '';

	public $btwCharge = '';

	public $invoiceSeries = '';

	

	public $listDepot = '';

	public $languageList = '';

	public $CurrencyList = '';

	public $customerlist = '';

	public $listHubuser = '';

	public $mode = '';

	public $parentId = '';

	public $drivername = '';

	public $companyName = '';

	public $WebshopList = array();

	public $WmsCompanyList = array();

	public $WarehouseList = array();

	public $senderLogo ='';

	public $sessionValue = array();

	public $invoiceLogo="";

	public $adminLogo="";

	public $ModelObj="";

	

	//public $InvoiceSeries = ''

	 

	//public $decoratorObj = '';

    

	public function init() 

    {

		$this->ModelObj = new Account_Model_Depotnetworkmanager();
		// $this->addElementPrefixPath(
  //               'My',
  //               APPLICATION_PATH.'/forms/validate/',
  //               'validate'
  //       );
    }

	

	

	public function setLevelId(){

		$levelId = $this->CreateElement('hidden', 'level_id')

					->setValue($this->LevelId);

		$levelId->removeDecorator('label');

		$levelId->removeDecorator('HtmlTag');

		

		return $levelId;

	}

	

	public function setdepotList(){

		//echo"<pre>";print_r($this->listDepot);die;

		$DepotList = new Zend_Form_Element_Select('parent_id');

        $DepotList->setLabel('Select Depot')

				  ->addMultiOptions(array('' => '--Select Depot--'))

				  ->addMultiOptions($this->listDepot)	

				  ->setAttrib('title', 'Please Select Depot')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		return $DepotList;		  

	}

	

	public function showcountryList(){

		$CountyList = new Zend_Form_Element_Select('country_id');

        $CountyList->setLabel('Country')

				  ->addMultiOptions(array('' => '--Select Country--'))

				  ->addMultiOptions($this->listCountry)	

				  ->setAttrib('title', 'Please Select Country')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		return $CountyList;

	}

	public function showforwarderList(){

		$ForwarderList = new Zend_Form_Element_Select('forwarder_id');

        $ForwarderList->setLabel('Forwarder')

				  ->addMultiOptions(array('' => '--Select Forwarder--'))

				  ->addMultiOptions($this->listForwarders)	

				  ->setAttrib('title', 'Please Select Forwarder')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		return $ForwarderList;

	}


	public function addDepotRoutingForm($isadd=true)
	{	

		$this->addElement('hidden', 'dn_routing_id');
		$this->addElement('hidden', 'depot_id');
		
		$CountyList = $this->showcountryList();

		// $ForwarderList = $this->showforwarderList();
		$this->addElement($CountyList);
		// $this->addElement($ForwarderList);
		 

		$ServiceList = new Zend_Form_Element_Select('service_id');

        $ServiceList->setLabel('Service')

				  ->addMultiOptions(array('' => '--Select Service--'))

				  ->addMultiOptions($this->listServices)	

				  ->setAttrib('title', 'Please Select Service')

				  ->setAttrib('class', 'inputfield')

				  ->setAttrib('multiple', $isadd)

				  ->setRequired(true)

				  ->setIsArray($isadd)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		$this->addElement($ServiceList);
		  
		$this->addElement($this->createElement('text', 'postcode_start')
											->setLabel('PostCode Start: ')
											->setAttrib("id","postcode_start_1")
											->setAttrib("class","inputfield")
											->setIsArray(true)
											->setRequired(true)
											);
		$this->addElement($this->createElement('text', 'postcode_end')
											->setLabel('PostCode End: ')
											->setAttrib("id","postcode_end_1")
											->setAttrib("class","inputfield")
											->setIsArray(true)
											->setRequired(true)
											);

		$ScheduledRoutings = new Zend_Form_Element_Select('sc_routing_id');

        $ScheduledRoutings->setLabel('Scheduled Routings')

				  ->addMultiOptions(array('' => '--Scheduled Routings--'))

				  ->addMultiOptions($this->listScheduledRoutings)	

				  ->setAttrib('title', 'Please Select Scheduled Routing')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->setIsArray(true);



		$this->addElement($ScheduledRoutings);

		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;

	}
	

	public function showcurrencyList(){

		$CurrencyList = new Zend_Form_Element_Select('currency');

        $CurrencyList->setLabel('Currency')

					 ->addMultiOptions(array('' => '--Select Currency--'))

					 ->addMultiOptions($this->CurrencyList)	

					 ->setAttrib('title', 'Please Select Currency')

					 ->setAttrib('class', 'inputfield')

					 ->setDecorators($this->decorator);

		

		return $CurrencyList;

	}

	

	public function showlanguageList(){

	

		$Language = new Zend_Form_Element_Select('language_id');

        $Language->setLabel('Language')

				  ->addMultiOptions(array('' => '--Select Language--'))

				  ->addMultiOptions($this->languageList)	

				  ->setAttrib('title', 'Please Select Language')

				  ->setAttrib('class', 'inputfield')
				  ->setDecorators($this->decorator);

				  

		return $Language;	

	}

	

	public function showcustomerList(){

		

		$CustomerList = new Zend_Form_Element_Select('parent_id');

        $CustomerList->setLabel('Customer')

				  ->addMultiOptions(array('' => '--Select Customer--'))

				  ->addMultiOptions($this->customerlist)	

				  ->setAttrib('title', 'Please Select Customer')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		return $CustomerList;	

	}

	

	public function fieldusername(){

		$Username = new Zend_Form_Element_Text('username');

        $Username->setLabel('Username')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Username')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setAttrib('onchange',"userExist()")

				  ->setDecorators($this->decorator);		  

		

		return $Username;		 

	}

	

	public function fieldpassword(){

		$Password = new Zend_Form_Element_Password('password');

        $Password->setLabel('Password')

				  ->setDescription('.')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Password')

				  ->setAttrib('class', 'inputfield')

				  ->setAttrib('style', 'width:290px')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->passworddecorator);		  

		

		return $Password;	

	}

	

	public function fieldrepassword(){

		$Confirm = new Zend_Form_Element_Text('retypepass');

        $Confirm->setLabel('Confirm Password')

				  ->addFilter('StringTrim')

				  ->setAttrib('title', 'Confirm Password')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setAttrib('onchange',"confirmpassword()")

				  ->setDecorators($this->decorator);	

				  

		return $Confirm;		  

	}

	

	public function fieldfirstname(){

		$Firstname = new Zend_Form_Element_Text('first_name');

        $Firstname->setLabel('First Name')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill firstname')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		return $Firstname;

	}

	

	public function fieldmiddlename(){

		$MiddleName = new Zend_Form_Element_Text('middle_name');

        $MiddleName->setLabel('Middle Name')

				  ->addFilter('StringTrim')

				  ->setAttrib('class', 'inputfield')

				   ->setDecorators($this->decorator);

		

		return $MiddleName;		

	}

	

	public function fieldlastname(){

		$Lastname = new Zend_Form_Element_Text('last_name');

        $Lastname->setLabel('Last Name')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill LastName')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);		  		  		  	  

		

		return $Lastname;		 

	}

	

	public function fieldemail(){

		$Email = new Zend_Form_Element_Text('email');

        $Email->setLabel('Email Address')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Email')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->addValidator('EmailAddress')

				  ->setAttrib('onchange',"emailvalidation()")

				  ->setDecorators($this->decorator);

				

		return $Email;		

	}

	

	public function fieldinvoicemail(){

		$InvoiceEmail = new Zend_Form_Element_Text('invoice_email');

        $InvoiceEmail->setLabel('Invoice Email')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Invoice Email')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->addValidator('EmailAddress')

				  ->setAttrib('onchange',"emailinvoicevalidation()")

				  ->setDecorators($this->decorator);			   	

		

		return $InvoiceEmail;

	}

	

	public function fieldcompanyname(){

		$Company = new Zend_Form_Element_Text('company_name');

        $Company->setLabel('Company')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Company Name')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		return $Company;

	}

	



		public function fieldbtwnumber(){

		$BTW = new Zend_Form_Element_Text('btw_number');

        $BTW->setLabel('BTW')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill BTW')

				  ->setAttrib('class', 'inputfield')
          		  ->setDecorators($this->decorator);

				  

		return $BTW;

	}

	

	public function fieldbtwcharge(){

		$chargeBtw = new Zend_Form_Element_Radio('btw_status');

    	$chargeBtw->setLabel('Btw Charge')

					->addMultiOptions(array(

        				'1' => 'BTW Charge',

        				'0' => "Don't Charge BTW"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);  	  

			

		return $chargeBtw;

	}

	

	public function fieldshowprice(){

		$showprice = new Zend_Form_Element_Radio('show_price');

    	$showprice->setLabel('Show Price')

					->addMultiOptions(array(

        				'1' => 'On',

        				'0' => 'Off'

      				  ))

					->setSeparator(' ')  

					->setDecorators($this->decorator);  	  

		

		return $showprice;

	}

	

	public function fieldphone(){

		$PhoneNumber = new Zend_Form_Element_Text('phoneno');

        $PhoneNumber->setLabel('Phone Number')

				  ->addFilter('StringTrim')	

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator);

		

		return $PhoneNumber;	

	}

	

	public function fieldaddress1(){

		$AddressOne = new Zend_Form_Element_Text('address1');

        $AddressOne->setLabel("Address1")

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Address')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		return $AddressOne;		  

	}

	

	public function fieldaddress2(){

		$AddressTwo = new Zend_Form_Element_Text('address2');

        $AddressTwo->setLabel('Address2')

				  ->setAttrib('title', 'Please Fill Address 2')

				  ->setAttrib('class', 'inputfield')

                  ->setDecorators($this->decorator);		  			  		  		  

		

		return $AddressTwo;	

	}

	

	public function fieldpincode(){

		$ZipCode = new Zend_Form_Element_Text('postalcode');

        $ZipCode->setLabel('Zip Code')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill zipcode')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		return $ZipCode;	

	}

	

	public function fieldcity(){

		$City = new Zend_Form_Element_Text('city');

        $City->setLabel('City')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill city')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);		  

		

		return $City;

	}

	

	public function fieldheadercolor(){

		$headerColor = new Zend_Form_Element_Text('header_color');

		$headerColor->setRequired(true)

					->setDescription('required')

					->setLabel('Header Background Color')

					->setAttrib('title', 'Please Fill Header Color')

					->setAttrib('class', 'inputfield look izzyColor')

					->setAttrib('style', 'width:220px')

					->setDecorators($this->decorator);

		

		return $headerColor;

	}

	

	public function fieldtextcolor(){

		$TextColor = new Zend_Form_Element_Text('font_color');

		$TextColor->setRequired(true)

					->setDescription('required')

					->setLabel('Header Text Color')

					->setAttrib('title', 'Please Fill Header Text Color')

					->setAttrib('class', 'inputfield look izzyColor')

					->setAttrib('style', 'width:220px')

					->setDecorators($this->decorator);

				

		return $TextColor;		

	}

	

	public function fieldlabeloption(){

		$labelOption = new Zend_Form_Element_Select('label_position');

        $labelOption->setLabel('Label Options')

				  ->addMultiOptions(array('' => '--Select--'))

				  ->addMultiOptions(array('a1'=>'A4 - 1Position',

				  						  'a4'=>'A4 - 4Position',

										  'a6'=>'A6 - Position'))	

				  ->setAttrib('title', 'Please Select Label Position')

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator);

		

		return $labelOption;

	}

	

	public function fieldsubmit(){

		$submit = new Zend_Form_Element_Submit('submit');

        $submit->setLabel('Submit')

				->setAttrib('class', 'btn btn-danger btn-round clearfix')

				->setAttrib('style', 'margin-left:auto;margin-right:auto;display:block;');

				

		return $submit;		

	}

	

	public function fieldmode(){

		$Mode = $this->CreateElement('hidden', 'modevalue')

					->setValue($this->mode);

		$Mode->removeDecorator('label');

		$Mode->removeDecorator('HtmlTag');

		

		return $Mode;

	}

	

// end all form's common function...





	public function DepotForm(){

		

        $this->setMethod('post');

   		$this->setName('addeditdepot');

 	 	$this->setAttrib('class', 'inputbox');

		$this->setAttrib('enctype', 'multipart/form-data');

		$Mode = $this->fieldmode();

		

		$levelId = $this->setLevelId();

		

		$parent_id = $this->CreateElement('hidden', 'parent_id')

					->setValue($this->parentId);

		$parent_id->removeDecorator('label');

		$parent_id->removeDecorator('HtmlTag');

		

		$type = new Zend_Form_Element_Text('administrator');

		$type->setLabel("Administrator's type")

				->setValue('Depot')

				->setAttrib('disable', 'disable')

				->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

								

		$CountyList = $this->showcountryList();

				  

		$CurrencyList = $this->showcurrencyList();

					 		  

		$Username = $this->fieldusername();	  

				  

		$Password = $this->fieldpassword();

		

		/*$Password = new Zend_Form_Element_Password('password');

        $Password->setLabel('Password')

				  ->setDescription('Link')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Password')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->passworddecorator);*/		  

		

		//$Confirm = $this->fieldrepassword();	

				  

		$Firstname = $this->fieldfirstname();

		

		$MiddleName = $this->fieldmiddlename();

				  

		$Lastname = $this->fieldlastname();		  		  		  	  

				  

		$Email = $this->fieldemail();

				   

		$InvoiceEmail = $this->fieldinvoicemail();		   	

		

		$Company = $this->fieldcompanyname();

				  

		$BTW = $this->fieldbtwnumber();

				  

		$chargeBtw = $this->fieldbtwcharge();

		

		$showprice = $this->fieldshowprice();

		

		$PhoneNumber = $this->fieldphone();

				  

		$AddressOne = $this->fieldaddress1();

		

		$AddressTwo = $this->fieldaddress2();

		

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();		  

		

		//$adminLogo = $this->fieldadminlogo();

		

		$headerColor = $this->fieldheadercolor();

		

		$TextColor = $this->fieldtextcolor();

		

		//$InvoiceLogo = $this->fieldinvoicelogo();

		

		$labelOption = $this->fieldlabeloption();

		

		$Invoice = new Zend_Form_Element_Text('invoice');

		$Invoice->setLabel("Invoice Series")

				->setValue(date('Y').$this->invoiceSeries.'XXXXXX')

				->setAttrib('disable', 'disable')

				->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

				

		

		$InvoiceSeries = $this->CreateElement('hidden', 'invoice_series')

					  ->setValue(date('Y').$this->invoiceSeries.'XXXXXX');

		$InvoiceSeries->removeDecorator('label');

		$InvoiceSeries->removeDecorator('HtmlTag');

		

		$invoiceheader = new Zend_Form_Element_Note('header');

		$invoiceheader->setvalue('Invoice Bank Detail')

					->setDecorators($this->headerdecorator);				

		

		$bank_name = new Zend_Form_Element_Text('bank_name');

        $bank_name->setLabel('Bank Name')

					->setAttrib('class','inputfield')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);	

		$bank_account = new Zend_Form_Element_Text('bank_account');

        $bank_account->setLabel('IBAN/SEPA1')

					->setAttrib('class','inputfield')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);

		$bank2 = new Zend_Form_Element_Text('bank2');

        $bank2->setLabel('Bank2 ')

					->setAttrib('class','inputfield')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);

		$bank_bic = new Zend_Form_Element_Text('bank_bic');

        $bank_bic->setLabel('BIC1')

					->setAttrib('class','inputfield')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);	

		$bank_kvk = new Zend_Form_Element_Text('bank_kvk');

        $bank_kvk->setLabel('K.v.K : ')

					->setAttrib('class','inputfield')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);	

		$bank_btw = new Zend_Form_Element_Text('bank_btw');

        $bank_btw->setLabel('BTW')

					->setAttrib('class','inputfield')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);	

				//start invoice setting 

				   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		if($this->mode=='edit'){

			$Invoice = new Zend_Form_Element_Text('invoice_series');

			$Invoice->setLabel("Invoice Series")

					->setValue('')

					->setAttrib('disable', 'disable')

					->setAttrib('style', 'font-weight:bold')

					->setDecorators($this->decorator);

			

			$this->addElements(array($Mode,$levelId,$parent_id,$type,$CountyList,$CurrencyList,$Username,$Firstname, $MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$BTW,$PhoneNumber,$chargeBtw,$showprice,$AddressOne,$AddressTwo,$ZipCode,$City));

						$this->fieldadminlogo();

						$this->fieldinvoicelogo();

						$this->addElements(array($labelOption,$Invoice));

		}

		else{

			$this->addElements(array($levelId,$parent_id,$type,$CountyList,$CurrencyList,$Username,$Password,$Firstname, $MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$BTW,$PhoneNumber,$chargeBtw,$showprice,$AddressOne,$AddressTwo,$ZipCode,$City,$this->fieldadminlogo(),$this->fieldinvoicelogo(),$labelOption,$Invoice,$InvoiceSeries));

		}

		$this->addElements(array($invoiceheader,$bank_name,$bank_account,$bank2,$bank_bic,$bank_kvk,$bank_btw,$submit));

		return $this;		  			  

	}

	

	public function CustomerForm(){

		

        $this->setMethod('post');

   		$this->setName('addeditcustomer');

 	 	$this->setAttrib('class', 'inputbox');

		$this->setAttrib('enctype', 'multipart/form-data');		

		$Mode = $this->fieldmode();

		

		$levelId = $this->setLevelId();

		

		$CountyList = $this->showcountryList();

		

		$DepotList = $this->setdepotList();

				  

		$Language = $this->showlanguageList();

				  		  

		$Username = $this->fieldusername();		  

				  

		$Password = $this->fieldpassword();		  

				  

		$Confirm = $this->fieldrepassword();

				  

		$Firstname = $this->fieldfirstname();

		

		$MiddleName = $this->fieldmiddlename();

				  

		$Lastname = $this->fieldlastname();		  		  		  	  

				  

		$Email = $this->fieldemail();

				   

		$InvoiceEmail = $this->fieldinvoicemail();		   	

		

		$Company = $this->fieldcompanyname();

		

		$Customer = new Zend_Form_Element_Text('customer_number');

        $Customer->setLabel('Customer Number')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Customer Number')

				  ->setAttrib('class','inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		$glscustomerno = new Zend_Form_Element_Text('glscustomer_no');

        $glscustomerno->setLabel('GLS Customer Number')

				  ->setAttrib('class','inputfield')

                  ->setDecorators($this->decorator);		  

		

		$glsDepotNo = new Zend_Form_Element_Text('glsdepot_no');

        $glsDepotNo->setLabel('GLS Depot Number')

				  ->setAttrib('class','inputfield')

                  ->setDecorators($this->decorator);		  

		

		$glsEmailID = new Zend_Form_Element_Text('gls_emailid');

        $glsEmailID->setLabel('GLS EmailID')

				  ->setAttrib('class','inputfield')

                  ->setDecorators($this->decorator);		  

								  

		$BTW = $this->fieldbtwnumber();

				  

		$PhoneNumber = $this->fieldphone();

				  

		$AddressOne = $this->fieldaddress1();

				  

		$AddressTwo = $this->fieldaddress2();		  			  		  		  

		

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();	  

		

		$headerColor = $this->fieldheadercolor();

		

		$TextColor = $this->fieldtextcolor();

		   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		if($this->mode=='edit'){

			$this->addElements(array($Mode,$levelId,$CountyList,$DepotList,$Language,$Username,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$Customer,$glscustomerno,$glsDepotNo,$glsEmailID,$BTW,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City));	

									$this->fieldadminlogo();

		}

		else{

			$this->addElements(array($Mode,$levelId,$CountyList,$DepotList,$Language,$Username,$Password,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$Customer,$glscustomerno,$glsDepotNo,$glsEmailID,$BTW,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$this->fieldadminlogo()));

		}

		$this->addElements(array($submit));

		return $this;		  			  

	}

	

	public function OperatorForm(){

		

        $this->setMethod('post');

   		$this->setName('addeditoperator');

 	 	$this->setAttrib('class', 'inputbox');

		$this->setAttrib('enctype', 'multipart/form-data');			

		$Mode = $this->fieldmode();

		

		$levelId = $this->setLevelId();

		

		$type = new Zend_Form_Element_Text('administrator');

		$type->setLabel("Administrator's type")

				->setValue('Operator')

				->setAttrib('disable', 'disable')

				//->setAttrib('class', 'inputfield')

				->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

				

		$DepotList = $this->setdepotList();

		

		$CountyList = $this->showcountryList();

		$Language = $this->showlanguageList();

			  

		$Username = $this->fieldusername();		  

				  

		$Password = $this->fieldpassword();		  

				  

		$Confirm = $this->fieldrepassword();

				  

		$Firstname = $this->fieldfirstname();

		

		$MiddleName = $this->fieldmiddlename();

				  

		$Lastname = $this->fieldlastname();		  		  		  	  

				  

		$Email = $this->fieldemail();

				   

		$InvoiceEmail = $this->fieldinvoicemail();		   	

		

		$Company = $this->fieldcompanyname();

		

		$PhoneNumber = $this->fieldphone();

				  

		$AddressOne = $this->fieldaddress1();

				  

		$AddressTwo = $this->fieldaddress2();		  			  		  		  

		

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();		  

		

		$headerColor = $this->fieldheadercolor();

		

		$TextColor = $this->fieldtextcolor();

		

		$labelOption = $this->fieldlabeloption();

		

		/*$privilege = new Zc_Form_Element_Privilege('priv');

		$privilege->setLabel('Set privilege :')

				   ->setvalue($this->privilege)

				   ->setDecorators($this->defineDecorator(''));*/

		//$this->clear4fix;

				   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		if($this->mode=='edit'){

			$this->addElements(array($Mode,$levelId,$type,$DepotList,$CountyList,$Language,$Username,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$labelOption));

												$this->fieldadminlogo();

		}

		else{

			$this->addElements(array($Mode,$levelId,$type,$DepotList,$CountyList,$Language,$Username,$Password,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$labelOption,$this->fieldadminlogo()));

		}	

			$this->addElements(array($submit));

		return $this;		  			  

	}

	

	public function CustomerOperatorForm(){

		

        $this->setMethod('post');

   		$this->setName('addeditcustomeroperator');

 	 	$this->setAttrib('class', 'inputbox');

		$this->setAttrib('enctype', 'multipart/form-data');			

		$levelId = $this->setLevelId();

		

		$type = new Zend_Form_Element_Text('administrator');

		$type->setLabel("Administrator's type")

				->setValue('Customer Operator')

				->setAttrib('disable', 'disable')

				//->setAttrib('class', 'inputfield')

				->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

		if($this->sessionValue['level_id']==1 || $this->sessionValue['level_id']==11 || $this->sessionValue['level_id']==4 || $this->sessionValue['level_id']==6){

		  $CustomerList = $this->showcustomerList();

		}else{

			$user_id = ($this->sessionValue['level_id']==10)?$this->sessionValue['parent_id']:$this->sessionValue['user_id'];

			$CustomerList = $this->CreateElement('hidden', 'parent_id')->setValue($user_id);

			$CustomerList->removeDecorator('label');

			$CustomerList->removeDecorator('HtmlTag');

		}		

		

		$CountyList = $this->showcountryList();

		$Language = $this->showlanguageList();		  		  

		$Username = $this->fieldusername();		  

				  

		$Password = $this->fieldpassword();	  

				  

		$Confirm = $this->fieldrepassword();

				  

		$Firstname = $this->fieldfirstname();

		

		$MiddleName = $this->fieldmiddlename();

				  

		$Lastname = $this->fieldlastname();		  		  		  	  

				  

		$Email = $this->fieldemail();

				   

		$Company = $this->fieldcompanyname();

							  

		$PhoneNumber = $this->fieldphone();

				  

		$AddressOne = $this->fieldaddress1();

				  

		$AddressTwo = $this->fieldaddress2();		  			  		  		  

		

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();		  

		

		$headerColor = $this->fieldheadercolor();

		

		$TextColor = $this->fieldtextcolor();

		

		$labelOption = $this->fieldlabeloption();

		

		/*$privilege = new Zc_Form_Element_Privilege('priv');

		$privilege->setLabel('Set privilege :')

				   ->setvalue($this->privilege)

				   ->setDecorators($this->defineDecorator(''));*/

		//$this->clear4fix;

				   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		if($this->mode=='edit'){		  

			$this->addElements(array($levelId,$type,$CustomerList,$CountyList,$Language,$Username,$Firstname,$MiddleName,$Lastname,$Email,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$labelOption));	

			$this->fieldadminlogo();

		}

		else{

			$this->addElements(array($levelId,$type,$CustomerList,$CountyList,$Language,$Username,$Password,$Firstname,$MiddleName,$Lastname,$Email,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$labelOption,$this->fieldadminlogo()));	

		}	

			$this->addElements(array($submit));	

		return $this;		  			  

	}

	

	public function HubuserForm(){

		

        $this->setMethod('post');

   		$this->setName('addedithubuser');

 	 	$this->setAttrib('class', 'inputbox');

		

		$Mode = $this->fieldmode();

		

		$levelId = $this->setLevelId();

		

		$type = new Zend_Form_Element_Text('administrator');

		$type->setLabel("Administrator's type")

				->setValue('Hubuser')

				->setAttrib('disable', 'disable')

				//->setAttrib('class', 'inputfield')

				//->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

							

		$CountyList = $this->showcountryList();

				  	  

		$Language = $this->showlanguageList();

				  

		$Username = $this->fieldusername();		  

				  

		$Password = $this->fieldpassword();		  

				  

		$Confirm = $this->fieldrepassword();	

				  

		$Firstname = $this->fieldfirstname();

		

		$MiddleName = $this->fieldmiddlename();

				  

		$Lastname = $this->fieldlastname();		  		  		  	  

				  

		$Email = $this->fieldemail();

				   

		$InvoiceEmail = $this->fieldinvoicemail();		   	

		

		$Company = $this->fieldcompanyname();

		

		$PhoneNumber = $this->fieldphone();

				  

		$AddressOne = $this->fieldaddress1();

				  

		$AddressTwo = $this->fieldaddress2();		  			  		  		  

		

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();		  

		

		$adminLogo = $this->fieldadminlogo();

		

		$headerColor = $this->fieldheadercolor();

		

		$TextColor = $this->fieldtextcolor();

				   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		if($this->mode=='edit'){		  

			$this->addElements(array($Mode,$levelId,$type,$CountyList,$Language,$Username,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$adminLogo,$submit));	

		}

		else{

			$this->addElements(array($Mode,$levelId,$type,$CountyList,$Language,$Username,$Password,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$adminLogo,$submit));	

		}	

		return $this;		  			  

	}

	

	public function HubOperatorForm(){

		//print_r($this->mode);die;

        $this->setMethod('post');

   		$this->setName('addedithuboperator');

 	 	$this->setAttrib('class','inputbox');

		

		$Mode = $this->fieldmode();

		

		$levelId = $this->setLevelId();

		

		$HubUsers = new Zend_Form_Element_Select('user_id');

        $HubUsers->setLabel('Hub User')

				  ->addMultiOptions(array('' => '--Select Hub User--'))

				  ->addMultiOptions($this->listHubuser)	

				  ->setAttrib('title', 'Please Select Hub User')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

				  

		$CountyList = $this->showcountryList();

				  	  

		$Language = $this->showlanguageList();

				  

		$Username = $this->fieldusername();		  

				  

		$Password = $this->fieldpassword();

		

		$Confirm = $this->fieldrepassword();

				  

		$Firstname = $this->fieldfirstname();

		

		$MiddleName = $this->fieldmiddlename();

				  

		$Lastname = $this->fieldlastname();		  		  		  	  

				  

		$Email = $this->fieldemail();

				   

		$InvoiceEmail = $this->fieldinvoicemail();	   	

		

		$Company = $this->fieldcompanyname();

		

		$PhoneNumber = $this->fieldphone();

				  

		$AddressOne = $this->fieldaddress1();

				  

		$AddressTwo = $this->fieldaddress2();		  			  		  		  

		

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();		  

		

		$headerColor = $this->fieldheadercolor();

		

		$TextColor = $this->fieldtextcolor();

				   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		if($this->mode=='edit'){		  

			$this->addElements(array($Mode,$levelId,$HubUsers,$CountyList,$Language,$Username,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$submit));

		}

		else{

			$this->addElements(array($Mode,$levelId,$HubUsers,$CountyList,$Language,$Username,$Password,$Firstname,$MiddleName,$Lastname,$Email,$InvoiceEmail,$Company,$PhoneNumber,$AddressOne,$AddressTwo,$ZipCode,$City,$submit));

		}		

		return $this;		  			  

	}

	

	public function driverForm(){

		

        $this->setMethod('post');

   		$this->setName('addeditdriver');

 	 	$this->setAttrib('class', 'inputbox');

		

		$Mode = $this->fieldmode();

		$levelId = $this->setLevelId();

		 if($this->parentId != ''){

		 	$DepotList = $this->CreateElement('hidden', 'parent_id');

			$DepotList->setValue($this->parentId);

			$DepotList->removeDecorator('label');

			$DepotList->removeDecorator('HtmlTag');

		}else{

			$DepotList = $this->setdepotList();

		}

		$Username = $this->fieldusername();	

		

		$Password = $this->fieldpassword();		  

		

		$Confirm = $this->fieldrepassword();

			

		$CountyList = $this->showcountryList();

				  	  

		$DriverType = new Zend_Form_Element_Select('driver_work_type');

        $DriverType->setLabel('Driver Type')

				  ->addMultiOptions(array('' => '--Select Driver Type--'))

				  ->addMultiOptions(array(1=>'Contract',2=>'Flexible Contract'))	

				  ->setAttrib('title', 'Please Select Driver Type')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

				  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		

		$workinghours = new Zend_Form_Element_Text('total_workhour');

        $workinghours->setLabel('Workin Hours')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Working Hours')

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator);

		

				  

		$driverName = new Zend_Form_Element_Text('driver_name');

        $driverName->setLabel('Driver Name')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill drivername')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		

		$driverCode = new Zend_Form_Element_Text('driver_code');

        $driverCode->setLabel('Driver Code')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill drivercode')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		$licenseNo = new Zend_Form_Element_Text('license_number');

        $licenseNo->setLabel('License Number')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill License Number')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);	  		  		  	  

				  

		$Issuedate = new Zend_Form_Element_Text('license_issue_date');

        $Issuedate->setLabel('License Issue Date')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill License Issue Date')

				  //->setAttrib('class', 'inputfield')

				  ->setAttrib('style', 'width:260px')

				  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

		

		$Expirydate = new Zend_Form_Element_Text('license_expiry_date');

        $Expirydate->setLabel('License Expiry Date')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill License Expiry Date')

				  //->setAttrib('class', 'inputfield')

				   ->setAttrib('style', 'width:260px')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		$VehicleType = new Zend_Form_Element_Text('type_of_vehicle');

        $VehicleType->setLabel('Type Of Vehicle')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Vehicle Type')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);		  	   	

		

		

		$PhoneNumber = $this->fieldphone();

				  

		$Street = new Zend_Form_Element_Text('street');

        $Street->setLabel('Street')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Street')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		$ZipCode = $this->fieldpincode();

				  

		$City = $this->fieldcity();

		

		$Email = new Zend_Form_Element_Text('email');

        $Email->setLabel('Email')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Email')

				  ->setAttrib('class', 'inputfield')

                  ->setDecorators($this->decorator);

		

		$Mallingadd = new Zend_Form_Element_Text('mailing_address');

        $Mallingadd->setLabel('Mailing Address')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Mailing Address')

				  ->setAttrib('class', 'inputfield')

                  ->setDecorators($this->decorator);

						   									    		  		   		   		  

		$submit = $this->fieldsubmit();

		

		if($this->mode=='edit'){

			$UserId = $this->CreateElement('hidden', 'user_id');

			$UserId->removeDecorator('label');

			$UserId->removeDecorator('HtmlTag');

				  

			$this->addElements(array($UserId,$Mode,$levelId,$DepotList,$Username,$CountyList,$DriverType,$workinghours,$driverName, $driverCode,$licenseNo,$Issuedate,$Expirydate,$VehicleType,$PhoneNumber,$Street,$ZipCode,$City,$Email,$Mallingadd,$submit));

		}

		else{

			$this->addElements(array($Mode,$levelId,$DepotList,$Username,$Password,$CountyList,$DriverType,$workinghours,$driverName, $driverCode,$licenseNo,$Issuedate,$Expirydate,$VehicleType,$PhoneNumber,$Street,$ZipCode,$City,$Email,$Mallingadd,$submit));

		}

		return $this;		  			  

	}

	

	

	public function changepasswordForm(){

		

		$this->setMethod('post');

   		$this->setName('changepassword');

 	 	$this->setAttrib('class','inputbox');

		

		

		$OldPassword = new Zend_Form_Element_Text('oldpassword');

        $OldPassword->setLabel('Old Password')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Old Password')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);		  

		

		

		$Password = new Zend_Form_Element_Password('password');

        $Password->setLabel('New Password')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Password')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);		  

		

		$Confirm = new Zend_Form_Element_Password('retypepass');

        $Confirm->setLabel('Confirm Password')

				  ->addFilter('StringTrim')

				  ->setAttrib('title', 'Confirm Password')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);	

				 

		$submit = $this->fieldsubmit();	



		$this->addElements(array($OldPassword,$Password,$Confirm,$submit));

		

		return $this;

	}

	

	

	public function drivercofigForm(){

		

		

		$this->setMethod('post');

   		$this->setName('driverconfig');

 	 	$this->setAttrib('class','inputbox');

		$UserId = $this->CreateElement('hidden', 'user_id');

		$UserId->removeDecorator('label');

		$UserId->removeDecorator('HtmlTag');

		

		$type = new Zend_Form_Element_Text('driver_name');

		$type->setLabel("Driver Name")

				->setValue($this->drivername)

				->setAttrib('disable', 'disable')

				//->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

			

		

		$WorkingType = new Zend_Form_Element_Select('driver_work_type');

        $WorkingType->setLabel('Working Type')

				  ->addMultiOptions(array('' => '--Select Working Type--'))

				  ->addMultiOptions(array(1=>'Weekly working hours',2=>'Flexible worker'))	

				  ->setAttrib('title', 'Please Select Working Type')

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator);		  

		

		

		$Totalhours = new Zend_Form_Element_Text('total_workhour');

        $Totalhours->setLabel('Total Working Hours')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Total Working Hours')

				  ->setAttrib('class', 'inputfield')

                  ->setDecorators($this->decorator);		  

		

		$Leave = new Zend_Form_Element_Text('assigned_leaves');

        $Leave->setLabel('Leave Allowed in Year')

				  ->addFilter('StringTrim')

				  ->setAttrib('title', 'Please Fill Assigned Leave')

				  ->setAttrib('class', 'inputfield')

                  ->setDecorators($this->decorator);	

		

		$submit = $this->fieldsubmit();	



		$this->addElements(array($UserId,$type,$WorkingType,$Totalhours,$Leave,$submit));

		

		return $this;

	}

	

	

	public function CustomerSettingForm(){

		$this->setMethod('post');

   		$this->setName('settingform');

 	 	$this->setAttrib('class', 'inputbox');

		

		$levelId = $this->setLevelId();

		

		$Customer = new Zend_Form_Element_Text('company_name');

		$Customer->setLabel("Customer Name")

				->setValue($this->companyName)

				->setAttrib('class', 'inputfield')

				->setAttrib('disable', 'disable')

				->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

				

		$PaymentTime = new Zend_Form_Element_Text('payment_days');

        $PaymentTime->setLabel('Max Payment Time')

					->setAttrib('class','inputfield')

					->setAttrib('style', 'width:80px')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);	

					

		$SPrice = new Zend_Form_Element_Radio('special_price');

    	$SPrice->setLabel('Allow Special Price')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);	

					

		$Checkin = new Zend_Form_Element_Radio('checkin_notification');

    	$Checkin->setLabel('Checkin Notification')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);	

					

		

		$chargeBtw = $this->fieldbtwcharge();

		

		$showprice = $this->fieldshowprice();

		

		$ProformaInvoice = new Zend_Form_Element_Radio('proforma_invoice');

    	$ProformaInvoice->setLabel('Proforma Invoice')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);	

					

		$UpdateNotify = new Zend_Form_Element_Radio('update_notify');

    	$UpdateNotify->setLabel('Account Update Notification')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$DuesInvoiceNotify = new Zend_Form_Element_Radio('dues_invoice_notify');

    	$DuesInvoiceNotify->setLabel('Email For Dues Invoice Notification')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$InvoiceNotify = new Zend_Form_Element_Radio('invoice_notification');

    	$InvoiceNotify->setLabel('Invoice Notification Message')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$NewsStatus = new Zend_Form_Element_Radio('newsletter_email_status');

    	$NewsStatus->setLabel('Newsletter E-mail Status')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);															

		

		$DailyStatus = new Zend_Form_Element_Radio('daily_status_notify');

    	$DailyStatus->setLabel('Daily Status Notify')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$HistoryExport = new Zend_Form_Element_Select('export_type');

        $HistoryExport->setLabel('Shipment History Export ')

				  ->addMultiOptions(array('1' => 'EXCEL','2'=>'CSV','3'=>'XML'))

				  ->setAttrib('class', 'inputfield')

				  ->setAttrib('style', 'width:150px')

				  ->setDecorators($this->decorator);

					

		$fuelSurcharge = new Zend_Form_Element_Radio('fuel_surcharge_status');

    	$fuelSurcharge->setLabel('Fuel Surcharge')

					->addMultiOptions(array(

        				'1' => 'ON',

        				'0' => "OFF"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$plannerstatus = new Zend_Form_Element_Radio('planner_status');

    	$plannerstatus->setLabel('Show on Planner List')

					->addMultiOptions(array(

        				'1' => 'YES',

        				'0' => "NO"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);			

		

		$Shopstatus = new Zend_Form_Element_Radio('shop_api_status');

    	$Shopstatus->setLabel('Use Shop API to Import Order')

					->addMultiOptions(array(

        				'1' => 'YES',

        				'0' => "NO"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$CCEmail = new Zend_Form_Element_Textarea('cc_email');

        $CCEmail->setLabel('CC Email')

					->setAttrib('class','inputfield')

					->setAttrib('rows','2')

					->setAttrib('cols','5')

					->addFilter('StringTrim')	

					->setAttrib('placeholder', 'one email in one row')

				  	->setDecorators($this->decorator);

					

		$BCCEmail = new Zend_Form_Element_Textarea('bcc_email');

        $BCCEmail->setLabel('BCC Email')

					->setAttrib('class','inputfield')

					->setAttrib('rows','2')

					->setAttrib('cols','5')

					->addFilter('StringTrim')

					->setAttrib('placeholder', 'one email in one row')	

				  	->setDecorators($this->decorator);							

															

		$submit = $this->fieldsubmit();

				

		$this->addElements(array($levelId,$Customer,$PaymentTime,$SPrice,$Checkin,$chargeBtw,$showprice,$ProformaInvoice,$UpdateNotify,$DuesInvoiceNotify,$InvoiceNotify,$NewsStatus,$DailyStatus,$HistoryExport,$fuelSurcharge,$plannerstatus,$Shopstatus,$CCEmail,$BCCEmail,$submit));			

		

		return $this;

	}

	

	public function DepotSettingForm(){

	

		$this->setMethod('post');

   		$this->setName('settingform');

 	 	$this->setAttrib('class', 'inputbox');

		

		$levelId = $this->setLevelId();

		

		$Depot = new Zend_Form_Element_Text('company_name');

		$Depot->setLabel("Depot Name")

				->setValue($this->companyName)

				->setAttrib('disable', 'disable')

				->setAttrib('class', 'inputfield')

				->setAttrib('style', 'font-weight:bold')

				->setDecorators($this->decorator);

		

		$PaymentTime = new Zend_Form_Element_Text('payment_days');

        $PaymentTime->setLabel('Max Payment Time')

					->setAttrib('class','inputfield')

					->setAttrib('style', 'width:80px')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator);

		 $ishub = new Zend_Form_Element_Radio('is_hub');
     	 $ishub->setLabel('Hub')
			 ->addMultiOptions(array('0' => "NO",'1' => 'YES')) 
			 ->setSeparator(' ')
			 ->setValue('0')
			 ->setDecorators($this->decorator);

		

		$this->addElements(array($levelId,$Depot,$PaymentTime,$ishub));

		

		//start invoice setting 

		$invoiceheader = new Zend_Form_Element_Note('header');

		$invoiceheader->setvalue('Invoice Settings')

					->setDecorators($this->headerdecorator);				

		

		$BtwRate = new Zend_Form_Element_Text('btw_rate');

        $BtwRate->setLabel('BTW Rate')

					->setAttrib('class','inputfield')

					->setAttrib('style', 'width:80px')

				  	->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

		

		$BtwName = new Zend_Form_Element_Text('btw_name');

        $BtwName->setLabel('BTW Name')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$InvName = new Zend_Form_Element_Text('invoice_name');

        $InvName->setLabel('Invoice Name')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$InvDate = new Zend_Form_Element_Text('invoice_date');

        $InvDate->setLabel('Invoice Date')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$InvNo = new Zend_Form_Element_Text('invoice_number');

        $InvNo->setLabel('Invoice Number')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$CustomerNo = new Zend_Form_Element_Text('customer_number');

        $CustomerNo->setLabel('Customer Number')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$Topic = new Zend_Form_Element_Text('topic');

        $Topic->setLabel('Topic')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

		

		$Packshipment = new Zend_Form_Element_Text('package_shipments');

        $Packshipment->setLabel('Package Shipments')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$To = new Zend_Form_Element_Text('to');

        $To->setLabel('TO')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$PakateCount = new Zend_Form_Element_Text('packate_count');

        $PakateCount->setLabel('Packate Count')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$Discription = new Zend_Form_Element_Text('description');

        $Discription->setLabel('Discription')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$Price = new Zend_Form_Element_Text('price');

        $Price->setLabel('Price')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);																																	

					

		$Total = new Zend_Form_Element_Text('total');

        $Total->setLabel('Total')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);																																	

					

		$PackateAnnex = new Zend_Form_Element_Text('sent_packets_annex');

        $PackateAnnex->setLabel('Sent Packets Annex')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);																																	

		

		$SubTotal = new Zend_Form_Element_Text('subtotal');

        $SubTotal->setLabel('Subtotal')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$Payable = new Zend_Form_Element_Text('payable');

        $Payable->setLabel('Payable')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);																																				

			

		$Basis = new Zend_Form_Element_Text('basis');

        $Basis->setLabel('Basis')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);																																				

			

		$PaymentTerm = new Zend_Form_Element_Text('payment_terms');

        $PaymentTerm->setLabel('Payment Terms')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$BankDetails = new Zend_Form_Element_Text('bank_details');

        $BankDetails->setLabel('Bank Details')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$BankAcc = new Zend_Form_Element_Text('bank_account');

        $BankAcc->setLabel('Bank Account')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$BankBic = new Zend_Form_Element_Text('bank_bic');

        $BankBic->setLabel('Bank BIC')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);																																													

			

		$Kvk = new Zend_Form_Element_Text('kvk');

        $Kvk->setLabel('Kvk')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

		

		$FuelSurcharge = new Zend_Form_Element_Text('fuel_surcharge');

        $FuelSurcharge->setLabel('Fuel Surcharge')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$FSurchargeText = new Zend_Form_Element_Text('fuel_surcharge_text');

        $FSurchargeText->setLabel('Fuel Surcharge Text')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$btwnotchargeText = new Zend_Form_Element_Text('btw_notcharge_text');

        $btwnotchargeText->setLabel('BTW NotCharge Text')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		

		$FooterKvkText = new Zend_Form_Element_Text('footer_kvk_text');

        $FooterKvkText->setLabel('Footer Kvk Text')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

					

		$FooterbtwText = new Zend_Form_Element_Text('footer_btw_text');

        $FooterbtwText->setLabel('Footer BTW Text')

					->setAttrib('class','inputfield')

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);	

					

		$FooterText = new Zend_Form_Element_Textarea('footer_text');

        $FooterText->setLabel('Footer Text')

					->setAttrib('rows',1)

					->setAttrib('cols',20)

					->addFilter('StringTrim')	

				  	->setDecorators($this->decorator3);

															

				

		$submit = $this->fieldsubmit();

					

		$this->addElements(array($invoiceheader,$BtwRate,$BtwName,$InvName,$InvDate,$InvNo,$CustomerNo,$Topic,$Packshipment,$To,$PakateCount,$Discription,$Price,$Total,$PackateAnnex,$SubTotal,$Payable,$Basis,$PaymentTerm,$BankDetails,$BankAcc,$BankBic,$Kvk,$FuelSurcharge,$FSurchargeText,$btwnotchargeText,$FooterKvkText,$FooterbtwText,$FooterText));															

		

		//end invoice setting

		

		// start Shop Setting

		$Shopheader = new Zend_Form_Element_Note('header5');

		$Shopheader->setvalue('Shop Settings')

					->setDecorators($this->headerdecorator);

					

		$ShopSetting = new Zend_Form_Element_Radio('shop_status');

    	$ShopSetting->setLabel('Web Shop')

					->addMultiOptions(array(

        				'1' => 'YES',

        				'0' => "NO"

      				  ))

					->setAttrib('onchange', 'showhidediv(this.value)')  

					->setSeparator(' ')

					->setDecorators($this->decorator3);

					

		$Warehouse = new Zend_Form_Element_Select('warehouse_id');

        $Warehouse->setLabel('Warehouse')

				  ->addMultiOptions(array('' => '--Select Company--'))

				  ->addMultiOptions($this->WarehouseList)	

				  ->setAttrib('title', 'Please Select Warehouse company')

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->wmsdecorator);			 

		

		// End Shop Setting

					

		$this->addElements(array($Shopheader,$ShopSetting,$Warehouse,$submit));	

			

		return $this;	

	}

	

	

	public function PickupSchedularForm(){

		

		$this->setMethod('post');

   		$this->setName('pickupschedular');

 	 	$this->setAttrib('class', 'inputbox');

		

		$levelId = $this->setLevelId();

			  

		$PickupStatus = new Zend_Form_Element_Radio('daily_pickup_status');

    	$PickupStatus->setLabel('Daily Pickup Status')

					->addMultiOptions(array(

        				'0' => "NO",

						'1' => 'YES'

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

						  		  

		

		$DriverSetting = new Zend_Form_Element_Radio('picked_by_driver');

    	$DriverSetting->setLabel('Picked By Driver Setting')

					->addMultiOptions(array(

        				'0' => "NO",

						'1' => 'YES'

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

					

		$PickWithoutParcel = new Zend_Form_Element_Radio('pickwithoutparcel');

    	$PickWithoutParcel->setLabel('Pickup Without Parcel')

					->addMultiOptions(array(

        				'0' => "NO",

						'1' => 'YES'

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);	

					

					

		/*$SundayStart = new Zend_Form_Element_Text('sun_start');

        $SundayStart->setLabel('Sunday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Sunday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib('placeholder', 'start time')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setDecorators($this->decorator8);

				  

		$SundayEnd = new Zend_Form_Element_Text('sun_end');

        $SundayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Sunday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);	*/

				  

				  

		$MondayStart = new Zend_Form_Element_Text('mon_start');

        $MondayStart->setLabel('Monday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Monday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$MondayEnd = new Zend_Form_Element_Text('mon_end');

        $MondayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Monday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);	

				  

		$TuesdayStart = new Zend_Form_Element_Text('tue_start');

        $TuesdayStart->setLabel('Tuesday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Tuesday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$TuesdayEnd = new Zend_Form_Element_Text('tue_end');

        $TuesdayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Tuesday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);	

				  

		$WednesdayStart = new Zend_Form_Element_Text('wed_start');

        $WednesdayStart->setLabel('Wednesday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Wednesday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$WednesdayEnd = new Zend_Form_Element_Text('wed_end');

        $WednesdayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Wednesday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);

				  

				  

		$ThursdayStart = new Zend_Form_Element_Text('thu_start');

        $ThursdayStart->setLabel('Thursday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Thursday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$ThursdayEnd = new Zend_Form_Element_Text('thu_end');

        $ThursdayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Thursday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);

				  

		$FridayStart = new Zend_Form_Element_Text('fri_start');

        $FridayStart->setLabel('Friday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Friday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$FridayEnd = new Zend_Form_Element_Text('fri_end');

        $FridayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Friday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);

				  

		/*$SaturdayStart = new Zend_Form_Element_Text('sat_start');

        $SaturdayStart->setLabel('Saturday Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Saturday Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$SaturdayEnd = new Zend_Form_Element_Text('sat_end');

        $SaturdayEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Saturday End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);*/

				  

		$DefaultStart = new Zend_Form_Element_Text('default_time_start');

        $DefaultStart->setLabel('Default Time')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Default Start Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'start time')

				  ->setDecorators($this->decorator8);

				  

		$DefaultEnd = new Zend_Form_Element_Text('default_time_end');

        $DefaultEnd->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Default End Time')

				  ->setAttrib('class','inputfield')

                  ->setAttrib('style', 'width:100px')

				  ->setAttrib ('onmouseover', 'clickTimePicker(this.id)')

				  ->setAttrib('placeholder', 'end time')

				  ->setDecorators($this->decorator4);			  			  				  		  			  		  		  	  					

		

		$Name = new Zend_Form_Element_Text('name');

        $Name->setLabel('Name')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Name')

				  ->setAttrib('class','inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		$Street1 = new Zend_Form_Element_Text('street1');

        $Street1->setLabel('Street1')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Street1')

				  ->setAttrib('class','inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		$Street2 = new Zend_Form_Element_Text('street2');

        $Street2->setLabel('Street2')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Street2')

				  ->setAttrib('class','inputfield')

                  ->setDecorators($this->decorator);		  		  

		

		$Zipcode = new Zend_Form_Element_Text('zipcode');

        $Zipcode->setLabel('Zip Code')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill zipcode')

				  ->setAttrib('class', 'inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);

				  

		$City = $this->fieldcity();				  

		

		$Country = new Zend_Form_Element_Text('country');

        $Country->setLabel('Country')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title','Please Fill Country')

				  ->setAttrib('class','inputfield')

                  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decorator);	

		

		$Phone = new Zend_Form_Element_Text('phone');

        $Phone->setLabel('Phone Number')

				  ->addFilter('StringTrim')	

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator);	

				  		  		  

		$submit = $this->fieldsubmit();				  		  

		

		$this->addElements(array($levelId,$PickupStatus,$DriverSetting,$PickWithoutParcel,$MondayStart,$MondayEnd,$TuesdayStart,$TuesdayEnd,$WednesdayStart,$WednesdayEnd,$ThursdayStart,$ThursdayEnd,$FridayStart,$FridayEnd,$DefaultStart,$DefaultEnd,$Name,$Street1,$Street2,$Zipcode,$City,$Country,$Phone,$submit));

		return $this;

		

	}

	

	public function SenderAddressForm(){

		

		$this->setMethod('post');

   		$this->setName('senderaddressform');

 	 	$this->setAttrib('class', 'inputbox');

		

		$Removeflag = $this->CreateElement('hidden', 'removeflag')

					->setValue(0);

		$Removeflag->removeDecorator('label');

		$Removeflag->removeDecorator('HtmlTag');

		

		

		$levelId = $this->setLevelId();

		

		$SetDefault = new Zend_Form_Element_Radio('set_default');

    	$SetDefault->setLabel('Set Default')

					->setValue('0')

					->addMultiOptions(array(

        				'0' => "NO",

						'1' => 'YES'

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator);

			

		

		$Companyname = new Zend_Form_Element_Text('name');

        $Companyname->setLabel('Name/Company Name')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Name')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

                  ->addValidator('NotEmpty')

                  ->setDecorators($this->decorator);

				  

		$StreetLabel = new Zend_Form_Element_Note('streetlabel');

        $StreetLabel->setLabel('Street/Number')

				  ->setRequired(true)

                  ->addValidator('NotEmpty')

                  ->setDecorators($this->decoratorstreet);

				  

		$Street = new Zend_Form_Element_Text('street');

        $Street->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Street!')

				  ->setAttrib('class', 'inputfield')

				  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->decStreet);		  

				  

		$StreetNo = new Zend_Form_Element_Text('streetnumber');

        $StreetNo->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Street Number')

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator2);

				  

		$StreetAdd = new Zend_Form_Element_text('streetaddress');

        $StreetAdd->setLabel('Address')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Street Address')

				  ->setAttrib('class', 'inputfield')

				  ->setDecorators($this->decorator);

		

		$Country = $this->showcountryList();

		

		$Zipcode = $this->fieldpincode();

				  

		$City = $this->fieldcity();	

		

		$Email = new Zend_Form_Element_Text('email');

        $Email->setLabel('Email')

				  ->addFilter('StringTrim')	

				  ->setAttrib('title', 'Please Fill Email')

				  ->setAttrib('class', 'inputfield')

				  ->addValidator('EmailAddress')

				  ->setDecorators($this->decorator);

		

		

		$submit = $this->fieldsubmit();

		

		if($this->mode=='edit' && (!empty($this->senderLogo))){

			$Logo = new Zend_Form_Element_File('logo');

        	$Logo->setLabel('Logo <br><b>(Dimension 210 X 62)</b></br>')

				  ->addFilter('StringTrim')

				  ->setDescription('View Previous Logo')

				  ->setAttrib('title','Please Select Logo')

				  ->setDecorators($this->filedecorator);

			

			$oldimage = new Zend_Form_Element_Image('old_logo');

			$oldimage->setLabel('Logo <br><b>(Dimension 210 X 62)</b></br>')

					  ->setAttrib('style','display: none')	

        			  ->addFilter('StringTrim')

					  ->setDescription('Remove')

					  ->setDecorators($this->ImageDecorator(LOGO_LINK.'/'.$this->senderLogo));	

			

			$this->addElements(array($Removeflag,$levelId,$SetDefault,$Companyname,$Country,$StreetLabel,$Street,$StreetNo,$StreetAdd,$Zipcode,$City,$Email,$oldimage,$Logo,$submit));

		}

		else{

			$Logo = new Zend_Form_Element_File('logo');

        	$Logo->setLabel('Logo <br><b>(Dimension 210 X 62)</b></br>')

				  ->addFilter('StringTrim')

				  ->setAttrib('title','Please Select Logo')

				  ->setDecorators($this->filedecorator);

					

		$this->addElements(array($levelId,$SetDefault,$Companyname,$Country,$StreetLabel,$Street,$StreetNo,$StreetAdd,$Zipcode,$City,$Email,$Logo,$submit));

		}			

		

		return $this;

		

	}

		public function fieldadminlogo(){

		if($this->mode != 'edit'){

		$adminLogo = new Zend_Form_Element_File('logo');

        $adminLogo->setLabel('Admin Logo<br>(Dimension 210 X 62)</br>')

				  ->addFilter('StringTrim')

				  ->setAttrib('title','Please Select Admin Logo')

				  ->setRequired(true)

                  ->addValidator('NotEmpty')

				  ->setDecorators($this->filedecorator);

		

			return $adminLogo;

		}else{

			 $this->addElement('hidden', 'admin_logo', array(

                'description' => '&nbsp;&nbsp;Admin Logo:<br>(Dimension 210 X 62)</br>',

                'ignore' => true,

				 'decorators' => array(array('Description', array('escape'=>false, 'tag'=>'div', 'class' => 'col-sm-4 col_paddingtop')),array(array('row' => 'HtmlTag'), array('tag' => 'span', 'class' => "odd", 'openOnly' => true)))));

        	$this->addElement('hidden', 'admin_logo_err', array(

                'description' => "<div id='admin_logo_targetTag'></div>

                                  <div id='edit_admin_text'><img src='".LOGO_LINK.'/'.$this->adminLogo."' width='185px' height='50px'><a href='javascript:void(0);' 

								  onclick=addElement('INPUT','admin_logo_targetTag','logo','".$this->logos."');>Edit</a></div>

								  <div id='adlogoerr' class='err' style='display:none' >require</div>",

                'ignore' => true,

                'decorators' => array(

                        array('Description', array('escape'=>false, 'tag'=>'div', 'class' => 'col-sm-8 col_paddingtop', 'align' => 'left', 'id' => 'editElement','colspan'=>2,'value'=>'$this->logos')),

                        array(array('row' => 'HtmlTag'), array('tag' => 'span', 'class' => "odd", 'closeOnly' => true)),),));

		return $this;

		}

	}

	

		public function fieldinvoicelogo(){

		if($this->mode != 'edit'){

		$InvoiceLogo = new Zend_Form_Element_File('invoice_logo');

		$InvoiceLogo->setLabel('Invoice Logo<br>(Dimension 210 X 62)</br>')

					->setRequired(true)

					->addFilter('StringTrim')

					->setAttrib('title', 'Please Select Invoice Logo')

					->setAttrib('style', 'width:100%')

					->setDecorators($this->filedecorator);

		

		return $InvoiceLogo;

		}else{	

	

			$this->addElement('hidden', 'invoice_logo', array(

                'description' => '&nbsp;&nbsp;Logo For Invoice :<br>(Dimension 210 X 62)</br>',

                'ignore' => true,

                'decorators' => array(

                        array('Description', array('escape'=>false, 'tag'=>'div', 'class' => 'col-sm-4 col_paddingtop')),

                        array(array('row' => 'HtmlTag'), array('tag' => 'span', 'class' => "odd", 'openOnly' => true)),

                ),

        	));

        	$this->addElement('hidden', 'invoice_logo_err', array(

                'description' => "<div id='invoice_logo_targetTag'></div>

                                    <div id='edit_invoice_text'><img src='".LOGO_LINK.'/'.$this->invoiceLogo."' width='185px' height='50px'><a href='javascript:void(0);' onclick=addElement('INPUT','invoice_logo_targetTag','invoice_logo','".$this->invoicelogos."');>Edit</a></div>

									<div id='invlogoerr' class='err' style='display:none' >require</div>",

                'ignore' => true,

                'decorators' => array(

                        array('Description', array('escape'=>false, 'tag'=>'div', 'class' => 'col-sm-8 col_paddingtop', 'align' => 'left', 'id' => 'editElement','colspan'=>2)),

                        array(array('row' => 'HtmlTag'), array('tag' => 'span', 'class' => "odd", 'closeOnly' => true)),

                ),

        	));

		}

	}



}