<?php
 
class Accounting_Form_Heads extends Application_Form_CommonDecorator
{

    public $ModelObj;
	public $conmmonDecObj;
	public $StatusModelObj;
	public $forwarder_id;
	public $forwarder_namevalue;	
	protected $_formParams;	
 
	public function __construct($options = null)
	{	 
    	 $this->_formParams = $options;
        $this->ModelObj = new Accounting_Model_Settingmanager();
	}

  //   public function init()
  //   {	 
  //       // $_formParams = $this->_attribs;
  //       // $this->Request = Zend_Controller_Front::getInstance()->getRequest()->getPost();
		// // $this->StatusModelObj = new settings_Model_Statuscode();
		// //$this = new settings_Form_CommonDecorator();
		
  //   }


	public function addBookForm()
	{
		$this->addElement('hidden', 'head_id');
		$this->addElement('hidden', 'ledger_head');
		$this->addElement('hidden', 'invoice_id');
 		
 		$this->addElement($this->createElement('text', 'invoice_date')
											->setLabel('Invoice Date: ')
											->setAttrib("class","inputfield")
											->setAttrib("id","assigndate")
											->setRequired(true)
											->addValidator(new Zend_Validate_Date(array("format" => 'Y-m-d')))
											);

 		 

		$this->addElement($this->createElement('text', 'invoice_number')
											->setLabel('Invoice Number:')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('Digits'))
											;
		$this->addElement($this->createElement('text', 'invoice_definition')
											->setLabel('Definition: ')
											->setAttrib("class","inputfield")
											->setRequired(true))
											;

		$this->addElement($this->createElement('select', 'customer_id')
											->setLabel('Customer Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addMultiOptions(array(''=>'Select Customer'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxListbyClass(USERS_DETAILS,array('user_id','company_name')),array('user_id','company_name')))
											);
		$this->addElement($this->createElement('select', 'supplier_id')
											->setLabel('Supplier Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addMultiOptions(array(''=>'Select Supplier'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxListbyClass(AccountingSuppliers,array('supplier_id','company_name')),array('supplier_id','company_name')))
											);																		
		$this->addElement($this->createElement('text', 'credit_amount')
											->setLabel('Total Credit: ')
											->setRequired(true)
											->setAttrib("class","inputfield")
											->addValidator('Digits'))
											;
		$this->addElement($this->createElement('text', 'debit_amount')
											->setLabel('Total Debit: ')
											->setRequired(true)
											->setAttrib("class","inputfield")
											->addValidator('Digits'))
											;									

		 																						

		$this->addElement($this->createElement('text', 'booknumber')
											// ->setLabel('Invoice Number: ')
											->setAttrib("id","booknumber_1")
											->setAttrib("class","inputfield")
											->setIsArray(true)
											->addValidator('Digits'))
											;
		$this->addElement($this->createElement('text', 'definition')
											// ->setLabel('Definition: ')
											->setAttrib("id","definition_1")
											->setAttrib("class","inputfield")
											->setIsArray(true))
											;																			


		 $this->addElement($this->createElement('select', 'btw')
											// ->setLabel('Btw Rate : ')
											->setAttrib("class","inputfield")
											->setAttrib("id","btw_1")
											->setAttrib("onchange","return calculateLedgerRule(this.value,this.id)")
											->setAttrib("labelname","btw")
											->setIsArray(true)
											// ->setRequired(true)
											// ->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Btw Rate'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getbtwrateforinvoice(),array('btw_rate','btwrates_desc')))
											);
		  	
		  $this->addElement($this->createElement('text', 'ledger_invoice_number')
											// ->setLabel('Invoice Number : ')
											->setAttrib("id","ledger_invoice_number_1")
											->setAttrib("labelname","ledger_invoice_number")
											->setIsArray(true)
											->setAttrib("class","inputfield"))
											;

		  $this->addElement($this->createElement('text', 'memorial_id')
											// ->setLabel('Memorial Number: ')
											->setAttrib("id","memorial_id_1")
											->setAttrib("labelname","memorial_id")
											->setIsArray(true)
											->setAttrib("class","inputfield"))
											;
																			


		  $this->addElement($this->createElement('select', 'ledger_id')
											// ->setLabel('Ledger Account: ')
											->setAttrib("class","inputfield")
											->setAttrib("id","ledger_1")
											->setAttrib("onchange","return calculateLedgerRule(this.value,this.id)")
											// ->setRequired(true)
											->setIsArray(true)
											// ->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Ledger'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getaccountheadlist(),array('head_id','description')))
											);

		  $this->addElement($this->createElement('text', 'credit')
											// ->setLabel('Credit : ')
											->setAttrib("id","credit_1")
											->setAttrib("labelname","credit")
											->setIsArray(true)
											->setAttrib("class","inputfield"))
											;

		  $this->addElement($this->createElement('text', 'debit')
											// ->setLabel('Debit : ')
											->setAttrib("id","debit_1")
											->setAttrib("labelname","debit")
											->setIsArray(true)
											->setAttrib("class","inputfield"))
											;									
		
		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;

	}



	
	public function addHeadForm()
    { 
		
        $this->addElement('hidden', 'head_id');

    	$HeadCodeExists = new Zend_Validate_Db_NoRecordExists(
	        array(
	            'table' => AccountingHead,
	            'field' => 'head_code'
	       	 )
   		 );

        $this->addElement($this->createElement('text', 'head_code')
											->setLabel('Head Code: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator($HeadCodeExists)
											->addValidator('Digits') 
											);

        $this->addElement('text', 'head_description', array(
            'label'      => 'Head Description:',
			'class'  => 'inputfield',
            'required'   => true
        ));

		$this->addElement($this->createElement('select', 'function_id')
											->setLabel('Function Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Function'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxList(AccountingFunction,array('function_id','description')),array('function_id','description')))
											);

		 $this->addElement($this->createElement('select', 'btwrate_id')
											->setLabel('Btw Rate Id: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Class'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getbtwratelist(),array('btwrate_id','btwrates_desc')))
											);

		 
		 $this->addElement($this->createElement('select', 'class_id')
											->setLabel('Class Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setAttrib("onchange","selectaccountinggroup(\$(this).val(),'".BASE_URL."/Accountsetting/getaccountgroupbyclass')")
											->addMultiOptions(array(''=>'Select Class'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxList(AccountingClass,array('class_id','class_name')),array('class_id','class_name')))
												);

		 $attribs = $this->_attribs;
		 $class_id = isset($attribs['class_id'])?$attribs['class_id']:'';	
		 $class_id = isset($this->_formParams['class_id'])?$this->_formParams['class_id']:'';
		 // echo $class_id."aa";	
		 $this->addElement($this->createElement('select', 'group_id')
											->setLabel('Group Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											// ->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Group'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxListbyClass(AccountingGroup,array('group_id','group_name'),'ASC',$class_id ),array('group_id','group_name')))
											);
		
		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;
    }



    public function addClassForm()
    { 
		
        $this->addElement('hidden', 'class_id');

    	$ClassNameExists = new Zend_Validate_Db_NoRecordExists(
	        array(
	            'table' => AccountingClass,
	            'field' => 'class_name'
	       	 )
   		 );

        $this->addElement($this->createElement('text', 'class_name')
											->setLabel('Class Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator($ClassNameExists)
											->addFilter('StringTrim') 
											);

        $this->addElement('textarea', 'description', array(
            'label'      => 'Description:',
			'class'  => 'inputfield',
            'required'   => true,
            'rows' => 3,
            'cols' => 40
        ));

		$this->addElement($this->createElement('select', 'activity_type')
											->setLabel('Activity Type: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Activity'))
											->addMultiOptions(commonfunction::scalarToAssociative(array(array('activity_type_id'=>1,'activity_type'=>'Credit'),array('activity_type_id'=>0,'activity_type'=>'Debit')),array('activity_type_id','activity_type') ))
											);

		  
		
		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		// echo "<pre>";
		// print_r($this);	

		return $this;
    }


    public function addGroupForm()
    { 
		
        $this->addElement('hidden', 'group_id');

    	$GroupNameExists = new Zend_Validate_Db_NoRecordExists(
	        array(
	            'table' => AccountingGroup,
	            'field' => 'group_name'
	       	 )
   		 );

        $this->addElement($this->createElement('text', 'group_name')
											->setLabel('Group Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator($GroupNameExists)
											->addFilter('StringTrim') 
											);

        $this->addElement('textarea', 'description', array(
            'label'      => 'Group Description:',
			'class'  => 'inputfield',
            'required'   => true,
            'rows' => 3,
            'cols' => 40
        ));

		$this->addElement($this->createElement('select', 'sub_group_id')
											->setLabel('Sub Group Of: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Sub Group'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxList(AccountingGroup,array('group_id','group_name')),array('group_id','group_name')))
											);

		 $this->addElement($this->createElement('select', 'class_id')
											->setLabel('Class Name: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Class'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxList(AccountingClass,array('class_id','class_name')),array('class_id','class_name')))
												);

		  
		
		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;
    }

