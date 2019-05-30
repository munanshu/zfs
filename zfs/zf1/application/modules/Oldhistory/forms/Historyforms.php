<?php
 
class Oldhistory_Form_Historyforms extends Application_Form_CommonDecorator
{

    
 
	public function __construct($options = null)
	{	 
    	  
	}

  	public function addInvoiceForm()
	{
		$this->addElement('hidden', 'invoice_id');
		$this->addElement('hidden', 'invoice_number');
 		

 		 $this->addElement($this->createElement('select', 'payment_mode')
											->setAttrib("class","inputfield")
											// ->setRequired(true)
											->addMultiOptions(array(''=>'Select Mode'))
											->addMultiOptions(array('bank'=>'Bank','paypal'=>'Paypal','cash'=>'Cash')  )
											);

		$this->addElement($this->createElement('text', 'paid_amount')
											->setAttrib("class","inputfield")
											// ->setRequired(true)
											);
 		$this->addElement($this->createElement('text', 'payment_date')
											->setAttrib("class","inputfield")
											->setAttrib("id","assigndate")
											// ->setRequired(true)
											->addValidator(new Zend_Validate_Date(array("format" => 'Y-m-d')))
											);

		 

		 $payment_status = new Zend_Form_Element_Radio('payment_status');

    	$payment_status->addMultiOptions(array(

        				'1' => 'Paid',

        				'0' => "Un Paid"

      				  ))

					->setSeparator(' ')

					->setDecorators($this->decorator); 

		  $this->addElement($payment_status);
		  								
		 										
		$this->addElement($this->createElement('textarea', 'remark')
											->setAttrib("class","inputfield")
											->setAttrib("rows",4)
											->setAttrib("cols",40)
											);

		 
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;

	}



	 




 
}

