<?php 
 

class Application_Model_Shipments extends Application_Model_Shipmentlabel
{
    
    public function __construct()
    {   parent::__construct();
        parent::addExtandable(new Application_Model_DPDLabel());
		parent::addExtandable(new Application_Model_GLSDELabel());
		parent::addExtandable(new Application_Model_GLSFreightLabel());
		parent::addExtandable(new Application_Model_GLSNLLabel());
		parent::addExtandable(new Application_Model_BpostLabel());
		parent::addExtandable(new Application_Model_ExpressLabel());
		parent::addExtandable(new Application_Model_PostatLabel());
		parent::addExtandable(new Application_Model_PostnlLabel());
		parent::addExtandable(new Application_Model_CODLabel());
		parent::addExtandable(new Application_Model_UPSLabel());
		parent::addExtandable(new Application_Model_ColissimoLabel());
		parent::addExtandable(new Application_Model_ParcelnlLabel());
		parent::addExtandable(new Application_Model_LDELabel());
		parent::addExtandable(new Application_Model_DHLLabel());
		parent::addExtandable(new Application_Model_MondialRelayLabel());
		parent::addExtandable(new Application_Model_AnpostLabel());
		parent::addExtandable(new Application_Model_YodelLabel());
		parent::addExtandable(new Application_Model_CorreosLabel());
		parent::addExtandable(new Application_Model_RDPAGLabel());
		parent::addExtandable(new Application_Model_WwplLabel());
		parent::addExtandable(new Application_Model_GLSITLabel());
		parent::addExtandable(new Application_Model_BRTLabel());
		parent::addExtandable(new Application_Model_FadelloLabel());
		parent::addExtandable(new Application_Model_DeburenLabel());
		parent::addExtandable(new Application_Model_OmnivaLabel());
		parent::addExtandable(new Application_Model_AramexLabel());
		parent::addExtandable(new Application_Model_RussianPostLabel());
		parent::addExtandable(new Application_Model_SystematicLabel());
		parent::addExtandable(new Application_Model_RswisspostLabel());
		parent::addExtandable(new Application_Model_HamacherLabel());
		parent::addExtandable(new Application_Model_UrgentSwisspostLabel());
		parent::addExtandable(new Application_Model_DCPostalLabel());
		parent::addExtandable(new Application_Model_BizCourierLabel());
    }

	public function addShipment(){
		// echo "<pre>sdf"; print_r($this->getData);die;
	   global $objSession,$labelObj;
	   $this->DataValidation();
	   $errorData = $this->ErrorCheck();
	   if($errorData){
	       if(($this->getData['shipment_type']==4 || $this->getData['shipment_type']==10)){
		      return $errorData;  
		   }elseif($this->getData['shipment_type']==2){
		         $this->getData['data_error'] =  $errorData; 
				 return false; 
		   }else{
		       $objSession->errorMsg = "Shipment Not Added Due to following error:-<br>".$errorData;
			   return false;
		   }
	   }
	   if(isset($this->getData['addservice_id']) && ($this->getData['addservice_id']==126 || $this->getData['addservice_id']==3 ||$this->getData['addservice_id']==149) && $this->getData['parcel_shop']!=''){
		  $shopdata =  json_decode($this->getData['parcel_shop']);
		  $forwarder_id = isset($shopdata->forwarder_id)?$shopdata->forwarder_id:0;
		  if($forwarder_id>0 && $forwarder_id!=$this->getData[FORWARDER_ID]){
		    $this->getData[FORWARDER_ID] = $forwarder_id;
		  }
	   }
	   
	   $shipment_id = $this->insertInToTable(SHIPMENT,array($this->getData));//echo "<pre>";print_r($this->getData);die;
	   if(isset($this->getData['addservice_id']) && ($this->getData['addservice_id']==126 || $this->getData['addservice_id']==3 ||$this->getData['addservice_id']==149) && $this->getData['parcel_shop']!=''){
	      $this->_db->insert(SHIPMENT_PARCELPOINT,array_filter(array('shipment_id'=>$shipment_id,'parcel_shop'=>$this->getData['parcel_shop'])));
	   }
	   $this->shipment_id = array($shipment_id);
	   $this->SaveAddressBook();
	   if($this->getData['shipment_type']==2){
	     return $this->shipment_id;
	   }
		if($this->getData[FORWARDER_ID]<=3) {
		    $logicSeesion = new Zend_Session_Namespace('logicSeesion');  
			if(isset($logicSeesion->userconfig['label_position']) && $logicSeesion->userconfig['label_position']=='a6'){
				$labelObj = new Zend_Labelclass_PDFLabel('P','mm','label');
		     }
		}
		if(isset($this->getData['addservice_id']) && $this->getData['addservice_id']==148){
		   $this->getData['shipment_type'] = 16;
		}	
	   $objSession->successMsg = "Shipment Added successfully!!";
	   if($this->getData['shipment_type']!=16){
	   	 return $this->CreateLabel();
	   }
	}

