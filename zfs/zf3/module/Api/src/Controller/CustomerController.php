<?php


namespace Api\Controller;


 
class CustomerController extends AbstractRestController
{
	
	 public function getcustomersAction()
	 {
 		if($this->IsTokenValid()){

				$this->getCustomerService();
	 			// echo "<pre>"; print_r($this->ApiParamData); die;
			 	
			 	$result = $this->CustomerService->getCustomers();
			 	
				 
				if(!isset($result['error']) && is_array($result) ){

					$this->ok(array('customers'=>$result));

				}else $this->throwError(404,$result['message']);

 		}
	 	 
	 }

	 public function addCustomersAction()
	 {

	 	 
 		if($this->IsTokenValid()){

			$this->getCustomerService();

		 	$result = $this->CustomerService->addCustomers();
			 
			if(!isset($result['error']) && is_array($result) ){

				$this->ok(array('customers'=>$result));

			}else $this->throwError(404,$result['message']);

 		}
		 

	 }

}