<?php 


namespace Api\Controller;

use Zend\View\Model\ViewModel;


class AuthController extends AbstractRestController
{
	
	public function createTokenAction()
	{
		$result = $this->ApiauthService->authenticate($this->apiParams['username'],$this->apiParams['password']);
		 if(isset($result['error']))
		   	$this->throwError(404,$result['message']);
		 else {
			
		 	
		 	$RetToken = $this->ApiauthService->createJwtToken($result['data']['user_id'],$result['data']['email']);

		 	if(isset($result['error']))
		   		$this->throwError(404,$result['message']);
		   	else $this->ok($RetToken);
		 }

	}

	public function refreshTokenAction()
	{
		$result = $this->ApiauthService->authenticate($this->apiParams['username'],$this->apiParams['password']);
		 if(isset($result['error']))
		   	$this->throwError(404,$result['message']);
		 else {

		 	$RetToken = $this->ApiauthService->refreshJwtToken( $this->apiParams['refreshToken'] , $result['data']['user_id'],$result['data']['email']);

		 	if(isset($RetToken['error']))
		   		$this->throwError(404,$RetToken['message']);
		   	else $this->ok($RetToken);
		 }

	}

}