	public function editShipment($oldRecord){
	   global $objSession,$labelObj;
	   $this->getData['shipment_id'] =  Zend_Encript_Encription::decode($this->getData['shipment_id']);
	   
		$this->getData[ZIPCODE] = $this->ValidateZipcode($this->getData[ZIPCODE],$this->getData[COUNTRY_ID]);
		$this->getData[WEIGHT]  = commonfunction::stringReplace(',','.',$this->getData[WEIGHT]);
		$this->getData['cod_price']  = isset($this->getData['cod_price'])?commonfunction::stringReplace(',','.',$this->getData['cod_price']):0;
		$this->getData['shipment_worth']  =  isset($this->getData['shipment_worth'])?commonfunction::stringReplace(',','.',$this->getData['shipment_worth']):0;
	   $updateData = $this->getData;
	   unset($updateData['shipment_id']);
	   $updateData['modify_date'] = commonfunction::DateNow();
	   $updateData['addservice_id'] = (isset($updateData['addservice_id']) && $updateData['addservice_id']>0)?$updateData['addservice_id']:0;
	   $shipment_id = $this->UpdateInToTable(SHIPMENT,array($updateData),"shipment_id='".$this->getData['shipment_id']."'");
	   $this->shipmentEditLog($oldRecord);
	   $this->shipment_id = array($this->getData['shipment_id']);
	   $quantity_diff = $this->getData[QUANTITY] - $oldRecord[QUANTITY];
	   if($quantity_diff<0){
	       $this->deleteExtraShipment($quantity_diff);  
	   }
	   $this->getData['shipment_type'] = 0;
		if($this->getData[FORWARDER_ID]<=3) {
		    $logicSeesion = new Zend_Session_Namespace('logicSeesion');  
			if(isset($logicSeesion->userconfig['label_position']) && $logicSeesion->userconfig['label_position']=='a6'){
				$labelObj = new Zend_Labelclass_PDFLabel('P','mm','label');
		     }
		}
	   $objSession->successMsg = "Shipment Edited successfully!!";
	   $this->RecordData = $this->getData;
	   return $this->EditPrintLabel($oldRecord,$quantity_diff);
	}
	public function DataValidation(){
	    $this->getData[ZIPCODE] = $this->ValidateZipcode($this->getData[ZIPCODE],$this->getData[COUNTRY_ID]);
		$this->getData[WEIGHT]  = commonfunction::stringReplace(',','.',$this->getData[WEIGHT]);
		$this->getData['cod_price']  = isset($this->getData['cod_price'])?commonfunction::stringReplace(',','.',$this->getData['cod_price']):0;
		$this->getData['shipment_worth']  =  isset($this->getData['shipment_worth'])?commonfunction::stringReplace(',','.',$this->getData['shipment_worth']):0;
		$this->getData['create_date'] = commonfunction::DateNow();
	    $this->getData['create_by'] = $this->Useconfig['user_id'];
	    $this->getData['create_ip'] = (isset($this->getData['create_ip']))?trim($this->getData['create_ip']):commonfunction::loggedinIP();
		$this->getData['quantity'] = (!isset($this->getData['quantity']) || $this->getData['quantity']<=0 || $this->getData['quantity']>10)?1:$this->getData['quantity'];
		$this->getData['email_notification'] = (!empty($this->getData['rec_email'])) ? 1 : 0;
	}
	
