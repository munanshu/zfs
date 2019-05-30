<?php


namespace Api\Validator;

use Zend\Validator\AbstractValidator;

class AlnumValidator extends AbstractValidator
{
	
    const AlnumValidator = 'AlnumValidator';

    protected $messageTemplates = array(
        self::AlnumValidator => "%value% should be alphanumric value"
    );

    public function isValid($value)
    {
        $this->setValue($value);

     	if(preg_match('/[a-z][A-Z][0-9]$/', $value) === false){
            $this->error(self::AlnumValidator);
     		return false;
        }

        return true;
    }


	 

}