    public function addFunctionForm()
    { 
		
        $this->addElement('hidden', 'function_id');

    	$DescriptionExists = new Zend_Validate_Db_NoRecordExists(
	        array(
	            'table' => AccountingFunction,
	            'field' => 'description'
	       	 )
   		 ); 

    	 $this->addElement($this->createElement('textarea', 'description')
											->setLabel('Description : ')
											->setAttrib("class","inputfield")
											->setAttrib("rows",3)
											->setAttrib("cols",40)
											->setRequired(true)
											->addValidator($DescriptionExists)
											->addFilter('StringTrim') 
											);

		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		// echo "<pre>";
		// print_r($this);	

		return $this;
    }


    public function addBtwRateForm()
    { 
		
        $this->addElement('hidden', 'btwrate_id');

        $this->addElement($this->createElement('text', 'effective_date')
											->setLabel('Effective Date: ')
											->setAttrib("class","inputfield")
											->setAttrib("id","assigndate")
											->setRequired(true)
											->addValidator(new Zend_Validate_Date(array("format" => 'Y-m-d')))
											);

        
        $this->addElement($this->createElement('text', 'btw_rate')
											->setLabel('Btw Rate: ')
											->setAttrib("class","inputfield")
											->setAttrib("maxlength",3)
											->setAttrib("minlength",1)
											->setRequired(true)
											->addValidator('Digits')
											->addValidator(new Zend_Validate_Between(1, 100))
											);

         

		$this->addElement($this->createElement('select', 'btwrate_type')
											->setLabel('Btw Rate Type : ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addMultiOptions(array(''=>'Select Rate Type'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxList(AccountingBtwRateTypes,array('btwrate_type_id','btwrate_type_name')),array('btwrate_type_id','btwrate_type_name')))
											);

		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;
    }


    public function addSupplierForm()
    { 
		
        $this->addElement('hidden', 'supplier_id');

        $this->addElement($this->createElement('text', 'company_name')
											->setLabel('Supplier Name *: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addFilter('StringTrim') 
											->addValidator('regex', false, array(
											    '/^[a-zA-Z0-9 .&-]+$/',
											    'messages'=>array(
											    'regexNotMatch'=>'Please enter a valid Company name it should contain only small and capital letters , digits, and -,. , & '
											    )    
											))
											 
											);
         $this->addElement($this->createElement('text', 'contact_name')
											->setLabel('Contact Person *: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addFilter('StringTrim') 
											->addValidator('regex', false, array(
											    '/^[a-zA-Z]+$/',
											    'messages'=>array(
											    'regexNotMatch'=>'Please enter a valid Person name it should contain only small and capital alphabets'
											    )    
											))
											 
											);

        $this->addElement($this->createElement('text', 'postalcode')
											->setLabel('Postal Code (6 digits only)*: ')
											->setAttrib("class","inputfield")
											->setAttrib("minlength",6)
											->setAttrib("maxlength",6)
											->setRequired(true)
											->addFilter('StringTrim') 
											->addValidator('Alnum') 
											);
        $this->addElement($this->createElement('text', 'city')
											->setLabel('City Name *: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addFilter('StringTrim')
											->addValidator('regex', false, array(
											    '/^[a-zA-Z]+$/',
											    'messages'=>array(
											    'regexNotMatch'=>'Please enter a valid City name it should contain only small and capital alphabets'
											    )    
											)) 
											);
        $this->addElement($this->createElement('text', 'street')
											->setLabel('Street : ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim') 
											);
        $this->addElement($this->createElement('text', 'street_no')
											->setLabel('Street No: ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim') 
											);
        $this->addElement($this->createElement('text', 'address')
											->setLabel('Address *: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addFilter('StringTrim')

											);

		$this->addElement($this->createElement('select', 'country_id')
											->setLabel('Country Name *: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->addMultiOptions(array(''=>'Select Country'))
											->addMultiOptions(commonfunction::scalarToAssociative($this->ModelObj->getselectboxList(COUNTRIES,array('country_id','country_name')),array('country_id','country_name')))
											);

        $this->addElement($this->createElement('text', 'phoneno')
											->setLabel('Phone No *: ')
											->setAttrib("class","inputfield")
											->setAttrib("minlength",10)
											->setAttrib("maxlength",10)
											->setRequired(true)
											->addFilter('StringTrim') 
											->addValidator('Digits') 
											);
		  
        $this->addElement($this->createElement('text', 'email')
											->setLabel('Email *: ')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addFilter('StringTrim') 
											->addValidator(new Zend_Validate_EmailAddress()) 
											);
		 
		 $this->addElement($this->createElement('text', 'bank_account')
											->setLabel('Bank Account : ')
											->setAttrib("class","inputfield")
											->addFilter('StringTrim') 
											->addValidator("Digits")
											);

		 $this->addElement($this->createElement('text', 'account_holder')
											->setLabel('Account Holder : ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim')
											->addValidator('regex', false, array(
											    '/^[a-zA-Z]+$/',
											    'messages'=>array(
											    'regexNotMatch'=>'Please enter a valid Person name it should contain only small and capital alphabets'
											    )    
											)) 
											);

		  $this->addElement($this->createElement('text', 'btw_no')
											->setLabel('BTW Number : ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim') 
											->addValidator('Digits') 
											);
		 $this->addElement($this->createElement('text', 'kvk_no')
											->setLabel('KVK Number : ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim') 
											->addValidator('Digits') 
											);	
		 $this->addElement($this->createElement('text', 'creditcard_no')
											->setLabel('Credit Card No : ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim') 
											->addValidator('Digits') 
											);
		 $this->addElement($this->createElement('text', 'credit_period')
											->setLabel('Credit Period : ')
											->setAttrib("class","inputfield")
											 
											->addFilter('StringTrim') 
											->addValidator('Digits') 
											);
		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;
    }




 
}

