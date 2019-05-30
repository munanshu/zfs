<?php
class Application_Model_AramexLabel extends Zend_custom{

    private $client;    
    private $AramexAuth;    
    private $ApiData;   
    private $ApiDataXML;   
    private $xml_post_string;   
    private $ResponseXml;   
    private $api_login_url = "https://uat.centiro.com/Universe.Services/shipping/7/1/Common.asmx";    
    private $api_shipment_url = "https://uat.centiro.com/Universe.Services/shipping/7/1/ShipmentProcess.asmx";

  //   private $api_login_url = "https://cloud.centiro.com/Universe.Services/shipping/7/1/Common.asmx";    
  // private $api_shipment_url = "https://cloud.centiro.com/Universe.Services/shipping/7/1/ShipmentProcess.asmx";    
    
    public function init()
    {
        // $this->client = new SoapClient("https://uat.centiro.com/Universe.Services/shipping/7/1/Common.asmx?WSDL", array('trace' => 1));
        // $this->client = new SoapClient("https://cloud.centiro.com/Universe.Services/shipping/7/1/Common.asmx?WSDL", array('trace' => 1));

    }       

    public function CreateAramexLabel($shipmentObj,$newbarcode=true){
      if($newbarcode){   
        $res = $this->AuthenticateAramex(); 
        
        if($res['status']==1){


             $this->CreateApiData($shipmentObj->RecordData);
             
             if($shipmentObj->RecordData['quantity']>0){

                    
                     
                        $result['Shipment'] = $this->SendApiLabelRequest();
                    
                        // echo "<pre>"; print_r($result);

                    if($result['Shipment']['status']==1){

                          $filename = $shipmentObj->RecordData['forwarder_detail']['CustomerAddress']['company_name'].'_'.time().'_ShipmentRequest.xml';
                          $folder = $shipmentObj->RecordData['forwarder_detail']['forwarder_name'].'/xml/';
                          $filepath = $this->SaveXmlLastRequest($filename,$folder,$shipmentObj);

                          $response = $this->SaveApiLabelResponse($shipmentObj,$result['Shipment']['response']);
                          return true;

                    }
                    else { 
                        $this->UpdateForwarder($shipmentObj);
                       $resp = $result['Shipment'];
                      return true;
                    }
                     
             }
                   
        }
        else { 
           return false;
        }
      }
      else return false;



    }

    public function SaveApiLabelResponse($shipmentObj,$response)
    {     
      ini_set('display_errors', 1);


          $printresponse = $response->PrintShipmentResult->PrintedShipment;  
          $shipmentObj->RecordData[BARCODE] = $response->PrintShipmentResult->PrintedShipment->Parcels->Parcel->Attributes->NameValue[1]->Value;
          $shipmentObj->RecordData['BARCODE_READABLE']    = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[REROUTE_BARCODE] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
          $shipmentObj->RecordData[TRACENR] = $shipmentObj->RecordData[BARCODE];

          $Addresses = $this->ApiData['PrintShipmentRequest']['Shipment']['Addresses']['ShipmentAddress'];

          $Parcel = $this->ApiData['PrintShipmentRequest']['Shipment']['Parcels']['Parcel'];
          $pdfdata = array(
                'Origin'=>'',
                'Hawb'=>'',
                'Barcode'=>$shipmentObj->RecordData[BARCODE],
                'Destination'=>$printresponse->Receiver->City,
                'PickupDate'=>$printresponse->ShipDate,
                'CarrierServiceCode'=>$printresponse->CarrierServiceCode,
                'Payment'=>$printresponse->TranspPayer,
                'Exp'=>'EXP',
                'weight'=>$Parcel['Weight'],
                'pieces'=>$Parcel['ParcelCounter'],
                'value'=>0.0,
                'currency'=>$printresponse->Currency,
                'Description'=>$printresponse->CarrierServiceDescription,
                'Sender'=>$Addresses[1],
                'Reciever'=>$Addresses[0],
                'ShipRef'=>'',
                'ConsRef'=>$shipmentObj->RecordData[BARCODE],
                'Footer'=>'Logistics powered by Centrio - www.centrio.com',
                'ParcelResponse'=>$response->PrintShipmentResult->PrintedShipment->Parcels->Parcel,
            );


          $xml = $this->ConvertXmlToArray($this->ResponseXml);

          $jsonEncoded = json_encode($xml);
          $YodelLabel = new Application_Model_YodelLabel();
          $YodelLabel->storeLabelData($shipmentObj,$jsonEncoded);
          

          // echo "<pre>"; 
           
          // print_r($response);
          // print_r($pdfdata);
          // die;
         
      $shipmentObj->RecordData['pdf_data'] =  $pdfdata;
      return true;            
    }
    
