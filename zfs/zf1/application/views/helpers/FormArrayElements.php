    <?php
    /**
     * Helper for rendering mulpile form array elements.
     * @see http://stackoverflow.com/questions/13157113/zf-form-array-field-how-to-display-values-in-the-view-correctly
     */
    class Zend_View_Helper_FormArrayElements extends Zend_View_Helper_Abstract
    {
    	private $elements;
     
    	private $partial;
     
    	/**
    	 * Creates a helper instance.
    	 * @param array $arrayElements array of elements that are used in the given partial script; they must be an array fields
    	 * @param string $partial script to render for each value in the given form array elements
    	 * @return Website_View_Helper_FormArrayElements
    	 */
    	public function formArrayElements(array $arrayElements, $partial){
    		// check some preconditions
    		if(count($arrayElements) == 0)
    			throw InvalidArgumentException('Elements array must contain at least one element.');
    		foreach($arrayElements as $element){
    			if(!$element instanceof Zend_Form_Element)
    				throw InvalidArgumentException('Elements of class ' . get_class($element) . ' is not a form element.');
    			/* @var $element Zend_Form_Element */
    			if(!$element->isArray())
    				throw InvalidArgumentException('Element [' . $element->getName() . '] is not an array.');
    		}
    		$this->elements = $arrayElements;
    		$this->partial = $partial;
    		return $this;
    	}
     
    	/**
    	 * Render the fields.
    	 */
    	public function render(){
    		$values = array();
    		// only if the form were submitted we need to validate fields' values
    		// and display errors next to them; otherwise when user enter the page
    		// and render the form for the first time - he would see Required validator errors
    		$needsValidation = false;
    		foreach($this->elements as $element){

                // echo "<pre>";print_r($element->getValue());die;

    			$v = $element->getValue();
    			if(is_array($v)){
    				$needsValidation = true;
    				$values[$element->getName()] = $v;
    			}
    			else{
    				// print empty fields when the form is displayed the first time
    				$values[$element->getName()] = array('');
    			}
    		}




            // iterate over all values
            $rendered = '';
                $i =1;
            foreach(array_keys(current($values)) as $valueKey){


                $elements = array();
                $row = ($i%2==0)?  'even': 'odd' ; 
                $rendered .='<div class="row '.$row.'" rowid="'.$i.'" rowname="invrow_'.$i.'">';

                foreach($this->elements as $element){
                    
                    if($element->getName() == 'definition'){
                        $element->setAttrib('id','definition_'.$i);
                    }
                    if($element->getName() == 'ledger_invoice_number'){
                        $element->setAttrib('id','ledger_invoice_number_'.$i);
                    }
                    if($element->getName() == 'ledger_id'){
                        $element->setAttrib('id','ledger_'.$i);
                    }
                    if($element->getName() == 'btw'){
                        $element->setAttrib('id','btw_'.$i);
                    }
                    if($element->getName() == 'credit'){
                        $element->setAttrib('id','credit_'.$i);
                    }
                    if($element->getName() == 'debit'){
                        $element->setAttrib('id','debit_'.$i);
                    }
                    
                    $currentValue = $values[$element->getName()][$valueKey];
                    if($needsValidation)
                        $element->isValid($currentValue);
                    $elements[$element->getName()] = (string)$element->setValue($currentValue);
                }
                 $i++;
            // echo "<pre>";print_r($this->partial);die;
    			$rendered .= $this->view->partial($this->partial, $elements);
                $rendered .='</div> ';
    		}
            // echo $rendered;die;
            // die;
    		return $rendered;
    	}
     
    	public function __toString(){
    		return $this->render();
    	}
    }