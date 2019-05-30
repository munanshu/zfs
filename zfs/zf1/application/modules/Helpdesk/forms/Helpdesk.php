<?php
class Helpdesk_Form_Helpdesk extends Application_Form_CommonDecorator
{

    public $ModelObj;
	public $conmmonDecObj;
    public function init()
    {
         $this->ModelObj = new Helpdesk_Model_ClaimManager();
		 //$this = new settings_Form_CommonDecorator();
		
    }
	
	
	public function editclaimstatusForm(){
		$this->setName('editclaimstatus');
		$this->setAttrib('class', 'inputbox');
        $this->setMethod('post');
		$this->setDecorators(
			$this->form12Dec
		);
		$claimstatusname = $this->createElement('text', 'claimstatusname')
											->setLabel('Claim Status Name :')
											->setAttrib("class","inputfield")
											->setRequired(true)
											->addValidator('NotEmpty')
											->setValue('')
											->setDecorators($this->element6Dec);	
		
		$notification_type = $this->createElement('radio', 'select_notification', array(
            'label'      => 'Select Notification Option :',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
            'required'   => true,
			'multiOptions'=>array(
                '0' => 'Used Existing Notification',
				'1' => 'Create New Notification'
            )	
         ));
		 
		 $select_notification = $this->createElement('select', 'notification')
											->setLabel('Select Notification: ')
											->setAttrib("class","inputfield")
											->setDecorators($this->element6Dec);
											
         $new_notification = $this->createElement('text', 'newnotification')
											->setLabel('New Notification Name :')
											->setAttrib("class","inputfield")
											->setDecorators($this->element6Dec);
		 
		 $notification_status = $this->createElement('radio', 'notification_status', array(
            'label'      => 'Notification Status :',
			'label_class' => 'control control--radio',
			'decorators' => $this->radio6Dec,
			'separator' =>'',
			'class'  => 'printlabel',
            'required'   => true,
			'multiOptions'=>array(
                '0' => 'ON',
				'1' => 'OFF'
            )	
         ));
		 $submit = $this->createElement('submit', 'submit')
										->setAttrib("class","btn btn-danger btn-round")
										->setLabel('Update Status')
										->setIgnore(true)
										->setDecorators($this->submit6Dec);
		 $this->addElements(array($claimstatusname,$notification_type,$select_notification,$new_notification,$notification_status,$submit));
         return $this;
			
	  }
}