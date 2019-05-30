<?php

  

class Invoice_Model_Invoicemanager  extends Zend_Custom

{

   public $Invoice = array();

   //Invoice Pdf Object

   public $InvPdfObj = NULL;

    public function getInvoice(){ 

	   switch($this->Useconfig['level_id']){

	      case 1:

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.depot_price) as price');

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0";

			 if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

			    $where .= " AND AT.parent_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

			 }

		  break;

		  case 4:

		     $group = 'AT.user_id';

			 $coloumn = array('AT.user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.customer_price) as price');

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$this->Useconfig['user_id']."'";

			 if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

			    $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

			 }

		  break;

		  case 6:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.user_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.customer_price) as price');

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$parent_id."'";

			 if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

			    $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

			 }

		  break;

		  case 11:

		    // $parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.depot_price) as price');

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0";

			 if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

			    $where .= " AND AT.parent_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

			 }

		  break;

		 

	   }

	   $select = $this->_db->select()

			 					->from(array('BT'=>SHIPMENT_BARCODE),$coloumn)

								->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array())

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array(''))

								->where("BT.checkin_status='1'")

								->where($where)

								->group($group)

								->order("AT.company_name");//print_r($select->__toString());die;

		$result = $this->getAdapter()->fetchAll($select);

		return $result;

	}

	

	public function getMailInvoice(){

	   switch($this->Useconfig['level_id']){

	      case 1:

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(MS.quantity) as quantity','SUM(MS.depot_price) as price');

			 $where = "MS.depot_price>0 AND MS.depot_invoice_status='0' AND AT.parent_id<>0";

		  break;

		  case 4:

		     $group = 'AT.user_id';

			 $coloumn = array('AT.user_id','SUM(MS.quantity) as quantity','SUM(MS.customer_price) as price');

			 $where = "MS.customer_price>0 AND MS.customer_invoice_status='0' AND AT.parent_id='".$this->Useconfig['user_id']."'";

		  break;

		  case 6:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.user_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(MS.quantity) as quantity','SUM(MS.customer_price) as price');

			 $where = "MS.customer_price>0 AND MS.customer_invoice_status='0' AND AT.parent_id='".$parent_id."'";

		  break;

		  case 11:

		      //$parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(MS.quantity) as quantity','SUM(MS.depot_price) as price');

			 $where = "MS.depot_price>0 AND MS.depot_invoice_status='0' AND AT.parent_id<>0";

		  break;

		 

	   }
			if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		       $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		   }
		   $select = $this->_db->select()

									->from(array('MS'=>MAIL_POST),$coloumn)

									->joinleft(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=MS.'.ADMIN_ID,array())

									->where("MS.checkin_status='1'")

									->where($where)

									->group($group)

									->order("AT.company_name");

			//print_r($select->__toString());die;

			$results = $this->getAdapter()->fetchAll($select);

			$records = array();

			foreach($results as $result){

			  $records[$result[ADMIN_ID]] = $result;

			}

		return $records;

	}

	

	public function getExtraInvoice(){

		  switch($this->Useconfig['level_id']){

			  case 1:

				 $group = 'AT.parent_id';

				 $coloumn = array('AT.parent_id as user_id','SUM(EH.quantity) as quantity','SUM(EH.price) as price');

				 $where = "AT.level_id=4";

			  break;

			  case 4:

				 $group = 'AT.user_id';

				 $coloumn = array('AT.user_id','SUM(EH.quantity) as quantity','SUM(EH.price) as price');

				 $where = "AT.parent_id='".$this->Useconfig['user_id']."'";

			  break;

			  case 6:

				 $parent_id = $this->getDepotID($this->Useconfig['user_id']);

				 $group = 'AT.user_id';

				 $coloumn = array('AT.parent_id as user_id','SUM(EH.quantity) as quantity','SUM(EH.price) as price');

				 $where = "AT.parent_id='".$parent_id."'";

			  break;

			  case 11:

				 //$parent_id = $this->getDepotID($this->Useconfig['user_id']);

				 $group = 'AT.parent_id';

				 $coloumn = array('AT.parent_id as user_id','SUM(EH.quantity) as quantity','SUM(EH.price) as price');

				 $where = "AT.parent_id=4";

			  break;

	   }

		  if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){
		       $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";
		 }
			
		  $select = $this->_db->select()

			 					->from(array('EH'=>INVOICE_EXTRA_HEAD),array('*'))

								->joinleft(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=EH.'.ADMIN_ID,array())

								->where("invoice_number=?",0)

								->where($where);

		 			//print_r($select->__toString());die;

		  $results = $this->getAdapter()->fetchAll($select);

		$records = array();

		foreach($results as $result){

		  $records[$result[ADMIN_ID]] = $result;

		}

		return $records;

		 

	}

	

	public function GenerateInvoice(){

		echo "<pre>"; print_r($this->getData);die;	   

	   if(!empty($this->getData['invoice_ids'])){

	      foreach($this->getData['invoice_ids'] as $invoiceusers){
			$this->Invoice  = array();
			$this->Invoice['type'] = $this->getData['type'];
		    $this->InvPdfObj = new Zend_Labelclass_InvoicePdf('P','mm','a4');

		    $this->Invoice['user_id'] = Zend_Encript_Encription::decode($invoiceusers);

			$this->generateSingleInvoice();

		  }

	   }else{ 
           $this->Invoice  = array();
		   $this->Invoice['type'] = $this->getData['type'];
	       $this->InvPdfObj = new Zend_Labelclass_InvoicePdf('P','mm','a4');

	       $this->Invoice['user_id'] = Zend_Encript_Encription::decode($this->getData['invoice_id']);

		   $this->generateSingleInvoice(); 

		   

	   }

	   

	   

	}

	public function generateSingleInvoice(){

	   global $objSession;

	   switch($this->Useconfig['level_id']){

	      case 1:

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.depot_price) as price','CT.continent_id');

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0 AND AT.parent_id='".$this->Invoice['user_id']."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.depot_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight','BT.forwarder_id');

		  break;

		  case 4:

		     $group = 'AT.user_id';

			 $coloumn = array('AT.user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.customer_price) as price','CT.continent_id');

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$this->Useconfig['user_id']."' AND AT.user_id='".$this->Invoice['user_id']."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.customer_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight','BT.forwarder_id');

		  break;

		  case 6:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.user_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.customer_price) as price','CT.continent_id');

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$parent_id."' AND AT.user_id='".$this->Invoice['user_id']."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.customer_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight','BT.forwarder_id');

		  break;

		  case 11:

		    // $parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(BT.weight) as weight','SUM(1) as quantity','SUM(BT.depot_price) as price','CT.continent_id');

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0 AND AT.parent_id='".$this->Invoice['user_id']."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.depot_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight','BT.forwarder_id');

		  break;

		 

	   }

	   $select = $this->_db->select()

			 					->from(array('BT'=>SHIPMENT_BARCODE),$coloumn)

								->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array())

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array(''))

								->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array())

								->where("BT.checkin_status='1'")

								->where($where)

								->group($group)

								->group(new Zend_Db_Expr("CT.continent_id=2"));//print_r($select->__toString());die;

		$results = $this->getAdapter()->fetchAll($select);

		$this->Invoice['quantityEU'] = 0 ;

		$this->Invoice['invoice_amountEU'] = 0 ;

		$this->Invoice['weightEU'] = 0 ;

		$this->Invoice['quantityNEU'] = 0 ;

		$this->Invoice['invoice_amountNEU'] = 0 ;

		$this->Invoice['weightNEU'] = 0 ;

		foreach($results as $Invoicedata){

			if($Invoicedata['continent_id']==2){

			   $this->Invoice['quantityEU']    	= $Invoicedata[QUANTITY]; 

			   $this->Invoice['invoice_amountEU'] = $Invoicedata['price'];

			   $this->Invoice['weightEU'] 		= $Invoicedata[WEIGHT];

			 }else{

			   $this->Invoice['quantityNEU']   	= $Invoicedata[QUANTITY]; 

			   $this->Invoice['invoice_amountNEU']= $Invoicedata['price'];

			   $this->Invoice['weightNEU'] 		= $Invoicedata[WEIGHT];

			 }

		}

	   $select = $this->_db->select()

			 					->from(array('BT'=>SHIPMENT_BARCODE),$column1)

								->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('BT.barcode_id'))

								->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array())

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array(''))

								->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array('cncode'))

								->where("BT.checkin_status='1'")

								->where($where)

								->order("checkin_date ASC");

	   $this->Invoice['ParcelList'] = $this->getAdapter()->fetchAll($select);

		

	   $this->InvoiceSenderReceiver();

	   $this->getMailInvoiceAmount();

	   $this->getExtraAmount();

	   $this->getCodAdjustmentAmount($this->Invoice['user_id']);

	   $this->getWebshopAmount($this->Invoice['user_id']);

	   $this->CalculateInvoice();

	   $this->InvoiceNumber();

	   $this->InvPdfObj->outputparam = NULL;

	   $this->InvPdfObj->outputparam = $this->Invoice;

	   $this->InvPdfObj->labelInvoice();

	   $this->Invoice['create_by'] = $this->Useconfig['user_id'];

	   $this->Invoice['create_ip'] = commonfunction::loggedinIP();

	   $this->Invoice['file_name'] =  'INVOICE_'.$this->Invoice['invoice_number'].'_'.Zend_Encript_Encription::encode($this->Invoice['invoice_number'].'_'.date('YmdHi')).'.pdf';

	   if($this->getData['type']==1 || $this->getData['type']==2 || $this->getData['type']==4){

	    if(!is_dir(INVOICE_SAVE.date('Y'))){

		   mkdir(INVOICE_SAVE.date('Y'),0777);

		}

		if(!is_dir(INVOICE_SAVE.date('Y').'/'.date('M'))){

		   mkdir(INVOICE_SAVE.date('Y').'/'.date('M'),0777);

		}

		$savefile = INVOICE_SAVE.date('Y').'/'.date('M').'/'.$this->Invoice['file_name'];

		$openfile = INVOICE_OPEN.date('Y').'/'.date('M').'/'.$this->Invoice['file_name'];

	 }

		//ob_end_clean();

		//print_r('Hi');die;

		if($this->getData['type']==3){  

		   ob_end_clean();

		   //$this->InvPdfObj->Output('Test_'.$this->Invoice['invoice_number'].date('his').'.pdf','D'); 

		    $this->InvPdfObj->PopUpLabel('Test_'.$this->Invoice['invoice_number'].date('his').'.pdf');

		}else{

	   		$this->InvPdfObj->Output($savefile,'F');

		}

	   $objSession->Invoicefile = $openfile;

	   if($this->getData['type']==1 || $this->getData['type']==2 || $this->getData['type']==4){
		   $this->Invoice['create_date'] = date('Y-m-d H:i:s');
	       $this->insertInToTable(INVOICE,array($this->Invoice));

		   if($this->getData['type']!=4){

		     $this->insertInToTable(INVOICE_FINANCIAL,array($this->Invoice));
			 $this->InvoiceEmail();
		   }

		   $this->InvoiceNumberBarcodeUpdate();

	   }

	}

	public function CalculateInvoice(){

	    //Fuel Surcharge

		$this->Invoice['surcharge_amount'] = 0;

		$this->Invoice['fs_amountEU'] = 0;

		$this->Invoice['fs_amountNEU'] = 0;

		 if($this->Invoice['fuel_surcharge']>0 && $this->Invoice['fuel_surcharge_status']=='1' && $this->Invoice['parent_id']!=1){

			$this->Invoice['fs_amountEU'] = (($this->Invoice['invoice_amountEU']+$this->Invoice['MSNLAmount']) * $this->Invoice['fuel_surcharge'])/100;

			$this->Invoice['fs_amountNEU']= (($this->Invoice['invoice_amountNEU']+$this->Invoice['MSNEUAmount'])*$this->Invoice['fuel_surcharge'])/100;

			$this->Invoice['surcharge_amount'] = $this->Invoice['fs_amountEU'] + $this->Invoice['fs_amountNEU'];

	   }

		$this->Invoice['Subtotal'] 	= $this->Invoice['invoice_amountEU'] 

											+ $this->Invoice['invoice_amountNEU'] 

											+ $this->Invoice['extraAmount']

											+ $this->Invoice['MSNLAmount']

											+ $this->Invoice['MSNEUAmount']

											+ $this->Invoice['surcharge_amount']

											+ $this->Invoice['ws_amount']

											- $this->Invoice['CodAmount'];

		//Btw Charges

		 if(($this->Invoice['Rbtwcharge']=='0' && $this->Invoice['Rbtwno']!='') && ($this->Invoice['Rcountry']!=$this->Invoice['Scountry'])){

			   $this->Invoice['btwEU'] = 0;

               $this->Invoice['MSBTWAmount'] = 0;

			   $this->Invoice['nochargeble_btw_label'] 	= $this->Invoice['btw_notcharge_text'];

				$this->Invoice['fs_btw'] = 0;

			   //Btw Chargeble and Non Chargeble Price

			   $this->Invoice['GrondslagPrice']  =  $this->Invoice['extraAmount']+$this->Invoice['ws_amount'];



			   $this->Invoice['NonBtwTotalPrice'] = $this->Invoice['invoice_amountNEU']

													+ $this->Invoice['invoice_amountEU']

													+ $this->Invoice['nbcAmount']

													+ $this->Invoice['MSNLAmount']

													+ $this->Invoice['MSNEUAmount']

													+(- $this->Invoice['CodAmount']);

														

				$nobtwchargebleprice8020	   =  $this->Invoice['invoice_amountNEU']				

												+ $this->Invoice['invoice_amountEU']		

												+ $this->Invoice['MSNLAmount']				

												+ $this->Invoice['MSNEUAmount'];

			   //$this->Invoice['ExtraText'] = $this->Invoice['btw_notcharge_text'];

			   $this->Invoice['fs_amount_eu'] = 0;

			   $this->Invoice['fs_amount_neu'] = $this->Invoice['fs_amountEU'] + $this->Invoice['fs_amountNEU'];

			 }else{

			   $this->Invoice['btwEU']      	   = $this->Invoice['invoice_amountEU'] * $this->Invoice['btw_rate']/100;

			   $this->Invoice['fs_btw'] = ($this->Invoice['fs_amountEU']* $this->Invoice['btw_rate'])/100;

			   //Btw Chargeble and Non Chargeble Price

			   $this->Invoice['GrondslagPrice']  =  $this->Invoice['invoice_amountEU'] 

			   										    + $this->Invoice['bcAmount']

														+ $this->Invoice['MSNLAmount']+$this->Invoice['ws_amount'];



			   $this->Invoice['NonBtwTotalPrice']=  $this->Invoice['invoice_amountNEU'] 

													+ $this->Invoice['nbcAmount']

													+ $this->Invoice['MSNEUAmount']

													+(- $this->Invoice['CodAmount']);



			   $nobtwchargebleprice8020		 =  $this->Invoice['invoice_amountNEU']		

												+ $this->Invoice['MSNLAmount']				

												+ $this->Invoice['MSNEUAmount'];

														

			   $this->Invoice['fs_amount_eu'] = $this->Invoice['fs_amountEU'];

			   $this->Invoice['fs_amount_neu'] = $this->Invoice['fs_amountNEU'];										

			 }

		//Btw Total 

		$this->Invoice['BTWtotal'] = $this->Invoice['btwEU'] + $this->Invoice['btwAmount']+ $this->Invoice['MSBTWAmount']+ $this->Invoice['fs_btw']+ $this->Invoice['ws_btw_amount'];

		//Total Price

		$this->Invoice['total_amount']  =  $this->Invoice['Subtotal'] 

										 + $this->Invoice['BTWtotal'];	

		//Btw NEU

		$this->Invoice['btwNEU']          = 0;

		$this->Invoice['invoice_date']  = date('Y-m-d');

		$this->Invoice['1300'] =  $this->Invoice['total_amount'];

		$this->Invoice['8002'] =  $this->Invoice['GrondslagPrice'];

		$this->Invoice['8020'] =  $this->Invoice['8020'] + $nobtwchargebleprice8020;

		$this->Invoice['1602'] =  ($this->Invoice['BTWtotal'] - $this->Invoice['1640']);//1640 deduct because it is already added in total btw

		$this->Invoice['1260'] = (($this->Invoice['1260']) + (-$this->Invoice['CodAmount']));

		$this->Invoice['created_ip'] =  $_SERVER['REMOTE_ADDR'];

		$this->Invoice['8002_fs'] 	 =  $this->Invoice['fs_amountEU'];

		$this->Invoice['8020_fs'] 	 =  $this->Invoice['fs_amountNEU'];								  									

		//echo "<pre>";print_r($this->Invoice);die;

	}

	

	public function getExtraAmount($invoiceno=false){
		  $this->Invoice['EXTRADeatil'] = array();
		  $select = $this->_db->select()

			 					->from(INVOICE_EXTRA_HEAD,array('*'));

		  if($invoiceno){

		     $select->where("invoice_number=?",$invoiceno);

		  }else{

		     $select->where("invoice_number=?",'0');

			 $select->where("user_id=?",$this->Invoice['user_id']);

		  }	//print_r($select->__tostring());die;					

		  $results = $this->getAdapter()->fetchAll($select);

		  $this->Invoice['extraAmount'] = 0;

		  $this->Invoice['btwAmount'] = 0;

		  $this->Invoice['nbcAmount'] = 0;

		  $this->Invoice['bcAmount'] = 0;

		  $this->Invoice['8002'] = 0;

		  $this->Invoice['8010'] = 0;

		  $this->Invoice['8020'] = 0;

		  $this->Invoice['1602'] = 0;

		  $this->Invoice['1260'] = 0;

		  $this->Invoice['1640'] = 0;

		  $this->Invoice['1680'] = 0;

		  $this->Invoice['4970'] = 0;

		  $this->Invoice['8000'] = 0;

		  foreach($results as $extrahead){

			  switch($extrahead['btw_class']){

			   case 1:

			      //8002-sales high 21%

				  $this->Invoice['8002'] = $this->Invoice['8002'] + $extrahead['price'];

				  $this->Invoice['btwAmount']  = $this->Invoice['btwAmount'] + (($extrahead['price'] * $this->Invoice['btw_rate'])/100);

				  $this->Invoice['bcAmount'] = $this->Invoice['bcAmount'] + $extrahead['price'];

				  $this->Invoice['extraAmount']  = $this->Invoice['extraAmount'] +$extrahead['price'];

			   break;

			   case 2:

			      //8010-sales low 0% inside EU

				  $this->Invoice['8010']	= $this->Invoice['8010'] +$extrahead['price'];

				  $this->Invoice['extraAmount']  = $this->Invoice['extraAmount'] +$extrahead['price'];

			   break;

			   case 3:

			     //8020-sales export 0% outside EU

				 $this->Invoice['8020'] 	= $this->Invoice['8020'] + $extrahead['price'];

				 $this->Invoice['extraAmount']  = $this->Invoice['extraAmount'] +$extrahead['price'];

			   break;

			   case 4:

			     //1602-BTW to pay

				 $this->Invoice['1602'] 	= $this->Invoice['1602'] + $extrahead['price'];

				 $this->Invoice['btwAmount']  = $this->Invoice['btwAmount'] + $extrahead['price'];

			   break;

			   case 5:

			      //1260-COD no VAT

				  $this->Invoice['1260'] 	= ($this->Invoice['1260'] + ($extrahead['price']));

				  $this->Invoice['extraAmount']  = ($this->Invoice['extraAmount'] + ($extrahead['price']));

			   break;

			   case 6:

			      //1640-BTW custom no VAT

				  $this->Invoice['1640'] 	= $this->Invoice['1640'] + $extrahead['price'];

				  $this->Invoice['btwAmount']  = $this->Invoice['btwAmount'] + $extrahead['price'];

			   break;

			   case 7:

			      //1680- Duties custom no VAT

				  $this->Invoice['1680'] 	= $this->Invoice['1680'] + $extrahead['price'];

				  $this->Invoice['extraAmount']  = $this->Invoice['extraAmount'] +$extrahead['price'];

			   break;

			   case 8:

			       //4970- Damage no VAT

				   $this->Invoice['4970'] 	= $this->Invoice['4970'] + $extrahead['price'];

				   $this->Invoice['extraAmount']  = $this->Invoice['extraAmount'] +$extrahead['price'];

			   break;

			   case 9:

			      //8000-sales export 0% outside EU

				  $this->Invoice['8000'] 	= $this->Invoice['8000'] + $extrahead['price'];

				  $this->Invoice['extraAmount']  = $this->Invoice['extraAmount'] +$extrahead['price'];

			   break;

			}

			//if($extrahead['btw_class']==4 && $extrahead['btw_class']==6){

			//$this->Invoice['extraAmount']  = ($this->Invoice['extraAmount'] + ($extrahead['price']));

			//}

			$this->Invoice['EXTRADeatil'][]   = $extrahead;

		  }

		  //$this->Invoice['extraAmount']  = $this->Invoice['extraAmount']  + $this->Invoice['btwAmount'];

		  $this->Invoice['nbcAmount']  =  ($this->Invoice['8000'] + $this->Invoice['4970'] + $this->Invoice['1680'] + $this->Invoice['8020'] + $this->Invoice['8010']+ ($this->Invoice['1260']));

			return true;

	}

	

	public function getMailInvoiceAmount(){

         $where = '';

         switch($this->Useconfig['level_id']){

		   case 1:

		     $group = 'AT.parent_id';

			 $column = array('AT.parent_id as user_id','SUM(MS.quantity) as quantity','SUM(MS.depot_price) as price','MS.country_id');

			 $column1 = array('AT.parent_id as user_id','MS.depot_price as price','weight_class as weight','quantity','country_id','mailshipment_id','mail_id','create_date');

			 $where = "MS.depot_price>0 AND MS.depot_invoice_status='0' AND AT.parent_id<>0 AND AT.parent_id='".$this->Invoice['user_id']."'";

		  break;

		  case 4:

			 $coloumn = array('AT.user_id','SUM(MS.quantity) as quantity','SUM(MS.customer_price) as price','MS.country_id');

			 $group = 'AT.user_id';

			 $column1 = array('AT.user_id as user_id','MS.customer_price as price','weight_class as weight','quantity','country_id','mailshipment_id','mail_id','create_date');

			 $where = "MS.customer_price>0 AND MS.customer_invoice_status='0' AND AT.user_id='".$this->Invoice['user_id']."'";

		  break;

		  case 6:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

			 $group = 'AT.user_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(MS.quantity) as quantity','SUM(MS.customer_price) as price','MS.country_id');

			 $column1 = array('AT.user_id as user_id','MS.customer_price as price','weight_class as weight','quantity','country_id','mailshipment_id','mail_id','create_date');

			 $where = "MS.customer_price>0 AND MS.customer_invoice_status='0' AND AT.user_id='".$this->Invoice['user_id']."'";

		  break;

		  case 11:

		     $group = 'AT.parent_id';

			 $coloumn = array('AT.parent_id as user_id','SUM(MS.quantity) as quantity','SUM(MS.depot_price) as price','MS.country_id');

			 $column1 = array('AT.user_id as user_id','MS.depot_price as price','weight_class as weight','quantity','country_id','mailshipment_id','mail_id','create_date');

			 $where = "MS.depot_price>0 AND MS.depot_invoice_status='0' AND AT.parent_id<>0  AND AT.parent_id='".$this->Invoice['user_id']."'";

		  break;

	     }

		 try{

		   $select = $this->_db->select()

									->from(array('MS'=>MAIL_POST),$coloumn)

									->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=MS.'.ADMIN_ID,array())

									->joininner(array('CT'=>MAIL_POST_COUNTRY),"MS.country_id=CT.id",array('country_name'))

									 ->where($where)

									 ->group($group);

                                              // print_r($select->__tostring());die;

	          $results = $this->getAdapter()->fetchAll($select);

			  $this->Invoice['MSNLAmount'] = 0;

			  $this->Invoice['MSBTWAmount'] = 0;

			  $this->Invoice['MSNEUAmount'] = 0;

			  $this->Invoice['MSParcelDetal'] = array();

			  foreach($results as $result){

                      $this->Invoice['MSParcelDetal'][] = $result;

                      if($result['country_id']==1 || $result['country_id']==2){

                           $this->Invoice['MSNLAmount'] = $this->Invoice['MSNLAmount'] + $result['price'];

                           $this->Invoice['MSBTWAmount'] = $this->Invoice['MSBTWAmount'] +(($result['price']*$this->Invoice['btw_rate'])/100);

                      }elseif($result['country_id']==3){

                          $this->Invoice['MSNEUAmount'] = $this->Invoice['MSNEUAmount'] + $result['price'];

                      }

                  }

              $select = $this->_db->select()

									->from(array('MS'=>MAIL_POST),$column1)

									->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=MS.'.ADMIN_ID,array())

									->joininner(array('CT'=>MAIL_POST_COUNTRY),"MS.country_id=CT.id",array('country_name'))

									 ->where($where)

									 ->group("MS.country_id")

									 ->group("DATE(MS.create_date)");//print_r($select->__tostring());die;

			}catch(Exception $e){

			  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

			}						 

              $results = $this->getAdapter()->fetchAll($select);

               $this->Invoice['MSParcel'] = array();

			   foreach($results as $result){

                  $this->Invoice['MSParcel'][] = $result;

               }

     }

	  

	public function InvoiceSenderReceiver(){

		    $receivercol = array('AT.company_name as Rcompany','AT.first_name as Rfname','AT.middle_name as Rmname','AT.last_name as Rlname','AT.address1 as Raddress1','AT.address2 as Raddress2','AT.city as Rcity','AT.postalcode as Rzipcode','AT.phoneno as Rphone','AT.email as Remail','AT.invoice_email as Rinvoicemail','AT.btw_number as Rbtwno','US.btw_status as Rbtwcharge','AT.user_id as Ruserid','US.invoice_type','AT.customer_number');//,'US.fuel_surcharge_status'



			 $sendrcol = array('PT.company_name as Scompany','PT.address1 as Saddress1','PT.address2 as Saddress2','PT.city as Scity','PT.postalcode as Szipcode','PT.phoneno as Sphone','PT.email as Semail','PT.invoice_email as Sinvoicemail','PS.invoice_logo','PT.user_id as Suserid','PS.invoice_series','PS.logo');



		     $select = $this->_db->select()

			 					->from(array('AT'=>USERS_DETAILS),$receivercol)

								->joininner(array('US'=>USERS_SETTINGS),"AT.user_id=US.user_id",array(''))

								->joininner(array('CT'=>COUNTRIES),"CT.country_id=AT.country_id",array('CT.country_name AS Rcountry'))

								->joininner(array('PT'=>USERS_DETAILS),'PT.user_id=AT.parent_id',$sendrcol)

								->joininner(array('PS'=>USERS_SETTINGS),'PT.user_id=PS.user_id',array(''))

								->joininner(array('PCT'=>COUNTRIES),"PCT.country_id=PT.country_id",array('PCT.country_name AS Pcountry'))

								->where('AT.user_id=?',$this->Invoice['user_id']);

								//print_r($select->__tostring());die;

			 $result = $this->getAdapter()->fetchRow($select);

			  

		    if($this->Useconfig['level_id']==1){

			   $this->Invoice['Scompany'] 		= 'Parcel.nl BV';

			   $this->Invoice['Saddress1']	 	= 'Slachthuisweg 77';

			   $this->Invoice['Scity']    		= 'Hengelo';

			   $this->Invoice['Szipcode'] 		= '7556 AX';

			   $this->Invoice['Sphone'] 		= '074 8800700';

			   $this->Invoice['Semail']   		= 'boekhouding@parcel.nl';

			   $this->Invoice['bank_name'] 	= 'ING BANK N.V : 9511121';

			   $this->Invoice['bank_account'] 	= 'NL13INGB0009511121';

			   $this->Invoice['bank_bic']    	= 'INGBNL2A';

			   $this->Invoice['bank2'] 	 	= 'ABN-Amro : 45.32.36.030';

			   $this->Invoice['bank_kvk']  	= 'Enschede 06056231';

			   $this->Invoice['bank_btw']   	= 'NL812095972B01';

			   $this->Invoice['parent_id'] 	= 1;

			   $this->Invoice['invoice_logo']  = BASE_URL.'/public/headerlogo/';

			   $this->Invoice['invoice_logo']  .= (!empty($result['invoice_logo']))?$result['invoice_logo']:$result['logo'];



			 }else{

			    foreach($result as $key=>$senderReceiver){

				   $this->Invoice[$key] = $senderReceiver;

				}

				 $logo   = BASE_URL.'/public/headerlogo/';

				 $logo   .= (!empty($result['invoice_logo']))?$result['invoice_logo']:$result['logo'];

				 $this->Invoice['invoice_logo'] = $logo;

				 $this->Invoice['parent_id'] 	= $result['Suserid'];

				 $this->Invoice['create_id'] 	= $this->Useconfig['user_id'];

			    $select = $this->_db->select()

			 					->from(array('IB'=>INVOICE_BANK_DETAIL),array('*'));//print_r($select->__tostring());die;

				switch($this->Useconfig['level_id']){

					 case 4:

						$select->where("IB.user_id=?",$this->Useconfig['user_id']);

					 break;

					 case 6:

						$parent_id = $this->getDepotID($this->Useconfig['user_id']);

						$select->where("IB.user_id=?",$parent_id);

					 break;  

				 }				

				$bankdetail = $this->getAdapter()->fetchRow($select);

				$this->Invoice['bank_name'] 	= $bankdetail['bank_name'];

				$this->Invoice['bank_account']= $bankdetail['bank_account'];

				$this->Invoice['bank_bic']    = $bankdetail['bank_bic'];

				$this->Invoice['bank2'] 	 	= $bankdetail['bank2'];

				$this->Invoice['bank_kvk']  	= $bankdetail['bank_kvk'];

				$this->Invoice['bank_btw']   	= $bankdetail['bank_btw'];

			 } 

			$this->InvoiceSettings();

			return; 

	}

	public function InvoiceSettings(){

	    $select = $this->_db->select()

									->from(array('IS'=>INVOICE_SETTING),array('*'));

		switch($this->Useconfig['level_id']){

		 case 4:

			$select->where("IS.user_id=?",$this->Useconfig['user_id']);

		 break;

		 case 6:

			$parent_id = $this->getDepotID($this->Useconfig['user_id']);

			$select->where("IS.user_id=?",$parent_id);

		 break;

		 default:

		    $select->where("IS.user_id=?",1);

		}							

		$invoicesettings = $this->getAdapter()->fetchRow($select);

		$this->Invoice['btw_rate'] 				= (trim($invoicesettings['btw_rate'])>0)?trim($invoicesettings['btw_rate']):21;

		$this->Invoice['btw_name'] 				= (trim($invoicesettings['btw_name'])!='')?trim($invoicesettings['btw_name']):'BTW';

		$this->Invoice['invoice_name_label'] 		= (trim($invoicesettings['invoice_name'])!='')?trim($invoicesettings['invoice_name']):'FACTUUR';

		$this->Invoice['invoice_date_label'] 		= (trim($invoicesettings['invoice_date'])!='')?trim($invoicesettings['invoice_date']):'Factuurdatum';

		$this->Invoice['invoice_number_label'] 	= (trim($invoicesettings['invoice_number'])!='')?trim($invoicesettings['invoice_number']):'Factuurnummer';

		$this->Invoice['customer_number_label']	= (trim($invoicesettings['customer_number'])!='')?trim($invoicesettings['customer_number']):'Customer Number';

		$this->Invoice['topic_label']			 	= (trim($invoicesettings['topic'])!='')?trim($invoicesettings['topic']):'Onderwerp';

		$this->Invoice['pakegshipent_label']	 	= (trim($invoicesettings['package_shipments'])!='')?trim($invoicesettings['package_shipments']):'Pakketverzendingen';

		$this->Invoice['dateto_label']	 	= (trim($invoicesettings['to'])!='')?trim($invoicesettings['to']):'t/m';

		$this->Invoice['description_label']	 	= (trim($invoicesettings['description'])!='')?trim($invoicesettings['description']):'Omschrijving';

		$this->Invoice['paketcount_label']	 	= (trim($invoicesettings['packate_count'])!='')?trim($invoicesettings['packate_count']):'Aantal';

		$this->Invoice['price_label']			 	= (trim($invoicesettings['price'])!='')?trim($invoicesettings['price']):'Prijs';

		$this->Invoice['total_label'] 			= (trim($invoicesettings['total'])!='')?trim($invoicesettings['total']):'Totaal';

		$this->Invoice['subtotal_label'] 			= (trim($invoicesettings['subtotal'])!='')?trim($invoicesettings['subtotal']):'Subtotaal';

		$this->Invoice['paket_anex_label'] 		= (trim($invoicesettings['sent_packets_annex'])!='')?trim($invoicesettings['sent_packets_annex']):'Verstuurde pakketten zie bijlage';

		$this->Invoice['payble_label'] 			= (trim($invoicesettings['payable'])!='')?trim($invoicesettings['payable']):'Te betalen';

		$this->Invoice['basis_label'] 			= (trim($invoicesettings['basis'])!='')?trim($invoicesettings['basis']):'Grondslag';

		$this->Invoice['payment_term_label'] 		= (trim($invoicesettings['payment_terms'])!='')?trim($invoicesettings['payment_terms']):'Betalingstermijn is 14 dagen na factuurdatum.';

		$this->Invoice['bankdetail_label'] 		= (trim($invoicesettings['bank_details'])!='')?trim($invoicesettings['bank_details']):'Onze bankgegevens zijn';

		$this->Invoice['bankaccount_label'] 		= (trim($invoicesettings['bank_account'])!='')?trim($invoicesettings['bank_account']):'IBAN/SEPA nummer';

		$this->Invoice['bankbic_label'] 			= (trim($invoicesettings['bank_bic'])!='')?trim($invoicesettings['bank_bic']):'BIC nummer';

		$this->Invoice['sent_mail_destination'] 	= (isset($invoicesettings['sent_mail_destination']) && $invoicesettings['sent_mail_destination']!='')?trim($invoicesettings['sent_mail_destination']):'Verzonden post bestemming';

		$this->Invoice['kvk_label'] 					= (trim($invoicesettings['kvk'])!='')?trim($invoicesettings['kvk']):'K.v.K.';

		$this->Invoice['fuel_surcharge'] 			= (trim($invoicesettings['fuel_surcharge'])>0)?trim($invoicesettings['fuel_surcharge']):0;

		$this->Invoice['fuel_surcharge_label'] 			= (trim($invoicesettings['fuel_surcharge_text'])!='')?trim($invoicesettings['fuel_surcharge_text']):'Dieselodietoeslag';

		$this->Invoice['btw_notcharge_text'] 			= (trim($invoicesettings['btw_notcharge_text'])!='')?trim($invoicesettings['btw_notcharge_text']):'B.T.W. verlegd';

		$this->Invoice['footer_text'] 			= (trim($invoicesettings['footer_text'])!='')?trim($invoicesettings['footer_text']):'Op alle overeenkomsten met Parcel.nl BV voor goederenvervoer over de weg zijn de AVC 2002, van toepassing en op alle overeenkomsten met Parcel.nl BV voor opslag en andere logistieke handelingen de Physical Distribution voorwaarden. De toepasselijkheid van andere algemene voorwaarden wordt uitdrukkelijk van de hand gewezen. Bij interpretatiegeschillen van de algemene voorwaarden dient de Nederlandse tekst als uitgangspunt.';

	}

	

	public function InvoiceNumber(){

		 if($this->Invoice['type']<=3){

		  $select = $this->_db->select()

			 					->from(INVOICE,array('invoice_number'))

								->where("parent_id='".$this->Invoice['parent_id']."' AND is_list='0'")

								->order('invoice_number DESC')

								->limit(1); //print_r($select->__toString());die;

		  $result = $this->getAdapter()->fetchRow($select);

		  $check_invoice_number = date('Y').commonfunction::paddingleft($this->Useconfig['invoice_series'],4,0);

		  

		  $Invoice_no = $result['invoice_number'];

		  if(($this->Useconfig['level_id'] == 1 || $this->Useconfig['level_id'] == 4 || $this->Useconfig['level_id'] == 6) &&(commonfunction::sub_string($Invoice_no,0,8) != $check_invoice_number) ) {

				$this->Invoice['invoice_number'] = date('Y').commonfunction::paddingleft($this->Useconfig['invoice_series'],4,0).'000001';

			}else{

				$this->Invoice['invoice_number'] = $Invoice_no + 1;

				}

		  }else{

			   $select = $this->_db->select()

									->from(INVOICE,array('invoice_number'))

									->where("parent_id='".$this->Invoice['parent_id']."' AND is_list='1'")



									->order('invoice_number DESC')

									->limit(1); //print_r($select->__toString());die;



			   $result = $this->getAdapter()->fetchRow($select);

			   if(empty($result)){

			     $invoicenumber = '1001';

			   }else{

			     $invoicenumber = $result['invoice_number']+1;

			   }

			   $this->Invoice['invoice_number'] = $invoicenumber; 

			   $this->Invoice['is_list']  ='1';

		  }		

		 return true;

		}

	public function ExtraInvoices(){		  

		  $select = $this->_db->select()

			 					->from(array('EH'=>INVOICE_EXTRA_HEAD),array('*'))

								->where("invoice_number<=0")

								->where("user_id='".$this->getData['user_id']."'");

		 			//print_r($select->__toString());die;

		  $results = $this->getAdapter()->fetchAll($select);

	

		return $results;

		 

	}

	public function getCodAdjustmentAmount($user_id,$invoicecheck=false){

	    $select = $this->_db->select()

		  						->from(array('COD'=>INVOICE_COD),array('COD.cod_price','IF(1,1,1) AS quantity'))

			 					->joininner(array('BT'=>SHIPMENT_BARCODE),"COD.barcode_id=BT.barcode_id",array('BT.barcode','BT.barcode_id'))

								->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('BD.rec_reference'))

								->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array())

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array());

								//print_r($select->__toString());die;

								

		if($invoicecheck){

		   switch($this->Useconfig['level_id']){

		      case 1:

			  case 11:

			   $select->where("COD.depot_invoice_number='".$user_id."'");

			  break;

			   case 4:

			   case 6:

			   $select->where("COD.customer_invoice_number='".$user_id."'");

			  break;

		   }

		}else{

		   switch($this->Useconfig['level_id']){

		      case 1:

			   $select->where("AT.parent_id='".$user_id."' AND COD.cod_price>0 AND COD.depot_invoice_number=0");

			  break;

			   case 4:

			   $select->where("AT.user_id='".$user_id."' AND COD.cod_price>0 AND COD.customer_invoice_number=0");

			  break;

			  case 6:

			   $parent_id = $this->getDepotID($this->Useconfig['user_id']);

			   $select->where("AT.user_id='".$parent_id."' AND COD.cod_price>0 AND COD.customer_invoice_number=0");

			  break;

			  case 11:

			   $parent_id = $this->getDepotID($this->Useconfig['user_id']);

			   $select->where("AT.parent_id='".$parent_id."' AND COD.cod_price>0 AND COD.depot_invoice_number=0");

			  break;

		   }

		}

		//print_r($select->__tostring());die;

		$result = $this->getAdapter()->fetchAll($select);

		$coddetail = array();

		$codamount = 0;

		if($result){

		      foreach($result as $cod){

			     $coddetail[] = $cod;

				 $codamount = $codamount + $cod['cod_price'];

			  }

		  }

		  $this->Invoice['CodAmount']  = $codamount;

		  $this->Invoice['CodDetail']  = $coddetail;

	}

	

	public function getWebshopAmount($user_id,$invoicecheck=false){

	   try{

	    $select = $this->_db->select()

										->from(array('WO'=>WEBSHOP_ORDER),array('AT.user_id','sum(WOP.price) as price','count(WOP.quantity) as quantity'))

										->joininner(array('WOP'=>WEBSHOP_ORDER_PRODUCTS),'WOP.order_id=WO.order_id',array())

										->joininner(array('BT'=>SHIPMENT_BARCODE),'BT.barcode_id=WO.barcode_id',array())

										->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=WO.user_id',array());	//print_r($select->__tostring());die;

								

		if($invoicecheck){

		   $select->where("WO.invoice_no='".$user_id."'");

		}else{

		   $select->where("AT.user_id='".$user_id."' AND

						  WO.barcode_id!='' AND BT.checkin_status='1' AND  WO.invoice_no=''");

		}

		$select->group("AT.user_id");

		//print_r($select->__tostring());die;

		$result = $this->getAdapter()->fetchAll($select);

		}catch(Exception $e){

	      $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  $result = array();

	   }

		$suppliesdetail =array();

		$supplaiseamount = 0;

		if($result){

		      foreach($result as $supplies){

			     $suppliesdetail[] = $supplies;

				 $supplaiseamount = $supplaiseamount + $supplies['price'];

			  }

		  }

		  $this->Invoice['ws_amount']  = $supplaiseamount;

		  $this->Invoice['ws_btw_amount']= ($supplaiseamount * $this->Invoice['btw_rate'])/100;

		  $this->Invoice['WSParcel']  = $suppliesdetail;

	

	}

	

	public function getBTWClasses(){

		$select = $this->_db->select()

			 					->from(array('BC'=>INVOICE_BTW_CLASS),array('*'));

		 			//print_r($select->__toString());die;

		  $results = $this->getAdapter()->fetchAll($select);

		return $results;

	}

	public function AddExtraHeadInvoice(){

	   //echo "<pre>";print_r($this->getData);die;

	   foreach($this->getData['quantity'] as $key=>$quantity){

	     if($quantity>0 && $this->getData['price'][$key]<>0){

		 $this->_db->insert(INVOICE_EXTRA_HEAD,array_filter(array('user_id'=>$this->getData['user_id'],'quantity'=>$quantity,'description'=>$this->getData['description'][$key],'price'=>$this->getData['price'][$key],'btw_class'=>$this->getData['btw_class'][$key],'date'=>new Zend_Db_Expr('NOW()'))));

		 }

	   }

	   

	   return true;

	}

	public function InvoiceNumberBarcodeUpdate(){

	   //echo "<pre>";print_r($this->Invoice);die;

	   foreach($this->Invoice['ParcelList'] as $ParcelList){

	      switch($this->Useconfig['level_id']){

		     case 1:

			 case 11:

			   $update = array('depot_invoice_status'=>1);

			   $update1 = array('depot_invoice_number'=>$this->Invoice['invoice_number']);

			 break;

			 case 4:

			 case 6:

			  $update =  array('customer_invoice_status'=>1);

			  $update1 =  array('customer_invoice_number'=>$this->Invoice['invoice_number']);

			 break;

		  }

		   //print_r("barcode_id='".$ParcelList['barcode_id']."'");die;

		  $this->_db->update(SHIPMENT_BARCODE,$update,"barcode_id='".$ParcelList['barcode_id']."'");

		  $this->_db->update(SHIPMENT_BARCODE_DETAIL,$update1,"barcode_id='".$ParcelList['barcode_id']."'");

	   }
		$this->InvoiceNumberExtraFieldUpdate();
		$this->InvoiceNumberCodUpdate();
		$this->updateMailshipment();
	    return true;



	}

	public function InvoiceList(){

	   $where = '';

	   if($this->getData['action']=='myinvoice'){

	      $where = "UD.user_id='".$this->Useconfig['user_id']."'";

	   }elseif($this->Useconfig['level_id']==1 || $this->Useconfig['level_id']==11){

	      $where = "UD.parent_id=1";

	   }elseif($this->Useconfig['level_id']==4){

	     $where = "UD.parent_id='".$this->Useconfig['user_id']."'";

	   }elseif($this->Useconfig['level_id']==6){

	     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

	     $where = "UD.parent_id='".$parent_id."'";

	   }else{

	      $where = "UD.user_id='".$this->Useconfig['user_id']."'";

	   }

	   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

	      $where .= " AND UD.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

	   }

	   if(isset($this->getData['payment_status']) && $this->getData['payment_status']!=''){

	      $where .= " AND IT.payment_status='".$this->getData['payment_status']."'";

	   }

	   if(isset($this->getData['search_invoice']) && $this->getData['search_invoice']!=''){

	      //$where .= " AND SUBSTRING(IT.invoice_number,9,6)='".commonfunction::paddingleft($this->getData['search_invoice'],6,0)."'";
		  $where .= " AND SUBSTRING(IT.invoice_number,9,6) = '".str_pad($this->getData['search_invoice'],6,'0',STR_PAD_LEFT)."'";

	   }

	   if(isset($this->getData['from_date']) && isset($this->getData['to_date']) && $this->getData['from_date']!='' && $this->getData['to_date']!=''){

	      $where .= " AND IT.invoice_date BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";

	   }

	   $OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'IT.invoice_number','DESC');

	    $select = $this->_db->select()

									->from(array('IT'=>INVOICE),('COUNT(1) AS CNT'))

									->joininner(array('UD'=>USERS_DETAILS),"UD.user_id=IT.user_id",array('UD.company_name'))

									->where("IT.is_list='0'")

									->where($where);

									//echo $select->__toString();die;

		$total = $this->getAdapter()->fetchRow($select);

		$select = $this->_db->select()

									->from(array('IT'=>INVOICE),('*'))

									->joininner(array('UD'=>USERS_DETAILS),"UD.user_id=IT.user_id",array('UD.company_name'))

									->where("IT.is_list='0'")

									->where($where)

									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

									//echo $select->__toString();die;

		$result = $this->getAdapter()->fetchAll($select);	

		

		return array('Total'=>$total['CNT'],'Record'=>$result);						

	}

	public function getPricelistData(){

	  $this->Invoice['user_id'] = $this->getData['user_id'];

	  switch($this->Useconfig['level_id']){

	      case 1:

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0 AND AT.parent_id='".$this->Invoice['user_id']."'";

		  break;

		  case 4:

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$this->Useconfig['user_id']."' AND AT.user_id='".$this->Invoice['user_id']."'";

		  break;

		  case 6:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$parent_id."' AND AT.user_id='".$this->Invoice['user_id']."'";

		  break;

		  case 11:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0 AND AT.parent_id='".$parent_id."'";

		  break;

		 

	   }

	   $select = $this->_db->select()

			 					->from(array('BT'=>SHIPMENT_BARCODE),array('BT.barcode_id','BT.barcode','BT.depot_price','BT.tracenr_barcode','BT.weight','BT.customer_price'))
								->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('rec_reference'))
								->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('quantity'))

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array(''))

								->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array('country_name'))

								->joininner(array('FT'=>FORWARDERS),'FT.'.FORWARDER_ID.'=BT.'.FORWARDER_ID,array('forwarder_name'))

								->where("BT.checkin_status='1'")

								->where($where)
								->order("BT.barcode_id DESC");//print_r($select->__toString());die;

		return $this->getAdapter()->fetchAll($select);

	}

	public function updatePrice(){

	  $pricefield = ($this->Useconfig['level_id']==1 || $this->Useconfig['level_id']==11)?'depot_price':'customer_price';

	  if($this->getData['price']>0){

	  	$this->_db->update(SHIPMENT_BARCODE,array($pricefield=>$this->getData['price']),"barcode_id='".$this->getData['barcode_id']."'");

	  }

	  echo 'Success';die;

	}

	public function SingleInvoiceRecord($invoice_number){

	   $select = $this->_db->select()

									->from(array('IT'=>INVOICE),('*'))

									->joininner(array('UD'=>USERS_DETAILS),"UD.user_id=IT.user_id",array('UD.company_name'))

									->where("invoice_number='".$invoice_number."'");

									//echo $select->__toString();die;

		return $this->getAdapter()->fetchRow($select);

	}

	public function updateInvoice(){

	    global $objSession;

	   try{ 

	    $this->_db->update(INVOICE,array('payment_mode'=>$this->getData['payment_mode'],'payment_date'=>$this->getData['payment_date'],'paid_amount'=>$this->getData['paid_amount'],'payment_status'=>$this->getData['payment_status'],'remark'=>$this->getData['remark']),"invoice_number='".$this->getData['invoice_number']."'");

		$objSession->successMSG = 'Invoice Number '.$this->getData['invoice_number'].' updated successfully';

		}catch(Exception $e){

		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

		return true;

	}

	

	 public function ExportInvoice($return =  false){

	   $nxtcol = "\t";

	   $nxtrow = "\n";

	   $dataheader = '';

	   $dataheader .= "\"Customer Name \"".$nxtcol."\"Street\"".$nxtcol."\"House No. \"".$nxtcol.

					  "\"Address \"".$nxtcol."\"PostalCode \"".$nxtcol."\"City\"".$nxtcol.

					  "\"Country Name \"".$nxtcol."\"Receiver Name \"".$nxtcol.

					  "\"Street\"".$nxtcol."\"Street No. \"".$nxtcol."\"Address\"".$nxtcol."\"PostalCode. \"".$nxtcol.

					  "\"City \"".$nxtcol."\"Country \"".$nxtcol."\"Create Date\"".$nxtcol.

					  "\"CheckIn Date \"".$nxtcol."\"Weight \"".$nxtcol."\"Service\"".$nxtcol.

					  "\"Depot Price\"".$nxtcol.

					  "\"Customer Price \"".$nxtcol."\"Reference Number \"".$nxtcol.

					  "\"Forwarder\"".$nxtcol."\"Barcode\"".$nxtrow.$nxtrow;

		switch($this->Useconfig['level_id']){

	      case 1:

		     $group = 'AT.parent_id';

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0 AND AT.parent_id='".$this->Invoice['user_id']."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.depot_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight','ST.street','ST.streetnr','ST.street2','ST.rec_name','ST.rec_city','ST.create_date','BD.checkin_date','SV.service_name','FT.forwarder_name');

		  break;

		  case 4:

		     $group = 'AT.user_id';

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$this->Useconfig['user_id']."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.customer_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight','ST.rec_street','ST.rec_streetnr','ST.rec_street2','ST.rec_name','ST.rec_city','ST.create_date','BD.checkin_date','SV.service_name','FT.forwarder_name','BT.tracenr_barcode','BT.depot_price','BT.customer_price');

		  break;

		  case 6:

		     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

		     $group = 'AT.user_id';

			 $where = "BT.customer_price>0 AND BT.customer_invoice_status='0' AND BT.customer_invoice_unlist='0' AND AT.parent_id='".$parent_id."'";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.customer_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight');

		  break;

		  case 11:

		     $group = 'AT.parent_id';

			 $where = "BT.depot_price>0 AND BT.depot_invoice_status='0' AND BT.depot_invoice_unlist='0' AND AT.parent_id<>0";

			 $column1 = array('BT.barcode','BD.rec_reference','BT.depot_price AS price','ST.rec_zipcode','BT.tracenr_barcode','BD.checkin_type','BD.checkin_date','BT.weight');

		  break;

		 

	   }

	   $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

	   $select = $this->_db->select()

			 					->from(array('BT'=>SHIPMENT_BARCODE),$column1)

								->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID,array('BT.barcode_id'))

								->joininner(array('ST'=>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('country_id'))

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID,array('company_name','address1','address2','postalcode','postalcode','country_id AS Ccountry','city'))

								->joininner(array('CT'=>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID,array('cncode'))

								->joininner(array('SV'=>SERVICES),'SV.'.SERVICE_ID.'=BT.'.SERVICE_ID,array('service_name'))

								->joininner(array('FT'=>FORWARDERS),'FT.'.FORWARDER_ID.'=BT.'.FORWARDER_ID,array('forwarder_name'))

								->where("BT.checkin_status='1'")

								->where($where)

								->order("checkin_date ASC");//echo $select->__toString();die;

	  $result = $this->getAdapter()->fetchAll($select);	//print_r($result);die;		

	  if($return){

	     return $result;

	  }  

	  if(count($result)>0)

		{

			foreach($result as $RecordExport)

			{

				$customercountry = $this->getCountryDetail($RecordExport['Ccountry']);

				$receivercountry = $this->getCountryDetail($RecordExport['country_id']);

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['company_name']) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['address1'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['address2'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['address2'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['postalcode'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['city'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$customercountry['country_name']) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[RECEIVER] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[STREET] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[STREETNR] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[STREET2] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[ZIPCODE] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[CITY] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$receivercountry['country_name']) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",date('d - F Y',strtotime($RecordExport['create_date'])) ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",date('d - F Y',strtotime($RecordExport[CHECKIN_DATE])) ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['weight'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['service_name']). "\"" . $nxtcol;

				//$dataheader .= "\"" . str_replace( "\"", "\"\"",$this->serviceName($RecordExport[ADDSERVICE_ID])) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['depot_price'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['customer_price'] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport[REFERENCE] ) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"",$RecordExport['forwarder_name']) . "\"" . $nxtcol;

				$dataheader .= "\"" . str_replace( "\"", "\"\"","'".$RecordExport[TRACENR_BARCODE]."'" ) . "\"" . $nxtcol;

				$dataheader .= "\n";

			}

			commonfunction::ExportCsv($dataheader,'Export Invoice','xls');

			exit;

	}

	else{

		$dataheader .= "\"" . str_replace( "\"", "\"\"",'No Record Found!') . "\"" . $nxtcol;

		commonfunction::ExportCsv($dataheader,'Export Invoice','xls');

		exit;

	}			  

  }

  

     public function UnlistInvoice(){

 			switch($this->Useconfig['level_id']){

			  case 1:

				 $update = array('depot_invoice_unlist'=>'1');

			  break;

			  case 4:

				$update = array('customer_invoice_unlist'=>'1');

			  break;

			  case 6:

				 $update = array('customer_invoice_unlist'=>'1');

			  break;

			  case 11:

				 $update = array('depot_invoice_unlist'=>'1');

			  break;

			 

	  	 }

	      $invoiceData = $this->ExportInvoice(true);

		  if(!empty($invoiceData)){

			  foreach($invoiceData as $parcel){

			     $this->_db->update(SHIPMENT_BARCODE,$update,"barcode_id='".$parcel[BARCODE_ID]."'");

			  }

		   }  

	   }

	   

	 /**

	 *Export Financial Invoice

	 *Function : ExportFinancialInvoice()

	 *This function Export financial Invoice

	 **/

	 public function ExportFinancialInvoice(){

	  if($this->Useconfig['level_id']==1 || $this->Useconfig['level_id']==11){

	      $where = "AT.parent_id=1";

	  }elseif($this->Useconfig['level_id']==4){

	     $where = "AT.parent_id='".$this->Useconfig['user_id']."'";

	   }elseif($this->Useconfig['level_id']==6){

	     $parent_id = $this->getDepotID($this->Useconfig['user_id']);

	     $where = "AT.parent_id='".$parent_id."'";

	   }else{

	      $where = "AT.user_id='".$this->Useconfig['user_id']."'";

	   }

	   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

	      $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

	   }

	   if(isset($this->getData['payment_status']) && $this->getData['payment_status']!=''){

	      $where .= " AND IT.payment_status='".$this->getData['payment_status']."'";

	   }

	   if(isset($this->getData['search_invoice']) && $this->getData['search_invoice']!=''){

	      $where .= " AND SUBSTRING(IT.invoice_number,9,6)='".commonfunction::paddingleft($this->getData['search_invoice'],6,0)."'";

	   }

	   if(isset($this->getData['from_date']) && isset($this->getData['to_date']) && $this->getData['from_date']!='' && $this->getData['to_date']!=''){

	      $where .= " AND IT.invoice_date BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";

	   }

	   if(isset($this->getData['invoice_number']) && !empty($this->getData['invoice_number']) && count($this->getData['invoice_number'])>0){

		      $where .= " AND IT.invoice_number IN (".commonfunction::implod_array($this->getData['invoice_number'],',').")";  

		}

		   

		 $select = $this->_db->select()

			 					->from(array('ICD'=>INVOICE_FINANCIAL),array('*'))

								->joininner(array('IT'=>INVOICE),'IT.invoice_number=ICD.invoice_number',array('invoice_date'))

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=IT.'.ADMIN_ID,array('customer_number','company_name'))

								->where("IT.is_list='0'")

								->where($where);



							//echo $select->__toString();die;

		 $results = $this->getAdapter()->fetchAll($select);

		 // echo "<pre>"; print_r($results); die;

		 $out = "Dagboek".";"."Boekingcode".";"."datum".";"."grootboeknummer".";"."debet".";"."credit".";"."omschrijving".";"."relatiecode".";"."faktuurnummer".";"."Bedrijfsnaam\n";

		 $count = 1;

		 $yearcode = '';

		 foreach($results as $key=>$result){

			$yearcode = substr($result['invoice_number'],3,1);

			if($result['1300']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'1300'.';';

				$out .=''.str_replace('.',',',$result['1300']).';';

				$out .=''.''.';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['8002']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'8002'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['8002']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['8010']>0){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'8010'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['8010']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['8020']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'8020'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['8020']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['1260']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'1260'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['1260']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['1640']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'1640'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['1640']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['1680']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'1680'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['1680']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['4970']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'4970'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['4970']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['8000']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'8000'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['8000']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			if($result['1602']!='0.00'){

				$out .=''.'1300'.';';

				$out .=''.$count.';';

				$out .=''.$result['invoice_date'].';';

				$out .=''.'1602'.';';

				$out .=''.''.';';

				$out .=''.str_replace('.',',',$result['1602']).';';

				$out .=''.$yearcode.substr($result['invoice_number'],7,7).';';

				$out .=''.$result['customer_number'].';';

				$out .=''.$result['invoice_number'].';';

				$out .=''.utf8_decode($result['company_name']).';';

				$out .="\n";

			}

			$count++;

		}

		// print_r($out);die;

		commonfunction::ExportCsv($out,'Financial Invoice','csv');  



	 } 
	 
	 public function InvoiceEmail(){
        global $EmailObj;
	    if($this->Invoice['invoice_type']=='PDF'){

		 $EmailObj->emailData = array('SenderEmail'=>'invoice@dpost.be',
						 'NoReply'=>$this->Invoice['Semail'],
						 'ReceiverEmail'=>($this->Invoice['Rinvoicemail'])?$this->Invoice['Rinvoicemail']:$this->Invoice['Remail'],

						 'Sender'=>$this->Invoice['Scompany'],

						 'Receiver'=>$this->Invoice['Rcompany'],

						 'Attachemnt'=>INVOICE_SAVE.date('Y').'/'.date('M').'/'.$this->Invoice['file_name'],

						 NOTIFICATION_ID=>3,

						 ADMIN_ID=>$this->Invoice['Ruserid'],

						 COUNTRY_ID=>$this->Invoice['Rcountry'],

						 PARENT_ID=>$this->Invoice['parent_id'],

						 'Dynamic'=>array($this->Invoice['Rcompany'],

						   ($this->Invoice['quantityEU']+$this->Invoice['quantityNEU']),

						   number_format($this->Invoice['total_amount'],2),

						   number_format($this->Invoice['BTWtotal'],2),

						   ($this->Invoice['weightEU']+$this->Invoice['weightNEU']),

						   $this->Invoice['invoice_number']));
			$EmailObj->GeneralMail();
		}
		return true;

	 } 
	 
	 public function invoicesync(){

	 	ini_set('display_errors', 1);
	 	$success = false;
	 	$year = 2013;
         $select  = $this->_db->select()
         ->from(array('IF'=>INVOICE_FINANCIAL),array('*'))
         ->joininner(array('IT'=>INVOICE),"IT.invoice_number=IF.invoice_number",array('invoice_date','user_id','create_by','YEAR(invoice_date) as year'))
         ->where("YEAR(IT.invoice_date)=$year AND IT.parent_id=188")
         // ->limit(10)
         ;

		  try {
		  	
		   $result = $this->getAdapter()->fetchAll($select);
		  } catch (Exception $e) {
		  	echo $e->getMessage();die;
		  }
		 if(($totalRecords = count($result) )<1){
		 	 echo "No data available to be inserted in year $year";die;
		 } 	
		  // echo "<pre>"; print_r($result); die; 

		   
		   $invoiceheads = array('8002'=>21,'8020'=>0,'8010'=>0,'1602'=>0,'4970'=>0,'1680'=>0);
		   foreach($result as $heads){
		      $isertArr = array('ledger_head'=>5,'invoice_number'=>$heads['invoice_number'],'invoice_date'=>$heads['invoice_date'],'credit_amount'=>$heads['1300'],'invoice_definition'=>'Shipping invoice','customer_id'=>$heads['user_id'],'created_by'=>$heads['create_by'],'created_ip'=>$_SERVER['REMOTE_ADDR'],'created_date'=>new Zend_Db_Expr('NOW()'));

		      	
		      try {
		      	
		      	$this->_db->insert(AccountingInvoice, array_filter($isertArr));
		      } catch (Exception $e) {
		      	echo $e->getMessage()."<br><pre>"; print_r($isertArr);  
		      }
		      $invoiceid =  $this->getAdapter()->lastInsertId();
		      
		      // echo $invoiceid; die;

		      foreach($invoiceheads as $invoicehead=>$btw){ 
			      $select  = $this->_db->select()
			          ->from(array('AH'=>AccountingHead),array('*'))
			          ->where("head_code='".$invoicehead."'");
			     $ledger_id = $this->getAdapter()->fetchRow($select);

			     if($heads[$invoicehead]<>0){

			      	$dataArr = array('invoice_id'=>$invoiceid,'definition'=>'Shipping invoice','ledger_id'=>$ledger_id['head_id'],'btw'=>$btw,'credit'=>$heads[$invoicehead]);
			      	try {
		      	
			     		$res = $this->_db->insert(AccountingInvoiceDetails, array_filter($dataArr)); 
				      } catch (Exception $e) {
				      	echo $e->getMessage()."<br><pre>"; print_r($isertArr); 
				      }
			     	if($res)
			     		$success = true;
			     }
		      } 
		   }
		   if($success)
		   	echo "all $totalRecords records inserted successfully for year $year";
		   else echo "some error occurred while inserting records";

		   die;
		 }

	 public function InvoiceNumberExtraFieldUpdate(){
		   if(count($this->Invoice['EXTRADeatil'])>0){
		    foreach($this->Invoice['EXTRADeatil'] as $extrafield){
		     $this->_db->update(INVOICE_EXTRA_HEAD,array('invoice_number'=>$this->Invoice['invoice_number']),"user_id='".$extrafield[ADMIN_ID]."' AND invoice_number=0"); 
		   }
		  }
		  return; 	
		}

		/**

		*Cod Table update with Invoice number

		*Function : InvoiceNumberCodUpdate()

		*This function Update Cod Table with current Invoice Number

		**/

		public function InvoiceNumberCodUpdate(){

		  switch($this->Useconfig['level_id']){

			     case 1:

				  $codArr	  =array('depot_invoice_number'=>$this->Invoice['invoice_number']);

				 break;

				 case 4:
				  $codArr	  =array('customer_invoice_number'=>$this->Invoice['invoice_number']);

				 break;

				 case 6:
				   $codArr	  =array('customer_invoice_number'=>$this->Invoice['invoice_number']);
				 break;  

			}

		   if(count($this->Invoice['CodDetail'])>0){

		    foreach($this->Invoice['CodDetail'] as $cod){

		     $this->_db->update(INVOICE_COD,$codArr,"barcode_id='".$cod['barcode_id']."'"); 

		   }

		  } 

		  return;	

		}
		
		public function updateMailshipment(){

           switch($this->Useconfig['level_id']){
                 case 1:
                      $barcodeArr =array('depot_invoice_status'=>'1','depot_invoice_number'=>$this->Invoice['invoice_number']);
                     break;
                     case 4:
                      $barcodeArr =array('customer_invoice_status'=>'1','customer_invoice_status'=>$this->Invoice['invoice_number']);

                     break;

                     case 6:

                     $barcodeArr =array('customer_invoice_status'=>'1','customer_invoice_status'=>$this->Invoice['invoice_number']);

                     break;

                }

              foreach($this->Invoice['MSParcelDetal'] as $mailshipment){
                 $this->_db->update(MAIL_POST,$barcodeArr,"mailshipment_id='".$mailshipment['mailshipment_id']."'");

              }

      }

}





