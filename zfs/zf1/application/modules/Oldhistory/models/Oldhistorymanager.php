<?php


require_once 'Olddbcustom.php';

class Oldhistory_Model_Oldhistorymanager extends OlddbCustom

{	



	public function tabNavigation(){

		// echo "<pre>"; 
		//  print_r($this->getData);	
		//  die;
		$html = '';
		$controller = $this->getData['controller'];

 
		 
		$years = $this->getOldDbYears();

		$tabdata = array(  

						array(

							'controller' => 'Invoicehistory',
							'action' => 'getinvoicehistory',
							'years'  => $years
						),
						array(

							'controller' => 'Shipmenthistory',
							'action' => 'getshipmenthistory',
							'years'  => $years
						),
						array(

							'controller' => 'Edihistory',
							'action' => 'getoldedihistory',
							'years'  => $years
						),
					);

		foreach ($tabdata as $key => $value) {

			if($value['controller'] == $controller){

				foreach ($value['years'] as $k => $val) {
						$active =     isset($this->getData['year']) ? (($controller.$this->getData['action'].$this->getData['year'] == $value['controller'].$value['action'].$val )? 'active':''): ($val== $this->CurrentYear? 'active' : '' ); 	
					$html .= '<li class="'.$active.'"> <a href="'.BASE_URL.'/'.$value['controller'].'/'.$value['action'].'/'.$val.'" data-toggle="">'.$val.'</a> </li>'; 
			
				}
			}
		}
		return $html;
 

	}

	 

	public function getInvoicehistoryDetails()
	{	

		$filters = $this->getData;

		// print_r($this->getData);die;
		// $year = isset($this->getData['year']) ? $this->getData['year']:'2016';
		// echo $year;die;
		$where = "1";


		$filters['user_id'] = Zend_Encript_Encription::decode($filters['user_id']);


		if(isset($filters['user_id']) && $filters['user_id']>0){
		    $where .=  " AND INV.user_id='".$filters['user_id']."'";
		}

		if(  (isset($filters['from_date']) && !(empty($filters['from_date'])) )  ){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(INV.create_date) BETWEEN '{$filters['from_date']}' AND NOW() ";
		}

		if(  (isset($filters['payment_status']) && !empty($filters['payment_status']) ) || 
			(isset($filters['payment_status']) && $filters['payment_status'] !='') ){
			// echo $filters['payment_status'];die;
			$where .= " AND INV.payment_status ='{$filters['payment_status']}' ";
		}


		if( (isset($filters['to_date']) && !(empty($filters['to_date'])) ) ){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(INV.create_date) <= '{$filters['to_date']}' ";
		}
		if(  (isset($filters['from_date']) && !(empty($filters['from_date'])) )  &&  (isset($filters['to_date']) && !(empty($filters['to_date'])) )){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(INV.create_date) BETWEEN '{$filters['from_date']}' AND '{$filters['to_date']}' ";
		}
		if(isset($filters['search_word']) && !empty($filters['search_word'])){
 		
	 		if(preg_match('/^[a-zA-Z0-9 ]+$/', $this->getData['search_word']) == false)
	 			{
	 				global $objSession;$objSession->errorMsg = "Search Content Should be AlphaNumeric only";
	 			}
	 		else $where.= " AND (INV.invoice_number LIKE '{$filters['search_word']}%' || UD.company_name LIKE '{$filters['search_word']}%' ) ";	
 		}

 		// echo $where;die;

		$select = $this->oldDb->select()->from( array('INV'=>INVOICE), array('count(1) as allinvoice'))
		->joinleft(array('UD'=>USERS_DETAILS),'INV.user_id=UD.user_id')
		->where($where);

		$count = $this->oldDb->fetchAll($select)[0]['allinvoice'];

		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'INV.create_date','DESC');

