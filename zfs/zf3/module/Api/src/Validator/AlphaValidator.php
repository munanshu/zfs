<?php


namespace Api\Validator;

use Zend\Validator\AbstractValidator;

class AlphaValidator extends AbstractValidator
{
	
    const AlnumValidator = 'AlnumValidator';

    protected $messageTemplates = array(
        self::AlnumValidator => "%value% should be alphabetic value only"
    );

    public function isValid($value)
    {
        $this->setValue($value);

     	if(preg_match('/^[a-z][A-Z]+$/', $value,$matches) === false){
            $this->error(self::AlnumValidator);
     		return false;
        }

        return true;
    }


	 

}