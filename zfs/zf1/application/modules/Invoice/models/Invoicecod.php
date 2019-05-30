<?php

 

class Invoice_Model_Invoicecod  extends Zend_Custom

{

   public $Invoice = array();   

	public function getcodparcellist(){

	   try{

	   $where = $this->LevelClause();

	   if(isset($this->getData['user_id']) && $this->getData['user_id']!=''){

	      $where .= " AND AT.user_id='".Zend_Encript_Encription::decode($this->getData['user_id'])."'";

	   }

	   if(isset($this->getData['country_id']) && $this->getData['country_id']>0){

	      $where .= " AND ST.country_id='".$this->getData['country_id']."'";

	   }

	   if(isset($this->getData['payment_status']) && $this->getData['payment_status']!=''){

	     // $where .= " AND IT.payment_status='".$this->getData['payment_status']."'";

		  $invoice_status = ($this->Useconfig['level_id']==1 || $this->Useconfig['level_id']==11)?'depot_invoice_number':'customer_invoice_number';

		  if($this->getData['payment_status']=='Invoice'){

		       $where .=  " AND IC.".$invoice_status.">0";

		  }elseif($this->getData['payment_status']=='Paid'){

		       $where .=  " AND IC.".$invoice_status."=0 AND IC.cod_price>0";

		  }elseif($this->getData['payment_status']=='Unpaid'){
		       $where .=  " AND (IC.cod_price <= 0 OR ISNULL(IC.cod_price)) AND (IC.status != 'Return' OR ISNULL(IC.status)) AND IC.status != 'Paid'";
		  }elseif($this->getData['payment_status']=='Refuse'){
		       $where .=  " AND (IC.status = 'Refuse' OR IC.status = 'Return')";
		  }

	   }

	   if(isset($this->getData['search_word']) && $this->getData['search_word']!=''){

	      $where .= " AND (BT.barcode='".$this->getData['search_word']."' OR BT.tracenr_barcode='".$this->getData['search_word']."')";

	   }

	   if(isset($this->getData['search_amount']) && $this->getData['search_amount']!=''){

	      $where .= " AND BT.cod_price='".$this->getData['search_amount']."'";

	   }

	   $OrderLimit = commonfunction::OdrderByAndLimit($this->getData,'DATE(BD.checkin_date)','DESC');

	   $select = $this->_db->select()

									->from(array('BT'=>SHIPMENT_BARCODE),array('COUNT(1) AS CNT'))

									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array(''))

									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array(''))

