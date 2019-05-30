<?php


namespace Api\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Http\Response;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Api\JWTAuth\JWT;
use Api\Response\JsonResponse;
use Zend\Http\Request;
use Api\Model\UserTable;
use Zend\Crypt\BlockCipher;

class ApiauthService extends Request
{
	 
	public $getApiParams;
	private $secretKey = "998b4bbf2ab1bfe46b28e487b961dc586430197cb881240c8f73291c6f92d380F13rWym3+10Rk7rXkFDqLS1QEXzi5J0uP6VG+/hAWFY=";
	private $expirationTime = (200 * 60);
	private $refreshExpirationTime = (200 * 60);
	private $usertable;


	public function __construct(Adapter $Adapter, UserTable $usertable)
	{
		$this->Adapter = $Adapter;
		$this->usertable = $usertable;
	}

	public function authenticateUser()
	{
		$username = $this->getApiParams['username'];
		$password = $this->getApiParams['password'];

   		$DbAdapter = $this->Adapter;

		$authAdapter = new  AuthAdapter($DbAdapter,
			'tbl_users',
			'username',
			'password', 
			"MD5( CONCAT( pass_hash , ?) )"
			);
		 $authAdapter->setIdentity($username)->setCredential($password);

		 $auth = new AuthenticationService();
         $select = $authAdapter->getDbSelect();
         $select->where('is_Active =1');
		 $result = $auth->authenticate($authAdapter);
		 
		 switch ($result->getCode()) {

		    case Result::FAILURE_IDENTITY_NOT_FOUND:
		    	return array('error'=>true,'message'=>'This identity does not exists ' );
		        break;

		    case Result::FAILURE_CREDENTIAL_INVALID:
		    	return array('error'=>true,'message'=>'password is not valid' );

		        break;

		    case Result::SUCCESS:
				
		    	$columnsToReturn = array(
				    'user_id','email'
				);
				
				$resultIdentity = $authAdapter->getResultRowObject($columnsToReturn)->user_id;
				$resultIdentityEmail = $authAdapter->getResultRowObject($columnsToReturn)->email;
		       	 

				return array('data'=>array('user_id'=>$resultIdentity,'email'=>$resultIdentityEmail));				
				break;
		    default:
		    	return array('error'=>true,'message'=>'Some internal error occurred' );
		        break;
		}
	}

	public function createJwtToken($resultIdentity,$email)
	{
 		try {

 			$ret = $this->usertable->deleteExistingTokens($resultIdentity);

	  		if( $ret ){
	  			$token = $this->createToken($resultIdentity,$email);
	  			if( !empty($token) ){
	  				$refreshToken = $this->createToken($resultIdentity,$email,'refresh');
		  			  
		  				$tokenData = array('user_id'=> $resultIdentity,'token'=>$refreshToken['token'],'type'=>'2','created_on'=>date('y-m-d h:i:s') );
		  				$ret = $this->usertable->saveToken($tokenData);
		  				if($ret)
			  				return array('token'=>$token['token'],'refreshToken'=>$refreshToken['token']);

		  			else return array('error'=>true,'message'=>'some internal error occurred while creating token');

	  			}else return array('error'=>true,'message'=>'token can not be created');
	  		
	  		}else return array('error'=>true,'message'=>'token can not be created');


 		} catch (Exception $e) {
			
			return array('error'=>true,'message'=>$e->getMessage());
 			
 		}
		
	}

	public function EncryptIdentity($identity,$key)
	{
		try {

			$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
			$blockCipher->setKey($key);
			$result = $blockCipher->encrypt($identity);

			return $result;

		} catch (Exception $e) {
			return false;
		}

	}

	public function DeCryptIdentity($identity,$key)
	{
		try {

			$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
			$blockCipher->setKey($key);
			$result = $blockCipher->decrypt($identity);

			return $result;

		} catch (Exception $e) {

			return false;
		}

	}


	public function createToken($resultIdentity,$email,$type='access')
	{  
		$extime = $type == "access" ? $this->expirationTime  :  $this->refreshExpirationTime ;

		$encryptedIdentity = $this->EncryptIdentity($resultIdentity,'userIdentitykey');

		$payload = array(
			"iat" => time(),
			"iss" => 'localhost',
		    "id"  => $encryptedIdentity,
    		"exp" => time() + ( $extime ),
    		'Tokentype' => $type
  		);
 		try {
 			
  			$token = JWT::encode($payload, $this->secretKey );
  			if(!empty($token))
  				return array('token'=>$token);
 		} catch (Exception $e) {
			
			return array('error'=>true,'message'=>$e->getMessage());
 			
 		}

	}

	public function refreshJwtToken($refreshToken,$user,$email)
	{
		
		try {
				$userTokenFetched = $this->usertable->fetchTokenbyUser( $refreshToken , $user);

				if(property_exists($userTokenFetched, 'token')){

					$this->expirationTime = $this->refreshExpirationTime;
					$payload = $this->matchToken($refreshToken,'refresh');
					// print_r($payload);die;
					if( isset($payload['error']) )
							return $payload;
					  else {
					  		if(isset($payload['data']) && property_exists($payload['data'], 'Tokentype') ){

						  		if($payload['data']->Tokentype == "refresh"){
						  			$data = $this->createJwtToken($user,$email);
						  			return $data;
								}else return array('error'=>true,'message'=>'refresh token invalid');
					  		}

					  	}	

				  }else return array('error'=>true,'message'=>'refresh token invalid');

			 } catch (Exception $e) {
			
			return array('error'=>true,'message'=>$e->getMessage());
		 } 
				 

		
	}


	public function matchToken($token='',$type='access')
	{
		try {
			$payload = JWT::decode($token, $this->secretKey ,'HS256');
			if( !empty($payload) && is_object($payload) && property_exists($payload, 'iat') ){

				// echo date("y-m-d h:i:s", $payload->iat ) ;die;
				// echo $this->isExpired($payload->iat); 
				// print_r($payload);die;
					if( $type=="access" && $this->isExpired($payload->iat))
						return array('error'=>true,'message'=>'token has been expired');
					else return array('data'=>$payload);
			}else return array('error'=>true,'message'=>'token invalid');

		} catch (Exception $e) {

			return array('error'=>true,'message'=>$e->getMessage());
		}
	}

	public function isExpired($issuedat='')
	{
		 return ( time() - $issuedat ) > $this->expirationTime  ? true  : false ;
	}



}