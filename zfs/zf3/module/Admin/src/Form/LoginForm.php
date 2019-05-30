<?php


namespace Admin\Form;

use Zend\Form\Form;

 
class LoginForm extends Form
{
	
	public function __construct()
	{
		parent::__construct('admin_form');
		$this->setAttribute('method' , 'post');
		$this->setAttribute('id' , 'admin_form');

		$this->add(array(
            'name' => 'username',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'Enter Username',
                'class' => 'form-control error',
                'id'=>'username'
            ),
            'options' => array(
                'label' => NULL,
            ),
        )); 

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'placeholder' => 'Enter Password',
                'class' => 'form-control error',
                'id'=>'password'
            ),
            'options' => array(
                'label' => NULL,
            ),
        )); 

         $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Log in',
                'id' => 'submit',
                'class' => 'btn btn-default'
            ),
        )); 

	}
}
