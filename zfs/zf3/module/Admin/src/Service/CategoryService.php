<?php


namespace Admin\Service;

use Admin\Mapper\CategoryMapper;
use Zend\Custom\Custom;

class CategoryService extends Custom
{
	private $CategoryMapper;
	protected $AllCategories = array();

	public function __construct(CategoryMapper $CategoryMapper)
	{
		parent::__construct();
		$this->CategoryMapper = $CategoryMapper;
	}

	public function fetchAllCategories()
	{
		$categories = $this->CategoryMapper->fetchAll();

		$Categories = $this->ManipulateCategories($categories,0,true);

	    $MappedCategories = $this->getRecursiveRows($Categories);

	    return $MappedCategories;
	}

	public function addCategory(array $data)
	{
		if(isset($data['submit']))
		  unset($data['submit']);
		$categories = $this->CategoryMapper->addCategory($data);

	} 

	public function ManipulateCategories(array $elements, $parentId = 0 ,$getrows=false) {
	    $branch = array();
	    // echo "<pre>";print_r($elements);die;
	    foreach ($elements as $element) {
	        if ($element['parent_id'] == $parentId) {
	            $children = $this->ManipulateCategories($elements, $element['category_id']);
	            if ($children) {
	                $element['children'] = $children;
	            }
	            $branch[] = $element;
	        }
	    }

	    // if($getrows){

	    // 	$this->getRecursiveRows($branch);
	    // 	// print_r($this->AllCategories);die;
	    // 	return $this->AllCategories;
	    // }
	    return $branch;
	}

	public function getRecursiveRows($rows,$level=0)
	{
	    // $branch = array();

		if(!empty($rows)){
			foreach ($rows as $key => $row) {

				if($level > 4)
					continue;
				$prefix = str_repeat("_ ", $level);
				$row['CustomName'] = $prefix." ".$row['category_name'];
				$this->AllCategories[$row['category_id']] = $row;
				if(isset($row['children']) && isset($row['children'][0])){
					$newRow = $row['children'];
					unset($row['children']);
					$this->getRecursiveRows($newRow,$level + 1);
				}
			}
		}

	    return $this->AllCategories;


	}

	public function getLib()
	{
		 
	}

	 
}






