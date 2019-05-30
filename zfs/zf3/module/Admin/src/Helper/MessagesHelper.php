<?php


namespace Admin\Helper;


use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\FlashMessenger;
use Zend\Db\Adapter\Adapter;
use Zend\Crypt\BlockCipher;

class MessagesHelper extends AbstractHelper
{

	public function renderMsgsTemp($template)
	{
		$type = false;

		$FlashMessenger = new FlashMessenger();

		if($FlashMessenger->setnamespace('error')->hasMessages()){
			$messages = $FlashMessenger->setnamespace('error')->getMessages();
			$type = 'error';
		}elseif($FlashMessenger->setnamespace('success')->hasMessages()){
			$messages = $FlashMessenger->setnamespace('success')->getMessages();
			$type = 'success';
		}
		
		// print_r($type);
		// print_r($messages);

		if($type)
		echo $this->getView()->partial('layout/messages',array('type'=>$type,'messages'=>$messages));
	}

	public function EncryptIdentity($identity,$key)
	{
		try {

			@$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
			$blockCipher->setKey($key);
			$result = $blockCipher->encrypt($identity);

			return $result;

		} catch (Exception $e) {
			return false;
		}

	}

	public static function DeCryptIdentity($identity,$key)
	{
		try {

			@$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
			$blockCipher->setKey($key);
			$result = $blockCipher->decrypt($identity);

			return $result;

		} catch (Exception $e) {

			return false;
		}

	}

}