    public function saveAramexLabelData($shipmentObj='',$pdfdata)
    {
       
        $select = $this->_db->select()
                      ->from(ARAMEX_PDF,array('*'))
                      ->where("barcode='".$shipmentObj->RecordData[BARCODE]."'");
                      //print_r($select->__toString());die;
        $record = $this->getAdapter()->fetchRow($select);
        if(empty($record)){
           $this->_db->insert(ARAMEX_PDF,array_filter(array('barcode'=>$shipmentObj->RecordData[BARCODE],'data_matrix_code'=>'dfgdfg','sequence_no2'=>'dfgdfg','pdf_contant'=>$pdfdata)));
        }else{
           $this->_db->update(ARAMEX_PDF,array('pdf_contant'=>$pdfdata),"barcode='".$shipmentObj->RecordData[BARCODE]."'");
        }
        return; 
    }

    public function SaveXmlLastRequest($file,$folder,$shipmentObj)
    {
        // $data = $this->client->__getLastRequest();
        $data = $this->xml_post_string;
        $folder_new = PRINT_SAVE_LABEL.$folder;
        // echo $file."<br>".$folder_new;die;
        $filepath = $folder_new.$file;
        // echo $filepath;die;
        $folder_new = str_replace('/', "\\", $folder_new);
         
        // $xmlfile = fopen($filepath, 'w');
        // fwrite($xmlfile, $data);
          
          return PRINT_OPEN_LABEL.$folder_new.$file;
    }

    public function SendApiLabelRequest()
    { 

  //     header('HTTP/1.1 200 OK');
  //           header("Pragma: no-cache");
  //           header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
  //           header('Content-type', 'text/xml');
  //           header('Content-Disposition: attachment; filename="example.xml"');

  //           echo $this->xml_post_string;
  // die;

          try {
        
            $this->setApiHeaders('http://www.centiro.com/Universe.Shipping.ServiceContracts/ShipmentProcess/7/1/PrintShipment');  
            

            $res = $this->CurlRequest($this->api_shipment_url,'post',$this->httpHeaders,$this->xml_post_string,true);
            // echo "<pre>"; print_r($res);
            $this->ResponseXml = $res;
            $res = $this->ConvertXmlToArray($res);
            // echo "<pre>"; print_r($res);
            // die;
        
          } catch (Exception $e) {
             echo $e->getMessage();die; 
          }

          // die;
              // echo "<pre>"; print_r($res);


            // die;
               // $res = $this->client->PrintShipment($this->ApiData);

               if(is_soap_fault($res))
                  $resp = array('status'=>0,'message'=>'Couldnt Send Request');
                 else {
                      
                      if($res->Body->PrintShipmentResponse->PrintShipmentResult->ShipmentStatus->StatusCode == 'PrintedOk')

                        $resp = array('status'=>1,'message'=>'Shipment Created Successfully','response'=>$res->Body->PrintShipmentResponse);
                      else $resp = array('status'=>0,'message'=>'Some Internal Error Occurred Sorry for inconvienience','response'=>$res);

                 }                  
          // } 
        return $resp;

    }


    public function array2XML($obj, $array)
    {
        foreach ($array as $key => $value)
        {
            if(is_numeric($key))
                $key = 'item' . $key;

            if (is_array($value))
            {
                $node = $obj->addChild($key);
                $this->array2XML($node, $value);
            }
            else
            {
                $obj->addChild($key, htmlspecialchars($value));
            }
        }
    }

    public function setApiHeaders($action)
    {   

          if(isset($this->xml_post_string) && !empty($this->xml_post_string)){

              $this->httpHeaders = array(
                        // "Host: cloud.centiro.com",
                        "Host: uat.centiro.com",
                        "Connection: Keep-Alive",
                        // "User-Agent: PHP-SOAP/7.1.6",
                        "Content-type: text/xml;charset=\"utf-8\"",
                        "Content-length: ".strlen($this->xml_post_string),
                        "SOAPAction: $action", 
                    );
          }


    }