	public function getNewShipment(){ 
		$this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$where = $this->LevelClause();
		if($this->getData['user_id']>0){
		    $where .=  " AND AT.user_id='".$this->getData['user_id']."'";
		}
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'AT.company_name','ASC');
		
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array("AT.user_id"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(""))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0'".$where)
									->group("AT.user_id");
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchAll($select);							
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array('SUM(BT.weight) AS Total_weight',"COUNT(barcode_id) AS total_quantity"))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array("COUNT(ST.shipment_id) AS Total_parcel"))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name","AT.user_id"))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0'".$where)
									->group("AT.user_id")
									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);//print_r($select->__toString());die;
		$records =  $this->getAdapter()->fetchAll($select);
		return array('Total'=>count($count),'Records'=>$records);
	}
	
	public function getShowAllShipments(){
	   try{
	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';
		$where = $this->LevelClause();
		$where .= commonfunction::filters($this->getData);
		if(isset($this->getData['shipment_type']) && $this->getData['shipment_type']>0){
		    $where .=  " AND ST.shipment_type='".$this->getData['shipment_type']."'";
		}
		if(isset($this->getData['from_date']) && isset($this->getData['to_date']) &&  $this->getData['from_date']!='' && $this->getData['to_date']!=''){
		    $where .=  " AND DATE(ST.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
		}
		if(isset($this->getData['print_status']) && $this->getData['print_status']>0){
		    $where .=  ($this->getData['print_status']==1)?" AND BD.label_date!='0000-00-00 00:00:00'":" AND BD.label_date='0000-00-00 00:00:00'";
		}
		if(isset($this->getData['action']) && $this->getData['action']=='refshipmentlist'){
		   $where .=  " AND ST.shipment_type=5";
		}
		
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ST.shipment_id','DESC');
		
		$select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('COUNT(1) AS CNT'))
									->joininner(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array(''))
									->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array(''))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array(""))
									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array(""))
									->joininner(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array(""))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0'".$where)
									->group("ST.shipment_id");
									//print_r($select->__toString());die;
		$count = $this->getAdapter()->fetchAll($select);							
		
	    $select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('rec_name','rec_reference','create_date','rec_zipcode','quantity','addservice_id','shipment_id'))
									->joininner(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array('pickup_status','weight'))
									->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference','assigned_date','label_date'))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name"))
									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array("FT.forwarder_name"))
									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name",'continent_id'))
									->joininner(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array("SV.service_name"))
									->where("BT.checkin_status='0' AND BT.delete_status='0' AND ST.delete_status='0'".$where)
									->group("ST.shipment_id")
									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
		 $records =  $this->getAdapter()->fetchAll($select);								
		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}
		
		return array('Total'=>count($count),'Records'=>$records);						
	}

	public function getdeletedshipment()
	{	
		// print_r($this->getData);die;

		$filters = $this->getData;	

		$where = 'ST.delete_status="1" && BR.delete_status="1" && BR.checkin_status="0"';

		if(isset($filters['user_id']) && $filters['user_id']>0){
		    $where .=  " AND ST.user_id='".$filters['user_id']."'";
		}
		 
		if(isset($filters['country_id']) && $filters['country_id']>0){
		    $where .=  " AND ST.country_id='".$filters['country_id']."'";
		}
		if(isset($filters['forwarder_id']) && $filters['forwarder_id']>0){
		    $where .=  " AND ST.forwarder_id='".$filters['forwarder_id']."'";
		}


		if(  (isset($filters['from_date']) && !(empty($filters['from_date'])) )  ){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(ST.create_date) BETWEEN '{$filters['from_date']}' AND NOW() ";
		}
		if( (isset($filters['to_date']) && !(empty($filters['to_date'])) ) ){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(ST.create_date) <= '{$filters['to_date']}' ";
		}
		if(  (isset($filters['from_date']) && !(empty($filters['from_date'])) )  &&  (isset($filters['to_date']) && !(empty($filters['to_date'])) )){
			// echo $filters['from_date'];die;
			$where .= " AND DATE(ST.create_date) BETWEEN '{$filters['from_date']}' AND '{$filters['to_date']}' ";
		}
		if(isset($filters['search_word']) && !empty($filters['search_word'])){
 		
	 		if(preg_match('/^[a-zA-Z0-9 ]+$/', $this->getData['search_word']) == false)
	 			{
	 				global $objSession;$objSession->errorMsg = "Search Content Should be AlphaNumeric only";
	 			}
	 		else $where.= " AND (ST.rec_name LIKE '{$filters['search_word']}%' || ST.rec_contact LIKE '{$filters['search_word']}%' || ST.rec_address LIKE '{$filters['search_word']}%' || ST.rec_reference LIKE '{$filters['search_word']}%' || ST.rec_streetnr LIKE '{$filters['search_word']}%' || ST.rec_zipcode LIKE '{$filters['search_word']}%' || ST.rec_city LIKE '{$filters['search_word']}%' ) ";	
 		}

		 
		// echo $where;die;
		

		$select = $this->_db->select()->from(array('ST'=>SHIPMENT),array('ST.shipment_id','ST.rec_name','ST.rec_reference','ST.rec_zipcode','ST.weight','ST.quantity','ST.create_date'))
			->joininner(array('CT'=>COUNTRIES),'CT.country_id=ST.country_id',array("CT.country_name"))
			->joininner(array('BR'=>SHIPMENT_BARCODE),'BR.shipment_id=ST.shipment_id',array("BR.barcode"))
			->joininner(array('FD'=>FORWARDERS),'FD.forwarder_id=ST.forwarder_id',array("FD.forwarder_name"))
			->joininner(array('UD'=>USERS_DETAILS),'UD.user_id=ST.user_id',array("UD.company_name"))
			->joininner(array('SD'=>SHIPMENT_DELETED),'SD.shipment_id=ST.shipment_id',array("SD.deleted_date"))
			->joininner(array('SR'=>SERVICES),'SR.service_id=ST.service_id',array("SR.service_name"))
		->where($where);

		$count = $this->getAdapter()->fetchAll($select);

		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ST.shipment_id','DESC');


		$selectall = $this->_db->select()->from(array('ST'=>SHIPMENT),array('ST.shipment_id','ST.rec_name','ST.rec_reference','ST.rec_zipcode','ST.weight','ST.quantity','ST.create_date'))
			->joininner(array('CT'=>COUNTRIES),'CT.country_id=ST.country_id',array("CT.country_name"))
			->joininner(array('BR'=>SHIPMENT_BARCODE),'BR.shipment_id=ST.shipment_id',array("BR.barcode","BR.barcode_id",'BR.checkin_status'))
			->joininner(array('FD'=>FORWARDERS),'FD.forwarder_id=ST.forwarder_id',array("FD.forwarder_name"))
			->joininner(array('UD'=>USERS_DETAILS),'UD.user_id=ST.user_id',array("UD.company_name"))
			// ->joininner(array('SR'=>SERVICES),'SR.service_id=ST.service_id',array("SR.service_name",new Zend_Db_Expr("CASE when ST.addservice_id!='0' then (select service_name from ".SERVICES." where service_id=ST.addservice_id ) else '--' END as subservice ") ))
			->joininner(array('SD'=>SHIPMENT_DELETED),'SD.shipment_id=ST.shipment_id',array("SD.deleted_date"))
			->joininner(array('SR'=>SERVICES),'SR.service_id=ST.service_id',array("SR.service_name"))
			->where($where);

			 
				
		if(isset($this->getData['export'])){
			  
			return array('selectall'=>$selectall);
		}

		$selectlimit = $selectall->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
			->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
		$res = $this->getAdapter()->fetchAll($selectlimit);
		
		return array('Total'=>count($count),'Records'=>$res);						
 
	}

	public function ExportDeletedShipments($data)
	{
		 $filename = "DeletedShipments.csv";
		 // echo $data['selectall']->__toString();die;
		 $res = $this->getAdapter()->fetchAll($data['selectall']);

		 	if(count($res) > 0)
			 	foreach ($res[0] as $key => $value) {
			 		$fields[] = str_replace('_', ' ', ucfirst($key)); 
			 	}

		 $this->ExporttoCsv($res,$filename,$fields);

	}


	public function ExporttoCsv($data,$filename,$fields='')
	{
		$fp = fopen('php://output', 'w');
		header('Content-type: application/csv');
		header('Content-Disposition: attachment; filename='.$filename);
		if($fields!='')
			fputcsv($fp, $fields);

		foreach ($data as $key => $value) {
			fputcsv($fp, $value);
		}
		exit;
	}




	public function reverttoshipment($data)
	{	
		if( (isset($data['reverthis']) && !empty($data['reverthis'])) && (isset($data['bar']) && !empty($data['bar']))){
			if(!is_array($data['reverthis']) && !is_array($data['bar'])){

				$arr = array('shipment_id'=>array($data['reverthis']),
						'bar'=> array($data['bar']) );
    				// print_r($arr);die;
    				$this->reverttoshipment($arr);
			   }
    			
    		}else{

					foreach ($data['shipment_id'] as $key => $value) {
						$shipment_id = Zend_Encript_Encription::decode( $value);
						$barcode_id = Zend_Encript_Encription::decode( $data['bar'][$key]);

						$select = $this->_db->select()->from(array('ST'=>SHIPMENT),array('COUNT(ST.shipment_id) as rowcnt'))
							->where("ST.shipment_id='$shipment_id'");
							$available = $this->getAdapter()->fetchAll($select)[0]['rowcnt'];
							if($available == 1){
								 // echo $shipment_id."--".$barcode_id."<br>";
								$this->_db->update(SHIPMENT,array('delete_status'=>0),"shipment_id='".$shipment_id."'");
								$this->_db->update(SHIPMENT_BARCODE,array('delete_status'=>0),"barcode_id='".$barcode_id."'");
								
							}

					}//die;
		 
		 	}
	}
	

	public function revertparcelwithcheckin()
	{	

	 try{	
		if( (isset($this->getData['reverthis']) && !empty($this->getData['reverthis'])) && 
			(isset($this->getData['bar']) && !empty($this->getData['bar']))
			){
				$shipment_id = Zend_Encript_Encription::decode($this->getData['reverthis']);
				$barcode_id = Zend_Encript_Encription::decode($this->getData['bar']);
					$this->reverttoshipment( array('shipment_id'=> array($this->getData['reverthis']),'bar'=> array($this->getData['bar']) )  );
					$this->CheckIN($barcode_id,1);
				$resp = array('status'=>1,'message'=>'Parcel Restored with checkin successfully');						
		}
	  }		
	   catch(Exception $e){
	   		$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		  $resp = array('status'=>0,'message'=>'Some internal error occurred');
		  return $resp;
		}
		  return $resp;
	}


	public function getShipmentHistory($limit=true){
		try{
	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';
		$where = $this->LevelClause();
		$where .= commonfunction::filters($this->getData);
		if(isset($this->getData['shipment_type']) && $this->getData['shipment_type']>0){
		    $where .=  " AND ST.shipment_type='".$this->getData['shipment_type']."'";
		}
		if(isset($this->getData['from_date']) && isset($this->getData['to_date']) &&  $this->getData['from_date']!='' && $this->getData['to_date']!=''){
		    //$where .=  " AND DATE(BD.checkin_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
			if(isset($this->getData['create_checkin']) && $this->getData['create_checkin']=='create_date'){
		      $where .=  " AND DATE(ST.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
		   }else{
			   $where .=  " AND DATE(BD.checkin_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";
			}
		}
		if(isset($this->getData['search_word']) && $this->getData['search_word']!=''){
		    $where .=  " AND (BT.tracenr_barcode='".trim($this->getData['search_word'])."' OR ST.rec_name LIKE '%".trim($this->getData['search_word'])."%' OR BD.rec_reference LIKE '%".trim($this->getData['search_word'])."%')";
		}
		if(isset($this->getData['status_id']) && $this->getData['status_id']!=''){
		    switch($this->getData['status_id']){
			   case 1:
			       $where .=  " AND BT.delivery_status='1'"; 
			   break;
			   case 2:
			       $where .=  " AND BT.delivery_status='0'"; 
			   break;
			   case 3:
			       $where .=  " AND BT.error_status='1'"; 
			   break;
			}
		}
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'BD.checkin_date','DESC');
		
		$select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))
									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array())
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array())
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array())
									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.delete_status='0'".$where);
		$count =$this->getAdapter()->fetchRow($select);//$count =  array('CNT'=>100);;
		
		//$paginations = commonfunction::PageCounter(500,$this->getData);
	    $select = $this->_db->select()
									->from(array('BT' =>SHIPMENT_BARCODE),array('*'))
									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference','checkin_date','checkin_ip','delivery_date','received_by'))
									//->joininner(array('CL'=>SHIPMENT_BARCODE_LOG),"CL.barcode_id=BT.barcode_id",array(''))
									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('rec_name','rec_street','rec_streetnr','rec_address','rec_street2','country_id','addservice_id','create_date','quantity','goods_id','create_ip','senderaddress_id','rec_zipcode','rec_city','rec_phone','rec_email'))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name",'customer_number','user_id'))
									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name"))
									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array("FT.forwarder_name"))
									->joininner(array('SR' =>SERVICES),"SR.service_id=BT.service_id",array("SR.service_name"))
									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.delete_status='0'".$where)
									->order("BT.error_status DESC")
									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType']);
									//print_r($select->__toString());die;
		 if($limit){
		   $select->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
		 }							
		$records =  $this->getAdapter()->fetchAll($select);
		return array('Total'=>$count['CNT'],'Records'=>$records);
		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());
		 return array('Total'=>0,'Records'=>array());
		}
	}
	public function getGoodsCategory($goods_check='D',$check=false){
	       $select = $this->_db->select()
									->from(array('GC' =>GOODS_CATEGORY),array('*'))
									->where("status='1'");
			if($check){
			    $select->where("goods_code=?",$goods_check);
			}else{
			    return $this->getAdapter()->fetchAll($select); 
			} 
									//print_r($select->__toString());die;
		return $this->getAdapter()->fetchRow($select);
	}
	public function AddressBookList(){
	     $select = $this->_db->select()
									->from(array('AB' =>ADDRESS_BOOK),array('name','contact','street','street_no','address','street2','postalcode','city','phone','email','country_id'))
									->joininner(array('CT'=>COUNTRIES),"AB.country_id=CT.country_id",array('country_name'))
									->where("user_id=?",$this->getData['user_id']);
		 if(isset($this->getData['Name'])){
		    $select->where("name LIKE ?", $this->getData['Name'].'%');
		 }
		 if(isset($this->getData['postalcode'])){
		    $select->where("postalcode LIKE ?", $this->getData['postalcode'].'%');
		 }
		 if(isset($this->getData['city'])){
		    $select->where("city LIKE ?", $this->getData['city'].'%');
		 }
		 if(isset($this->getData['country_id']) && $this->getData['country_id']>0){
		    $select->where("AB.country_id=?", $this->getData['country_id']);
		 }	//print_r($select->__toString());die;						
		 return $this->getAdapter()->fetchAll($select);							
	}
	public function ErrorCheck($chckType=0){
	   $error = array();
	    $this->getData[ZIPCODE] = $this->ValidateZipcode($this->getData[ZIPCODE],$this->getData[COUNTRY_ID]);
		$this->getData[WEIGHT]  = commonfunction::stringReplace(',','.',$this->getData[WEIGHT]);
		$this->getData['cod_price']  = isset($this->getData['cod_price'])?commonfunction::stringReplace(',','.',$this->getData['cod_price']):0;
		$this->getData['shipment_worth']  =  isset($this->getData['shipment_worth'])?commonfunction::stringReplace(',','.',$this->getData['shipment_worth']):0;

		print_r($this->getData);die;

	   if($this->getData['shipment_type']==5){
		   return false;
		}
	    $country_detail = $this->getCountryDetail($this->getData[COUNTRY_ID],1);
	   if($this->getData[ZIPCODE]=='' && ($country_detail['postcode_validate']=='' || $country_detail['postcode_validate']==1)){
	      $error[107] = 'Zipcode Can not be Blank!!';
	   }
	   if(!isset($this->getData[FORWARDER_ID]) || !isset($this->getData[SERVICE_ID])){
	      $error[111] = 'Routing Not found!!';
	   }
	   
	   if($this->getData[RECEIVER]==''){
	      $error[108] = 'Receiver Can not be blank!!';
	   }
	   if($this->getData[STREET]==''){
	      $error[109] = 'Street Can not be blank!!';
	   }
	   if($this->getData[CITY]==''){
	      $error[110] = 'City Can not be blank!!';
	   }
	   if($this->getData[WEIGHT]<=0){
	      $error[101] = 'Weight must be greater than 0!!';
	   }
	   if($this->getData[SERVICE_ID]<=0){
	      $error[102] =  'Please select/send valid service!!';
	   }
	   if($this->getData[ADMIN_ID]<=0){
	      $error[103] =  'Some problem in data Please try again!!';
	   }
	   if($this->getData[COUNTRY_ID]<=0){
	      $error[104] =  'Please assign valid country!!';
	   }
	   if(isset($this->getData['addservice_id']) && ($this->getData['addservice_id']==7 || $this->getData['addservice_id']==146) && isset($this->getData['cod_price']) && $this->getData['cod_price']<=0 && $chckType==0){
	     $error[105] =  'Either COD price is blank or 0, Please enter valid amount!!';
	   }
	   if(isset($this->getData['addservice_id']) && in_array($this->getData['addservice_id'],array(101,102,103,104,105,106,151,152,153,154,155,156)) && $this->getData[PHONE]<=0){
	     $error[106] =  'Phone number is required for Express service!!';
	   }
	   if($this->getData[SERVICE_ID]==6 && (!isset($this->getData['addservice_id']) || $this->getData['addservice_id']<=0)){
	      $error[112] =  'Additional Service Required for Freight!!';
	   }

	   
	   /*$prices = $this->CalculateParcelPrice($data);
	   if($this->_getData[CUSTOMER_PRICE]<=0){
	      return 'Either Customer Price is not set or Some problem in data Please try again!!';
	   }*/
	   if($chckType==1){
	      if(!empty($error)){
		       echo json_encode(array('error'=>1,'error_msg'=>commonfunction::implod_array($error,'<br>')));die;
		  }else{
		       echo json_encode(array('error'=>0,'error_msg'=>'Proceed'));die;
		  }
	   }
	   if((in_array($this->getData['shipment_type'],array(0,1,2,6,7,8,9,11,12,13,14))) && !empty($error)){
	       return commonfunction::implod_array('<br>',$error); 
	   }elseif(($this->getData['shipment_type']==4 || $this->getData['shipment_type']==10) && !empty($error)){
	        $error = array('Error'=>array('ErrorMessage'=>commonfunction::implod_array($error,',')));
	   }


	   
	   return $error;
	}
	
	public function getShipmentById(){
	  try{
	    $shipment_id = Zend_Encript_Encription::decode($this->getData['shipment_id']);
		$select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('*'))
									->joininner(array('BT' =>SHIPMENT_BARCODE),"BT.shipment_id=ST.shipment_id",array(''))
									->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference'))
									->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('continent_id','postcode_length'))
									->where("ST.shipment_id='".$shipment_id."'")
									->group("ST.shipment_id");
		}catch(Exception $e){ return array();}
		return $this->getAdapter()->fetchRow($select);	
	}
	
   public function shipmentEditLog($oldRecord){
       $oldRecord['edit_date'] =  commonfunction::DateNow();
	   $oldRecord['edit_by'] = $this->Useconfig['user_id'];
	   $oldRecord['edit_ip'] = commonfunction::loggedinIP();
	   $this->insertInToTable(SHIPMENT_EDITED,array($oldRecord));
	   return true;
   }  
   public function deleteExtraShipment($quantitydiff){
			$select = $this->_db->select()
								->from(SHIPMENT_BARCODE,array(BARCODE_ID))
								->where('shipment_id=?',$this->getData['shipment_id'])
								->where('checkin_status=?','0')
								->order("barcode_id DESC")
								->limit(abs($quantitydiff));
								//print_r($select->__toString());die;
			$result = $this->getAdapter()->fetchAll($select);
			foreach($result as $record){
			   $this->_db->delete(SHIPMENT_BARCODE,"barcode_id='".$record['barcode_id']."'");
			   $this->_db->delete(SHIPMENT_BARCODE_DETAIL,"barcode_id='".$record['barcode_id']."'");
			   $this->_db->delete(SHIPMENT_BARCODE_REROUTE,"barcode_id='".$record['barcode_id']."'");
			}
			return true;					
	}
	public function getImportheaders(){
	   $select = $this->_db->select()
								->from(IMPORT_HEADER,array("*"))
								->order('header_id DESC');
								//print_r($select->__toString());die;
	   $results = $this->getAdapter()->fetchAll($select); 
	   
	   return commonfunction::AssociatToLeanier($results,'header_index');
	}
	public function getImportSample(){
	   $select = $this->_db->select()
								->from(IMPORT_HEADER,array("*"))
								->order('header_id DESC');
								//print_r($select->__toString());die;
	   $results = $this->getAdapter()->fetchAll($select); 
	   
	   return commonfunction::AssociatToLeanier($results,'standar_name');
	}
	
	public function importShipment($flag=1,$imp_reco = array()){
	    global $objSession;
		if($flag==1){
			$this->getData['user_id'] = Zend_Encript_Encription::decode($this->getData['user_id']);
			$csvheader = $this->getImportheaders();
			$file_name = commonfunction::ImportFile('import_shipment','csv',$this->getData['user_id']);
			$button_type = (isset($this->getData['import']))?1:2;
			$CsvData = commonfunction::ReadCsv($file_name,';',$button_type);
			$ImportData = commonfunction::importAssociative($CsvData,$csvheader,$this->getData);
		}else{
		   $ImportData = $imp_reco;
		}
		$errorCount = 0;
		$successcount = 0;
		$ErrorList = array();
		foreach($ImportData as $key=>$import){
		  $error = 0 ;
		  $this->getData = $import;
		 /* $this->getData['quantity'] = (!isset($this->getData['quantity']) || $this->getData['quantity']<=0 || $this->getData['quantity']>10)?1:$this->getData['quantity'];
		  $this->getData['email_notification'] = (!empty($this->getData['rec_email'])) ? 1 : 0;*/
		  $this->DataValidation();
		  $routingerr = $this->checkRouting();
		  if(!$routingerr){
		    $ErrorList[] = 'Routing Error in row '.($key+1);
			$error =1 ;
		  }
		  $generalErr = $this->ErrorCheck(); 
		  if($generalErr){
		    $ErrorList[] = 'Following Error in row '.($key+1).'<br>'.$generalErr;
			$error =1 ;
		  }
		  if($error<=0){
		     	$shipment_id = $this->insertInToTable(SHIPMENT,array($this->getData));
	  			$this->shipment_id[] = $shipment_id;//$this->shipment_id = array($shipment_id);
				if(isset($this->getData['addservice_id']) && $this->getData['addservice_id']==126 && !empty($this->getData['parcel_shop'])){
					 $this->_db->insert(SHIPMENT_PARCELPOINT,array_filter(array('shipment_id'=>$shipment_id,'parcel_shop'=>$this->getData['parcel_shop'])));
				}
				if($flag==0){
				    $this->_db->insert(SHOP_API_SHIPMENT,array_filter(array('shipment_id'=>$shipment_id,'shop_id'=>$import['shop_id'],'shop_order_id'=>$import['shop_order_id']))); 
				}
				$successcount++;
		  }else{
		      $shipment_id = $this->insertInToTable(SHIPMENT_TEMP,array($this->getData)); 
			  $errorCount++;    
		  }
		  $error = 0;
		}
	
	   if($successcount > 0){
	      $objSession->successMsg = $successcount." Row Imported successfully!!";
	   }
	   if($errorCount>0){
	     $objSession->errorMsg = $errorCount. " Rows Rejected!<br>";
		 $objSession->errorMsg .= implode('<br>',$ErrorList);
		 $objSession->errorMsg .= '<a href="'.BASE_URL.'/Shipment/importerror">Click here</a>';
	   }
	}
	
	public function checkRouting($return=false){
	    $service_id = ($this->getData['addservice_id']>0 )?$this->getData['addservice_id']:$this->getData['service_id'];
	    $services = $this->getRoutingID($service_id,$service_id);
		$customerRouting = $this->getCustomerRouting($this->getData[ADMIN_ID],$this->getData[COUNTRY_ID]);
		foreach($services as $service){
		   if($service_id==$service['service_id']){
		     if($return){
			    if((!empty($customerRouting) && isset($customerRouting[$service['service_id']]) && $customerRouting[$service['service_id']]>0)){
				  $service['forwarder_id'] =   $customerRouting[$service['service_id']];
				}
			   return $service;
			 }else{
		      $this->getData['forwarder_id'] = (!empty($customerRouting) && isset($customerRouting[$service['service_id']]) && $customerRouting[$service['service_id']]>0)?$customerRouting[$service['service_id']]:$service['forwarder_id'];
		   	  $this->getData['original_forwarder'] = $service['forwarder_id'];
			  return true;
			 } 
		   }  
		} 
		return false;
	}
	public function PrintAction(){
		// echo $this->getData['shipment_mode'];die;
	   global $objSession;
	   switch($this->getData['shipment_mode']){
	      case 'Delete':
		     $this->DeleteParcel();
			 $objSession->successMsg = "Selected shipment(s) deleted successfully!";
		  break;
		  case 'PrintAll':
		    $this->PrintAllLabel();
		  break;
		  case 'PrintShippingList':
		    $this->PrintShippingList();
		  break;
		  case 'PerformaInvoice':
		    $obj = new Application_Model_Performainvoice();
			$result = $obj->Invoice($this->getData);
			if($result==false)
				return false;
		  break;
	   }
	}
	public function BulkPrinting(){
	   		// echo "dsfsdf<pre>";print_r($this->Useconfig);die;
	   if($this->getData['BulkShipping']>0){ 
	         $this->bulkshipment = $this->getData['BulkShipping'];
			 $this->PrintShippingList();
	   }
	   if($this->getData['BulkPrint']>0){
	        $this->bulkshipment = $this->getData['BulkPrint'];
		    $this->PrintAllLabel();  
	   }
	   if($this->getData['BulkProformaPrint']>0){
	        $this->bulkshipment = $this->getData['BulkProformaPrint'];
		    $obj = new Application_Model_Performainvoice();
			$result = $obj->Invoice($this->getData);
			if($result==false)
				return false;
	   }
	}
	public function DeleteParcel(){
	  if(!empty($this->getData[SHIPMENT_ID])){
		 foreach($this->getData[SHIPMENT_ID] as $shipment_id){	
			$select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array('shipment_id','barcode_id'))
								->where("BT.".SHIPMENT_ID."=".$shipment_id." AND BT.checkin_status='0'");
			$results = $this->getAdapter()->fetchAll($select);
			foreach($results as $result){
			    $result['deleted_by'] = $this->Useconfig['user_id'];
				$result['deleted_date'] = commonfunction::DateNow();
				$result['deleted_ip'] = commonfunction::loggedinIP();
				$this->insertInToTable(SHIPMENT_DELETED,array($result));
				$this->_db->update(SHIPMENT_BARCODE,array('delete_status'=>1),"barcode_id='".$result['barcode_id']."'");
			}
			$select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))
								->where("BT.".SHIPMENT_ID."=".$shipment_id." AND BT.checkin_status='1' AND BT.delete_status='0'");
			$checkinCount = $this->getAdapter()->fetchRow($select);
			if($checkinCount['CNT']<=0){
			   $this->_db->update(SHIPMENT,array('delete_status'=>1),"shipment_id='".$shipment_id."'");
			}
		}	 
	  }
	}
	
	public function getImportShipmentList(){
	    try{
	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';
		$where = $this->LevelClause();
		$where .= commonfunction::filters($this->getData);
		
		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'ST.shipment_id','DESC');
		
		$select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('COUNT(1) AS CNT'))
									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array(''))
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array(""))
									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=ST.forwarder_id",array(""))
									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array(""))
									->where("ST.delete_status='0' AND ISNULL(BT.barcode_id) AND ST.shipment_type!=16".$where);
									
		$count = $this->getAdapter()->fetchRow($select);							
		
	    $select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('rec_name','rec_reference','create_date','rec_zipcode','quantity','addservice_id','shipment_id','weight'))
									->joinleft(array('BT' =>SHIPMENT_BARCODE),"ST.shipment_id=BT.shipment_id",array())
									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name"))
									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=ST.forwarder_id",array("FT.forwarder_name"))
									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name"))
									->joininner(array('SV' =>SERVICES),"ST.service_id=SV.service_id",array("SV.service_name"))
									->where("ST.delete_status='0'  AND ISNULL(BT.barcode_id) AND ST.shipment_type!=16".$where)
									->group("ST.shipment_id")
									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])
									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);
		 $records =  $this->getAdapter()->fetchAll($select);								
		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}
		
		return array('Total'=>$count['CNT'],'Records'=>$records);
	}
	public function importPrint(){  
	    global $objSession;
	  if($this->getData['shipment_mode']!=''){ 
	   switch($this->getData['shipment_mode']){
			case 'Print':
				    $this->shipment_id = $this->getData[SHIPMENT_ID];
			 		$this->CreateLabel();
					$objSession->successMsg = "Label Printed successfully!";
					break;
			case 'Move':
				    $this->shipment_id = $this->getData[SHIPMENT_ID];
			 		$this->CreateLabel();
					$objSession->successMsg = "Shipment move to new Shipment successfully!";
				break;
			case 'Delete':
			      $this->shipment_id = $this->getData[SHIPMENT_ID];
				  $this->DeleteImportShipment();
				  $objSession->successMsg = "Selected shipment(s) deleted successfully!";
				break;		
		 }
		} 
		 if(isset($this->getData['shipment_mode1']) && $this->getData['shipment_mode1']>0){
				$this->bulkshipment = $this->getData['shipment_mode1'];
				$this->CreateLabel();
				$objSession->successMsg = "Label Printed successfully!";
		 }
	}
	public function DeleteImportShipment(){
	     $select = $this->_db->select()
									->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))
									->where("BT.shipment_id IN ('".implode("','",$this->shipment_id)."')")
									->group("BT.shipment_id");
		 $records =  $this->getAdapter()->fetchRow($select);
		 if($records['CNT']<=0){
		     $this->_db->delete(SHIPMENT,"shipment_id IN ('".implode("','",$this->shipment_id)."')");
		 }
	}
	
	public function ExportHistory(){
	     $exportData = $this->getShipmentHistory(false);
		 $commonobj = new Application_Model_Common();
		 $commonobj->shipmenthistoryExport($exportData);
		 //print_r($exportData);die;
	}
	
	
	public function PrintShippingList(){
	    // echo "<pre>"; print_r($this->getData);die;
		 $this->_db->query("SET SESSION group_concat_max_len = 1000000");
		 $where = $this->LevelClause();
	    if(!empty($this->bulkshipment) && $this->bulkshipment>0){
		    $select = $this->_db->select()
								->from(array('ST'=>SHIPMENT),array('GROUP_CONCAT(BT.shipment_id) AS shipment_ids'))
								->joininner(array('BT'=>SHIPMENT_BARCODE),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('GROUP_CONCAT(BT.barcode_id) AS barcodesids','COUNT(1) AS total_quantity','SUM(BT.weight) AS total_weight'))
								->joininner(array('AT'=>USERS_DETAILS),"AT.".ADMIN_ID."=ST.".ADMIN_ID,array(PARENT_ID,ADMIN_ID))
								->where("ST.delete_status='0' AND BT.delete_status='0' AND ST.shipment_type!=16 AND BT.checkin_status='0'".$where)
								->group("AT.user_id")
								->limit($this->bulkshipment,0);
		}else{
		    $select = $this->_db->select()
								->from(array('ST'=>SHIPMENT),array('GROUP_CONCAT(BT.shipment_id) AS shipment_ids'))
								->joininner(array('BT'=>SHIPMENT_BARCODE),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID,array('GROUP_CONCAT(BT.barcode_id) AS barcodesids','COUNT(1) AS total_quantity','SUM(BT.weight) AS total_weight'))
								->joininner(array('AT'=>USERS_DETAILS),"AT.".ADMIN_ID."=ST.".ADMIN_ID,array(PARENT_ID,ADMIN_ID))
								->where("ST.delete_status='0' AND BT.delete_status='0'  AND ST.shipment_type!=16 AND BT.checkin_status='0' AND ST.shipment_id IN ('".implode("','",$this->getData['shipment_id'])."')".$where)
								->group("AT.user_id")
								->limit(10);
		 }				  
			
		   $shipmentdata =  $this->getAdapter()->fetchAll($select);
	       $manifestObj = new Zend_Labelclass_ManifestPdf('P','mm','a4');
		   $file = 'ShippingList'.time().'.pdf';
			$manifestObj->outputparam['ListNo'] = $this->shippingListNumber();
		    foreach($shipmentdata as $key=>$useriddata){
			    $manifestObj->AddPage(); 
				$manifestObj->outputparam['getQuantityAndWeightByCountry'] = $this->getQuantityAndWeightByCountry($useriddata);
				$manifestObj->outputparam['Maindata'] = $useriddata;
				$manifestObj->outputparam['Forwarderdata'] = $this->getForwarderData($useriddata);
				$custtomerdetail = $this->getCustomerDetails($useriddata['user_id']);
				$manifestObj->outputparam['CustomerAddress'] =array($custtomerdetail['company_name'],$custtomerdetail['address1'],$custtomerdetail['postalcode'],$custtomerdetail['city'],$custtomerdetail['phoneno'],$custtomerdetail[COUNTRY_NAME]);	
		        $manifestObj->outputparam['DepotCompany'] = $custtomerdetail['parent_company']; 
			    $manifestObj->ShippngListPdf();
				$this->_db->update(SHIPMENT,array('shipping_list_no'=>$manifestObj->outputparam['ListNo']),"shipment_id IN(".$useriddata['shipment_ids'].")");
			}
			if($this->Useconfig['user_id']==412){			  
			 //print_r($select->__toString());die;
			}
			
			 ob_end_clean();
			$manifestObj->Output($file,'D');
			$manifestObj->PopUpLabel();
			
			//$manifestObj->Output($file,'D');
			//$manifestObj->PopUpLabel();
	}
	public function getForwarderData($input){
	   $select = $this->_db->select()
                        ->from(array('BT' => SHIPMENT_BARCODE), array('tracenr_barcode','weight'))
						->joininner(array('FT' => FORWARDERS), 'FT.forwarder_id=BT.forwarder_id', array('forwarder_name'))
                        ->where("BT.delete_status='0'  AND BT.barcode_id IN(".$input['barcodesids'].") AND BT.checkin_status='0'");
						//echo $select->__tostring();die;
        $result = $this->getAdapter()->fetchAll($select);
		$forwarderData =array();
		foreach($result as $parcels){
		 $forwarderData[$parcels['forwarder_name']][] = $parcels;
		}
	  return $forwarderData;	
	}
	public function getQuantityAndWeightByCountry($input){
		$barcodeids = array();
        $user_id = '';
        $select = $this->_db->select()
                        ->from(array('BT' => SHIPMENT_BARCODE), array('CT.country_name as Country', 'BT.weight as Weight','service_id'))
                        ->joininner(array('ST' => SHIPMENT), 'ST.shipment_id=BT.shipment_id', array('original_forwarder','country_id'))
						->joininner(array('AT' => USERS_DETAILS), 'AT.user_id=ST.user_id', array('parent_id'))
                        ->joininner(array('CT' =>COUNTRIES), 'CT.country_id=ST.country_id', array('CT.cncode'))
                        ->where("ST.delete_status='0' AND BT.delete_status='0'  AND  BT.barcode_id IN(".$input['barcodesids'].") AND BT.checkin_status='0' AND ST.user_id='" . $input['user_id'] . "'")
						->order('FIELD(CT.continent_id,2,0,1,2,3)')
						->order('CT.country_name')
						->order('BT.weight');
						//echo $select->__tostring();die;
        $result = $this->getAdapter()->fetchAll($select);
		$finaldata = array();
		foreach($result as $parcels){
		 if($parcels['Weight']<=2){
		   $class = '0-2'; 
		 }elseif($parcels['Weight']<=5){
		   $class = '2-5'; 
		 }elseif($parcels['Weight']<=10){
		   $class = '5-10'; 
		 }elseif($parcels['Weight']<=20){
		   $class = '10-20'; 
		 }elseif($parcels['Weight']>20){
		   $class = '20-30'; 
		 }
		 if($parcels['service_id']==7 || $parcels['service_id']==146){
		    $servicefield = 'COD'; 
		 }else{
		   $servicefield = 'HOME';
		 }
		 $finaldata[$parcels['cncode']][$servicefield][$class]['Country'] =  $parcels['cncode'];
		 $finaldata[$parcels['cncode']][$servicefield][$class]['WeightClass'] =  $class;
		 $finaldata[$parcels['cncode']][$servicefield][$class]['Weight'] =  (isset($finaldata[$parcels['cncode']][$servicefield][$class]['Weight'])?$finaldata[$parcels['cncode']][$servicefield][$class]['Weight']:0) + $parcels['Weight'];
		 $finaldata[$parcels['cncode']][$servicefield][$class]['Qantity'] =  (isset($finaldata[$parcels['cncode']][$servicefield][$class]['Qantity'])?$finaldata[$parcels['cncode']][$servicefield][$class]['Qantity']:0) + 1;
		}
        return $finaldata;
	}
	public function shippingListNumber(){
	   $select = $this->_db->select()
                    ->from(SHIPMENT, array(new Zend_Db_Expr('MAX(shipping_list_no)').' AS listnumber'));//echo $select->__tostring();die;
       $result = $this->getAdapter()->fetchRow($select);
	   return commonfunction::paddingleft(($result['listnumber']+1),5,'0');
	}
	
	public function getImportErrorList(){
	   try{
	    $this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';
		$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';
		$where = $this->LevelClause();
		$where .= commonfunction::filters($this->getData);
	    $select = $this->_db->select()
									->from(array('ST'=>SHIPMENT_TEMP),array('*'))
									->joininner(array('AT' => USERS_DETAILS), 'AT.user_id=ST.user_id', array('parent_id'))
									->joininner(array('CT' =>COUNTRIES), 'CT.country_id=ST.country_id', array('CT.country_name'))
									->joininner(array('SV' =>SERVICES), 'SV.service_id=ST.service_id', array('SV.internal_code'))
									->where("1".$where)
									->order("ST.temp_id DESC");//echo $select->__tostring();die;
		 $records =  $this->getAdapter()->fetchAll($select);								
		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());}
		
		return $records;
	}
	
	public function ChangeWeightAndService(){
	    $formData = $this->getData;
		global $objSession;
	   foreach($formData['shipment_id'] as $shipment_id){
	       $select = $this->_db->select()
									->from(array('ST'=>SHIPMENT),array('*'))
									->joininner(array('AT' => USERS_DETAILS), 'AT.user_id=ST.user_id', array('parent_id'))
									->where("ST.shipment_id='".$shipment_id."'");//echo $select->__tostring();die;
		  $records =  $this->getAdapter()->fetchRow($select);
		  // echo "<pre>";print_r($records);die;
		  $select = $this->_db->select()
                ->from(array('SV'=>SERVICES), array('*'))
				->where("SV.service_id='".$formData['change_service_id']."'");
		  $servicedetails = $this->getAdapter()->fetchRow($select);	
		  if(!empty($servicedetails)){
			   if($servicedetails['parent_service']==0){
				  $records['service_id'] 	= $servicedetails['service_id'];
				  $records['addservice_id'] = 0;
			   }else{
			      $records['service_id'] 	= $servicedetails['parent_service'];
				  $records['addservice_id'] = $servicedetails['service_id'];
			   }
		  }
		  if(!empty($formData['change_weight'])){
		  	$records['weight'] = commonfunction::stringReplace(',','.',$formData['change_weight']);
		  }
		  $this->getData = $records;  
		  $routingChecked = $this->checkRouting();
		  if($routingChecked){
		     $this->_db->update(SHIPMENT,array('weight'=>$this->getData['weight'],'service_id'=>$this->getData['service_id'],'addservice_id'=>$this->getData['addservice_id'],'forwarder_id'=>$this->getData['forwarder_id'],'original_forwarder'=>$this->getData['original_forwarder']),"shipment_id='".$shipment_id."'"); 
			 $objSession->successMsg .= "Weight or Service updated for record ".$this->getData['rec_name']."!<br>";
		  }else{
		    $objSession->errorMsg .= "Weight or Service not changed for record ".$this->getData['rec_name']." due to Routing not found!<br>";
		  }
		 // echo "<pre>";print_r($serviceRecord);die;
	   }
	}
	
	public function SaveAddressBook(){
	  try{
		if($this->getData['shipment_type']<=1) {
			$select = $this->_db->select()
						->from(ADDRESS_BOOK,array('COUNT(1) AS CNT'))
						->where("user_id='".$this->getData['user_id']."' AND country_id='".$this->getData['country_id']."'
								 AND name='".addslashes($this->getData['rec_name'])."' 
								 AND street='".addslashes($this->getData['rec_street'])."'
								 AND city='".addslashes($this->getData['rec_city'])."' 
								 AND LOWER(REPLACE(postalcode,' ',''))='".strtolower(str_replace(' ','',$this->getData['rec_zipcode']))."'");
			$result = $this->getAdapter()->fetchRow($select);
			if($result['CNT']<=0){
			  $this->_db->insert(ADDRESS_BOOK,array_filter(array('user_id'=>$this->getData['user_id'],
														  'country_id'=>$this->getData['country_id'],
														  'name'=>addslashes($this->getData['rec_name']),
														  'street'=>addslashes($this->getData['rec_street']),
														  'city'=>addslashes($this->getData['rec_city']),
														  'postalcode'=>$this->getData['rec_zipcode'],
														  'contact'=>$this->getData['rec_contact'],
														  'street_no'=>$this->getData['rec_streetnr'],
														  'address'=>$this->getData['rec_address'],
														  'streeet2'=>$this->getData['rec_street2'],
														  'phone'=>$this->getData['rec_phone'],
														  'email'=>$this->getData['rec_email'])));
			}
		}
	  }catch(Exception $e){ }	
	  return true;
	}
}