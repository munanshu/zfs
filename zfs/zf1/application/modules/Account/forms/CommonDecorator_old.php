<?php

class Account_Form_CommonDecorator extends Zend_Form
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
	// End Radio Decorator
   
   
    public $element2Dec = array('ViewHelper','Errors','Description','label',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-2 col_paddingtop')));
	public $element3Dec = array('ViewHelper','Errors','Description','label',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-3 col_paddingtop')));
	public $element4Dec = array('ViewHelper','Errors','Description','label',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-4 col_paddingtop')));
    public $element6Dec = array('ViewHelper','Errors','Description','label',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop')));
	public $element9Dec = array('ViewHelper','Errors','Description','label',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-9 col_paddingtop')));
									
	public $element12Dec = array('ViewHelper','Errors','Description','label',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-12 col_paddingtop')));
	public $submit2Dec = array('ViewHelper','Errors','Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-2 col_paddingtop')));
	public $submit3Dec = array('ViewHelper','Errors','Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-3 col_paddingtop')));
	public $submit4Dec = array('ViewHelper',
                                    'Errors',
                                    'Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-4 col_paddingtop')));
	public $submit6Dec = array('ViewHelper','Errors','Description',
                                    array('HtmlTag',array('tag' => 'div', 'class' => 'col-sm-6 col_paddingtop')));
									
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
									  'tag' => 'div',
									  'class' => 'ol-lg-6 col-md-6 col-sm-6 b-email')),
									  'Errors',array(array('data' => 'HtmlTag'), array('tag' => 'div','class' => "col-sm-6 col_paddingtop")), 
											array('Label',
												array('requiredPrefix' => '<b style="color:#FF0000">*</b>',
													  'escape' => false, 
													  'tag'=>'div',
													  'class' => "bold_text col-sm-4 col_paddingtop")),
										array(array('row' => 'HtmlTag'),
										array('tag' => 'div', 
												'class' => "col-sm-12 col_paddingtop")));							

}