									->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array(''))

									->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array())

									->joinleft(array('IC'=>INVOICE_COD),"BT.barcode_id=IC.barcode_id",array(''))

									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.addservice_id IN(7,146,141)".$where);

									//echo $select->__toString();die;

		$total = $this->getAdapter()->fetchRow($select);

		

		$select = $this->_db->select()

									->from(array('BT'=>SHIPMENT_BARCODE),array('BT.tracenr_barcode','BT.cod_price','BT.weight','BT.barcode_id'))

									->joininner(array('BD'=>SHIPMENT_BARCODE_DETAIL),"BT.barcode_id=BD.barcode_id",array('BD.rec_reference'))

									->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('rec_name','rec_zipcode','user_id','cod_price AS scod_price'))

									->joininner(array('AT'=>USERS_DETAILS),"AT.user_id=ST.user_id",array('AT.company_name'))

									->joininner(array('CT'=>COUNTRIES),"CT.country_id=ST.country_id",array('CT.country_name'))

									->joinleft(array('IC'=>INVOICE_COD),"BT.barcode_id=IC.barcode_id",array('IC.cod_price AS paid','resion','status','IC.depot_invoice_number','IC.customer_invoice_number'))

									->where("BT.checkin_status='1' AND BT.delete_status='0' AND ST.addservice_id IN(7,146,141)".$where)

									->order($OrderLimit['OrderBy'].' '.$OrderLimit['OrderType'])

									->limit($OrderLimit['Toshow'],$OrderLimit['Offset']);

									//echo $select->__toString();die;

		$result = $this->getAdapter()->fetchAll($select);	

		}catch(Exception $e){

		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  return array('Total'=>0,'Record'=>array());	

		}

		return array('Total'=>$total['CNT'],'Record'=>$result);	

	}

	

	public function UpdateCodPrice(){

	  try{

	  $select = $this->_db->select()

							->from(array('IC'=>INVOICE_COD),array('COUNT(1) AS CNT'))

							->where("barcode_id='".$this->getData['barcode_id']."'");

									//echo $select->__toString();die;

		$total = $this->getAdapter()->fetchRow($select);

		if($total['CNT']>0){

	  	  $this->_db->update(INVOICE_COD,array('cod_price'=>$this->getData['cod_price'],'resion'=>$this->getData['reasion'],'status'=>$this->getData['status']),"barcode_id='".$this->getData['barcode_id']."'");

		}else{

		   $this->_db->insert(INVOICE_COD,array_filter(array('user_id'=>$this->getData['barcode_user'],'cod_price'=>$this->getData['cod_price'],'resion'=>$this->getData['reasion'],'status'=>$this->getData['status'],'barcode_id'=>$this->getData['barcode_id'],'paid_date'=>new Zend_Db_Expr('NOW()'))));

		}

	  }catch(Exception $e){

		  $this->_logger->info('Class-'.__CLASS__.',Function-'.__FUNCTION__.',Line-'.__LINE__.',Error-'.$e->getMessage());

		  echo 0;die;

		}

	  echo 1;die;	

	}

	

	public function importCODfile(){

	   $file_name = commonfunction::ImportFile('cod_amount_file','csv',1);

	   switch($this->getData['import_type']){

	      case 1:

		     $this->updateGLSDE($file_name);

		  break;

		  case 2:

		    $this->updateMondialRelay($file_name);

		  break;

		  case 10:

		    $this->updateOtherformat($file_name);

		  break;

	   }

	}

	public function updateOtherformat($Csvfile){ 

	     $string = "Row No.".";"."Status".";"."Reference/Local Barcode/COD Price\n";

		 if (($handle = fopen($Csvfile, "r")) !== FALSE) {

		     $counter = 1;

            while (($data = fgetcsv($handle, 1000, ';', ' ')) !== FALSE) {

			  if($counter>1){ 

			    $filedata['barcode'] = $data[0];

				$filedata['cod_price'] = commonfunction::stringReplace('.',',',$data[1]);

				$filedata['status'] = commonfunction::first_upper($data[2]);

				if($filedata['barcode']!=''){

				    $select = $this->_db->select()

										->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','barcode'))

										->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('user_id'))

										->where("BT.barcode='".$filedata['barcode']."'");

					$result = $this->getAdapter()->fetchRow($select);

					if(!empty($result)){

					     $string .= $this->AddCodData($result,$filedata,$counter);

					}else{

					    $string .= $counter.";"."Record Not Found in databse".";".$result['barcode'].'/NA/NA'."\n";

					}					

				}

                $importdata[] = $filedata;

               } 

				$counter++;

            }



            fclose($handle);

        }

		commonfunction::ExportCsv($string,'Update COD price','csv'); 

	}

	

	public function updateGLSDE($Csvfile){ 

	     $string = "Row No.".";"."Status".";"."Reference/Local Barcode/COD Price\n";

		 if (($handle = fopen($Csvfile, "r")) !== FALSE) {

		     $counter = 1;

            while (($data = fgetcsv($handle, 1000, ';', ' ')) !== FALSE) {

			  if($counter>1){

			    $filedata['currency'] = $data[1];

				$filedata['cod_price'] = commonfunction::stringReplace(array('"',','),array('','.'),$data[6]);

				$detaildata = explode('/',$data[7]);

				$explodebyspace = explode(' ',$detaildata[10]);

				if(isset($explodebyspace[2]) && trim($explodebyspace[2])=='reason'){

				  $reference = '';

				  $barcode = $explodebyspace[4].commonfunction::GlsDECheck($explodebyspace[4]);

				}elseif(isset($explodebyspace[2]) && strlen($explodebyspace[2])==11){

				  $reference = $explodebyspace[1];

				  $barcode = $explodebyspace[2].commonfunction::GlsDECheck($explodebyspace[2]);

				}

				$filedata['reference'] = $reference;

				$filedata['localbarcode'] = $barcode;

				$filedata['Detail'] = $data[7]; 

				$filedata['status'] = 'Paid';

                if($filedata['localbarcode']!='' && $filedata['cod_price']>0){

				     $select = $this->_db->select()

										->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','barcode'))

										->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('user_id'))

										->where("BT.barcode='".$filedata['localbarcode']."'");

					  $result = $this->getAdapter()->fetchRow($select);

					if(!empty($result)){

					     $string .= $this->AddCodData($result,$filedata,$counter);

					}else{

					    $string .= $counter.";"."Record Not Found in databse".";".$result['barcode'].'/NA/NA'."\n";

					}					

				}

               

			  } 

				$counter++;

            }



            fclose($handle);

        }

		commonfunction::ExportCsv($string,'Update COD price','csv'); 

	}

	

	public function updateMondialRelay($Csvfile){ 

	     $string = "Row No.".";"."Status".";"."Reference/Local Barcode/COD Price\n";

		 if (($handle = fopen($Csvfile, "r")) !== FALSE) {

		     $counter = 1;

            while (($data = fgetcsv($handle, 1000, ';', ' ')) !== FALSE) {

			  if($counter>1){ 

			    $filedata['tracenr'] = $data[2];

				$filedata['cod_price'] = str_replace('.',',',$data[3]);

				$filedata['status'] = 'Paid';

				if($filedata['tracenr']!='' && $filedata['cod_price']>0){

				    $select = $this->_db->select()

										->from(array('BT'=>SHIPMENT_BARCODE),array('barcode_id','barcode'))

										->joininner(array('ST'=>SHIPMENT),"ST.shipment_id=BT.shipment_id",array('user_id'))

										->where("BT.tracenr='".$filedata['tracenr']."'");

					$result = $this->getAdapter()->fetchRow($select);

					if(!empty($result)){

					     $string .= $this->AddCodData($result,$filedata,$counter);

					}else{

					    $string .= $counter.";"."Record Not Found in databse".";".$result['barcode'].'/NA/NA'."\n";

					}					

				}

                $importdata[] = $filedata;

               } 

				$counter++;

            }



            fclose($handle);

        }

		commonfunction::ExportCsv($string,'Update COD price','csv'); 

	}

	

	public function AddCodData($result,$filedata,$counter){

	    $string ='';

		$getCODdata = $this->getAlreadyUpdated($result['barcode_id']);

	    if(empty($getCODdata)){ 

		 $this->_db->insert(INVOICE_COD,array_filter(array('user_id'=>$result['user_id'],'cod_price'=>$filedata['cod_price'],'status'=>$filedata['status'],'paid_date'=>new Zend_Db_Expr('NOW()'),'barcode_id'=>$result['barcode_id'])));

		 $string .= $counter.";"."Status ".$filedata['status']." for".";".$result['barcode'].'/'.$filedata['status'].'/'.$filedata['cod_price']."\n";

	   }

	   else{

		 $string .= $counter.";"."Already Updated".";".$result['barcode'].'/'.$getCODdata['status'].'/'.$getCODdata['cod_price']."\n";

	   }

	   return $string;

	}

	

	public function getAlreadyUpdated($barcode_id){

	    $select = $this->_db->select()

								->from(array('COD'=>INVOICE_COD),array('*'))

								->where("COD.barcode_id='".$barcode_id."'");

		$result = $this->getAdapter()->fetchRow($select);

		return $result;

	} 	

}





