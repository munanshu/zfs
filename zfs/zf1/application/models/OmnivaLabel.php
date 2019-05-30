<?php
class Application_Model_OmnivaLabel extends Zend_custom{
	  public function CreateOmnivaLabel($shipmentObj,$newbarcode=true)
	   {
	      if($shipmentObj->RecordData['addservice_id']==119 || $shipmentObj->RecordData['addservice_id']==120 || $shipmentObj->RecordData['addservice_id']==122 || $shipmentObj->RecordData['addservice_id']==123)
		   { 
				$barcode_prefix = 'EF' ;
		    }
			else
			{ 
				$barcode_prefix = ($shipmentObj->RecordData['addservice_id']==115 || $shipmentObj->RecordData['addservice_id']==145) ? $shipmentObj->RecordData['forwarder_detail']['barcode_prefix'] : $shipmentObj->RecordData['forwarder_detail']['service_indicator'];
			}
		  $checkdigit = $this->OmnivaCheckDigit($shipmentObj->RecordData[TRACENR]);
		  $shipmentObj->RecordData[BARCODE] = $barcode_prefix.$shipmentObj->RecordData[TRACENR].$checkdigit.'EE';
		  $shipmentObj->RecordData[TRACENR_BARCODE] = $shipmentObj->RecordData[BARCODE];
		  $countryDetails = $this->getCountryDetail($shipmentObj->RecordData[COUNTRY_ID],1); 
		  $shipmentObj->RecordData['NoOfCN23']= ($countryDetails['no_of_cn23']>0)?$countryDetails['no_of_cn23']:1;
		  $shipmentObj->RecordData['shipment_worth_inword'] = $this->ConvertToWords($shipmentObj->RecordData['shipment_worth']); 
		  $shipmentObj->RecordData['chunk_barcode'] = commonfunction::String_chunk($shipmentObj->RecordData[BARCODE],1,' ');
	  }
	  public function OmnivaCheckDigit($base_val)
	  {
	   $weightingFactors = array(8,6,4,2,3,5,9,7);
	   $sum=0;
	   for ($i=0; $i<strlen($base_val); $i++)
	   { 
		  $sum = $sum + (substr($base_val,$i,1)*$weightingFactors[$i]);
	   }
	   $remainder = ($sum%11);
	   return ($remainder==0) ? 5 : (($remainder==1) ? 0 : (11-$remainder));
	}
	public function ConvertToWords($n, $followup='Yes')
	 {
 
 if($n==0)
 {
 if($followup=='no')
 {
 return "";
 exit();
 }
 else
 {
 return "zero";
 exit();
 }
 }
 switch($n)
 {
 case 1: return "one"; break;
 case 2: return "two"; break;
 case 3: return "three"; break;
 case 4: return "four"; break;
 case 5: return "five"; break;
 case 6: return "six"; break;
 case 7: return "seven"; break;
 case 8: return "eight"; break;
 case 9: return "nine"; break;
 case 10: return "ten"; break;
 case 11: return "eleven"; break;
 case 12: return "twelve"; break;
 case 13: return "thirteen"; break;
 case 14: return "fourteen"; break;
 case 15: return "fifteen"; break;
 case 16: return "sixteen"; break;
 case 17: return "seventeen"; break;
 case 18: return "eighteen"; break;
 case 19: return "nineteen"; break;
 case 20: return "twenty"; break;
 case 30: return "thirty"; break;
 case 40: return "forty"; break;
 case 50: return "fifty"; break;
 case 60: return "sixty"; break;
 case 70: return "seventy"; break;
 case 80: return "eighty"; break;
 case 90: return "ninety"; break;
 case 100: return "one hundred"; break;
 case 1000: return "one thousand"; break;
 case 100000: return "one lakh"; break;
 default:
 {
 if($n<100)
 {
 return $this->ConvertToWords(floor($n/10)*10, 'no')." ".$this->ConvertToWords($n%10, 'no'); break;
 }
 elseif($n<1000)
 {
 return $this->ConvertToWords(floor($n/100), 'no')." hundred ".$this->ConvertToWords($n%100, 'no'); break;
 }
 elseif($n<100000)
 {
 return $this->ConvertToWords(floor($n/1000), 'no')." thousand ".$this->ConvertToWords($n%1000, 'no'); break;
 }
 elseif($n<10000000)
 {
 return $this->ConvertToWords(floor($n/100000), 'no')." lakh ".$this->ConvertToWords($n%100000, 'no'); break;
 }
 else
 {
 return "Something else"; break;
 }
 }
 }
 }
}