<?php



class Checkin_Model_OmnivaEdi extends Zend_Custom

{

   public $ForwarderRecord = array();

   public $Forwarders	= array();

   public $_DateCreated = NULL;

   public $_CreatedTime = NULL;

   public $objPHPExcel=''; 

	 public function generateEDI($data){ 

		$this->Forwarders = $this->ForwarderName($data[FORWARDER_ID],true);

		$this->_DateCreated = date('Ymd'); 

	    $this->_CreatedTime = date('his');

	  try{

	   $select = $this->_db->select()

							->from(array('BT'=>SHIPMENT_BARCODE),array('*'))

							->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(REFERENCE))

							->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,CONTACT,STREET,STREETNR,ADDRESS,CITY,ZIPCODE,STREET2,PHONE,EMAIL,

							 ADDSERVICE_ID,CREATE_DATE,ADMIN_ID,STREETNR,'currency',QUANTITY,'senderaddress_id','goods_id','shipment_worth','goods_description'))

							->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,'user_id'))

							->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array(COUNTRY_NAME,'cncode','cncode3','iso_code'))

							->joinleft(array('SRB' =>SHIPMENT_BARCODE_REROUTE),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.REROUTE_BARCODE.''))

							->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($data[BARCODE_ID]).")"); //print_r($select->__toString());die;

	    $results = $this->getAdapter()->fetchAll($select);

		 }catch(Exception $e){ $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage()); }

/////////////////////Excel header/////////////////////////////////////////////////////////////////		  

		  $this->objPHPExcel = new PHPExcel();

		  $this->objPHPExcel->setActiveSheetIndex(0);

		  $this->objPHPExcel->getActiveSheet()->setCellValue('A1', "AWB");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('B1', "BAG ID");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('C1', "PARCEL ID");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('D1', "CLIENT ID");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('E1', "NAME");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('F1', "ZIP");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('G1', "REGION");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('H1', "CITY");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('I1', "ADDRESS");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('J1', "PHONE NUMBER");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('K1', "EMAIL");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('L1', "COUNTRY");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('M1', "DESCRIPTION OF CONTENT");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('N1', "QUANTITY");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('O1', "WEIGHT PER ITEM, KG");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('P1', "WEIGHT PER PARCEL, KG");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('Q1', "PRICE PER ITEM, EUR");

		  $this->objPHPExcel->getActiveSheet()->setCellValue('R1', "PRICE PER ITEM, EUR");

		  $this->objPHPExcel->getActiveSheet()

    		->getStyle('A1:R1')

    		->getFill()

    		->setFillType(PHPExcel_Style_Fill::FILL_SOLID)

    		->getStartColor()

    		->setARGB('FFFF50');



/////////////////////Excel header/////////////////////////////////////////////////////////////////

		  

