<?php


namespace Api\Service;

use Api\Mapper\CustomerMapper;
 
class CustomerService extends AbstractService implements CustomerServiceInterface
{
	
	protected $CustomerMapper;

	public function __construct(CustomerMapper $CustomerMapper)
	{
		$this->CustomerMapper = $CustomerMapper;
		 
	}



	public function addCustomers()
	{
 
		$data = array(
			'first_name' => $this->ServiceParams['firstname'],
			'last_name' => $this->ServiceParams['lastname'],
			'code' => $this->ServiceParams['code'],
			'email' => $this->ServiceParams['email'],
			'mobile' => $this->ServiceParams['mobile'],
			'addressline1' => $this->ServiceParams['addressline1'],
			'addressline2' => $this->ServiceParams['addressline2'],
			'pincode' => $this->ServiceParams['pincode']
		);

			$result = $this->CustomerMapper->addCustomer($data);

			if(isset($result['error']))
				return array('error'=>true,'message'=>$result['message']);
 			$result = $this->CustomerMapper->getCustomers($result);

			return $result; 
	}

	public function getCustomers()
	{
		$id = $this->ServiceParams['id'];
		$page = $this->ServiceParams['page'];
		$limit = $this->ServiceParams['limit'];

		$result = $this->CustomerMapper->getCustomers($id,$page,$limit);

		if(isset($result['error']))
			return array('error'=>true,'message'=>$result['message']);
		else return $result;
	}

}