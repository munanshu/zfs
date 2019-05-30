<?php

namespace Admin\Form;

use Zend\Form\Form;
 
class BackendForm extends Form
{
    public $Categories= array();


    public function setCategories($Categories)
    {
        $this->Categories = $Categories;
        # code...
    }
	
	 public function addCategoryForm($form_name)
	{
		parent::__construct($form_name);
		$this->setAttribute('method' , 'post');
		$this->setAttribute('id' , $form_name);

		$this->add(array(
            'name' => 'category_name',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'Enter Name',
                'class' => 'form-control error',
                'id'=>'category_name'
            ),
            'options' => array(
                'label' => 'Category Name',
            ),
        )); 

        $this->add(array(
            'name' => 'category_desc',
            'attributes' => array(
                'type' => 'textarea',
                'placeholder' => 'Enter Description',
                'class' => 'form-control error',
                'id'=>'category_desc'
            ),
            'options' => array(
                'label' => 'Category Description',
            ),
        )); 

        $this->add(array(
            'name' => 'parent_id',
                'type' => 'Zend\Form\Element\Select',
                'class' => 'form-control error',
                'id'=>'parent_id',
            'options' => array(
                'label' => 'Child Of :',
                // 'empty_option' => 'Select Parent',
                'value_options' => $this->Categories
            ),
        )); 

         $this->add(array(
            'name' => 'category_image',
            'attributes' => array(
                'type' => 'file',
                'class' => 'form-control error',
                'id'=>'category_image',
                // 'multiple' => true
            ),
            'options' => array(
                'label' => 'Category Image'
            ),
        ));
         
        $this->add(array(
            'name' => 'is_Active',
                'type' => 'Zend\Form\Element\Select',
                'class' => 'form-control error',
                'id'=>'is_Active',
            'options' => array(
                'label' => 'Status :',
                'value_options' => [
                	'0' => 'InActive',
                	'1' => 'Active',
                ]
            ),
        )); 

         $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Add',
                'id' => 'submit',
                'class' => 'btn btn-primary'
            ),
        )); 

        return $this; 

	}
}