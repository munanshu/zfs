<?php



class Supplies_Model_Supplies extends Zend_Custom

{

	/**

     * Fetch supplies products

     * Function : getProductList()

     * Fetch all webshop product list

	 * DATE : 06/01/2017

     **/

	 public $cartsession = NULL;

	 public function getProductList(){

		    try{

				$filterparam =  '1';

				$select = $this->_db->select()

								->from(array('WS'=>WEBSHOP_PRODUCTS),array('*'))

								->joininner(array('WPD'=>WEBSHOP_PRODUCT_DESC),"WPD.product_id=WS.product_id",array('language_id','product_name','product_desc'))

								->joininner(array('LAN'=>LANGUAGE),"WPD.language_id=LAN.language_id",array('LAN.language_name'))

								->where($filterparam." AND WS.isDelete = '0' AND WPD.language_id = 1")

								->order(array("WS.added_date DESC","WPD.product_name ASC")); //echo $select->__tostring();die;

								

				return $this->getAdapter()->fetchAll($select);

				

			  }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			  }



		}

	/**

     * Fetch Product Detail For edit

     * Function : getProductDetailById()

	 * DATE : 13/01/2017

     **/

	public function getProductDetailById(){

		    try{

				$resultArray = array();

				$filterparam = (isset($this->getData['id'])) ? 'WS.product_id='.Zend_Encript_Encription:: decode($this->getData['id']) : '1';

				$select = $this->_db->select()

								->from(array('WS'=>WEBSHOP_PRODUCTS),array('*'))

								->joininner(array('WPD'=>WEBSHOP_PRODUCT_DESC),"WPD.product_id=WS.product_id",array('language_id','product_name','product_desc'))

								->where($filterparam." AND WS.isDelete = '0'")

								->order(array("WPD.language_id ASC")); //echo $select->__tostring();die;

								

				$product = $this->getAdapter()->fetchAll($select);

				foreach($product as $key=>$value){

					if($key == '0'){

						$resultArray['webshop_id'] = $value['webshop_id'];

						$resultArray['eancode'] = $value['eancode'];

						$resultArray['price'] = $value['price'];

						$resultArray['image'] = $value['image'];

					}

					$resultArray['name_'.$value['language_id']] = $value['product_name'];

					$resultArray['desc_'.$value['language_id']] = $value['product_desc'];

				}

				return $resultArray;

			  }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			  }



		}

	/**

     * Fetch webshop list

     * Function : GetShopList()

     * Fetch webshop detail

	 * Date : 11/01/2017

     **/

    public function GetShopList() {

        try {

            $select = $this->_db->select()

								->from(WEBSHOPS, array('*'))

								->where('isStatus =?','1')

								->order('shop_name');

            $result = $this->getAdapter()->fetchAll($select);

        }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

        return $result;

    }

	/**

     * check eancode existance

     * Function : CheckProductEancode()

     * check eancode existance 

     **/

	public function CheckProductEancode(){ 

		 try {

			$where = (isset($this->getData['product_id'])) ? "product_id !=".$this->getData['product_id'] : '1';

            $select = $this->_db->select()

								->from(WEBSHOP_PRODUCTS, array('COUNT(1) AS CNT'))

								->where($where)

								->where('isDelete =?','0')

								->where("eancode='".$this->getData['eancode']."'");

								//echo $select->__tostring();die;

            $result = $this->getAdapter()->fetchrow($select);

        }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

        return $result;

	}

	/**

     * Add Webshop product

     * Function : Addwebshop()

     * Add new webshop product and peoduct description in multiple languages

	 * Date : 12/01/2017

     **/

   	public function addProductDetail(){

	try{

		$barcodecheck = $this->CheckProductEancode();

		if($barcodecheck['CNT']==0){  

			$uploadFiles = array('image');

			$uploadedFiles = $this->uploadFiles($uploadFiles);

			$this->getData['image'] = $uploadedFiles['image'];     

			$added_ip = commonfunction::loggedinIP();

			$added_by = $this->Useconfig['user_id'];

			$create_date = new Zend_Db_Expr('NOW()');

			

			//create thumb image in logicparcel

			if(!empty($this->getData['image'])) {

				mb_internal_encoding("utf-8");

				$imageurl  = BASE_URL.'/public/webshop_product/'.$this->getData['image'];

				$imageInfo = pathinfo($imageurl);

				$imagename = $imageInfo['filename'].'.'.$imageInfo['extension'];

				

				$this->createthumb($imagename,$imageInfo['extension'],'parcel');

			}

			//echo "<pre>";print_r($this->getData); die;

			if($this->getData['image']!=''){	

				$product_id = $this->insertInToTable(WEBSHOP_PRODUCTS, array(array('webshop_id'=>$this->getData['webshop_id'],'eancode'=>$this->getData['eancode'],'price'=>$this->getData['price'],'image'=>$this->getData['image'],'added_by'=>$added_by,'added_ip'=>$added_ip,'created_id'=>5)));

			}else{

				$product_id = $this->insertInToTable(WEBSHOP_PRODUCTS, array(array('webshop_id'=>$this->getData['webshop_id'],'eancode'=>$this->getData['eancode'],'price'=>$this->getData['price'],'added_by'=>$added_by,'added_ip'=>$added_ip,'created_id'=>5)));

			}

			foreach($this->activeLanguage() as $value){

					$this->_db->insert(WEBSHOP_PRODUCT_DESC, array('product_id'=>$product_id,'language_id'=>$value['language_id'],'product_name'=>$this->getData['name_'.$value['language_id']],'product_desc'=>$this->getData['desc_'.$value['language_id']]));

				

			}

			

			// Add Product in warehouse

		    $WMSData = $this->getwmsCompanyBydepot(array('webshop_id'=>$this->getData['webshop_id']));

			

			$CompanyArr = $this->GetWmsCompanyDetails($WMSData);

			

			$apiArr['username'] 	= (isset($CompanyArr->username)) ? $CompanyArr->username : '' ; 

			$apiArr['password'] 	= (isset($CompanyArr->password)) ? $CompanyArr->password : '';

			

			$apiArr['product_name'] = $this->getData['name_1']; //Product Name

			$apiArr['ean_code'] 	= $this->getData['eancode']; // Product Unique EAN Code

			$apiArr['product_desc'] = $this->getData['desc_1']; //Product Description - Optional Field

			$apiArr['weight'] 		= ''; //Product Weight 

			$apiArr['length'] 		= ''; //Product Length

			$apiArr['height'] 		= ''; //Product Height

			$apiArr['width'] 		= ''; //Product Width

			$apiArr['article_code'] = ''; //Product Article Code - Optional Field

			$apiArr['damai_status'] = ''; //Product Damai status - Optional Field

			$apiArr['image_url'] 	= $this->getData['image']; //Product Image Url to Get Image - Optional Field

			$apiArr['action'] 		= 'addproduct';

			//echo "<pre>";print_r($apiArr); die;

			$response = $this->Wmscurl_method($apiArr,$WMSData['warehouse']);

			

			//Write Image on Logicparcel

			if(!empty($this->getData['image'])) {

				mb_internal_encoding("utf-8");

				$imageurl  = BASE_URL.'/public/webshop_product/'.$this->getData['image'];;

				$imageInfo = pathinfo($imageurl);

				$imagename = $imageInfo['filename'].'.'.$imageInfo['extension'];

				$imageDir  = "/var/www/clients/client0/web4/warehouseweb/public/uploads/product";

				if (file_exists($imageDir.'/'.$imagename)) {

					$imagename = time().'_'.$imagename;

				}

				

				//ini_set('display_errors',1);

				//error_reporting(E_ALL);

				$filesname = $imageDir.'/'.$imagename;

				//$filesname  = 'warehouse.dpost.be/public/uploads/product/'.$imagename;

				$imageData = file_get_contents($imageurl);

				$handle    = fopen($filesname,'a+');//echo "<pre>";print_r($filesname);die;

				fwrite($handle, $imageData);

				fclose($handle);

				

				$this->createthumb($imagename,$imageInfo['extension']);

			}

			return true;

		}

		else{

			return false;

		}

	  }catch(Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	  }

	}

	/**

     * Upload image

     * Function : uploadFiles()

     * Upload webshop product image file

	 *Date : 12/01/2017

     **/

	public function uploadFiles($uploadArr){

  

        $adapter = new Zend_File_Transfer_Adapter_Http();

		$files = $adapter->getFileInfo();

		$fileinfo = NULL;

		for($i=0;$i<=count($uploadArr);$i++){

			foreach($files as $key=>$value){

			    if($key==$uploadArr[$i]){

				    $time = time();

					$fileName = $files[$key]['name'];

					if($fileName!=''){

					

					   $adapter->addFilter('Rename',array('target' =>WEBSHOP_IMG .$time.'.'.$fileName,'overwrite' => true));

					   $adapter->receive($fileName);

					   //Zend_FPDF_Image_Support::imageValidation(WEBSHOP_IMG . '/' .$time.'.'.$fileName);

					   $fileinfo[$key] = $time.'.'.$fileName;

					}

					unset($files[$key]);

				}

			}

		}

		return $fileinfo;

	}

	/**

     * Create Thumb Image

     * Function : createthumb()

     * Create thumb for uploaded image

	 *Date : 12/01/2017

     **/

	public function createthumb ($img_fileName,$extension,$flag=NULL) {

		//Image Storage Directory

		if($flag=='parcel'){

			$img_dir 		= WEBSHOP_IMG."thumbs/";

			$image_filePath = WEBSHOP_IMG.$img_fileName;

		}

		else{

			$img_dir 		= "/var/www/clients/client0/web4/warehouseweb/public/uploads/thumbs/product/";

			$image_filePath = "/var/www/clients/client0/web4/warehouseweb/public/uploads/product/".$img_fileName;

		}

		$img_thumb 		= $img_dir . $img_fileName;

		

		//Check the file format before upload

		if(in_array($extension , array('jpg','jpeg', 'gif', 'png', 'bmp'))){

		

			//Find the height and width of the image

			list($gotwidth, $gotheight, $gottype, $gotattr)= getimagesize($image_filePath);     

			

			//---------- To create thumbnail of image---------------

			if($extension=="jpg" || $extension=="jpeg" ){

				$src = imagecreatefromjpeg($image_filePath);

			}

			else if($extension=="png"){

				$src = imagecreatefrompng($image_filePath);

			}

			else{

				$src = imagecreatefromgif($image_filePath);

			}

			list($width,$height) = getimagesize($image_filePath);

			

			$newwidth  = 50; //($gotwidth>=124) ? 124 : $gotwidth;

			$newheight = 50; //round(($gotheight*$newwidth)/$gotwidth);

			$tmp = imagecreatetruecolor($newwidth,$newheight);

			imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);

			//Create thumbnail image

			$createImageSave = imagejpeg($tmp,$img_thumb,100);

		}

	}

	/**

     * Function : getwmsCompanyBydepot()

     * get wmsCompany By depot

	 * Date : 13/01/2017

     **/	



	public function getwmsCompanyBydepot($data){

		

		$where ='1';

		if((isset($data['user_id'])) && ($data['user_id']>0)){

			$where .= " AND depot_id=".$data['user_id'];

		}

		if((isset($data['webshop_id'])) && ($data['webshop_id']>0)){

			$where .= " AND webshop_id=".$data['webshop_id'];

		}

		

		$select = $this->_db->select()

							->from(array(WEBSHOPS),array('wms_company_id','warehouse'))

							->where($where);

		$result = $this->getAdapter()->fetchrow($select);

		//return (isset($result['wms_company_id']) ? $result['wms_company_id'] : 24);

		return $result;

	}

	/**

     * Function : GetWmsCompanyDetails()

     * Get Wms Company Details

	 * Date : 13/01/2017

     **/	

	public function GetWmsCompanyDetails($data){

        try {

			$apiArr['username'] = 'administrator';

			$apiArr['password'] = 'Simethinf';

			$apiArr['comp_id']  = $data['wms_company_id'];

			$apiArr['action']   = 'userdetails';

			

			$curl_response = $this->Wmscurl_method($apiArr,$data['warehouse']);

			$DataArr = json_decode($curl_response);

			//print_r($DataArr);die;

		}catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

		if(isset($DataArr->message)){ 

			return $DataArr->message; 

		}else{

			return '';

		}

    }

	public function Wmscurl_method($data,$warehouse)

	{	//echo"<pre>";print_r($url);//die;

		switch($warehouse) {

			case 1:

				//$url = "http://warehouse.dpost.be/api/warehouse/apirequest/requestData";

				// $url = 'http://warehouse.dpost.be/api/warehouse/apirequest/requestData';
				$url = 'http://demowarehouse.logicparcel.net/api/warehouse/apirequest/requestData';

				break;

			case 2:

				$url = 'http://demowarehouse.logicparcel.net/api/warehouse/apirequest/requestData';

				break;				

			default :

				$url = 'http://demowarehouse.logicparcel.net/api/warehouse/apirequest/requestData';

			break;

		}	

		//Prepare data for posting. That is, urlencode data 

		$post_str = '';

		foreach($data as $key=>$val) {

			if(is_array($val)){

			$val2='';

			 	foreach($val as $key1=>$val1){

					$val2.=$key1.'='.$val1.',';

					}

					$val2 = substr($val2, 0, -1);

					$post_str .= $key.'='.urlencode($val2).'&';

			}

			else{

				$post_str .= $key.'='.urlencode($val).'&';

			}

		}

		$post_str = substr($post_str, 0, -1);

		

		//Initialize cURL and connect to the remote URL

		$ch = curl_init();

		//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

		curl_setopt($ch, CURLOPT_URL, $url);

		//curl_setopt($ch, CURLOPT_URL, 'http://demowarehouse.logicparcel.net/api/warehouse/apirequest/requestData');

		//curl_setopt($ch, CURLOPT_URL, 'http://warehouse.dpost.be/api/warehouse/apirequest/requestData');



		//Instruct cURL to do a regular HTTP POST

		curl_setopt($ch, CURLOPT_POST, TRUE);

		//Specify the data which is to be posted

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);

		//Tell curl_exec to return the response output as a string

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// Execute the cURL session

		$response = curl_exec($ch );

		//Close cURL session and file

		curl_close($ch );

		

		return $response;

	}

   	/**

     * Update product details

     * Function : updateProductDetail()

     * Update webshop product detail

	 * Date : 14/01/2017

     **/

    public function updateProductDetail()

	{

		$dataArray = array();

		$dataArray['product_id'] = Zend_Encript_Encription:: decode($this->getData['id']);	//decode productId

		

		$uploadFiles = array('image');

		$uploadedFiles = $this->uploadFiles($uploadFiles);

		$this->getData['image'] = $uploadedFiles['image'];

		$this->getData['modify_ip'] = commonfunction::loggedinIP();

		$this->getData['modify_by'] = $this->Useconfig['user_id'];

		$this->getData['modify_date'] = new Zend_Db_Expr('NOW()');

		$this->getData['isModify'] = '1';

		$this->getData['price'] = $this->getData['price'];

			//create thumb image in logicparcel

			if(!empty($this->getData['image'])) {

				mb_internal_encoding("utf-8");

				$imageurl  = BASE_URL.'/public/webshop_product/'.$this->getData['image'];

				$imageInfo = pathinfo($imageurl);

				$imagename = $imageInfo['filename'].'.'.$imageInfo['extension'];

				

				$this->createthumb($imagename,$imageInfo['extension'],'parcel');

			}else{

				unset($this->getData['image']);

			}		

		

			$rerult = $this->UpdateInToTable(WEBSHOP_PRODUCTS,array($this->getData),"product_id=".$dataArray['product_id']);

			foreach($this->activeLanguage() as $value){

					$this->_db->update(WEBSHOP_PRODUCT_DESC, array('product_name'=>$this->getData['name_'.$value['language_id']],'product_desc'=>$this->getData['desc_'.$value['language_id']]),"product_id=".$dataArray['product_id']." AND language_id=".$value['language_id']);

				

				

			}

	}

	/**

     * Fetch webshop products

     * Function : getcartdetail()

     * Fetch all webshop product details

     **/

	public function getcartdetail(){  

		try{

			$filterparam ='1';

			if(isset($this->cartsession['webshop']['token']) && count($this->cartsession['webshop']['token'])>0) {

				$filterparam .= " AND WS.product_id IN (".implode(',',array_keys($this->cartsession['webshop']['token'])).")";



				$select = $this->_db->select()

								->from(array('WS'=>WEBSHOP_PRODUCTS),array('*'))

								->joininner(array('WPD'=>WEBSHOP_PRODUCT_DESC),"WPD.product_id=WS.product_id",array('language_id','product_name','product_desc'))

								->joininner(array('LAN'=>LANGUAGE),"WPD.language_id=LAN.language_id",array('LAN.language_name'))

								->where($filterparam." AND WS.isDelete = '0' AND WPD.language_id = 1")

								->order(array("WS.added_date DESC","WPD.product_name ASC")); //echo $select->__tostring();die;

								

				$result = $this->getAdapter()->fetchAll($select);

				return array('Records'=>$result);

			}

			else {

				return array('Records'=>array());

			}

		  }catch (Exception $e) {

			 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  }

	}

	/**     

     * Return list of customers according to lavel, selected depot

     * @param  null| get on the basis of logged in lavel

     * @return customer list in array

	 * 17/01/2017

     */

	public function getCustomerByDepot(){

			$depot_id = '';

		if(isset($this->getData['depot_id'])){

		 $depot_id = Zend_Encript_Encription:: decode($this->getData['depot_id']);

		 }

	     $select = $this->_db->select()

						  ->from(array('UT'=>USERS),array('*'))

						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('user_id','company_name','postalcode','city'))

						  ->where('UT.user_status=?', '1')

						  ->where('UT.delete_status=?', '0')

						  ->where('UT.level_id=?', 5);

		 switch($this->Useconfig['level_id']){

			case 1:

			$select->where('UD.parent_id=?',$depot_id);

			break;

		    case 4:

			  $select->where('UD.parent_id=?',$this->Useconfig['user_id']);

			break;

			case 6:

			$depot_id = $this->getDepotID($this->Useconfig['user_id']);

			$select->where('UD.parent_id=?', $depot_id);

			break;

		}

		

		 $select->order("UD.company_name ASC");				  

		return $this->getAdapter()->fetchAll($select);

	}

	

	/**

     * Fetch webshop orders

     * Function : getWebShopOrderDetail()

     * Fetch all webshop order details

     **/

	public function getWebShopOrderDetail(){

			try{

			   $where = '';

			   if(!empty($this->getData['filter_customer'])){

				  $where.= " AND WSO.user_id ='".Zend_Encript_Encription:: decode($this->getData['filter_customer'])."'";

			   }

			   if(!empty($this->getData['fromdate']) && !empty($this->getData['fromdate'])){

				  $where.= " AND date_format(WSO.added_date,'%Y-%m-%d') BETWEEN '".$this->getData['fromdate']."' AND '".$this->getData['todate']."'";

			   }	

			   $where.= $this->LevelClause();

				$select = $this->_db->select()

								->from(array('WSO'=>WEBSHOP_ORDER),array('*'))

								->joininner(array('WOP'=>WEBSHOP_ORDER_PRODUCTS),"WOP.order_id=WSO.order_id",array('product_id','quantity','price'))

								->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=WSO.user_id",array('company_name'))

								->joinleft(array('SB'=>SHIPMENT_BARCODE),"WSO.barcode_id=SB.barcode_id",array('SB.barcode','SB.tracenr_barcode','SB.checkin_status'))

								->joinleft(array('IT'=>'parcel_invoice'),"IT.invoice_number=WSO.invoice_no",array('IT.file_name as invFile','invoice_date'))

								->where("WSO.isDelete = '0' AND WSO.isStatus = '1'".$where)

								->group(array("WSO.order_id"))

								->order(array("WSO.added_date DESC")); //echo $select->__tostring();die;

				$resultData = $this->getAdapter()->fetchAll($select);

				foreach($resultData as $key=>$order){

					$Orderdata	= $this->GetOrderProductDetails($order['order_id']); 

					$resultData[$key]['totalquantity'] 		= $Orderdata['totalquantity'];

					$resultData[$key]['totalprice'] 		= $Orderdata['totalprice'];

					$resultData[$key]['totalproduct'] 		= $Orderdata['totalproduct'];

				}

				return $resultData;

			  }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			  }

		}

		

	/**

     * Fetch webshop order's product details

     * Function : GetOrderProductDetails()

     * Fetch all webshop order's product details for given orderId

     **/	

	public function GetOrderProductDetails($Id=NULL){

			$select = $this->_db->select()

								->from(array('WOP'=>WEBSHOP_ORDER_PRODUCTS),array('totalproduct'=>'COUNT(DISTINCT(product_id))','totalquantity'=>'sum(quantity)','totalprice'=>'sum(price)'))

								->where("WOP.order_id =".$Id);//echo $select->__tostring();die;

				$result = $this->getAdapter()->fetchRow($select);

				return $result;

		

		}

	/**

     * Fetch order details

     * Function : GetOrderById()

     * fetch webshop order and order's product detail for given id

     **/

	public function GetOrderById(){

	       try{

			$select = $this->_db->select()

							->from(array('WSO'=>WEBSHOP_ORDER),array('order_id','order_number','user_id'))

							->joininner(array('WOP'=>WEBSHOP_ORDER_PRODUCTS),"WOP.order_id=WSO.order_id",array('*'))

							->joininner(array('WP'=>WEBSHOP_PRODUCTS),"WP.product_id=WOP.product_id",array('price as unitprice','image'))

       						->joininner(array('UDT'=>USERS_DETAILS),"UDT.user_id=WSO.user_id",array('company_name'))

							->joininner(array('UST'=>USERS_SETTINGS),"UST.user_id=UDT.user_id",array())

							->joininner(array('WPD'=>WEBSHOP_PRODUCT_DESC),"WPD.product_id=WP.product_id AND WPD.language_id=IF(UST.language_id>0,UST.language_id,1)",array('product_name'))

							->where("WSO.isDelete = '0' AND WSO.isStatus = '1'")

							->where("WSO.order_id='".Zend_Encript_Encription:: decode($this->getData['order_id'])."'");//echo $select->__tostring();die;

				$result = $this->getAdapter()->fetchAll($select);

				return $result;

			  }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			  }

   }

 	/**

     * check order number in system

     * Function : CheckOrderNumber()

     * Date : 21/03/2017

     **/

	public function CheckOrderNumber(){ 

		 try {

			$where = (isset($this->getData['order_id'])) ? "order_id !=".$this->getData['order_id'] : '1';

            $select = $this->_db->select()

								->from(WEBSHOP_ORDER, array('COUNT(1) AS CNT'))

								->where($where)

								->where('isDelete =?','0')

								->where("order_number='".$this->getData['order_number']."'");//echo $select->__tostring();die;

            $result = $this->getAdapter()->fetchrow($select);

        }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

        return $result;

	}

	public function GetProductPrice($data){ 

		 try {

            $select = $this->_db->select()

								->from(WEBSHOP_PRODUCTS, array('price'))

								->where('isStatus =?','1')

								->where('product_id ='.$data['product_id']);

								//echo $select->__tostring();die;

            $result = $this->getAdapter()->fetchrow($select);

        }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

        return $result;

	}

	

	/**

     * Update Order details

     * Function : editorder()

     * Update order details in logicparcel & warehouse system

     **/

    public function editorder(){

	try{

		$this->getData['order_id'] = Zend_Encript_Encription:: decode($this->getData['order_id']);

		$value = $this->CheckOrderNumber();

		if($value['CNT']==0){

			 // check  wms order details (get order status)

			$WmsOrderDetails = $this->CheckWmsOrderStatus();

			//echo "<pre>";print_r($WmsOrderDetails);exit;

			if(!empty($WmsOrderDetails) && (isset($WmsOrderDetails->order_status))){

				if($WmsOrderDetails->order_status<=5){ 

					$productArr = array();

					$select = $this->_db->select()

									   ->from(array('WOP'=>WEBSHOP_ORDER_PRODUCTS),array('product_id'))	

									   ->where('order_id='.$this->getData['order_id']);

							$OldProduct = $this->getAdapter()->fetchAll($select);	//echo $select->__tostring();

					

										

					$modify_ip 		= commonfunction::loggedinIP();

					$modify_by 		= $this->Useconfig['user_id'];

					$modify_date 	= new Zend_Db_Expr('NOW()');

					

					$this->_db->update(WEBSHOP_ORDER, array('order_number'=>trim($this->getData['order_number']),'isModify'=>'1','modify_date'=>$modify_date,'modify_by'=>$modify_by,'modify_ip'=>$modify_ip),"order_id=".$this->getData['order_id']);

					

					foreach($this->getData['product'] as $key=>$value){

						if($this->getData['quantity'][$key]>0){

						  $Productdata = $this->GerProductBarcodeById($value);	// get barcode from logicparcel system

						 

							$pricedata = $this->GetProductPrice(array('product_id'=>$value));

							$Price = $this->getData['quantity'][$key] * $pricedata['price'];						

							

							$productArr[$Productdata['eancode']] = $this->getData['quantity'][$key];	//used in warehouse order 

							

							if(array_key_exists($value,$this->getData['old_product_id'])){

								$this->_db->update(WEBSHOP_ORDER_PRODUCTS,array('quantity'=>$this->getData['quantity'][$key],'price'=>$Price),array("order_id=".$this->getData['order_id'],"product_id=".$value));

							}

							else{

								if($this->getData['quantity'][$key]>0){

									$this->_db->insert(WEBSHOP_ORDER_PRODUCTS, array('order_id'=>$this->getData['order_id'],'product_id'=>$value,'quantity'=>$this->getData['quantity'][$key],'price'=>$Price));;

								}

							}

						}	

					}

					if(count($OldProduct)>0){

						foreach($OldProduct as $product){

							if(!array_key_exists($product['product_id'],$this->getData['old_product_id'])){	

								$this->_db->delete(WEBSHOP_ORDER_PRODUCTS,array("order_id=".$this->getData['order_id'],"product_id=".$product['product_id']));

							}

						}

					}

					//update order in warehouse system

					$userAddress = $this->GetCustomerDetails();

					

					$wmsCompany = $this->getwmsCompanyBydepot(array('user_id'=>$userAddress['parent_id']));

					$CompanyArr = $this->GetWmsCompanyDetails($wmsCompany);

		

					$apiArr['username'] 		= (isset($CompanyArr->username)) ? $CompanyArr->username : '' ; 

					$apiArr['password'] 		= (isset($CompanyArr->password)) ? $CompanyArr->password : ''; 

					$apiArr['ordernumber'] 		= $this->getData['order_number']; //Order Number for the Order

					$apiArr['shipto'] 			= $userAddress['first_name'].''.$userAddress['last_name']; 

					$apiArr['shipcontactname'] 	= $userAddress['company_name']; //Receiver's Contact  Name 

					$apiArr['shipstreet'] 		= $userAddress['address1']; //Receiver's Street Name 

					$apiArr['shiphousenumber'] 	= ''; //Receiver's House number for the Order - Optional Field

					$apiArr['shipaddress'] 		= ''; //Receiver's Street address for the Order - Optional Field

					$apiArr['shipaddress2'] 	= $userAddress['address2']; //Receiver's Street address2 

					$apiArr['shipcity'] 		= $userAddress['city']; //Receiver's City for the Order

					$apiArr['postcode'] 		= $userAddress['postalcode']; //Receiver's Postal Code for the Order

					$apiArr['state'] 			= ''; //Receiver's State Nmae for the Order

					$apiArr['countrycode'] 		= $userAddress['cncode']; //Country Code for the Order

					$apiArr['phone'] 			= $userAddress['phoneno']; //Receiver's Phone Number 

					$apiArr['email'] 			= $userAddress['email'];  //Receiver's Email for the Order - Optional Field

					$apiArr['servicecode'] 		= 'A'; //Service and Additional Service - Optional Field(Default is A)

					$apiArr['senderaddress'] 	= ''; //Sender code  for the Order - Optional Field (Betafresh)

					$apiArr['codprice'] 	    = ''; //Cod Price for the Order - Optional Field

					$apiArr['codcurrency'] 	    = ''; //Cod Price for the Order - Optional Field

					$apiArr['goodstype'] 		= ''; //Goods Type for the Order - Optional Field

					$apiArr['goodsdescription'] = ''; //Goods Description for the Order - Optional Field

					$apiArr['productcode'] 		= $productArr;

					$apiArr['action'] 			= 'updateorder';

					//echo"<pre>";print_r($apiArr);//die;

					$response = $this->Wmscurl_method($apiArr,$wmsCompany['warehouse']);

					//echo"<pre>";print_r($response);die;

					return true;

				}

				else{

				return false;

				}

			}else{	//print_r('$WmsOrderDetails');die;

					$select = $this->_db->select()

									   ->from(array('WOP'=>WEBSHOP_ORDER_PRODUCTS),array('product_id'))	

									   ->where('order_id='.$this->getData['order_id']);

							$OldProduct = $this->getAdapter()->fetchAll($select);	//echo $select->__tostring();

					

										

					$modify_ip 		= commonfunction::loggedinIP();

					$modify_by 		= $this->Useconfig['user_id'];

					$modify_date 	= new Zend_Db_Expr('NOW()');

					

					$this->_db->update(WEBSHOP_ORDER, array('order_number'=>trim($this->getData['order_number']),'isModify'=>'1','modify_date'=>$modify_date,'modify_by'=>$modify_by,'modify_ip'=>$modify_ip),"order_id=".$this->getData['order_id']);

				

					foreach($this->getData['product'] as $key=>$value){

						$pricedata = $this->GetProductPrice(array('product_id'=>$value));

      					$Price = $this->getData['quantity'][$key] * $pricedata['price'];

						if(array_key_exists($value,$this->getData['old_product_id'])){

							$this->_db->update(WEBSHOP_ORDER_PRODUCTS,array('quantity'=>$this->getData['quantity'][$key],'price'=>$Price),array("order_id=".$this->getData['order_id'],"product_id=".$value));

						}

						else{

							if($this->getData['quantity'][$key]>0){

									$this->_db->insert(WEBSHOP_ORDER_PRODUCTS, array('order_id'=>$this->getData['order_id'],'product_id'=>$value,'quantity'=>$this->getData['quantity'][$key],'price'=>$Price));

							}

						}

					}

					if(count($OldProduct)>0){

						foreach($OldProduct as $product){

							if(!array_key_exists($product['product_id'],$this->getData['old_product_id'])){	

								$this->_db->delete(WEBSHOP_ORDER_PRODUCTS,array("order_id=".$this->getData['order_id'],"product_id=".$product['product_id']));

							}

						}

					}

				return true;

			}	

		}else{

			return false;

	   }

	 }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

	 }

	}



	/**

     * fetch order status 

     * Function : CheckWmsOrderStatus()

     * fetch order status form warehouse system , use in edit order

     **/

	public function CheckWmsOrderStatus(){

		

		$this->getData['user_id']  = (isset($this->getData['user_id'])) ? $this->getData['user_id'] : $this->Useconfig['user_id'];

		

		$userAddress = $this->GetCustomerDetails();

		$depotId = $userAddress['parent_id'];

		

		$WMSData = $this->getwmsCompanyBydepot(array('user_id'=>$depotId));

			

		$CompanyArr = $this->GetWmsCompanyDetails($WMSData);

		

		$apiArr['username'] 	= (isset($CompanyArr->username)) ? $CompanyArr->username : '' ; 

		$apiArr['password'] 	= (isset($CompanyArr->password)) ? $CompanyArr->password : ''; 

		$apiArr['ordernumber'] 	= (isset($this->getData['oldorder_number'])) ? $this->getData['oldorder_number'] : $this->getData['order_number']; 

		$apiArr['company'] 		= $WMSData['wms_company_id'];

		$apiArr['action'] 		= 'webshoporderstatus'; 

		//echo"<pre>";print_r($apiArr);die;

		$response = $this->Wmscurl_method($apiArr,$WMSData['warehouse']);

		

		$record = json_decode($response);

		//echo"<pre>";print_r($record);die;

		if(isset($record->message)){ 

			return $record->message; 

		}else{

			return '';

		}

	}

	

    /**

     * fetch customer Details

     * Function : GetCustomerDetails()

     * fetch customer details and used to order customer address detais 

     **/

	public function GetCustomerDetails(){ 

		 try {

            $select = $this->_db->select()

								->from(array('UDT'=>USERS_DETAILS), array('parent_id','first_name','middle_name','last_name','email','customer_name','address1','address2','city','postalcode','phoneno','company_name'))

								->joininner(array('CT'=>COUNTRIES),"CT.country_id=UDT.country_id",array('cncode'))

								->where('UDT.user_id ='.$this->getData['user_id']);//echo $select->__tostring();die;

            $result = $this->getAdapter()->fetchrow($select);

        }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

        return $result;

	}

	/**

     * fetch product barcode for given productId

     * Function : GerProductBarcodeById()

     * fetch barcode form logicparcel system 

     **/

	public function GerProductBarcodeById($id){

		 try {

            $select = $this->_db->select()

								->from(WEBSHOP_PRODUCTS, array('eancode'))

								->where('product_id ='.$id);

								//echo $select->__tostring();die;

            $result = $this->getAdapter()->fetchrow($select);

        }catch (Exception $e) {

				 $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

        return $result;

	}

	/**

     * Add order in orderlist

     * Function : addorder()

     * Date : 22/03/2017 

     **/

	public function addorder(){

		$apiArr = array();

		$this->getData['order_number'] = date('Ymdhis');

		$this->getData['user_id']  = (isset($this->getData['customer'])) ? $this->getData['customer'] : $this->Useconfig['user_id'];

		$depotId = 188;	// parcelnl

		$userAddress = $this->GetCustomerDetails();

		if($this->Useconfig['level_id']==1){

			$depotId  =  $this->getData['depot'];

		}

		if($this->Useconfig['level_id']==4){

			$depotId =  $this->Useconfig['user_id'];

		}

		if($this->Useconfig['level_id']==5){

			$depotId  	 =  $userAddress['parent_id'];

		}

		if($this->Useconfig['level_id']==6){

			$depotId  	 =  $this->getDepotID($this->Useconfig['user_id']);

		}

		if($this->Useconfig['level_id']==10){

			$depotId  	 =  $this->getDepotID($this->Useconfig['user_id']);

		}

		

		$productArr = array();

		

		$added_ip = commonfunction::loggedinIP();

		$added_by = $this->Useconfig['user_id'];

		 	

	    $order_id = $this->insertInToTable(WEBSHOP_ORDER, array(array('order_number'=>$this->getData['order_number'],'user_id'=>$this->getData['user_id'],'added_by'=>$added_by,'added_ip'=>$added_ip)));

		// add order's product 

		foreach($this->getData['token'] as $key=>$value){

			$prdQuantity = $this->getData['qantity'][$key];

			if($prdQuantity>0){

				$prdID 		= Zend_Encript_Encription:: decode($value);

				$pricedata 	= $this->GetProductPrice(array('product_id'=>$prdID));

				$Price		= $this->getData['qantity'][$key] * $pricedata['price'];

 				

				$Productdata = $this->GerProductBarcodeById($prdID);

				$productArr[$Productdata['eancode']] = $prdQuantity;	// used for warehouse update

				

				$this->_db->insert(WEBSHOP_ORDER_PRODUCTS, array('order_id'=>$order_id,'product_id'=>$prdID,'quantity'=>$prdQuantity,'price'=>$Price));

			}

	    }

	   

	   	// add order in warehouse

		$wmsCompany = $this->getwmsCompanyBydepot(array('user_id'=>$depotId));

		$CompanyArr = $this->GetWmsCompanyDetails($wmsCompany);

		//echo"<pre>";print_r($userAddress);//die;

	   	$apiArr['username'] 		= (isset($CompanyArr->username)) ? $CompanyArr->username : '' ; 

		$apiArr['password'] 		= (isset($CompanyArr->password)) ? $CompanyArr->password : ''; 

		$apiArr['ordernumber'] 		= $this->getData['order_number']; //Order Number for the Order

		$apiArr['shipto'] 			= $userAddress['first_name'].''.$userAddress['last_name']; //Receiver's Name for the Order

		$apiArr['shipcontactname'] 	= $userAddress['company_name']; //Receiver's Contact  Name for the Order - Optional Field

		$apiArr['shipstreet'] 		= $userAddress['address1']; //Receiver's Street Name for the Order

		$apiArr['shiphousenumber'] 	= ''; //Receiver's House number for the Order - Optional Field

		$apiArr['shipaddress'] 		= ''; //Receiver's Street address for the Order - Optional Field

		$apiArr['shipaddress2'] 	= $userAddress['address2']; //Receiver's Street address2 for the Order - Optional Field

		$apiArr['shipcity'] 		= $userAddress['city']; //Receiver's City for the Order

		$apiArr['postcode'] 		= $userAddress['postalcode']; //Receiver's Postal Code for the Order

		$apiArr['state'] 			= ''; //Receiver's State Nmae for the Order

		$apiArr['countrycode'] 		= $userAddress['cncode']; //Country Code for the Order

		$apiArr['phone'] 			= $userAddress['phoneno']; //Receiver's Phone Number for the Order - Optional Field

		$apiArr['email'] 			= $userAddress['email'];  //Receiver's Email for the Order - Optional Field

		$apiArr['servicecode'] 		= ''; //Service and Additional Service for the Order - Optional Field(Default is A)

		$apiArr['senderaddress'] 	= ''; //Sender code  for the Order - Optional Field (Betafresh)

		$apiArr['codprice'] 	    = ''; //Cod Price for the Order - Optional Field

		$apiArr['codcurrency'] 	    = ''; //Cod Price for the Order - Optional Field

		$apiArr['goodstype'] 		= ''; //Goods Type for the Order - Optional Field

		$apiArr['goodsdescription'] = ''; //Goods Description for the Order - Optional Field

		$apiArr['productcode'] 		= $productArr;

		

		$response = $this->Wmscurl_method($apiArr,$wmsCompany['warehouse']);

		//echo"<pre>";print_r($response);die;

	   return TRUE;

	}

   /**

	*Faetch active Language

	*Function : activeLanguage()

	*Fetch All Active languages of application

	**/

	public function activeLanguage(){

		$select = $this->_db->select()

							->from(LANGUAGE,array('*'))

							->where('status = "1"');

		//echo $select->__toString();die;

		$result = $this->getAdapter()->fetchAll($select);

		return $result; 

	}	

}



