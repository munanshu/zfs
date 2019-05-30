<?php

class settings_Form_Translation extends Application_Form_CommonDecorator

{

	public $LanguageList = NULL;

    /**

     * Function : addTranslationTextForm()

	 * Params : NULL

     * add Text in Translation module

	 * Date : 24/03/2017

     * */

     public function addTranslationTextForm(){
			$this->setName('addtranslationtext');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators($this->form12Dec);
			$this->addElements(array($this->createElement('text', 'translationFor')
						->setLabel('Label Name')
						->setAttrib("class","inputfield")
						->setRequired(true)
						->addValidator('NotEmpty')
						->setDecorators($this->decorator)));
			foreach($this->LanguageList as $language){
				if($language['language_name'] == 'English'){
					$this->addElements(array($this->createElement('text', $language['language_name'])
										->setLabel($language['language_name'])
										->setAttrib("class","inputfield")
										->setRequired(true)
										->addValidator('NotEmpty')
										->setDecorators($this->decorator)));
				}else{
					$this->addElements(array($this->createElement('text', $language['language_name'])
										->setLabel($language['language_name'])
										->setAttrib("class","inputfield")
										->setDecorators($this->decorator)));
				}
			}
			$submit = $this->createElement('submit', 'submit')
							->setAttrib('class', 'btn btn-danger btn-round clearfix')
							->setAttrib('style', 'margin-left:auto;margin-right:auto;display:block;')
							->setLabel('Add Text');
			$this->addElements(array($submit));
			return $this;
    }









}