////////////////////////////////////////////XML start////////////////////////////////////////////////////////////		  

		  

		   $FinalDataArra = array();		

		   $FinalDataArra['TYPE'] 		  			         				= 'EECONSIGNMENT';     

		   $FinalDataArra['ORGANIZATION'] 		  							= 'Parcel.nl';  

		   $FinalDataArra['SENDER_ID'] 		  							= '38800'; 	

		   $FinalDataArra['RECIPIENT_ID'] 		  							= 'CD000'; 	

		   

			$FinalDataArra['DESPATCH']['DES_YEAR']           = date('y'); 

			$FinalDataArra['DESPATCH']['DES_CLOSE_DATE']          =date('ymd'); 

			$FinalDataArra['DESPATCH']['DES_CLOSE_TIME']        ='1849';

		    $row_count=2;	

		foreach($results as $i=>$result){

		    $this->RecordData = $result;

		  	$this->ForwarderRecord = $this->ForwarderDetail();

		//============================= Mail Item Data  ======================

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ITEM_ID']       = $result['barcode'];   

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['SERVICE_CODE']     = 'CP';   

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['SERVICE_PRIORITY']  = '1';  

			//Article 

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ARTICLE']['ARTICLE_CATALOG_NO']    = 1;   

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ARTICLE']['ARTICLE_QUANTITY']    	= '1';   

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ARTICLE']['ARTICLE_NAME']  		= $result['goods_description'];  

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ARTICLE']['ARTICLE_WEIGHT'] 		= $result['weight']; 

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ARTICLE']['ARTICLE_PRICE']  		= $result['shipment_worth'];

			//Address 

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ADRESS']['COUNTRY']    = $this->RecordData['rec_cncode'];  

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ADRESS']['AREA']    	= trim($result['rec_address'].' '.$result['rec_street2']);   

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ADRESS']['REGION']  		= '';  

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ADRESS']['CITY'] 		= $result['rec_city'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ADRESS']['ZIP']  		= $result['rec_zipcode'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ADRESS']['STREET']  		= $result['rec_street'].' '.$result['rec_streetnr'];

			

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['RECEIVER_NAME']    = $result['rec_name'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['RECEIVER_COMPANY']    = $result['rec_contact'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['RECEIVER_PHONE_NUMBER']    = '';

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['RECEIVER_EMAIL']    = $result['rec_email'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['RECEIVER_NO']    = $result['rec_phone'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ITEM_WEIGHT']    = $result['weight'];

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ITEM_COD_AMOUNT']    = 0;

			

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['ITEM_INSURANCE_VALUE']    = '0';

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['INSURANCE_CURRENCY']    = 'EUR';

			$FinalDataArra['DESPATCH']['ITEM_EVENT'.$i]['COD_CURRENCY']    = 'EUR';

/////////////////////////////Excel Body///////////////////////////////////////////////////////////////////////////

			$this->objPHPExcel->getActiveSheet()->setCellValue('A'.$row_count, '' );

			$this->objPHPExcel->getActiveSheet()->setCellValue('B'.$row_count, '' );

			$this->objPHPExcel->getActiveSheet()->setCellValue('C'.$row_count, utf8_decode( $result['barcode']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('D'.$row_count, '' );

			$this->objPHPExcel->getActiveSheet()->setCellValue('E'.$row_count, utf8_decode($result['rec_name']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('F'.$row_count, utf8_decode($result['rec_zipcode']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('G'.$row_count, '' );

			$this->objPHPExcel->getActiveSheet()->setCellValue('H'.$row_count, utf8_decode($result['rec_city']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('I'.$row_count, utf8_decode($result['rec_street']) );	

			$this->objPHPExcel->getActiveSheet()->setCellValue('J'.$row_count, utf8_decode($result['rec_phone']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('K'.$row_count, utf8_decode( $result['rec_email']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('L'.$row_count, utf8_decode($this->RecordData['rec_cncode']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('M'.$row_count, utf8_decode($result['goods_description']) );

			$this->objPHPExcel->getActiveSheet()->setCellValue('N'.$row_count, '1' );

			$this->objPHPExcel->getActiveSheet()->setCellValue('O'.$row_count, $result['weight']  );

			$this->objPHPExcel->getActiveSheet()->setCellValue('P'.$row_count, $result['weight']  );

			$this->objPHPExcel->getActiveSheet()->setCellValue('Q'.$row_count, $result['shipment_worth']  );

			$this->objPHPExcel->getActiveSheet()->setCellValue('R'.$row_count, $result['shipment_worth']  );

			$row_count=$row_count+1;

		}

		$filename = 'omniva_'.$this->Forwarders['IFD_number'].'_'.$this->_DateCreated.'_'.$this->_CreatedTime.'.xml';

////////////////////Excel Creat file///////////////////////////////////////////////////////////////////////////////////

		$excel_filename='manifestparcelnl'.date("YmdHi").'.xlsx';

		$letters = range('A', 'R');

		foreach ($letters as $letter_one) 

		{

		$this->objPHPExcel->getActiveSheet()->getColumnDimension($letter_one)->setAutoSize(true);

		}

	 $styleArray = array(

		  'borders' => array(

			'allborders' => array(

			  'style' => PHPExcel_Style_Border::BORDER_THIN

			)

		  )

		);





		$this->objPHPExcel->getActiveSheet()->getStyle('A1:R'.$row_count)->applyFromArray($styleArray);

		unset($styleArray);

		$this->objPHPExcel->setActiveSheetIndex(0);

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

		   //$objWriter->save('php://output');

		$objWriter->save(EDI_SAVE.'/'.$excel_filename);

		  ////////////////Send mail/////////////////////////

		$mailOBj = new Zend_Custom_MailManager();

		$email_text = 'New shipment of Parcel.nl';

		$mailOBj->emailData['SenderEmail'] = 'info@dpost.be';

		$mailOBj->emailData['SenderName']    = 'Parcelnl';

		$mailOBj->emailData['Subject'] = 'New shipment of Parcel.nl';

		$mailOBj->emailData['MailBody'] = $email_text;

		$mailOBj->emailData['ReceiverEmail'] = 'kristina.liivat@omniva.ee'; 

		$mailOBj->emailData['ReceiverName'] = 'kristina.liivat@omniva.ee';

		$mailOBj->emailData['user_id'] = $data['user_id'];

		$mailOBj->emailData['BCCEmail']  = array("dennis@parcel.nl");

		$mailOBj->emailData['notification_id'] = 0;

		$mailOBj->emailData['Attachemnt'] = EDI_SAVE.'/'.$excel_filename;

		$mailOBj->Send();

	

		return array('EdiData'=>$FinalDataArra,'EdiFilename'=>$filename);

    }

	

}



