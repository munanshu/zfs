<?php 



class Form_Validate_Duplicacy extends Zend_Validate_Abstract
{
    const EMAIL_EXISTS='';
    protected $_messageTemplates = array(
        self::EMAIL_EXISTS=>'Email "%value%" exists'
    );
    public function __construct(Model_User $model)
    {
        $this->_model = $model;
    }
    public function isValid($value, $context=null)
    {
        $this->_setValue($value);
           echo $value;die; 
        //insert logic to check here...
        if (!error)
            return true;
 
        $this->_error(self::EMAIL_EXISTS); 
        return false;
    }
}