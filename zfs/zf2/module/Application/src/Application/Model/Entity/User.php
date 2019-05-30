<?php
namespace Application\Model\Entity;

use Zend\InputFilter\Factory as InputFactory;    
use Zend\InputFilter\InputFilter;                
use Zend\InputFilter\InputFilterAwareInterface;  
use Zend\InputFilter\InputFilterInterface;       

class User implements InputFilterAwareInterface
{
    public $id;
    public $user_type_id;
	public $name_title_user;
    public $full_name;
	public $name_title_father;
    public $father_name;
	public $gender;
	public $address; 
	public $country;
    public $state;
	public $city;
	public $rustagi_branch;
    public $username;
	public $password; 
	public $email;
    public $mobile_no;
	public $profession; 
	public $native_place;	
    public $gothra_gothram; 
	 
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id']))     ? $data['id']     : null;
		$this->user_type_id = (isset($data['user_type_id'])) ? $data['user_type_id'] : null; 
		$this->name_title_user = (isset($data['name_title_user'])) ? $data['name_title_user'] : null;
  		$this->full_name = (isset($data['full_name'])) ? $data['full_name'] : null;
		$this->name_title_father = (isset($data['name_title_father'])) ? $data['name_title_father'] : null;
		$this->father_name = (isset($data['father_name'])) ? $data['father_name'] : null;
		$this->gender = (isset($data['gender'])) ? $data['gender'] : null;
  		$this->address = (isset($data['address'])) ? $data['address'] : null; 
		$this->country = (isset($data['country'])) ? $data['country'] : null;
		$this->state = (isset($data['state'])) ? $data['state'] : null;
		$this->city = (isset($data['city'])) ? $data['city'] : null;
		$this->rustagi_branch = (isset($data['rustagi_branch'])) ? $data['rustagi_branch'] : null;
        $this->username = (isset($data['username'])) ? $data['username'] : null; 
  		$this->password = (isset($data['password'])) ? $data['password'] : null;
		$this->email = (isset($data['email'])) ? $data['email'] : null; 
  		$this->mobile_no = (isset($data['mobile_no'])) ? $data['mobile_no'] : null;
		 $this->profession = (isset($data['profession'])) ? $data['profession'] : null;
        $this->native_place = (isset($data['native_place'])) ? $data['native_place'] : null;
		$this->gothra_gothram = (isset($data['gothra_gothram'])) ? $data['gothra_gothram'] : null; 
        
        return $this;
    }
    public function getInputFilter()
    {
        //if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            ))); 
			$inputFilter->add($factory->createInput(array(
                'name'     => 'user_type_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'name_title_user',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            ))); 
			  $inputFilter->add($factory->createInput(array(
                'name'     => 'full_name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            ))); 
			$inputFilter->add($factory->createInput(array(
                'name'     => 'name_title_father',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            ))); 
			$inputFilter->add($factory->createInput(array(
                'name'     => 'father_name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'gender',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'address',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));   
			$inputFilter->add($factory->createInput(array(
                'name'     => 'country',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'state',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'city',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'rustagi_branch',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
				'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email',
                'required' => true,
                 'filters' => [ 
                    ['name' => 'StripTags'], 
                    ['name' => 'StringTrim'], 
                ], 
                'validators' => [ 
                    [ 
                        'name' => 'EmailAddress', 
                        'options' => [ 
                            'encoding' => 'UTF-8', 
                            'min'      => 5, 
                            'max'      => 50, 
                        ], 
                    ], 
                ], 
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'mobile_no',
                'required' => true,
                'filters'  => array(
                 array('name' => 'Int'),
                 ),
                'validators' => array(
                 /* array(
                  'name' => 'Between',
                  'options' => array(
                      'min' => 10,
                      'max' => 11,
                  ),
                 ), */
                ),
            )));
			  $inputFilter->add($factory->createInput(array(
                'name'     => 'profession',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'native_place',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'gothra_gothram',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
            )));  
			
			
            
            $this->inputFilter = $inputFilter;
        //}

        return $this->inputFilter;
    }
     // Add content to this method:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}