		$selectlimit = $this->oldDb->select()->from( array('INV'=>INVOICE), array('INV.invoice_number','INV.total_amount','INV.invoice_date','INV.paid_amount','INV.payment_status','INV.payment_mode','INV.payment_date','INV.create_date','INV.file_name','INV.invoice_date'))
		->joinleft(array('UD'=>USERS_DETAILS),'INV.user_id=UD.user_id',array('UD.company_name'))
		->where($where)
		->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
		->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
		
		$res = $this->oldDb->fetchAll($selectlimit);
			// echo "<pre>";
		 // 	print_r($res);die;
		return array('Total'=>$count,'Record'=>$res);

		 
	}

	public function getShipmenthistoryDetails()
	{	
		// $year = isset($this->getData['year']) ? $this->getData['year']:'2016';
		$where = "1";
		$filters = $this->getData;

		if(isset($filters['search_word']) && !empty($filters['search_word'])){
 		
	 		if(preg_match('/^[a-zA-Z0-9 ]+$/', $this->getData['search_word']) == false)
	 			{
	 				global $objSession;$objSession->errorMsg = "Search Content Should be AlphaNumeric only";
	 			}
	 		else $where.= " AND (ST.rec_name LIKE '{$filters['search_word']}%' || ST.rec_contact LIKE '{$filters['search_word']}%' || ST.rec_address LIKE '{$filters['search_word']}%' || ST.rec_reference LIKE '{$filters['search_word']}%' || ST.rec_streetnr LIKE '{$filters['search_word']}%' || ST.rec_zipcode LIKE '{$filters['search_word']}%' || ST.rec_city LIKE '{$filters['search_word']}%' || BT.tracenr_barcode LIKE '{$filters['search_word']}%' ) ";	
 		}

		// echo $where;die;	
		$selectcount = $this->oldDb->select()
									->from(array('ST'=>SHIPMENT),array('ST.shipment_id'))
									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array(''))
									->joinleft(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(''))
									->joinleft(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->joinleft(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array(""))
									->joinleft(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array(""))
									->where($where);
		$count = $this->oldDb->fetchAll($selectcount);							

		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ST.shipment_id','DESC');
		 
		 $selectlimit = $this->oldDb->select()
									->from(array('ST'=>SHIPMENT),array('rec_name','rec_reference','create_date','rec_zipcode','quantity','ST.weight','addservice_id','shipment_id'))
									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('barcode','delivery_status'))
									->joinleft(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('checkin_date','barcode_id','received_by','delivery_date','BD.rec_reference as bdrefer'))
									->joinleft(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name"))
									->joinleft(array('FT' =>FORWARDERS),"FT.forwarder_id=ST.forwarder_id",array("FT.forwarder_name"))
									->joinleft(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name"))
									->joinleft(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array("SV.service_name",new Zend_Db_Expr("CASE when ST.addservice_id!='0' then (select service_name from ".SERVICES." where service_id=ST.addservice_id ) else '--' END as subservice ")))
									->where($where)
									->group("ST.shipment_id")
									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
									

		 		
			$res = $this->oldDb->fetchAll($selectlimit); 
			return array('Total'=>count($count),'Records'=>$res);						
		 	// echo "<pre>";
		 	// print_r($res);die;
	}

	 
	 public function SingleInvoiceRecord($invoice_no){
	   $invoice_number = Zend_Encript_Encription::decode($invoice_no);	
	   $select = $this->oldDb->select()
									->from(array('IT'=>INVOICE),('*'))
									->joininner(array('UD'=>USERS_DETAILS),"UD.user_id=IT.user_id",array('UD.company_name'))
									->where("invoice_number='".$invoice_number."'");

		return $this->oldDb->fetchRow($select);

	}

	public function getoledihistory()
	{
		// $year = isset($this->getData['year']) ? $this->getData['year']:'2016';
		$where = "1";
		$filters = $this->getData;

		 if(isset($filters['forwarder_id']) && $filters['forwarder_id']>0){
		    $where .=  " AND ET.forwarder_id='".$filters['forwarder_id']."'";
		}


		if(  (isset($filters['from_date']) && !(empty($filters['from_date'])) )  ){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(ET.create_date) BETWEEN '{$filters['from_date']}' AND NOW() ";
		}
		if( (isset($filters['to_date']) && !(empty($filters['to_date'])) ) ){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(ET.create_date) <= '{$filters['to_date']}' ";
		}
		if(  (isset($filters['from_date']) && !(empty($filters['from_date'])) )  &&  (isset($filters['to_date']) && !(empty($filters['to_date'])) )){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(ET.create_date) BETWEEN '{$filters['from_date']}' AND '{$filters['to_date']}' ";
		}  

		 // echo $where;die;

		$select = $this->oldDb->select()->from(array('ET'=>SHIPMENT_EDI),array('COUNT(1) AS CNT'))
							->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=ET.'.FORWARDER_ID.'', array('FT.forwarder_name'))->where($where);

		$total = $this->oldDb->fetchRow($select);

		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ET.create_date','DESC');
		

		$selectlimit = $this->oldDb->select()->from(array('ET'=>SHIPMENT_EDI),array('*'))
						->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=ET.'.FORWARDER_ID.'', array('FT.forwarder_name'))
						->where($where)
						->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
						->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

		$result = $this->oldDb->fetchAll($selectlimit);	
		 
		return array('Total'=>$total['CNT'],'Records'=>$result);
		// echo "<pre>";
		// print_r($result);	
		// die;
	}

	public function updateInvoice(){

	    global $objSession;

	   try{ 
	   	$invoice_number = Zend_Encript_Encription::decode($this->getData['invoice_number']);
	   	// print_r($this->getData);die;
	    $res = $this->oldDb->update(INVOICE,array('payment_mode'=>$this->getData['payment_mode'],'payment_date'=>$this->getData['payment_date'],'paid_amount'=>$this->getData['paid_amount'],'payment_status'=>$this->getData['payment_status'],'remark'=>$this->getData['remark']),"invoice_number='".$invoice_number."'");
	    if($res)
	    	$resp = array('status'=>1,'message'=>'Invoice updated successfully');
	    else $resp = array('status'=>0,'message'=>'Some Internal error Occurred');
	     return $resp;

		}catch(Exception $e){

		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		}

		return true;

	}


	






	public function ExportFinancialInvoice(){
		// echo "gf";die;

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

	   if(isset($this->getData['search_word']) && $this->getData['search_word']!=''){

	      $where .= " AND SUBSTRING(IT.invoice_number,9,6)='".commonfunction::paddingleft($this->getData['search_word'],6,0)."'";

	   }

	   if(isset($this->getData['from_date']) && isset($this->getData['to_date']) && $this->getData['from_date']!='' && $this->getData['to_date']!=''){

	      $where .= " AND IT.invoice_date BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";

	   }

	   if(isset($this->getData['invoice_number']) && !empty($this->getData['invoice_number']) && count($this->getData['invoice_number'])>0){

		      $where .= " AND IT.invoice_number IN (".commonfunction::implod_array($this->getData['invoice_number'],',').")";  

		}

		   // echo $where;die;

		 $select = $this->oldDb->select()

			 					->from(array('ICD'=>INVOICE_FINANCIAL),array('*'))

								->joininner(array('IT'=>INVOICE),'IT.invoice_number=ICD.invoice_number',array('invoice_date'))

								->joininner(array('AT'=>USERS_DETAILS),'AT.'.ADMIN_ID.'=IT.'.ADMIN_ID,array('customer_number','company_name'))

								->where("IT.is_list='0'")

								->where($where);

		 $results = $this->oldDb->fetchAll($select);



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

		//print_r($out);die;

		commonfunction::ExportCsv($out,'Financial Invoice','csv');  



	 } 




}



