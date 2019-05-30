<?php
class Application_Model_PostatLabel extends Zend_custom{
	   public $ForwarderDetails =  array();
	  public function CreatePostatLabel($shipmentObj,$newbarcode=true){ 
	  			$this->ForwarderDetails  =  $shipmentObj->RecordData['forwarder_detail'];
	  			$this->postAtProduct($shipmentObj);
				$shipmentObj->RecordData['First17Digit'] = $this->ForwarderDetails['barcode_prefix']." ".$this->ForwarderDetails['customer_number']." ".$shipmentObj->RecordData[TRACENR]." ".$shipmentObj->RecordData['product_id'];
				
			   $shipmentObj->RecordData['spaceReplace17Digit'] = commonfunction::stringReplace(' ','',$shipmentObj->RecordData['First17Digit']);
			   $postalcode = commonfunction::onlynumbers($shipmentObj->RecordData[ZIPCODE]);
			   if($shipmentObj->RecordData[COUNTRY_ID]!=16){
			        $country_detail = $this->getCountryDetail($shipmentObj->RecordData[COUNTRY_ID]);
					$postalcode = commonfunction::stringReplace($country_detala['iso_code'],4,'0');
			    }
			
			$shipmentObj->RecordData['Last4Digit'] = commonfunction::sub_string($postalcode,0,4);
			$shipmentObj->RecordData['CheckDigit'] = $this->calculatePostAtCheckDigit($shipmentObj->RecordData['spaceReplace17Digit'].$shipmentObj->RecordData['Last4Digit']);
			
			// call function if additional service >0
			$this->postAtAdditionalServiceDetail($shipmentObj);
			$shipmentObj->RecordData[BARCODE] =  $shipmentObj->RecordData['spaceReplace17Digit'].$shipmentObj->RecordData['Last4Digit'].$shipmentObj->RecordData['CheckDigit'];
		    $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
			$this->postAtService($shipmentObj);
	  }
	  
	  public function postAtProduct($shipmentObj) {
			$serviceDetail = $this->getServiceDetails($shipmentObj->RecordData['addservice_id'],1);
			$additionalService = isset($serviceDetail['service_name'])?commonfunction::lowercase($serviceDetail['service_name']):'';
			$productArr = array('124'=>'10','115'=>'11');
			
			if(isset($productArr[$shipmentObj->RecordData['addservice_id']])){
				$select = $this->masterdb->select()
											  ->from(POSTAT_PRODUCTS,array('*'))
											  ->where('id=?',$productArr[$shipmentObj->RecordData['addservice_id']]);
			}else{
				$select = $this->masterdb->select()
										  ->from(POSTAT_PRODUCTS,array('*'))
										  ->where('id=?','3');
			}
			$result = $this->masterdb->fetchRow($select);
			
			$shipmentObj->RecordData['product_id']   = (isset($result['product_id']))?$result['product_id']:'0';
			$shipmentObj->RecordData['product_abbr'] = (isset($result['product_abbr']))?$result['product_abbr']:'0';
			return;
		} 
		/**
		 * @Function : postAtAdditionalServiceDetail()
		 * @Description : this function get the detail of postat additional service
		 */
		public function postAtAdditionalServiceDetail($shipmentObj) { 
			if($shipmentObj->RecordData['addservice_id']==7 || $shipmentObj->RecordData['addservice_id']==146){
			   $where = "ST.id=2"; 
			}else{
			   $where = "ST.id=0"; 
			}
			$select = $this->masterdb->select()
			          ->from(array('ST'=>POSTAT_SUB_SERVICES),array('additional_service'=>'name','ocr'=>'OCR',
								        'postat_symbol'=>'symbol','postat_verbal'=>'verbal'))
					  ->where($where);
			//echo $select->__tostring();die;
			$result = $this->masterdb->fetchRow($select);

			$shipmentObj->RecordData['Additional_Services'] = isset($result['additional_service'])?$result['additional_service']:'';
			$shipmentObj->RecordData['Additional_OCR']      = isset($result['ocr'])?$result['ocr']:'';
			$shipmentObj->RecordData['postat_symbol']       = isset($result['postat_symbol'])?$result['postat_symbol']:'';
			$shipmentObj->RecordData['postat_verbal']       = isset($result['postat_verbal'])?trim($result['postat_verbal']):'';
			return;
		}
		/**
		 * @Function : postAtService()
		 * @Description : this function find postat service from table
		 */
		public function postAtService($shipmentObj) {
			if($shipmentObj->RecordData['addservice_id']==7 || $shipmentObj->RecordData['addservice_id']==146){
				$select = $this->masterdb->select()
											  ->from(POSTAT_SERVICES,array('service_name'))
											  ->where('internal_code=?','COD');
				$result = $this->masterdb->fetchRow($select);
			}
			$shipmentObj->RecordData['IsCODService'] = isset($result['service_name'])?$result['service_name'] : '';
			return;
		}
		
	  public function calculatePostAtCheckDigit($number) {

		$multiplyArr = array(3,1,3,1,3,1,3,1,3,1,3,1,3,1,3,1,3,1,3,1,3);

		$resultSum = 0;

		for($i=0;$i<strlen($number);$i++) {

			$digit = (int)$number{$i};

			$resultSum += ($multiplyArr[$i] * $digit);

		}

		

		$remainder = ($resultSum % 10);

		if($remainder == 0)

		{

		    $checkDigit = 0;

		}

		else

		{

		    $checkDigit = (10 - $remainder);

		}

		

		return $checkDigit;

	}

}