<?php
class Supplies_Form_Supplies extends Application_Form_CommonDecorator
{
	public $shopList = NULL;
	public $LanguageList = NULL;
    /**
     * Function : addeditshopproductForm()
	 * Params : NULL
     * add edit product shop in supplies module
	 * Date : 11/01/2017
     * */
    public function addeditshopproductForm(){
			$this->setName('shopproduct');
			$this->setAttrib('class', 'inputbox');
			$this->setMethod('post');
			$this->setDecorators($this->form12Dec);
			$shopList = $this->createElement('select', 'webshop_id')
									->setLabel('Webshop')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->addMultiOptions(array(''=>'Select Shop'))
									->addMultiOptions(commonfunction::scalarToAssociative($this->shopList,array('webshop_id','shop_name')))
					->setDecorators($this->element6Dec);
			$eancode = $this->createElement('text', 'eancode')
									->setLabel('Eancode')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->setDecorators($this->element6Dec);
			$this->addElements(array($shopList,$eancode));
			foreach($this->LanguageList as $language){
			    if($language['language_id'] == '1'){
				$this->addElements(array($this->createElement('text', 'name_'.$language['language_id'])
										->setLabel('Product Name In '.$language['language_name'])
										->setAttrib("class","inputfield")
										->setRequired(true)
										->addValidator('NotEmpty')
										->setDecorators($this->element6Dec),
										
										$this->createElement('textarea', 'desc_'.$language['language_id'])
										->setLabel('Product Desc. In '.$language['language_name'])
										->setAttrib('COLS', '20')
										->setAttrib('ROWS', '1')
										->setRequired(true)
										->addValidator('NotEmpty')
										->setAttrib("class","inputfield")
										->setDecorators($this->element6Dec)
										
										));
			  }else{
				$this->addElements(array($this->createElement('text', 'name_'.$language['language_id'])
										->setLabel('Product Name In '.$language['language_name'])
										->setAttrib("class","inputfield")
										->setDecorators($this->element6Dec),
										$this->createElement('textarea', 'desc_'.$language['language_id'])
										->setLabel('Product Desc. In '.$language['language_name'])
										->setAttrib('COLS', '20')
										->setAttrib('ROWS', '1')
										->setAttrib("class","inputfield")
										->setDecorators($this->element6Dec)
									));
			  
			  }
			}
			
			$price = $this->createElement('text', 'price')
									->setLabel('Price ')
									->setAttrib("class","inputfield")
									->setRequired(true)
									->addValidator('NotEmpty')
									->setDecorators($this->element6Dec);
			$image = $this->createElement('file', 'image')
									->setLabel('Product Image')
									->setAttrib("class","inputfield")
									->setDecorators($this->file6Dec);
			
			$submit = $this->createElement('submit', 'submit')
							->setAttrib("class","btn btn-danger btn-round")
							->setLabel('Add Product')
							->setIgnore(true)
							->setDecorators($this->submit3Dec);
			$this->addElements(array($price,$image,$submit));
			return $this;
    }


}

