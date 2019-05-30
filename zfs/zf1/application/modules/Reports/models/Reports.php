<?php



class Reports_Model_Reports extends Zend_Custom

{

    

	 public function getWeightDIfferenceParcel(){

	      try{

			$this->getData['user_id'] = isset($this->getData['user_id'])?Zend_Encript_Encription::decode($this->getData['user_id']):'';

			$this->getData['parent_id'] = isset($this->getData['parent_id'])?Zend_Encript_Encription::decode($this->getData['parent_id']):'';

			$where = $this->LevelClause();

			$where .= commonfunction::filters($this->getData);

			if(isset($this->getData['shipment_type']) && $this->getData['shipment_type']>0){

				$where .=  " AND ST.shipment_type='".$this->getData['shipment_type']."'";

			}

			if(isset($this->getData['from_date']) && isset($this->getData['to_date']) &&  $this->getData['from_date']!='' && $this->getData['to_date']!=''){

				$where .=  " AND DATE(BT.checkin_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";

			}

			if(isset($this->getData['search_word']) && $this->getData['search_word']!=''){

				$where .=  " AND (BT.tracenr_barcode='".trim($this->getData['search_word'])."' OR ST.rec_name LIKE '%".trim($this->getData['search_word'])."' OR BD.rec_reference='".trim($this->getData['search_word'])."')";

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

		$OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'BT.barcode_id','DESC');

		

		$select = $this->_db->select()

									->from(array('BT' =>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))

									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array())

									->joininner(array('WC'=>WEIGHT_CHANGE),"WC.barcode_id=BT.barcode_id",array(''))

									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array())

									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array())

									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.delete_status='0'".$where);

		$count =$this->getAdapter()->fetchRow($select);//$count =  array('CNT'=>100);;

		

	    $select = $this->_db->select()

									->from(array('BT' =>SHIPMENT_BARCODE),array('*'))

									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BD.barcode_id=BT.barcode_id",array('rec_reference','checkin_date','checkin_ip','delivery_date','received_by'))

									->joininner(array('WC'=>WEIGHT_CHANGE),"WC.barcode_id=BT.barcode_id",array('*'))

									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('rec_name','rec_street','rec_streetnr','rec_address','rec_street2','country_id','addservice_id','create_date','quantity','goods_id','create_ip','senderaddress_id','rec_zipcode','rec_city','rec_phone','rec_email'))

									->joininner(array('AT' =>USERS_DETAILS),"AT.user_id=ST.user_id",array("AT.company_name",'customer_number','user_id'))

									->joininner(array('CT' =>COUNTRIES),"CT.country_id=ST.country_id",array("CT.country_name"))

									->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array("FT.forwarder_name"))

									->joininner(array('SR' =>SERVICES),"SR.service_id=BT.service_id",array("SR.service_name"))

									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.delete_status='0'".$where)

									->order("BT.error_status DESC")

									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

									//print_r($select->__toString());die;

		$records =  $this->getAdapter()->fetchAll($select);

		return array('Total'=>$count['CNT'],'Records'=>$records);

		}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		 return array('Total'=>0,'Records'=>array());

		}

	 }

	 public function getForwarderReport(){

	    $select = $this->_db->select()

						  ->from(array('FT'=>FORWARDERS),array('*'))

						  ->order("FT.forwarder_name ASC");

		return $this->getAdapter()->fetchAll($select);

	}


	

	public function WrongAddressReport(){

	   try{

			$select = $this->_db->select()

					->from(array('PEL'=>PARCEL_TRACKING),array('*'))

					->joininner(array('EL'=>STATUS_LIST),'EL.error_id=PEL.status_id',array('error_desc'))

					->joinleft(array('PEM'=>WRONG_ADDRESS_MODIFICATION),'PEM.barcode_id=PEL.barcode_id',array(''))

					->joininner(array('SB'=>SHIPMENT_BARCODE),'SB.barcode_id=PEL.barcode_id',array('forwarder_id','barcode_id','tracenr_barcode'))

					->joininner(array('SA'=>SHIPMENT),'SA.shipment_id=SB.shipment_id',array('user_id','country_id','rec_street','rec_streetnr','rec_address','rec_zipcode','rec_city','rec_name'))

					->joininner(array('FT' =>FORWARDERS),"FT.forwarder_id=SB.forwarder_id",array("FT.forwarder_name"))

					->joininner(array('AT'=>USERS_DETAILS),'AT.user_id=SA.user_id',array('AT.company_name'))

					->joininner(array('CTR'=>COUNTRIES),'SA.country_id=CTR.country_id',array('CTR.country_name'))

					->where("PEL.status_id = (select status_id as maxid from ".PARCEL_TRACKING." as PEL1 where PEL.barcode_id=PEL1.barcode_id ORDER BY status_date DESC LIMIT 1)")

					->where("EL.master_id=1 AND ISNULL(PEM.errorlog_id) AND SB.checkin_status='1' AND PEL.status_date > NOW()- INTERVAL 1 MONTH AND SB.delivery_status='0'")

					->group('PEL.barcode_id')

					->order("PEL.status_date DESC");

					//print_r($select->__toString());die;

				return $this->getAdapter()->fetchAll($select);

			}catch(Exception $e){$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		 return array();

		}

	 }

	 public function CloseWorkDayBulkSend()
	{
		ini_set('display_erros', 1);
		try {
			
			$select = $this->_db->select()->from(array('BT'=>SHIPMENT_BARCODE),
				array(  
					'BT.barcode_id'
					// "substring_index( GROUP_CONCAT(BT.barcode_id),',',20 ) as barcodes"
					))
				->joinleft(array('MR'=>MANIFEST_API_REQUESTS),'BT.barcode_id=MR.barcode_id',array())
				->where("BT.forwarder_id=?",34)
				->where("MR.request_id IS NULL")
				// ->group("BT.forwarder_id")
				->limit(20)
				;
				// echo $select->__toString();die;
			$res = $this->getAdapter()->fetchAll($select);
			$barcodes = array_reduce($res, function($entry,&$reduced){
			echo "<pre>"; print_r($reduced); die;
				
				// $reduced = $entry['barcode_id'];
			});
			echo "<pre>"; print_r($barcodes); die;
			 
			if(!empty($barcodes)){

				$EdimodelObj = new Checkin_Model_GLSITEdi();
				$EdimodelObj->generateEDI(array(FORWARDER_ID=>34,BARCODE_ID=>$barcodes));

			}else echo "No barcode found ";
			echo "<pre>"; print_r($res); die;


		} catch (Exception $e) {
			echo $e->getMessage();die;	
		}


	}



}



