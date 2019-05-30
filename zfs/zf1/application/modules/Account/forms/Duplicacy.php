<?php 



class Account_Form_Duplicacy extends Zend_Validate_Abstract
{
    public $error = true;
    const VALUE_EXISTS='';
    protected $_messageTemplates = array(
        self::VALUE_EXISTS=>'Value "%value%" is repeated'
    );
    public function __construct($model,$field)
    {
        $this->_model = $model;
        $this->_field = $field;
    }
    public function isValid($values, $context=null)
    {
        $this->_setValue($values);

            if(strlen($values)<5)
              $this->error = false;

        //   // if(is_array($context['postcode_start']) && !empty($context['postcode_start']) ) { 
        //   //       $valuesArr = array_merge($context['postcode_start'],$context['postcode_end']);
        //   //       $vals = array_count_values($valuesArr);  
        //   //          // print_r($vals);die; 
        //   //       if($vals[$value]>1){
        //   //           echo $value."---".$vals[$value]."<br>";
        //   //           $this->error = false;
        //   //       }
        //   //       $valuesArr = array();
        //   //       $vals = array();
        //   //   } 

            // $ele = $this->getelement();
            // echo "<pre>"; print_r($ele);
             $this->setMessage('dfgdfgdfg'); 
            
        if ($this->error)
            return true;
 
        $this->_error(self::VALUE_EXISTS,$value); 
        return false;
    }
}