  public function CurlRequest($url='',$method='',$headers='',$data='',$xml=false,$AuthData='')
  { 
    $method = strtoupper($method);
    $headers = (isset($headers) && !empty($headers))?$headers:array();
    $ch = curl_init();
    $options = array(
      CURLOPT_URL=>$url,
      CURLOPT_RETURNTRANSFER=>true,
      CURLOPT_HTTPHEADER=>$headers,
      CURLOPT_SSL_VERIFYPEER=>false,
      CURLOPT_SSL_VERIFYHOST=>false,
      );
    // $this->debug($options);
    if($method =='GET'){
      $requestUrl = (isset($data) && !empty($data))? $url."?".http_build_query($data):$url;
      $options[CURLOPT_URL] = $requestUrl;
      $options[CURLOPT_CUSTOMREQUEST] = $method;
    }
    if($method =='POST'){
      $data = $xml?$data:http_build_query($data);
      // $this->debug($data);die;
      $options[CURLOPT_HTTPAUTH] = isset($AuthData) && !empty($AuthData) ? CURLAUTH_ANY :  false ;
      $options[CURLOPT_USERPWD] = isset($AuthData) && !empty($AuthData) ? $AuthData['UserName'] ." : ".$AuthData['Password'] :  false ;
      $options[CURLOPT_POSTFIELDS] = $data;
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_CUSTOMREQUEST] = $method;
    }
    // $this->debug($options);die;
    curl_setopt_array($ch, $options);
    return curl_exec($ch);
  }

   public function ConvertXmlToArray($response='')
    { 

      $response = str_replace(array('soap:'), array(''), $response); 
      $p = simplexml_load_string($response);
      $p = json_encode($p);
      $p = json_decode($p);
      return $p;
    }

    public function AuthenticateAramex()
    {  
      ini_set('display_errors', 1);
      ini_set('default_socket_timeout', 600);
      $logindata = array('LoginRequest'=>array('UserName'=>'test@sti','Password'=>'testAGS1!'));
        // $logindata = array('LoginRequest'=>array('UserName'=>'ws@sti.uk.flostream','Password'=>'Ws@2017'));

      $this->xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <Authenticate xmlns="http://www.centiro.com/Universe.Common.ServiceContracts/7/1">
              <LoginRequest>
                <UserName>'.$logindata['LoginRequest']['UserName'].'</UserName>
                <Password>'.$logindata['LoginRequest']['Password'].'</Password>
              </LoginRequest>
            </Authenticate>
          </soap:Body>
        </soap:Envelope>';
      try {
        
        $this->setApiHeaders('http://www.centiro.com/Universe.Common.ServiceContracts/7/1/Authenticate');  

        $res = $this->CurlRequest($this->api_login_url,'post',$this->httpHeaders,$this->xml_post_string,true,$logindata['LoginRequest']);
        $res = $this->ConvertXmlToArray($res);
        
      } catch (Exception $e) {
         echo $e->getMessage();die; 
      }




      //   try {
          
      //    $res = $this->client->Authenticate($logindata);
      //   } catch (Exception $e) {
      //     echo $e->getMessage();die;
      //   }
        // echo "<pre>"; 
        // print_r($this->client->__getFunctions());
        // print_r($res);
        // die;
          
         if(is_soap_fault($res))
            $resp = array('status'=>0,'message'=>'Couldnt authenticate please try later');

          else { 
            $this->AramexAuth = $res->Body;
            $resp = array('status'=>1);
          } 
      // echo "<pre>"; 
      // print_r($this->AramexAuth);
      // print_r($resp);
      // die; 
        return $resp;
    }


    public function getTimeZone($CountryName)
    { 
        foreach(timezone_abbreviations_list() as $abbr => $timezone){
              foreach($timezone as $val){
                    if( strpos($val['timezone_id'],$CountryName)!== -1 ){


                          return $val['offset'];
                    }
              }
      }
    }


    public function UpdateForwarder($shipmentObj){
      $this->_db->update(SHIPMENT,array('forwarder_id'=>44,'wrong_parcel'=>'1'),"shipment_id=".$shipmentObj->RecordData[SHIPMENT_ID]."");
    }

    
    public function CreateApiData($shipmentObj)
    {   
        ini_set('display_errors', 1);
        $timezone = $this->getTimeZone($shipmentObj['rec_country_name']);
        $datetime = date('Y-m-d\TH:i:s');
        $weight = ($shipmentObj['quantity']>1)? $shipmentObj['weight']*$shipmentObj['quantity'] : $shipmentObj['weight'];

        $customer_detail =  $shipmentObj['forwarder_detail']['CustomerAddress'];
        $sender_detail =  $shipmentObj['forwarder_detail']['SenderAddress'];
            $customer_no =  Zend_Encript_Encription::encode($shipmentObj['user_id']);  
            // echo ;
            // die; 
                            // die;
        $dom = new DOMDocument('1.0','utf-8');
               $root = $dom->appendChild($dom->CreateElement('soapenv:Envelope'));
                $root->setAttribute('xmlns:soapenv','http://schemas.xmlsoap.org/soap/envelope/');
                 
                $root->setAttribute('xmlns:ns3','http://www.centiro.com/Universe.Shipping.ServiceContracts/ShipmentProcess/7/1');
                 


                $root->appendChild($dom->CreateElement('soapenv:Header'));  
              $Body = $root->appendChild($dom->CreateElement('soapenv:Body'));  
              $PrintShipment = $Body->appendChild($dom->CreateElement('ns3:PrintShipment')); 

              // $PrintShipment->setAttribute('xmlns','http://www.centiro.com/Universe.Shipping.ServiceContracts/ShipmentProcess/7/1') ;

               $SegmentSelectionList = $dom->CreateElement('ns3:SegmentSelectionList'); 
               $SegmentSelectionList->appendChild($dom->CreateElement('ns3:ShipmentInformationSegment','Parcels'));
               // $SegmentSelectionList->appendChild($dom->CreateElement('ns3:ShipmentInformationSegment','Attributes'));
               
               // $SegmentSelectionList->appendChild($dom->CreateElement('ns3:ShipmentInformationSegment','DeliveryInstructions'));


              $PrintShipmentRequest = $PrintShipment->appendChild($dom->CreateElement('ns3:PrintShipmentRequest'));  


              $PrintShipmentRequest->appendChild($dom->CreateElement('ns3:AuthenticationTicket',$this->AramexAuth->AuthenticateResponse->AuthenticateResult->AuthenticationTicket));

              $PrintShipmentRequest->appendChild($SegmentSelectionList);

                $ExecutePreProcessStep = $dom->CreateElement('ns3:ExecutePreProcessStep');
                $ExecutePreProcessStep->appendChild($dom->CreateElement('ns3:ProcessStepName','STI.PRE'));
               $PrintShipmentRequest->appendChild( $ExecutePreProcessStep );

               $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:MessageId' , 'msgid' ));
               $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:DocumentFormat' , 'PDF' ));
               $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:IdentifierKey' , 'GlobalId' ));
               // $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:CreateShipmentIfNotFound' , true ));
               $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:IncludeShipmentInResponse' , true ));
               // $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:DisableParcelCounterUpdate' , true ));
               // $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:UseIdentifier' , true ));
               $PrintShipmentRequest->appendChild( $dom->CreateElement( 'ns3:PrinterType' , 'zebra' ));


               $Shipment = $dom->CreateElement('ns3:Shipment');

               $Addresses = $dom->CreateElement('ns3:Addresses');
               $ShipmentAddressRECEIVER = $dom->CreateElement('ns3:ShipmentAddress');
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Address1',$shipmentObj['rec_street']." ". $shipmentObj['rec_streetnr']." ".$shipmentObj['rec_address']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Address2',$shipmentObj['rec_street2']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Address3'));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:CellPhone'));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Code','RECEIVER'));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:City',$shipmentObj['rec_city']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Contact',$shipmentObj['rec_contact']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:CustNo',$customer_no));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Email',$shipmentObj['rec_email']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:ISOCountry',$shipmentObj['rec_cncode']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Name',$shipmentObj['rec_name']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:Phone',$shipmentObj['rec_phone']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:State',$shipmentObj['rec_state']));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:VatNo'));
               $ShipmentAddressRECEIVER->appendChild($dom->CreateElement('ns3:ZipCode',$shipmentObj['rec_zipcode']));



               $ShipmentAddressSENDER = $dom->CreateElement('ns3:ShipmentAddress');
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Address1',$sender_detail[2]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Address2'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Address3'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:CellPhone'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Code','SENDER'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:City',$sender_detail[3]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Contact',$sender_detail[1]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:CustNo'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Email',$sender_detail[9]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:ISOCountry',$sender_detail[5]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Name',$sender_detail[1]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:Phone',$sender_detail[10]));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:State'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:VatNo'));
               $ShipmentAddressSENDER->appendChild($dom->CreateElement('ns3:ZipCode',$sender_detail[4]));
              

               $Addresses->appendChild($ShipmentAddressRECEIVER);
               $Addresses->appendChild($ShipmentAddressSENDER);


               $Attributes = $dom->CreateElement('ns3:Attributes');
                   $NameValue1  = $dom->CreateElement('ns3:NameValue');

                   $NameValue1->appendChild($dom->CreateElement('ns3:Name','HAWB Destination'));
                   $NameValue1->appendChild($dom->CreateElement('ns3:Value'));

                   $NameValue2  = $dom->CreateElement('ns3:NameValue');

                   $NameValue2->appendChild($dom->CreateElement('ns3:Name','HAWB Origin'));
                   $NameValue2->appendChild($dom->CreateElement('ns3:Value'));

                   $NameValue3  = $dom->CreateElement('ns3:NameValue');

                   $NameValue3->appendChild($dom->CreateElement('ns3:Name','Product Type'));
                   $NameValue3->appendChild($dom->CreateElement('ns3:Value','eParcelPremium'));

                   $NameValue4  = $dom->CreateElement('ns3:NameValue');

                   $NameValue4->appendChild($dom->CreateElement('ns3:Name','Additional Services'));
                   $NameValue4->appendChild($dom->CreateElement('ns3:Value'));

                   $NameValue5  = $dom->CreateElement('ns3:NameValue');

                   $NameValue5->appendChild($dom->CreateElement('ns3:Name','Attribute3'));
                   $NameValue5->appendChild($dom->CreateElement('ns3:Value'));


                $Attributes->appendChild($NameValue1);   
                $Attributes->appendChild($NameValue2);   
                $Attributes->appendChild($NameValue3);   
                $Attributes->appendChild($NameValue4);   
                $Attributes->appendChild($NameValue5);   


               $Shipment->appendChild($Addresses);
               $Shipment->appendChild($Attributes);
               $Shipment->appendChild($dom->CreateElement('ns3:DeliveryInstruction1','ins1'));
               $Shipment->appendChild($dom->CreateElement('ns3:DeliveryInstruction2','ins2'));
               $Shipment->appendChild($dom->CreateElement('ns3:FreightCurrency','USD'));
               $Shipment->appendChild($dom->CreateElement('ns3:FreightPrice',0));
               $Shipment->appendChild($dom->CreateElement('ns3:LastModifiedBy','Admin user'));
               // $Shipment->appendChild($dom->CreateElement('ns3:OrderNo','SJM4782765MRS'));

               $Lines = $dom->CreateElement('ns3:Lines');
               $Lines->appendChild($dom->CreateElement('ns3:OrderLine'));

               $Parcels = $dom->CreateElement('ns3:Parcels');
               $Parcel = $dom->CreateElement('ns3:Parcel');
               $Parcel->appendChild($dom->CreateElement('ns3:Height',$shipmentObj['height']));
               $Parcel->appendChild($dom->CreateElement('ns3:Length',$shipmentObj['length']));
               $Parcel->appendChild($Lines);
               $Parcel->appendChild($dom->CreateElement('ns3:NumberOfTags',1));
               $Parcel->appendChild($dom->CreateElement('ns3:OrderNo',$shipmentObj['rec_reference']));
               $Parcel->appendChild($dom->CreateElement('ns3:ParcelCounter',$shipmentObj['parcelcount']));
               $Parcel->appendChild($dom->CreateElement('ns3:ParcelID',$shipmentObj['rec_reference']));
               $Parcel->appendChild($dom->CreateElement('ns3:Reference',$shipmentObj['rec_reference']));
               $Parcel->appendChild($dom->CreateElement('ns3:TypeOfGoods',$shipmentObj['goods_id']));
               $Parcel->appendChild($dom->CreateElement('ns3:TypeOfPackage','PACKAGE'));
               $Parcel->appendChild($dom->CreateElement('ns3:Volume',0.0));
               $Parcel->appendChild($dom->CreateElement('ns3:Weight',$shipmentObj['weight']));
               $Parcel->appendChild($dom->CreateElement('ns3:Width',$shipmentObj['width']));
               $Parcels->appendChild($Parcel);

               $Shipment->appendChild($Parcels);
               $Shipment->appendChild($dom->CreateElement('ns3:Reference',$shipmentObj['rec_reference']));
               $Shipment->appendChild($dom->CreateElement('ns3:SenderCode','sti.uk.test'));
               $Shipment->appendChild($dom->CreateElement('ns3:TranspPayer','P'));
               $Shipment->appendChild($dom->CreateElement('ns3:Currency','GBP'));
               $Shipment->appendChild($dom->CreateElement('ns3:Value',$shipmentObj['shipment_worth']));
               $Shipment->appendChild($dom->CreateElement('ns3:Volume',0.0));
               $Shipment->appendChild($dom->CreateElement('ns3:Weight',$weight));
                

               $PrintShipmentRequest->appendChild( $Shipment );


               $dom->formatOutput = true;
              $this->xml_post_string = $dom->saveXML();
              
              $response1 = str_replace(array("soapenv:","ns3:"),array(''),$this->xml_post_string);

              $resXml = simplexml_load_string($response1);
              $resXml = json_encode($resXml);
              $resXmlArr = json_decode($resXml,true);
              $this->ApiData = $resXmlArr['Body']['PrintShipment'];
               // echo "<pre>";

               // print_r($this->ApiDataXML);
               // print_r($this->ApiData);
               // die;

    }

      


    // public function CreateApiData($shipmentObj)
    // {   
    //     ini_set('display_errors', 1);
    //     $timezone = $this->getTimeZone($shipmentObj['rec_country_name']);
    //     $datetime = date('Y-m-d\TH:i:s');
    //     $weight = ($shipmentObj['quantity']>1)? $shipmentObj['weight']*$shipmentObj['quantity'] : $shipmentObj['weight'];

    //     $customer_detail =  $shipmentObj['forwarder_detail']['CustomerAddress'];
    //     $sender_detail =  $shipmentObj['forwarder_detail']['SenderAddress'];
    //         $customer_no =  Zend_Encript_Encription::encode($shipmentObj['user_id']);  
    //         // echo ;
    //         // die; 
    //                         // die;
    //     $shipment =  array('PrintShipmentRequest'=> 
    //   array(
    //            'AuthenticationTicket'=> $this->AramexAuth->AuthenticateResponse->AuthenticateResult->AuthenticationTicket,
    //            'ExecutePreProcessStep'=> array('ProcessStepName' => 'STI.PRE'),
    //            // 'ExecuteProcessStep'=> 1,
    //            'PrinterType'=>'zebra',
    //            'IdentifierKey'=>'GlobalId',
    //            /*  data added if not available start  */
    //            // 'UseIdentifier' => 0,
    //            // 'DisableParcelCounterUpdate' => 0,
    //            // 'CreateShipmentIfNotFound' => 1,
    //            // 'SegmentSelectionList' => array( 
                      
    //                     // 'ShipmentInformationSegment'  => array('Shipments','Parcels','Addresses')
    //                   // ),
    //            /*  data added if not available end  */
    //            'IncludeShipmentInResponse'=> 1,
    //            'DocumentFormat'=>'PDF',
    //            'MessageId'=>'msgid',
    //            'Shipment'=>array(
    //                         // 'SenderCode'=>'sti.uk.test',
    //                         'SenderCode'=>'sti.uk.flostream',
    //                         'ParcelCount' => $shipmentObj['parcelcount'],
    //                         'Reference' => $shipmentObj['rec_reference'] , 
    //                         'Weight' => $weight, 

    //                         /*  data not available start  */
    //                         'Value' => $shipmentObj['shipment_worth']  , 
    //                         // 'Volume' => 0, 
    //                         // 'LabelStatus' => 'LabelsPrinted', 
    //                         // 'CAR_CODAmount' => 0,
    //                         // 'CAR_InsuranceAmount' => 0, 
    //                         // 'UpdateTimeLocal' => $datetime, 
    //                         // 'NoOfEURPallets' => 0, 
    //                         // 'Status' => 'Created', //
    //                         // 'ValidationOnly' => 0, 
    //                         // 'ShipmentType' => 'Regular', 
    //                         // 'EndOfDayID' => 0, 
    //                         // 'ChargedVolume' => 0, 
    //                         // 'ChargedWeight' => 0, 
    //                         // 'LoadingMeasure' => 1.0, 
    //                         // 'CarrierServiceAttributes' => array(
    //                                  // array('ServiceAttribute'=> $shipmentObj['service_attribute'] ), 

    //                           // ),
    //                         // 'CarrierCode' => 'std.post.au', 
    //                         // 'CarrierServiceCode' => '93', 
    //                         // 'CarrierCode' => 'std.aramex.com', 
    //                         // 'CarrierServiceCode' => 'ppx',

    //                         // 'CarrierServiceDescription' => 'Priority Parcel Express', 
                            
    //                         // 'ServiceAttributes' => $shipmentObj['service_attribute'],
    //                         // 'GlobalId' => '', 
    //                         // 'SequenceNo' => '', 
    //                         // 'SequenceNo2' => '', 
    //                         // 'ExternalId' => '', 
    //                         'TranspPayer' => 'P', 
    //                         // 'DeliveryInstruction1' => 'ins1', 
    //                         // 'DeliveryInstruction2' => 'ins2', 
    //                         'FreightCurrency' => 'USD', 
    //                         'FreightCost' => 0, 
    //                         'FreightPrice' => 0, 
                             
    //                         /*  data not available end  */
    //                         'ShipDate' => date('Y-m-d\TH:i:s',  strtotime($shipmentObj['create_date'])) , 
    //                         'LastModifiedBy' => $shipmentObj['create_by'],
    //                         'ActualCost' => $shipmentObj['customer_price'] , 
    //                         'CreationTimeUTC' => $datetime, 
    //                         'UpdateTimeUTC' => $datetime, 
    //                         'Timezone' => $timezone, 
    //                         'CreationTimeLocal' => date('Y-m-d\TH:i:s',  strtotime($shipmentObj['create_date'])), 

    //                         'OrderNo' => $shipmentObj['rec_reference'] , 
    //                         'Currency' => $shipmentObj['currency'] ,

                             
    //                         'Buyer' => array(

    //                                 'CustomerNo' => $customer_no, 
    //                                 'Name' => $customer_detail['name'] , 
    //                                 'Address1' => $customer_detail['address1'] , 
    //                                 'Address2' => '', 
    //                                 'Address3' => '', 
    //                                 'ZipCode' => $customer_detail['postalcode'] , 
    //                                 'City' => $customer_detail['city'] , 
    //                                 'State' => '' , 
    //                                 'ISOCountry' => $customer_detail['cncode'] , 
    //                                 'CountryName' => $customer_detail['country_name'] , 
    //                                 'Reference' => '' , 
    //                                 'Phone' => $customer_detail['phoneno'] , 
    //                                 'Fax' => '', 
    //                                 'Email' => $customer_detail['email'] , 
    //                                 'VatNo' => '', 
    //                                 'Freetext1' => '', 
    //                                 'Freetext2' => '', 
    //                                 'Freetext3' => '', 
    //                                 'UniqueFieldsData' => '',
    //                                 'UniqueFields' => '',

    //                           ),
    //                         'Receiver' => array(

    //                                 'CustomerNo' => '', 
    //                                 'Name' => $shipmentObj['rec_name'], 
    //                                 'Address1' => $shipmentObj['rec_street']." ". $shipmentObj['rec_streetnr']." ".$shipmentObj['rec_address'], 
    //                                 'Address2' => '', 
    //                                 'Address3' => '', 
    //                                 'ZipCode' => $shipmentObj['rec_zipcode'], 
    //                                 'City' => $shipmentObj['rec_city'], 
    //                                 'State' => $shipmentObj['rec_state'], 
    //                                 'ISOCountry' => $shipmentObj['rec_cncode'], 
    //                                 'CountryName' => $shipmentObj['currency'], 
    //                                 'Reference' => $shipmentObj['rec_reference'], 
    //                                 'Phone' => $shipmentObj['rec_phone'], 
    //                                 'Fax' => 'saas', 
    //                                 'Email' => $shipmentObj['rec_email'], 
    //                                 'VatNo' => '', 
    //                                 'Freetext1' => '', 
    //                                 'Freetext2' => '', 
    //                                 'Freetext3' => '', 
    //                                 'UniqueFieldsData' => '',
    //                                 'UniqueFields' => '',

    //                           ),
                            
                                 
                             
    //                         'Parcels' => array(
                                 
    //                             'Parcel'=> array(

    //                                           array(
    //                                             'Height'=>$shipmentObj['height'] ,
    //                                             'Length'=>$shipmentObj['length'] ,
    //                                             'Lines'=>array(
    //                                                 'OrderLine' => ''
    //                                             ),
    //                                             'NumberOfTags' => 2, 
    //                                             'OrderNo' => $shipmentObj['rec_reference'], 
    //                                             'ParcelCounter' => $shipmentObj['parcelcount'], 
    //                                             'ParcelID' => '', 
    //                                             'Reference' => '', 
    //                                             'TypeOfGoods' => $shipmentObj['goods_id'], 
    //                                             'TypeOfPackage' => 'pallet', 
    //                                             'Volume' => '', 
    //                                             'Weight' => $shipmentObj['weight'], 
    //                                             'Width' => $shipmentObj['width'],
    //                                             /*  data added if not available start  */
    //                                             'LoadingMeasure' => '',
    //                                             'NumberOfPackages' => $shipmentObj['quantity'] ,
    //                                             'MaxParcelCounter' => $shipmentObj['quantity'],
    //                                             'Dangerous' => 0,
    //                                             'Value' => 0.0,
    //                                             'FreightCost' => 0.0,
    //                                             'FreightPrice' => 0.0,
    //                                             'Height' => $shipmentObj['height'],
    //                                             'Length' => $shipmentObj['length'],
    //                                             'NetWeight' => 0.0,
    //                                             'TaraWeight' => 0.0,
    //                                             'ChargedWeight' => 0.0,
    //                                             'ActualCost' => $shipmentObj['customer_price'],
    //                                             'CreationTimeLocal' => $datetime,
    //                                             'CreationTimeUTC' => $datetime,
    //                                             'UpdateTimeUTC' => $datetime,
    //                                             'UpdateTimeLocal' => $datetime,
    //                                             'Timezone' => $timezone,
    //                                             /*  data added if not available end  */
    //                                           ) 
                                              
    //                                 )
                                 
    //                         ),
    //                         'Addresses' =>   

    //                                 array('ShipmentAddress'=>array(
    //                                       array(

    //                                            'Address1' => '34 PORT DRIVE', 
    //                                            'Address2' => '', 
    //                                            'Address3' => '', 
    //                                            'CellPhone' => '', 
    //                                            'Code' => 'RECEIVER', 
    //                                            'City' => 'TWEED HEADS SOUTH, NSW', 
    //                                            'Contact' => $shipmentObj['rec_name'],       
    //                                            'CustNo' => $customer_no, 
    //                                            'Email' => $shipmentObj['rec_email'], 
    //                                            'ISOCountry' => $shipmentObj['rec_cncode'],       
    //                                            'Name' => 'MRS KONIECZNY',     
    //                                            'Phone' => $shipmentObj['rec_phone'], 
    //                                            'State' => $shipmentObj['rec_state'], 
    //                                            'VatNo' => '', 
    //                                            'ZipCode' => '2486',
    //                                            // 'CreationTimeLocal' => date('Y-m-d\TH:i:s',  strtotime($shipmentObj['create_date']))  
    //                                           ),
    //                                           array(
    //                                             'Address1' => $sender_detail[2] , 
    //                                            'Address2' => '', 
    //                                            'Address3' => '', 
    //                                            'CellPhone' => '', 
    //                                            'Code' => 'SENDER', 
    //                                            'City' => $sender_detail[3], 
    //                                            'Contact' => $sender_detail[1],       
    //                                            'CustNo' => '', 
    //                                            'Email' => $sender_detail[9], 
    //                                            'ISOCountry' => $sender_detail[5],       
    //                                            'Name' => $sender_detail[1],     
    //                                            'Phone' => $sender_detail[10], 
    //                                            'State' => '', 
    //                                            'VatNo' => '', 
    //                                            'ZipCode' => $sender_detail[4],

    //                                            // 'CreationTimeLocal' => $datetime
    //                                         ) 




    //                    )
                                               
    //                                       ),
    //                       ),
               
               
    //         ) 
    //   );
      
    //   echo "<pre>"; print_r($shipmentObj); 
    //   echo "<pre>"; print_r($shipment); 

    //   $this->ApiData = $shipment;

    // }

    


}