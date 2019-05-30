<?php 


namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form\BackendForm; 
use Admin\Service\CategoryService; 
use Admin\Filter\AbstractFilter;

class CategoriesController extends MasterController
{
	protected $categoryService;
	protected $mediaService;

	public function __construct($categoryService='',$mediaService='')
	{
		$this->categoryService = $categoryService;
		$this->mediaService = $mediaService;
	}
	
	
	public function getallAction()
	{

		$Categories = $this->categoryService->fetchAllCategories();
		// echo "<pre>"; print_r($Categories);die;
		return new ViewModel(array('Categories'=>$Categories));
	}

	public function addcategoryAction()
	{
		$Categories = $this->categoryService->fetchAllCategories();
		$Categories = $this->ToArraySelect($Categories,'category_id','CustomName');	
		$form = new BackendForm();
		$form->setCategories($Categories);
		$form = $form->addCategoryForm('category_form'); 

		$request = $this->getRequest();

		if($request->isPost()){

			$filter = new AbstractFilter();
			$filter->setCategoryFilter();
			$form->setInputFilter($filter);
			$form->setData($request->getPost());

			if($form->isValid()){

				$postData = $request->getPost()->toArray();
				
				$FilesData = $request->getFiles()->toArray();
				if(!empty($FilesData))
				$postData = array_merge($postData,$FilesData);


				$AddedResp = $this->mediaService->saveFiles('category_image', $FilesData,'categories');

				if(!is_array($AddedResp) && $AddedResp ){
					$postData['category_image'] = $AddedResp;
					
					$FinalResp = $this->categoryService->addCategory($postData);


					if(isset($FinalResp['message']))
						$this->flashMessenger()->setnamespace('error')->addMessage($FinalResp['message']);
					else $this->flashMessenger()->setnamespace('sucess')->addMessage('New Category added successfuly');

				}
				else if(isset($AddedResp['message']))
						$this->flashMessenger()->setnamespace('error')->addMessage($AddedResp['message']);

					echo '<script type="text/javascript">parent.window.location.reload();

                                parent.$.jeegoopopup.close();</script>';
						exit();

			} 

		}

		$view = new ViewModel(array('form'=>$form));
		$view->setTerminal(true);
		return $view;
	}

	public function getcustomAction()
	{
		$this->categoryService->getLib();

	}

}