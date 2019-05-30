<?php 

namespace Admin\Model\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Admin {

public $username;	
public $password;   
public $passhash;	
protected $inputFilter; 

     public function exchangeArray($data)
     {
         $this->username     = (!empty($data['username'])) ? $data['username'] : null;
         $this->password = (!empty($data['password'])) ? $data['password'] : null;
         $this->passhash = (!empty($data['passhash'])) ? $data['passhash'] : null;
         
     }
     public function getArrayCopy() {
        return get_object_vars($this);
    	}

    public function getInputFilter()
     {
         if (!$this->inputFilter) {
             $inputFilter = new InputFilter();
             
             

             $inputFilter->add(array(
                 'name'     => 'username',
                 'required' => true,
                 'filters'  => array(
                     array('name' => 'StripTags'),
                     array('name' => 'StringTrim'),
                 ),
                 'validators' => array(
                     array(
                         'name'    => 'StringLength',
                         'options' => array(
                             'encoding' => 'UTF-8',
                             'min'      => 1,
                             'max'      => 100,
                         ),
                     ),
                 ),
             ));


             $inputFilter->add(array(
                 'name'     => 'password',
                 'required' => true,
                 'filters'  => array(
                     array('name' => 'StripTags'),
                     array('name' => 'StringTrim'),
                 ),
                 'validators' => array(
                     array(
                         'name'    => 'StringLength',
                         'options' => array(
                             'encoding' => 'UTF-8',
                             'min'      => 1,
                             'max'      => 100,
                         ),
                     ),
                 ),
             ));


              

             $this->inputFilter = $inputFilter;
         }

         return $this->inputFilter;
     }	

}