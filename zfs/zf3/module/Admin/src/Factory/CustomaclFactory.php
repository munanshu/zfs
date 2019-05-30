<?php


namespace Admin\Factory;

use Zend\ServiceManager\FactoryInterface; 
use Admin\Navigation\Customacl;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Navigation;
use Zend\Navigation\Service\AbstractNavigationFactory;


class CustomaclFactory extends AbstractNavigationFactory
{
	
	 public $ResultMenu = array();

	 public function getName()
	 {
	 	return 'Customacl';
	 }

	public function __invoke(ContainerInterface $container , $requestedName, array $options = null)
	{
		
        return new Navigation(self::getPages($container));

	}

	public function getPages(ContainerInterface $container)
	 {

		 	if (null === $this->pages) {

		 		$ModuleMapper = $container->get('ModuleMapper');
		 		$CustomaclData = $ModuleMapper->fetchAllModules();

				$CustomaclData = $this->ManipulateMenu($CustomaclData);


	        	$configuration = array( 'navigation' => [

				        'Customacl' =>  $CustomaclData

				    ] 
				);

				$pages = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);
		        $this->pages = $this->preparePages($container, $pages);
		    }
		    
        return $this->pages;
	 }


	 public function ManipulateMenu($menu,$parent=false)
	{
		if(!empty($menu)){

			foreach ($menu as $key => $menuitem) {

				if( $menuitem['parent_id']>0 ){
					if($parent == $menuitem['parent_id']){
						 
						$this->ResultMenu[$menuitem['parent_id']]['pages'][] = $this->AdminNavData($menuitem);
					}
					else { 

						$this->ManipulateMenu(array($menuitem),$menuitem['parent_id']); 
					}
				}else $this->ResultMenu[$menuitem['id']] = $this->AdminNavData($menuitem);


			}

		}
			return $this->ResultMenu;	


	}

	public function AdminNavData(array $data)
	{
		$ReturnArr = array();

		$ReturnArr['label'] = $data['module_name'];
		$ReturnArr['route'] = $data['route_name'];
		$ReturnArr['controller'] = $data['controller'];
		$ReturnArr['action'] = $data['action'];

		return $ReturnArr;
	}


}