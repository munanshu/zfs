<?php
class Checkin_Form_Parcel extends Application_Form_CommonDecorator
{

    public $ModelObj;
	public $conmmonDecObj;
	public $Forwarders;
	public $Statuses = array();

	
	public function getParcelDeliveryForm(){


		$this->addElement($this->createElement('text', 'date_time')
											->setLabel('Date:')
											->setAttrib("class","inputfield")
											->setAttrib("id","assigndate")
											->setRequired(true)
											->addValidator(new Zend_Validate_Date(array("format" => 'Y-m-d')))
											);

		$this->addElement($this->createElement('text', 'time')
											->setLabel('Time:')
											->setAttrib("class","inputfield")
											->setAttrib("id","assigntime")
											->setRequired(true)
											 
											);

		$this->addElement($this->createElement('text', 'barcode')
											->setLabel('Barcode: ')
											->setAttrib("class","inputfield")
											->setAttrib("readonly",true)
											->setRequired(true))
											;

		$this->addElement($this->createElement('text', 'rec_name')
											->setLabel('Reciever Name : ')
											->setAttrib("class","inputfield")
											->setRequired(true))
											;										

		$this->addElement($this->createElement('select', 'forwarder_id')
											->setLabel('Forwarder Name: ')
											->setAttrib("class","inputfield")
											->setAttrib("id","forwarder_id")
											->setAttrib("disabled","disabled")
											->setAttrib('onchange',"submitMe(this.value)")
											->setRequired(true)
											->addMultiOptions(array(''=>'Select Forwarder'))
											->addMultiOptions($this->Forwarders)
											);

		$this->addElement($this->createElement('select', 'status')
											->setLabel('Statuses: ')
											->setAttrib("class","inputfield")
											->setAttrib("id","statuses")
											->setRequired(true)
											->addMultiOptions(array(''=>'Select Status'))
											->addMultiOptions($this->Statuses)
											);
		 	

		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Update',
			'class'  => 'btn btn-danger btn-round',
        ));
		
		return $this;


	}


}