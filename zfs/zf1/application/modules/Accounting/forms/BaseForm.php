<?php 

class BaseForm extends Zend_Form_SubForm
{
    public function init ()
    {
        $this->addElement('text', 'user');
        $this->addElement('password', 'pwd');
    }
}

