<?php



class Checkin_Model_Edimanager extends Zend_Custom

{

	public $EDIData = array();

	public $ManifestPdfObj = NULL;

	public $forwarders = array();

	public function forwarderManifest($group = true){

	     $where = $this->LevelClause();
	     // echo $this->getData['forwarder_id']."dfsdf<br>";
		 if(isset($this->getData['forwarder_id']) && $this->getData['forwarder_id']>0){

		   $where .= " AND BT.forwarder_id='".$this->getData['forwarder_id']."'";

		 }

		 if($group){

		   $column = array('COUNT(1) as total_quantity','SUM(BT.weight) AS total_weight','forwarder_id');

		}else{

		   $column = array('BT.barcode_id','BT.forwarder_id','BT.barcode','ST.rec_name','AT.company_name','BD.checkin_date','ST.create_date','BT.weight');

		}
		$where .= ($this->getData['action']=='urgentletter')?' AND (ST.addservice_id=115 OR ST.addservice_id=145) AND BT.weight<=0.5':' AND (IF(((ST.addservice_id=115 OR ST.addservice_id=145) AND BT.weight<=0.5),(BT.forwarder_id!=45),(1)))';
		$where .= ($this->getData['action']=='specialbpost')?' AND AT.parent_id=186':'AND (IF(AT.parent_id=186,(BT.forwarder_id!=7),(1)))';
		$where .= ($this->getData['action']=='specialups')?' AND AT.user_id=3553':' AND (IF(AT.user_id=3553,(BT.forwarder_id NOT IN(17,18,19)),(1)))';
		$where .= ($this->getData['action']=='specialdhl')?' AND AT.user_id IN(3256,3545) AND ST.addservice_id NOT IN(124,148)':' AND (IF((AT.user_id=3256 || AT.user_id=3545),(BT.forwarder_id NOT IN(24,25)),(1)))';
		try{
		 $select = $this->_db->select()

								->from(array('BT'=>SHIPMENT_BARCODE),$column)

								->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(''))

								->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(''))

