<?php



namespace Api\Controller;



class UserController extends AbstractRestController
{

	public function getAllAction()
	{

		$token = $this->getBearerToken();

		if($token){

			$this->getApiauthService();
			$result = $this->ApiauthService->matchToken($token);

			if(!isset($result['error']) && (isset($result['data']) && !empty($result['data'])) ){

					$this->getUserService();
					
					$userExists = $this->UserService->getUser( $this->ApiauthService->DeCryptIdentity($result['data']->id, 'userIdentitykey'));
					
						if($userExists){

							$users = $this->UserService->getAllUsers();
							if(!isset($users['error']) ){
								$this->ok(array('users'=>$users));
							}else $this->throwError(404,$users['message']);

						}else $this->throwError(404,'Invalid User Entry ');


			}else $this->throwError(404,$result['message']);

		}else $this->throwError(404,'Invalid Token');

	}
	 
}