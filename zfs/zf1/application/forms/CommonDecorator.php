<?php

class Application_Form_CommonDecorator extends Zend_Form
{
	//Form Decorator
	public $form9Dec = array('FormElements',
								 array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'row')),
								 array('HtmlTag', array('tag' => 'div', 'class' => 'col-lg-9 col-md-8 col-sm-12 b-email')),
								 'Form');
	public $form12Dec = array('FormElements',
								 array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'row')),
								 array('HtmlTag', array('tag' => 'div', 'class' => 'col-lg-12 col-md-12 col-sm-12 b-email')),
								 'Form');
   //End Form Decorator
   
	//Checkbox Decorator
    public $checkbox2Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-2 col_paddingtop')));
	public $checkbox3Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-3 col_paddingtop')));
	 public $checkbox4Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-4 col_paddingtop')));
	public $checkbox6Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop')));
	// End Checkbox Decorator

	// Start Radio Decorator
    public $radio2Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-2 col_paddingtop')));
	public $radio3Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-3 col_paddingtop')));
	public $radio4Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-4 col_paddingtop')));
	public $radio6Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop')));
	public $radio12Dec = array('ViewHelper','Errors','Description',
									array('decorator' => array('FooBar' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
									'label',array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-12 col_paddingtop clearfix')));
	// End Radio Decorator
   
   
    public $element2Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-2 col_paddingtop')));
	public $element3Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-3 col_paddingtop')));
	public $element4Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-4 col_paddingtop')));
    public $element6Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop')));
	public $element8Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-8 col_paddingtop')));
	public $element9Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-9 col_paddingtop')));
									
	public $element12Dec = array('ViewHelper','Errors','Description',array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false)),
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-12 col_paddingtop')));
	public $submit2Dec = array('ViewHelper','Errors','Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-2 col_paddingtop clearfix')));
	public $submit3Dec = array('ViewHelper','Errors','Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-3 col_paddingtop clearfix')));
	public $submit4Dec = array('ViewHelper',
                                    'Errors',
                                    'Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-4 col_paddingtop clearfix')));
	public $submit6Dec = array('ViewHelper','Errors','Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop clearfix')));
									
	public $clear4fix   = array('required' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'div','class' => 'col-sm-4 col_paddingtop clearfix'))));
	
	public $decorator = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 'tag' => 'div',
								 'class' => 'ol-lg-6 col-md-6 col-sm-6 b-email',
								 'style' => 'display:none;color:#FF0000;',)),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'div','class' => "col-sm-6 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'div',
									   'class' => "bold_text col-sm-4 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-12 col_paddingtop")));
								
	public $filedecorator = array('File',
							array('Description',
								array('placement' => Zend_Form_Decorator_Abstract::APPEND,
									  'tag'=>'i',
									  'href'=>'javascript:void();',
									  'tag'=>'<a>',
									  'title'=> 'Previous Image',
									  'onclick'=>"ShowImage();",)),
									  'Errors',array(array('data' => 'HtmlTag'), array('tag' => 'div','class' => "col-sm-6 col_paddingtop")), 
											array('Label',
												array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
													  'escape' => false, 
													  'tag'=>'div',
													  'class' => "bold_text col-sm-4 col_paddingtop")),
										array(array('row' => 'HtmlTag'),
										array('tag' => 'div',
											  'id'=>'imagefile', 
											  'class' => "col-sm-12 col_paddingtop")));
	
	public $passworddecorator = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 'tag'=>'i',
								 'href'=>'javascript:void();',
								 'tag'=>'<a>',
								 'class'=> 'fa fa-eye',
								 'onclick'=>"javascript:showhidetext();",
								 )),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'div','class' => "col-sm-6 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'div',
									  
									   'class' => "bold_text col-sm-4 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-12 col_paddingtop")));																		

   	public $headerdecorator = array('ViewHelper',array(array('rowdata'=>'HtmlTag'),array('tag'=>'span','class'=>"cell",'style'=>"text-align:center")),
           array(array('data' => 'HtmlTag'), array('tag' => 'div','class' => "header-row row")),
        array(array('row' => 'HtmlTag'),array('tag' => 'div','id' => "table")));
		
	public $decorator3 = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 )),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'span','class' => "col-sm-7 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'span',
									   'class' => "col-sm-4 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-6 col_paddingtop")));
								
								
	public $wmsdecorator = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 )),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'span','class' => "col-sm-7 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'span',
									   'class' => "col-sm-4 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'id'=>'warehouse',
								'class' => "col-sm-6 col_paddingtop")));
								
	public $decorator4 = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 )),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'span','class' => "col-sm-4 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'span',
									   'class' => "col-sm-4 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-4 col_paddingtop")));
								
	
	public $decorator8 = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 )),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'span','class' => "col-sm-2 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'span',
									   'class' => "col-sm-7 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-7 col_paddingtop")));
			
								
	public $decorator6 = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 )),
						'Errors',
						   array(array('data' => 'HtmlTag'), array('tag' => 'span','class' => "col-sm-3 col_paddingtop")),
							  array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
									   'escape' => false,
									   'tag'=>'span',
									   'class' => "col-sm-6 col_paddingtop")),
								array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-6 col_paddingtop")));	
			
			
	public $decoratorstreet = array('ViewHelper',
						array('Description',
						   array('placement' => Zend_Form_Decorator_Abstract::APPEND,
								 )),
						'Errors',
						   array('Label',
								 array('requiredPrefix' => '<b style="color:#FF0000">&nbsp;&nbsp;&nbsp;&nbsp;*</b>',
									   'escape' => false,
									   'tag'=>'span',
									   'class' => "col-sm-4 col_paddingtop")));
	
	public $decStreet = array('ViewHelper',
						'Errors',
						   array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'class' => "col-sm-4 col_paddingtop")));							
								
								
	public $decorator2 = array('ViewHelper',
						'Errors',
						   array(array('row' => 'HtmlTag'),
								array('tag' => 'div',
								'style' => 'text-align:right',
								'class' => "col-sm-2 col_paddingtop")));
				
								
	public function ImageDecorator($path){
		$imagedecorator = array('image',
							array('Description',
								array('placement' => Zend_Form_Decorator_Abstract::APPEND,
									  'tag'=>'i',
									  'href'=>'javascript:void();',
									  'tag'=>'<a>',
									  'title'=> 'Remove Old Logo',
									  'onclick'=>"removeImage();",
									  )),
									  'Errors',array(array('imagerow' => 'HtmlTag'), array('tag' => 'div',
											  'class' => "col-sm-4 col_paddingtop")),
											  		  
									  array(array('imagedata' => 'HtmlTag'),array('tag' => 'img','src' => $path,'width'=>'155px','height'=>'70px','style'=> 'text-align:right')),
									  array(array('data' => 'HtmlTag'), array('tag' => 'div',
											  'class' => "col-sm-4 col_paddingtop")), 
									  array('Label',
										array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
											  'escape' => false, 
											  'tag'=>'div',
											  'class' => "col-sm-4 col_paddingtop")),
									  array(array('row' => 'HtmlTag'), array('tag' => 'div','class' => "col-sm-12 col_paddingtop",'id'=>'oldImange')));
		return $imagedecorator;							  
	}									  
	public $file6Dec = array('File','Errors',
				array('Description', array('escape' => false, 'tag' => '', 'placement' => 'append')),
				array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'itemR')),
				array('Label',array('requiredSuffix' => '<b style="color:#FF0000">*</b>','escape' => false,'style'=>'font-weight: bold;')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop'))
	);																										
}