								->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(''))

								->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=BT.'.FORWARDER_ID.'', array('FT.forwarder_name'))

								->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array('CT.country_name'))

								->joininner(array('GST' =>SERVICES),'GST.'.SERVICE_ID.'=BT.'.SERVICE_ID.'',array('GST.service_name'))
								->joinleft(array('HUB' =>SHIPMENT_HUB),'HUB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(''))
		// echo "<pre>"; print_r($this->getData);die;
								->where("BT.edi_status='0' AND ((BT.hub_status='0') OR (BT.hub_status='1' AND HUB.hub_checkin_status='1' AND HUB.hub_edi='0')) AND  BT.checkin_status='1'".$where);

		if($group){

		  $select->group("BT.forwarder_id"); 

		  $select->order("FT.forwarder_name ASC");

		}else{
		   $select->order("BD.checkin_date DESC");
		}						

		$result = $this->getAdapter()->fetchAll($select);
		// echo "<pre>"; print_r($result);die;
		return $result;
	 }catch(Exception $e){
	   echo  $e->getMessage();die;
	 }	

	}

	

	 

	public function GenerateEDIAndManifest(){ 
	   if(!isset($this->getData['barcode_id']) && empty($this->getData['barcode_id']) && !isset($this->getData['hub_manifest'])){

	        $barcodrecords = $this->forwarderManifest(false);

			foreach($barcodrecords as $barcode_ids){

			  $this->getData['barcode_id'][] = $barcode_ids['barcode_id'];

			}

	   }

	   $this->EDIData['manifest_file'] = 'Manifest_'.date('Y_m_d_h_i_s').'.pdf';
	   $this->getData['manifest_file'] =  $this->EDIData['manifest_file'];
	   $this->EDIData['manifest2'] = '';

	   // echo "<pre>"; print_r($this->getData);die;
	   
	   if(isset($this->getData['forwarder_manifest'])){ 
	     $this->GenerateEDI();

	   		echo "dont go",die;
		 // $this->GenerateForwarderManifest();

	   }

	   die;

	   if(isset($this->getData['hub_manifest'])){

	       $this->SendToHub();

	   }

	}

	public function GenerateForwarderManifest(){

	     $where = $this->LevelClause();

		 if(!isset($this->getData['barcode_id']) && empty($this->getData['barcode_id'])){

	        $barcodrecords = $this->forwarderManifest(false);

			foreach($barcodrecords as $barcode_ids){

			  $this->getData['barcode_id'][] = $barcode_ids['barcode_id'];

			}

	    }

	     $this->ManifestPdfObj = new Zend_Labelclass_ManifestPdf('P','mm','a4');

		if( $this->getData[FORWARDER_ID]==17 ||  $this->getData[FORWARDER_ID]==18 ||  $this->getData[FORWARDER_ID]==19){

										 $select = $this->_db->select()

                                        ->from(array('BT'=>SHIPMENT_BARCODE),array('SUM(BT.weight) as Totalweight','COUNT(1) as Totalquantity',SERVICE_ID,'forwarder_id','service_id'))

                                        ->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,ADDSERVICE_ID,CREATE_DATE,

                                         'GROUP_CONCAT(ST.addservice_id) as addservice','senderaddress_id','goods_id'))

                                        ->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID,ADMIN_ID))

                                         ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('manifest_customer_number','manifest_customer_name'))

                                        ->where(BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")");

          }

	   else{

	      $select = $this->_db->select()

                                            ->from(array('BT'=>SHIPMENT_BARCODE),array('SUM(BT.weight) as Totalweight','COUNT(1) as Totalquantity',SERVICE_ID))

                                            ->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,ADDSERVICE_ID,CREATE_DATE,

                                             'GROUP_CONCAT(ST.addservice_id) as addservice'))

                                            ->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(PARENT_ID))

                                             ->joininner(array('FT'=>FORWARDERS),"FT.forwarder_id=BT.forwarder_id",array('manifest_customer_number','manifest_customer_name','depot_address'))

                                            ->where('BT.'.BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")")

                                            ->group('BT.'.SERVICE_ID);

          }

							//print_r($select->__toString());die;

		$result = $this->getAdapter()->fetchAll($select);

		

		$this->ManifestPdfObj->outputparam['ManifestNo'] = $this->ForwarderManifestNumber();

		$this->ManifestPdfObj->outputparam['ParcelData'] = $result;

		$this->ManifestPdfObj->outputparam['forwarder_id'] = $this->getData[FORWARDER_ID];

		if($this->getData[FORWARDER_ID]==17 || $this->getData[FORWARDER_ID]==18 || $this->getData[FORWARDER_ID]==19){

			$this->RecordData = $result[0];

			$this->ForwarderRecord = $this->ForwarderDetail();

			$this->ManifestPdfObj->outputparam['UPSdetail'] = $this->ForwarderRecord;

            $this->ManifestPdfObj->outputparam['UPSParcelNumber'] = $this->UPSParcelNumberforManifest();

			$this->ManifestPdfObj->UPSManifest();

		}

		elseif($this->getData[FORWARDER_ID]==36){

			$this->ManifestPdfObj->outputparam['DPParcelNumber'] = $this->RDPAGParcelNumberforManifest();

			$this->ManifestPdfObj->RDPAGmanifest();



		}

		elseif($this->getData[FORWARDER_ID]==45 && $this->getData['urgentletter']){
				
			$this->ManifestPdfObj->outputparam['WeightClasses']=$this->EscorreosParcelNumberforManifest();

			$this->ManifestPdfObj->Escorreosmanifest();

		}

		elseif($this->getData[FORWARDER_ID]==49 || $this->getData[FORWARDER_ID]==51){

			$this->ManifestPdfObj->outputparam['DPParcelNumber']=$this->RDPAGParcelNumberforManifest();

			$this->ManifestPdfObj->outputparam['InputData'] = $this->getData;

			$this->ManifestPdfObj->SwisspostManifest();

		}

		else{

			$this->ManifestPdfObj->outputparam['WeightClasses'] = $this->wightClassParcel();

			$this->ManifestPdfObj->ForwarderManifest();

			if($this->getData[FORWARDER_ID]==5){

				  // $objFrieght = new glsfreightLabel();

				  // $objFrieght->releaseFrieghtParcel($data);

			}

		}

		

		global $objSession;	

		$Directory = FORWARDER_MANIFEST_SAVE.date('Y_m');

		if(!is_dir($Directory)){

			mkdir($Directory);

			chmod($Directory, 0777);

		} 

		$this->ManifestPdfObj->outputparam['ManifestNo'] = $this->ForwarderManifestNumber();

        $this->ManifestPdfObj->Output($Directory.'/'.$this->EDIData['manifest_file'],'F');

        $objSession->ManifestFile = FORWARDER_MANIFEST_OPEN.date('Y_m').'/'.$this->EDIData['manifest_file'];

		$this->_db->update(SHIPMENT_BARCODE,array('edi_status'=>'1'),"".BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")");

		$this->_db->update(SHIPMENT_BARCODE_DETAIL,array('manifest_number'=>$this->ManifestPdfObj->outputparam['ManifestNo'],'edi_date'=>new Zend_Db_Expr('NOW()')),"".BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")");

		 if($this->getData[FORWARDER_ID]==49 || $this->getData[FORWARDER_ID]==51){

		    $this->sendManifestEmail($Directory.'/'.$this->EDIData['manifest_file']);

		 }

	}

	public function wightClassParcel(){

	    $select = $this->_db->select()

									->from(array('BT'=>SHIPMENT_BARCODE),array('BT.weight',SERVICE_ID))

									->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array('ST.addservice_id'))

									->where(BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")")

									->order('BT.weight');

	    $result = $this->getAdapter()->fetchAll($select);

		$finaldata = array();

		  foreach($result as $parcels){

		    if($this->getData['forwarder_id']==33){	 

				 if(($parcels['weight']*1000)<=100){

				   $class = '0-100'; 

				 }elseif(($parcels['weight']*1000)<=250){

				   $class = '100-250'; 

				 }elseif(($parcels['weight']*1000)<=500){

				   $class = '250-500'; 

				 }elseif(($parcels['weight']*1000)<=750){

				   $class = '500-750'; 

				 }elseif(($parcels['weight']*1000)>750){

				   $class = '750-1000'; 

				 }

			}else{

			    if($parcels['weight']<=1){

				   $class = '0-1'; 

				 }elseif($parcels['weight']<=5){

				   $class = '1-5'; 

				 }elseif($parcels['weight']<=10){

				   $class = '5-10'; 

				 }elseif($parcels['weight']<=20){

				   $class = '10-20'; 

				 }elseif($parcels['weight']>20){

				   $class = '20-30'; 

				 }



			}

			 $finaldata[$parcels['service_id']][$class]['Service'] =  $parcels['service_id'];

			 $finaldata[$parcels['service_id']][$class]['AddService'] =  $parcels['addservice_id'];

			 $finaldata[$parcels['service_id']][$class]['WeightClass'] =  $class;

			 $finaldata[$parcels['service_id']][$class]['Weight'] =   (isset($finaldata[$parcels['service_id']][$class]['Weight'])?$finaldata[$parcels['service_id']][$class]['Weight']:0) + $parcels['weight'];

			 $finaldata[$parcels['service_id']][$class]['Qantity'] = (isset($finaldata[$parcels['service_id']][$class]['Qantity'])?$finaldata[$parcels['service_id']][$class]['Qantity']:0) + 1;

		} 

		return $finaldata;

	}

	

	public function SendToHub(){

		// ini_set('display_errors', 1);
		
		if(!isset($this->getData['barcode_id']) && empty($this->getData['barcode_id'])){

			if(!empty($this->getData['forwarder_id']) && is_array($this->getData['forwarder_id'])){
				$this->forwarders = $this->getData['forwarder_id'];
			
			// echo "<pre>"; print_r($this->getData);die;

			  	foreach ($this->forwarders as $key => $value) {
		   				// print_r($this->forwarders); echo "<br>";
		   			$this->getData['forwarder_id'] = $value;

			        $barcodrecords = $this->forwarderManifest(false);

					foreach($barcodrecords as $barcode_ids){

					  $this->getData['barcode_id'][] = $barcode_ids['barcode_id'];

					}
				}
		  }		

	   }
		 
		// echo "<pre>"; print_r($this->getData);die;

	      $where = $this->LevelClause();
			 $this->getData['hub_user_id'] = Zend_Encript_Encription::decode($this->getData['hub_user_id']);
			$depot_id = 188;
			switch($this->Useconfig['level_id']){
			   case 4:
				 $depot_id = $this->Useconfig['user_id'];
			   break;
			   case 6:
				 $depot_id = $this->Useconfig['parent_id'];
			   break;
			   default:
				 $depot_id = Zend_Encript_Encription::decode($this->getData['user_id']);
			}
		 // if(!isset($this->getData['barcode_id']) && empty($this->getData['barcode_id'])){

	  //       $barcodrecords = $this->forwarderManifest(false);

			// foreach($barcodrecords as $barcode_ids){

			//   $this->getData['barcode_id'][] = $barcode_ids['barcode_id'];

			// }

	  //  		}
		 

	     $this->ManifestPdfObj = new Zend_Labelclass_ManifestPdf('P','mm','a4');

		 $select = $this->_db->select()

                                        ->from(array('BT'=>SHIPMENT_BARCODE),array('SUM(BT.weight) as Totalweight','COUNT(1) as Totalquantity',SERVICE_ID,'COUNT(DISTINCT ST.shipment_id) as total_parcel'))

                                        ->joinleft(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(COUNTRY_ID,RECEIVER,ADDSERVICE_ID,CREATE_DATE,

                                         'GROUP_CONCAT(ST.addservice_id) as addservice'))

                                        ->joinleft(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'', array(PARENT_ID))

                                        ->joinleft(array('PT' =>USERS_DETAILS),'PT.'.ADMIN_ID.'=AT.'.PARENT_ID.'', array('company_name','address1','address2','postalcode','city','phoneno','email'))

                                        ->joinleft(array('CT'=>COUNTRIES),"CT.country_id=PT.country_id",array('country_name'))

                                        ->where(BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")".$where)

                                        ->group("BT.service_id");

							//print_r($select->__toString());die;

                 $this->ManifestPdfObj->outputparam['ParcelDetail'] = $this->getAdapter()->fetchAll($select);

				 $hubuserData = $this->getCustomerDetails($this->getData['hub_user_id']);
				 $DepotData = $this->getCustomerDetails($depot_id);
				 $this->ManifestPdfObj->outputparam['Hubdetail'] = $hubuserData;
				 $this->ManifestPdfObj->outputparam['Depotdetail'] = $DepotData;

                 $select = $this->_db->select()

                                        ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','tracenr_barcode','weight','forwarder_id'))

										->joinleft(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array())

										->joinleft(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'', array())

                                        ->joininner(array('FT'=>FORWARDERS),"BT.forwarder_id=FT.forwarder_id",array('forwarder_name'))

										//->joinleft(array('SRB' =>'shipmentreroute'),'SRB.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('SRB.'.SHIPMENT_REROUTE_BARCODE.''))

                                         ->where("BT.barcode_id IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")".$where);

                 $datas = $this->getAdapter()->fetchAll($select);

                 foreach($datas as $forwarderdata){

                    $this->ManifestPdfObj->outputparam['Forwarderdata'][$forwarderdata[FORWARDER_ID]][] = $forwarderdata;

                 }

                 // echo "<pre>"; 
           	 // print_r($this->ManifestPdfObj);
           	 
            // die;

		$file = 'Hub_Manifest'.time().'.pdf';	

		global $objSession;	

		$Directory = FORWARDER_MANIFEST_SAVE.date('Y_m');

		// echo $Directory.$file; die;

		if(!is_dir($Directory)){

			mkdir($Directory);

			chmod($Directory, 0777);

		} 

		$this->ManifestPdfObj->outputparam['ManifestNo'] = $this->ManifestNumber();

        $this->ManifestPdfObj->Hubmanifestpdf();

        $this->ManifestPdfObj->Output($Directory.'/'.$file,'F');

        $objSession->ManifestFile = FORWARDER_MANIFEST_OPEN.date('Y_m').'/'.$file;

        // $this->insertHubdetail();

        return;		 

	}

	

	public function insertHubdetail(){
		// echo "<pre>"; print_r($this->getData[BARCODE_ID]); die;
	    foreach($this->getData[BARCODE_ID] as $barcode_id){ 
               $this->_db->insert(SHIPMENT_HUB, array('barcode_id'=>$barcode_id,'hub_userid'=>$this->getData['hub_user_id'],'hub_date'=>new Zend_Db_Expr('NOW()')));
        }

		$this->_db->update(SHIPMENT_BARCODE,array('hub_status'=>'1'),"".BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")");
		$this->_db->update(SHIPMENT_BARCODE_DETAIL,array('hub_manifest_number'=>$this->ManifestPdfObj->outputparam['ManifestNo']),"".BARCODE_ID." IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")");

	}

	

	public function ManifestNumber(){

	   $select = $this->_db->select()

						   ->from(SHIPMENT_BARCODE_DETAIL,array('hub_manifest_number'))

						   ->order(array('hub_manifest_number DESC'))

						   ->limit(1,0);

		$result = $this->getAdapter()->fetchRow($select);

	   if(!empty($result)){

		 return str_pad($result['hub_manifest_number']+1,9,'0',STR_PAD_LEFT);

	  }else{

		return '000000001';

	  }

	}

	

	public function ForwarderManifestNumber(){

	   $select = $this->_db->select()

						   ->from(SHIPMENT_BARCODE_DETAIL,array('manifest_number'))

						   ->order(array('manifest_number DESC'))

						   ->limit(1,0);

		$result = $this->getAdapter()->fetchRow($select);

	   if(!empty($result)){

		 return str_pad($result['manifest_number']+1,9,'0',STR_PAD_LEFT);

	  }else{

		return '000000001';

	  }

	}

	

	public function GenerateEDI(){

		$Directory = EDI_SAVE;

		if(!is_dir($Directory)){

			mkdir($Directory);

			chmod($Directory, 0777);

		}

	    switch($this->getData['forwarder_id']){

		   case 1:

		   case 2:

		   case 3:

		   case 32:

		   case 54:

		      $dpdEdi = new Checkin_Model_DPDEdi();

			  $ediinfo = $dpdEdi->generateEDI($this->getData);

			  $semfile = commonfunction::writeFile($ediinfo['EdiData'],"sem",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			  $noextnfile =commonfunction::writeFile($ediinfo['EdiData'],"",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			  $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			  $this->EDIData['upload_file'] = array(basename($semfile),basename($noextnfile));

		   break;

		   case 7:

		   	  $Bpostedi = new Checkin_Model_BpostEdi();

			  $ediinfo = $Bpostedi->generateEDI($this->getData);

			  $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			  $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;
		   case 11:
		       
		      $postatedi = new Checkin_Model_PostatEdi();
			  $ediinfo = $postatedi->generateEDI($this->getData);
			  $savefile = commonfunction::writeFile($ediinfo['EdiData'],"csv",EDI_SAVE.'/'.$ediinfo['EdiFilename']);
			  $this->EDIData['upload_file'] = array(basename($savefile));
		   break;

		   case 14:

		      $postnledi = new Checkin_Model_PostnlEdi();

			  $ediinfo = $postnledi->generateEDI($this->getData);

			  $savefile = commonfunction::writeFile($ediinfo['EdiData'],"LST",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			  $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;
		   case 16:
		      $colisprivedi = new Checkin_Model_ColispriveEdi();
			  $ediinfo = $colisprivedi->generateEDI($this->getData);
			  $savefile = commonfunction::writeFile($ediinfo['EdiData'],"dat",EDI_SAVE.'/'.$ediinfo['EdiFilename']);
			  $this->EDIData['upload_file'] = array(basename($savefile));
		   break;

		   case 17:

		   case 18:

		   case 19:

		   	 $upsedi = new Checkin_Model_UPSEdi();

			 $ediinfo = $upsedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 20:

		   	 $colissimoedi = new Checkin_Model_ColissimoEdi();

			 $ediinfo = $colissimoedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"dat",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $filesize = filesize($savefile);

			 commonfunction::writeFile($ediinfo['EdiFilename'].'.dat'." ".$filesize,"file",EDI_SAVE.'/'.'control',true);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 24:

		   	 $dhledi = new Checkin_Model_DHLEdi();

			 $ediinfo = $dhledi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"dat",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 25:

		   	 $dhledi = new Checkin_Model_DHLEdi();

			 $ediinfo = $dhledi->generateDHLGlobalEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"dat",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 26:

		     $dpdatedi = new Checkin_Model_DPDATEdi();

			 $ediinfo = $dpdatedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));

		   break;

		   case 27:

		   	 $mondialdi = new Checkin_Model_MondialRelayEdi();

			 $ediinfo = $mondialdi->generateMondialRelay($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 28:

		   	 $anpostedi = new Checkin_Model_AnpostEdi();

			 $ediinfo = $anpostedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"xls",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 30:

		   	 $correosedi = new Checkin_Model_CorreosEdi();

			 $ediinfo = $correosedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 34:

		   	 $glsitedi = new Checkin_Model_GLSITEdi();

			 $ediinfo = $glsitedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 37:

		   	 $BRTedi = new Checkin_Model_BRTEdi();

			 $ediinfo = $BRTedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"csv",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 43:

		   	 $Omnivaedi = new Checkin_Model_OmnivaEdi();

			 $ediinfo = $Omnivaedi->generateEDI($this->getData);

				$dom = new DOMDocument ( '1.0', 'UTF-8' );

				$root = $dom->createElement ('MESSAGE');

				$method = $root;

				$root->setAttribute ( 'status', 'complete' );

				$root->setAttribute ( 'name', 'EECONSIGNMENT' );

				$dom->appendChild ( $root );

				$this->createXmlstructure ( $ediinfo['EdiData'], $dom, $method );

				$struct = ( array ) $ediinfo['EdiData'];

				$saveData = $dom->save(EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			    $this->EDIData['upload_file'] = array(basename($ediinfo['EdiFilename']));

		   break;

		   case 45:
			 if($this->getData['urgentletter']){
				  return true;
			  }
		   	 $escorreosedi = new Checkin_Model_ESCorreosEdi();

			 $ediinfo = $escorreosedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 46:

		   	 $russianpostedi = new Checkin_Model_RussianpostEdi();

			 $ediinfo = $russianpostedi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"xls",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 48:

		   	 $systematicedi = new Checkin_Model_SystematicEdi();

			 $ediinfo = $systematicedi->generateEDI($this->getData);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   case 50:

		   	 $hamacheredi = new Checkin_Model_HamacherEdi();

			 $ediinfo = $hamacheredi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"dfa",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		   break;

		   default:

		     $gerenaledi = new Checkin_Model_GeneralEdi();

			 $ediinfo = $gerenaledi->generateEDI($this->getData);

			 $savefile = commonfunction::writeFile($ediinfo['EdiData'],"txt",EDI_SAVE.'/'.$ediinfo['EdiFilename']);

			 $this->EDIData['upload_file'] = array(basename($savefile));	

		}

		$this->EDIData['edi_file'] = basename($savefile);

		// $this->uploadEDI();
		//$this->EDIData['upload_status'] = '0';
		 if($this->getData['forwarder_id']!=48){
			$this->saveEDI();
		 }
	}

	

	public function saveEDI(){

	  $this->_db->insert(SHIPMENT_EDI,array_filter(array('forwarder_id'=>$this->getData['forwarder_id'],'edi_file_name'=>$this->EDIData['edi_file'],'manifest_file_name'=>$this->EDIData['manifest_file'],'create_ip'=>commonfunction::loggedinIP(),'manifest2'=>$this->EDIData['manifest2'],'create_date'=>new Zend_Db_Expr('NOW()'),'upload_status'=>$this->EDIData['upload_status'])));
	  if($this->getData['forwarder_id']==17 || $this->getData['forwarder_id']==18 || $this->getData['forwarder_id']==19){
	     $this->_db->update(FORWARDERS,array('IFD_number'=>new ZEND_DB_EXPR('IFD_number + 1')),"forwarder_id IN(17,18,19)");
	  }elseif(($this->getData['forwarder_id']==24 || $this->getData['forwarder_id']==25) && isset($this->getData['special_edi'])){
	      $this->_db->update(DHL_SETTINGS,array('IFD_number'=>new ZEND_DB_EXPR('IFD_number + 1')),"forwarder_id IN(24,25)");
	  }else{
	     $this->_db->update(FORWARDERS,array('IFD_number'=>new ZEND_DB_EXPR('IFD_number + 1')),"forwarder_id ='".$this->getData['forwarder_id']."'"); 
	  }
	 

    }

	public function uploadEDI(){

	   //$this->EDIData['upload_status'] = '0';

	   $ftp_sftp = new Zend_Custom_EdiUpload();

	   switch($this->getData['forwarder_id']){

	      case 1:

		  case 2:

		  case 3:

		  case 32:

		  case 54:

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

		    $ftp_sftp->close();

			

			$ftp_sftp = new Zend_Custom_EdiUpload();

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/'.$this->EDIData['upload_file'][1],EDI_SAVE.'/'.$this->EDIData['upload_file'][1],FTP_ASCII);

		    $ftp_sftp->close();

		  break;

		  case 7: //Bpost

		     $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			 $this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

		     $ftp_sftp->close(); 

		  break;

		 case 11: //Postat

		 	 $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			 $this->EDIData['upload_status'] = $ftp_sftp->uploadsftp(EDI_SAVE.'/'.$this->EDIData['upload_file'][0],'/'.$this->EDIData['upload_file'][0]);

			 //$this->EDIData['upload_status'] = $ftp_sftp->uploadsftp('/0021113016-20170320204737-902.csv',EDI_SAVE.'/0021113016-20170320204737-902.csv',FTP_ASCII);

		     $ftp_sftp->close(); 

		 break;

		 case 14:

			$mailOBj = new Zend_Custom_MailManager();

			$email_text = 'Post NL EDI File';

			$mailOBj->emailData['SenderEmail'] = 'info@dpost.be';

			$mailOBj->emailData['SenderName']    = 'Parcelnl';

			$mailOBj->emailData['Subject'] = 'Post NL EDI File';

			$mailOBj->emailData['MailBody'] = $email_text;

			$mailOBj->emailData['ReceiverEmail'] = 'voormeldenpostnl@brinkmail.nl'; //'sanjeev.kumar@sjmsoftech.com';

			$mailOBj->emailData['ReceiverName'] = 'voormeldenpostnl@brinkmail.nl';

			$mailOBj->emailData['user_id'] = $this->getData['user_id'];

			$mailOBj->emailData['notification_id'] = 0;

			$mailOBj->emailData['Attachemnt'] = EDI_SAVE.'/'.$this->EDIData['upload_file'][0];

			$mailOBj->Send();

			$this->EDIData['upload_status'] = 1;

		 break;
		 case 16:
		     $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);
			 $this->EDIData['upload_status'] = $ftp_sftp->uploadsftp(EDI_SAVE.'/'.$this->EDIData['upload_file'][0],'/sftp/vers_colisprive/'.$this->EDIData['upload_file'][0]);
		     $ftp_sftp->close(); 
		 break;

		 case 17:

		 case 18:

		 case 19:

		   $this->EDIData['upload_status'] = $this->uploadeups(EDI_SAVE.'/'.$this->EDIData['upload_file'][0]); 

			 //$this->EDIData['upload_status'] = 0;

		 break;

		 case 20:  //Colissimo

		    $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/pub/upload_new/newdbccom_plat/810106/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

		    $ftp_sftp->close();

			

			$ftp_sftp = new Zend_Custom_EdiUpload();

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/pub/upload_new/newdbccom_plat/810106/control.file'.$this->EDIData['upload_file'][1],EDI_SAVE.'/control.file',FTP_ASCII);

		    $ftp_sftp->close();

		 break;

		 case 24:

		 case 25:
			if(isset($this->getData['special_edi'])){
			  try{
		        $ftp_sftp->getForwarderSeparateFTP($this->getData['forwarder_id']);
			    $this->EDIData['upload_status'] = $ftp_sftp->uploadsftp(EDI_SAVE.'/'.$this->EDIData['upload_file'][0],'/prod/in/'.$this->EDIData['upload_file'][0]);
				}catch(Exception $e){
				   //echo $e->getMessage();die;
				}
			 }else{ 
			   $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);
			   $this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/prod/in/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);
		       $ftp_sftp->close(); 
			 } 

		 break;

		 case 26:

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/upload/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

			$ftp_sftp->close();

		 break;

		 case 27:

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('versmrelay/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

			//$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('versmrelay/dpc.20170317.175518.txt',EDI_SAVE.'/dpc.20170317.175518.txt',FTP_ASCII);

			$ftp_sftp->close();

		 break;
		 case 30:
		   	  $json_response   	= file_get_contents("http://newsystem.logicparcel.net/correos_edi_upload.php?filetoken=".$this->EDIData['upload_file'][0]);
			  $server_response 	= json_decode($json_response);
			  $ediStatus 		= $server_response->message;
			  $this->EDIData['upload_status'] 	= ($ediStatus == 1) ? 1 : 0;
		 break;

		 case 34:

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

			$ftp_sftp->close();

		 break;
		 case 37:
		 	$mailOBj = new Zend_Custom_MailManager();
			$mailOBj->emailData['SenderEmail'] = 'info@dpost.be';
			$mailOBj->emailData['SenderName']    = 'Parcelnl';
			$mailOBj->emailData['Subject'] = '1071071';
			$mailOBj->emailData['MailBody'] = '1071071';
			$mailOBj->emailData['ReceiverEmail'] = 'vas@brt.it';
			$mailOBj->emailData['ReceiverName'] = 'vas@brt.it';
			$mailOBj->emailData['BCCEmail']  = array("sanjeev.kumar@sjmsoftech.com");
			$mailOBj->emailData['user_id'] = $this->getData['user_id'];
			$mailOBj->emailData['notification_id'] = 0;
			$mailOBj->emailData['Attachemnt'] = EDI_SAVE.'/'.$this->EDIData['upload_file'][0];
			$mailOBj->Send();
			$this->EDIData['upload_status'] = 1;
		    
		 break;

		 case 43:

			$ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/manifests/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

			$ftp_sftp->close();

		 break;
		 case 45:
		      $json_response   	= file_get_contents("http://newsystem.logicparcel.net/ESCorreos_edi_upload.php?filetoken=".$this->EDIData['upload_file'][0]);
			  $server_response 	= json_decode($json_response);
			  $ediStatus 		= $server_response->message;
			  $this->EDIData['upload_status'] 	= ($ediStatus == 1) ? 1 : 0;
		 break;

		 case 46:

		 case 48:

		    $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/out/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

			$ftp_sftp->close();

		 break;

		 case 50:

		    $ftp_sftp->getForwarderFTP($this->getData['forwarder_id']);

			$this->EDIData['upload_status'] = $ftp_sftp->uploadeFtp('/D:/FTP/EX/9346/IN/'.$this->EDIData['upload_file'][0],EDI_SAVE.'/'.$this->EDIData['upload_file'][0],FTP_ASCII);

			$ftp_sftp->close();

		 break;

		  	 

		 default:

		 $this->EDIData['upload_status'] = '0'; 

	   }

	}

	

	public function uploadeups($filename){

            //$filename = EDI_FILE_LINK.$filename;

			global $objSession;

			$handle = fopen($filename, "r");

			$contents = fread($handle, filesize($filename));

			fclose($handle);

			//print_r($contents);die;

			$data = "--BOUNDARY\r\nContent-type: application/x-www-form-urlencoded\r\nContent-length: 140\r\n\r\nAppVersion=1.0&AcceptUPSLicenseAgreement=YES&ResponseType=application/x-ups-pld&VersionNumber=V4R1&UserId=logicparcel&Password=Let25Dance\r\n\r\n--BOUNDARY\r\nContent-type: application/x-ups-binary\r\nContent-length: 719\r\n\r\n".$contents."\r\n\r\n--BOUNDARY--"; 

			$tuCurl = curl_init(); 

			curl_setopt($tuCurl, CURLOPT_URL, "https://www.pld.ups.com/hapld/tos/kdwhapltos"); 

			curl_setopt($tuCurl, CURLOPT_PORT , 443); 

			curl_setopt($tuCurl, CURLOPT_VERBOSE, 0); 

			curl_setopt($tuCurl, CURLOPT_HEADER, 1); 

			//curl_setopt($tuCurl, CURLOPT_SSLVERSION, 3);

			curl_setopt($tuCurl, CURLOPT_POST, TRUE); 

			curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, FALSE); 

			curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);

			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Content-type: multipart/mixed; boundary=BOUNDARY", "Content-length: ".strlen($data)));

			curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, TRUE); 

			

			$tuData = curl_exec($tuCurl); 

			curl_close($tuCurl);

			 

			$response = explode('BOUNDARY',$tuData);

			$response1 = explode('%',$response[3]);

			$response2 = explode('-------------------------------------------------------------------------------',$response[4]); //print_r($response2);die;
			$this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$tuData);
			if($response1[2]=='0000'){

			  $objSession->successMsg = 'Edi Uploaded Successfully';

			  return 1;

			}

			else if($response1[2]=='6931' && $response1[3] == "3005Already Processed. --"){ //print_r($response);die;

			  $objSession->errorMsg = $response2[1];

			  return 1;

			}elseif(strpos($response[4],'Already Processed')!== false){

                $objSession->successMsg = 'Already Processed.';

			    return 1;

                        }

			else{ //print_r(substr($response2[2],5,strpos($response2[2],'.')));die;

			 $objSession->errorMsg = substr($response2[2],5,strpos($response2[2],'.'));

			  return 0;

			}

			

     }

	

	public function ediHistory(){

	    $where = '';

		if(isset($this->getData['forwarder_id']) && $this->getData['forwarder_id']>0){

		  $where .= " AND ET.forwarder_id='".$this->getData['forwarder_id']."'";

		}

		if(isset($this->getData['from_date']) && isset($this->getData['to_date']) && $this->getData['from_date']!=''  && $this->getData['to_date']!=''){

		  $where .= " AND DATE(ET.create_date) BETWEEN '".$this->getData['from_date']."' AND '".$this->getData['to_date']."'";

		}

		$select = $this->_db->select()

								->from(array('ET'=>SHIPMENT_EDI),array('COUNT(1) AS CNT'))

								->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=ET.'.FORWARDER_ID.'', array('FT.forwarder_name'))

								->where("1".$where);

		$total = $this->getAdapter()->fetchRow($select);

		

		$select = $this->_db->select()

								->from(array('ET'=>SHIPMENT_EDI),array('*'))

								->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=ET.'.FORWARDER_ID.'', array('FT.forwarder_name'))

								->where("1".$where)

								->order("ET.create_date DESC")

								->limit(100);

		$result = $this->getAdapter()->fetchAll($select);	

		return array('Total'=>$total['CNT'],'Records'=>$result);					

	}

	

	public function DownloadEDI(){

	     commonfunction::readFile(ROOT_PATH.'/private/EDI/'.$this->getData['file_name']);

	}

	

	public function UPSParcelNumberforManifest(){

	   $select = $this->_db->select()

                                    ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight'))

                                    ->where("BT.barcode_id IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")");

		$result = $this->getAdapter()->fetchAll($select);						   

		return $result;						   

	}

	public function RDPAGParcelNumberforManifest(){

	   $select = $this->_db->select()

                                    ->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight','forwarder_id'))

									->joininner(array('ST' =>SHIPMENT),'ST.shipment_id=BT.shipment_id',array('ST.rec_name','ST.rec_contact','ST.rec_zipcode','ST.rec_city','CONCAT(ST.rec_street," ",ST.rec_streetnr) AS address'))

									->joininner(array('CT' =>COUNTRIES),'CT.country_id=ST.country_id',array('CT.cncode'))

                                    ->where("BT.barcode_id IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).")")

									->order('BT.tracenr ASC');

		$result = $this->getAdapter()->fetchAll($select);	

		return $result;						   

	}

	public function EscorreosParcelNumberforManifest(){

			$select = $this->_db->select()

											->from(array('BT'=>SHIPMENT_BARCODE),array('barcode','weight'))
											->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(''))
											->where("BT.barcode_id IN(".commonfunction::implod_array($this->getData[BARCODE_ID]).") AND ST.addservice_id IN(115,145) AND BT.weight<=0.5");

		  //echo $select->__toString();die;

		  $result = $this->getAdapter()->fetchAll($select);         

		  return $result;         

	 }

	 

	 public function createXmlstructure($struct, DOMDocument $dom, DOMElement $parent){

		   $struct = ( array ) $struct;



              foreach ( $struct as $key => $value ) {//print_r($key);

               if ($value === false) {

                $value = 0;

               } elseif ($value === true) {

                $value = 1;

               }



               if (ctype_digit ( ( string ) $key )) {

                $key = 'key_' . $key;

               }



               if (is_array ( $value ) || is_object ( $value )) {

                $element = $dom->createElement ($key);

                $this->createXmlstructure ( $value, $dom, $element );

               } else {

                $element = $dom->createElement ($key);

				if($key=='Value' || $key=='PostalCharges'){

					$element->setAttribute('Currency', "GBP");

				}

                $element->appendChild ( $dom->createTextNode ( $value ) );

               }

            $parent->appendChild ( $element );

   		 }

	}
	public function UpdateMediatorForwarder(){ 
	   global $objSession;
	   foreach($this->getData['barcode_id'] as $barcode_id){
	      $select = $this->_db->select()
								->from(array('MF'=>SHIPMENT_MEDIATOR_FORWARDER),array('COUNT(1) AS CNT'))
								->where("MF.barcode_id='".$barcode_id."'");
		  $result = $this->getAdapter()->fetchRow($select);
		  if($result['CNT']){
		     $this->_db->update(SHIPMENT_MEDIATOR_FORWARDER,array('forwarder_id'=>$this->getData['bulk_forwarder_id'],'forwarder_barcode'=>$this->getData['bulk_mediator_barcodes'],'modify_by'=>$this->Useconfig['user_id'],'modify_date'=>new Zend_Db_Expr('NOW()'),'modify_ip'=>commonfunction::loggedinIP()),"barcode_id='".$barcode_id."'");
		  } else{ 
		    $this->_db->insert(SHIPMENT_MEDIATOR_FORWARDER,array_filter(array('barcode_id'=>$barcode_id,'forwarder_id'=>$this->getData['bulk_forwarder_id'],'forwarder_barcode'=>$this->getData['bulk_mediator_barcodes'],'created_by'=>$this->Useconfig['user_id'],'created_date'=>new Zend_Db_Expr('NOW()'),'created_ip'=>commonfunction::loggedinIP())));
		  }
	   }
	   $objSession->successMsg = 'Mediator Forwarder Added Successfully';
	}
	
	public function getParcelListforMediatorForwarder(){
	  $where = $this->LevelClause();
	  if(isset($this->getData['country_id'])){
	    $where .= " AND ST.country_id='".$this->getData['country_id']."'";
	  }
	  $select = $this->_db->select()
								->from(array('BT'=>SHIPMENT_BARCODE),array('BT.barcode_id','BT.forwarder_id','BT.barcode','ST.rec_name','AT.company_name','BD.checkin_date','ST.create_date','BT.weight'))
								->joininner(array('BD' =>SHIPMENT_BARCODE_DETAIL),'BD.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array(''))
								->joininner(array('ST' =>SHIPMENT),'ST.'.SHIPMENT_ID.'=BT.'.SHIPMENT_ID.'',array(''))
								->joininner(array('AT' =>USERS_DETAILS),'AT.'.ADMIN_ID.'=ST.'.ADMIN_ID.'',array(''))
								->joininner(array('FT' =>FORWARDERS),'FT.'.FORWARDER_ID.'=BT.'.FORWARDER_ID.'', array('FT.forwarder_name'))
								->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=ST.'.COUNTRY_ID.'',array('CT.country_name'))
								->joinleft(array('MF' =>SHIPMENT_MEDIATOR_FORWARDER),'MF.'.BARCODE_ID.'=BT.'.BARCODE_ID.'',array('MF.forwarder_barcode'))
								->joinleft(array('MFT' =>FORWARDERS),'MFT.'.FORWARDER_ID.'=MF.'.FORWARDER_ID.'', array('MFT.forwarder_name AS mediator_forwarder'))
								->where("BT.edi_status='0' AND BT.hub_status='0' AND  BT.checkin_status='1' AND BT.forwarder_id='".$this->getData['forwarder_id']."'".$where);
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function ReUploadEDI(){
	  $file_name = basename($this->getData['file_name']);
	  global $objSession;
	   switch($this->getData['forwarder']){
	      case 1:
		  case 2:
		  case 3:
		  case 32:
		  case 54:
		   $this->EDIData['upload_file'] = array($file_name);
		  break;
		 default: 
		  $this->EDIData['upload_file'] = array($file_name);
	   }
	   $this->getData['forwarder_id'] = $this->getData['forwarder'];
	   $this->getData['user_id'] =1;
	   $this->uploadEDI(); 
	   if($this->EDIData['upload_status']=='1'){
	      $this->_db->update(SHIPMENT_EDI,array('upload_status'=>1),"forwarder_id='".$this->getData['forwarder']."' AND edi_file_name='".$file_name."'"); 
		  $objSession->successMsg = 'EDI Uploaded successfully';
	   }else{
	     $objSession->successMsg = 'EDI Not  Uploaded!Try again';
	   }
	}
	
	public function getHubList(){
	     $select = $this->_db->select()
						  ->from(array('UT'=>USERS),array('*'))
						  ->joininner(array('UD'=>USERS_DETAILS),"UT.user_id=UD.user_id",array('city'))
						  ->joininner(array('CT' =>COUNTRIES),'CT.'.COUNTRY_ID.'=UD.'.COUNTRY_ID.'',array('CT.country_name'))
						  ->joininner(array('US'=>USERS_SETTINGS),"US.user_id=UD.user_id",array(''))
						  ->where("UT.user_status='1' AND UT.user_status='1' AND UT.level_id=4 AND US.is_hub='1'")
						  ->order("CT.country_name ASC");
						 // print_r($select->__toString());die;
		 return $this->getAdapter()->fetchAll($select);
						  
	}
	public function sendManifestEmail($file){
				$mailOBj = new Zend_Custom_MailManager();
				$mailOBj->emailData['SenderEmail'] = 'info@dpost.be';
				$mailOBj->emailData['SenderName']    = 'Parcelnl';
				$mailOBj->emailData['Subject'] = 'Parcel.nl-Standard Pre Alert';
				$mailOBj->emailData['MailBody'] = 'Parcel.nl-Standard Pre Alert';
				$mailOBj->emailData['ReceiverEmail'] = 'PreAlert.scl-europe@asendia.com';
				$mailOBj->emailData['ReceiverName'] = 'PreAlert.scl-europe@asendia.com';
				$mailOBj->emailData['CCEmail']  = array("Export.nl@asendia.com");
				$mailOBj->emailData['BCCEmail']  = array("sanjeev.kumar@sjmsoftech.com");
				$mailOBj->emailData['user_id'] = 1;
				$mailOBj->emailData['notification_id'] = 0;
				$mailOBj->emailData['Attachemnt'] = $file;
				$mailOBj->Send();
	} 

}



