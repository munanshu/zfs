<?php


namespace Admin\Filter;

use Zend\InputFilter\InputFilter;
 
class AbstractFilter extends InputFilter
{
	
	protected $inputFilter;

	public function setAuthFilter($value='')
	{

		$this->add( array(
				'name' => 'username',
				'required' => true,
				'validators' => [

					[
						'name' => 'Zend\Validator\NotEmpty'
					]

				]

			)
		);

		$this->add( array(
				'name' => 'password',
				'required' => true,
				'validators' => [

					[
						'name' => 'Zend\Validator\NotEmpty'
					]

				]

			)
		);


	} 


	public function setCategoryFilter($value='')
	{

		$this->add( array(
				'name' => 'category_name',
				'required' => true,
				'validators' => [

					[
						'name' => 'Zend\Validator\NotEmpty'
					]

				]

			)
		);

		$this->add( array(
				'name' => 'category_desc',
				'required' => true,
				'validators' => [

					[
						'name' => 'Zend\Validator\NotEmpty'
					]

				]

			)
		);


	} 


}