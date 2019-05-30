<?php

ini_set('display_errors','1');

class Reports_ReportsController extends Zend_Controller_Action

{

	public $Request = array();

    public function init()

    {

        try{	

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Reports_Model_Reports();

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->_helper->layout->setLayout('main');

	 }catch(Exception $e){

	    echo $e->getMessage();die;

	 }	

    }



    public function indexAction()

    {

        // action body

    }



    public function graphicalreportAction()

    {

        // action body

    }



    public function differencesAction()

    {

        $this->view->records = $this->ModelObj->getWeightDIfferenceParcel();

		$this->view->countrylist =  $this->ModelObj->getCountryList();

	    $this->view->depotlist =  $this->ModelObj->getDepotList();

		$this->view->customerlist =  $this->ModelObj->getCustomerList();

		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();

		$this->view->servicelist =  $this->ModelObj->getCustomServiceList();

		$this->view->addedtype = $this->ModelObj->ParcelAddedType();

    }



    public function forwarderreportAction()

    {

       $this->view->records = $this->ModelObj->getForwarderReport();

    }



    public function wrongaddressAction()

    {

        $this->view->records =$this->ModelObj->WrongAddressReport();

	    $this->view->countrylist =  $this->ModelObj->getCountryList();

	    $this->view->depotlist =  $this->ModelObj->getDepotList();

		$this->view->customerlist =  $this->ModelObj->getCustomerList();

		$this->view->forwarderlist =  $this->ModelObj->getForwarderList();

    }
	
	public function testmailAction(){
	    try{
		 $config = array('auth' => 'login',
          			   'username' => 'info@dpost.be',
                       'password' => 'kVykRTiQJLq',
                       'ssl' => 'ssl',
                       'port' => 465);
			$Transport = new Zend_Mail_Transport_Smtp('w1.dpost.be', $config);
			Zend_Mail::setDefaultTransport($Transport);
			$mailObj = new Zend_Mail();
			$mailObj->setType(Zend_Mime::MULTIPART_RELATED);
			$mailObj->setFrom ('info@dpost.be','info@dpost.be'); 
			$mailObj->addTo(array('ipm4wz@glockapps.com','marispurinsh@aol.com','caseywrighde@aol.de','baileehinesfr@aol.fr','brendarodgersuk@aol.co.uk','ingridmejiasri@aol.com','keelyrichardrk@aol.com','julianachristensen@aol.com','julia_g_76@icloud.com','bcc@spamcombat.com','glock.julia@bol.com.br','drteskissl@eclipso.de','exosf@glockeasymail.com','carloscohenm@freenet.de','emailtester493@gmail.com','glockapp.aurelio@gmail.com','lanawallert@gmail.com'));
			$mailObj->addCc(array('maceynicholsonqw@gmail.com','kennethsimonmce@gmail.com','bennybaldwinfte@gmail.com','heidigriffithgd@gmail.com','verifycom79@gmx.com','verifyde79@gmx.de','gd@desktopemail.com','verify79@buyemailsoftware.com','frankiebeckerp@hotmail.com','verify79ssl@laposte.net','amandoteo79@libero.it','glocktest@vendasrd.com.br','b2bdeliver79@mail.com','verifymailru79@mail.ru','verify79ssl@netcourrier.com','grantglover@openmailbox.org','brendonosbornx@outlook.com','tristonreevestge@outlook.com.br','brittanyrocha@outlook.de','glencabrera@outlook.fr','christopherfranklinhk@outlook.com','kaceybentleyerp@outlook.com','meaghanwittevx@outlook.com','aileenjamesua@outlook.com','verify79@seznam.cz','sa79@justlan.com','amandoteo79@virgilio.it','verify79@web.de','sebastianalvarezv@yahoo.com.br','verifyca79@yahoo.ca','emailtester493@yahoo.com','testiotestiko@yahoo.co.uk','justynbenton@yahoo.com','loganbridgesrk@yahoo.com','rogertoddw@yahoo.com','darianhuffg@yahoo.com','verifyndx79@yandex.ru','verifynewssl@zoho.com','chazb@userflowhq.com','lamb@glockdb.com'));
			$mailObj->setSubject ('Testing mail Verification');
			$mailObj->setBodyHtml('Testing mail for Verification, id: 03561db7-7e9d7113-f4092e78-2f725421');
			$mailObj->setBodyText('Test mail for verification, id: 03561db7-7e9d7113-f4092e78-2f725421');
			if($mailObj->send($Transport)){
			    echo "Sent";die;
			}else{
			   echo "Not Sent";die;
			}
		}catch(Exception $e){
		  echo $e->getMessage();die;
		}	
	 echo "HI";die;
	}


	public function glsitbulkcloseworkdaytestAction()
	{

		$this->ModelObj->CloseWorkDayBulkSend(); 	

	}


}



















