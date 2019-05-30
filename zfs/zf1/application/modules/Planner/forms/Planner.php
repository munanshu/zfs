<?php

class Planner_Form_Planner extends Application_Form_CommonDecorator
{
    /**
     * Function : routeSettingForm()
	 * Params : NULL
     * add edit route Setting Form in Planner module
	 * Date : 19/01/2017
     * */
    public function routeSettingForm(){
			$this->setName('routeSetting');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators($this->form12Dec);
			$routename = $this->createElement('text', 'routename')
									->setLabel('Route Name')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->setDecorators($this->element4Dec);
			$description = $this->createElement('textarea', 'description')
									->setLabel('Route Description')
									->setAttrib('ROWS', '1')
									->setAttrib("class","inputfield")
									->setDecorators($this->element8Dec);
			$starttime = $this->createElement('text', 'start_time')
									->setLabel('Start Time')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->setAttrib ( 'onmouseover', 'clickTimePicker(this.id)' )
									->setDecorators($this->element4Dec);
			$endtime = $this->createElement('text', 'end_time')
									->setLabel('End Time')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->setAttrib ( 'onmouseover', 'clickTimePicker(this.id)' )
									->setDecorators($this->element4Dec);
			$driven_days = $this->createElement('select', 'driven_days')
									->setLabel('Driven Days')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->addMultiOptions(array('All Days'=>'All Days','Monday'=>'Monday','Tuesday'=>'Tuesday','Wednesday'=>'Wednesday','Thursday'=>'Thursday','Friday'=>'Friday'))
									->setDecorators($this->element4Dec);
			$postalcode = $this->createElement('textarea', 'postalcode')
									->setLabel('Route Postal Codes')
									->setAttrib('placeholder', 'Please Use Enter After Each Zipcode')
									->setAttrib('COLS', '20')
									->setAttrib('ROWS', '1')
									->setAttrib("class","inputfield")
									->setDecorators($this->element6Dec);
			$submit = $this->createElement('submit', 'submit')
							->setAttrib("class","btn btn-danger btn-round")
							->setLabel('Add Route')
							->setIgnore(true)
							->setDecorators($this->submit3Dec);
			$this->addElements(array($routename,$description,$starttime,$endtime,$driven_days,$postalcode,$submit));
			return $this;
    }
}