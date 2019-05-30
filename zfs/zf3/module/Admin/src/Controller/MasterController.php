<?php


namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Crypt\BlockCipher;
 
class MasterController extends AbstractActionController
{
	protected $ServiceManager;
	protected $UserService;
	public $_request;


	public function OnDispatch(MvcEvent $e)
	{
		parent::OnDispatch($e);
		$this->_request = $this->getRequest();

	}

 


	public function setServiceManager(ServiceManager $sm)
	{
		$this->ServiceManager = $sm;
		return $this;
	}

	public function getServiceManager()
	{
		return $this->ServiceManager;
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

	public function ToArraySelect($Data,$optionId,$optionVal)
	{
		if(!empty($Data)){
			foreach ($Data as $key => $value) {
				$Options[$value[$optionId]] = $value[$optionVal];
			}
			return $Options;
		}
	}

}