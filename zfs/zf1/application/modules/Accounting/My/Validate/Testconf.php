<?php


class My_Validate_Testconf extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';
 
    protected $_messageTemplates = array(
        self::NOT_MATCH => 'value should lie between 1 to 100'
    );
 
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
 
         echo "SDfsdf";die;
            if ($value>0
                && $value<=100)
            {
                return true;
            }
         
 
        $this->_error(self::NOT_MATCH);
        return false;
    }
}