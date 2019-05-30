<?php
class Supplies_SuppliesController extends Zend_Controller_Action

{

    public $Request = array();



    public $ModelObj = null;

	public $cartsession = NULL;

	public $addtocart = NULL;

    public function init()

    {

       try{	

			$this->Request = $this->_request->getParams();

			$this->ModelObj = new Supplies_Model_Supplies();

			$this->formObj = new Supplies_Form_Supplies();

			$this->ModelObj->getData  = $this->Request;

			$this->view->ModelObj = $this->ModelObj;

			$this->addtocart = new Zend_Session_Namespace('logicSeesion');

			$this->cartsession = $this->addtocart->cartsession;

			$this->ModelObj->cartsession = $this->cartsession;

			$this->_helper->layout->setLayout('main');

	  }catch(Exception $e){

	    echo $e->getMessage();die;

	  }

    }



    public function addeditshopproductAction()

    {$this->_helper->layout->setLayout('popup');

		global $objSession;

		$this->formObj->shopList = $this->ModelObj->GetShopList();

	    $this->formObj->LanguageList = $this->ModelObj->activeLanguage();

		$this->formObj->addeditshopproductForm();

			if($this->Request['mode'] == 'add'){

			  $this->view->title = 'Add New Product';

			  if($this->_request->isPost() && !empty($this->Request['submit'])){

			 if($this->formObj->isValid($this->Request)){

			 $result = $this->ModelObj->addProductDetail();

			  if($result){

			  $objSession->successMsg = "Product Added Successfully !!";

			  echo '<script type="text/javascript">parent.window.location.reload();

				  parent.jQuery.fancybox.close();</script>';

				  exit();

			   }else{

					$objSession->errorMsg = "barcode [".$this->Request['eancode']."] already exist in system !";

			   }

			 }else{

			  $this->formObj->populate($this->Request);

			 }

			}

			  

		}elseif(($this->Request['mode'] == 'edit') && isset($this->Request['id'])){

			$this->view->title = 'Update Product';

			$this->formObj->addeditshopproductForm()->submit->setLabel('Update Product');

			if($this->_request->isPost() && !empty($this->Request['submit'])){

			 if($this->formObj->isValid($this->Request)){

			  $this->ModelObj->updateProductDetail();

			  $objSession->successMsg = "Product Updated Successfully !!";

			  echo '<script type="text/javascript">parent.window.location.reload();

				  parent.jQuery.fancybox.close();</script>';

				  exit(); 

			 }else{

			  $this->formObj->populate($this->Request);

			 }

			}else{

			   $fatchRowData = $this->ModelObj->getProductDetailById();

			   if(count($fatchRowData)>0){

					$this->formObj->populate($fatchRowData);

			   }

			}

		   }

	   $this->view->AddEditShopProductForm =  $this->formObj;

    }

	

    public function productlistAction()

    { 

       $this->view->Productlist = $this->ModelObj->getProductList();

    }

	/**

	 * Show All product list for shop

	 * Function : productshopAction()

	 * Date : 16/01/2017

	 **/

	public function productshopAction(){

		$this->view->DepotList = $this->ModelObj->getDepotList();	// get depot list

		$this->view->CustomerList = $this->ModelObj->getCustomerByDepot();

		

		$this->view->ProductShop = $this->ModelObj->getProductList();;

		$this->view->summary  = $this->cartsummaryAction();

  	}

	/**

	 * Show Alladd to cart summary 

	 * Also product total price and view details link

	 * Function : cartsummaryAction()

	 **/

	public function cartsummaryAction() {

		$this->cartsession = $this->addtocart->cartsession;

		$this->ModelObj->cartsession = $this->cartsession;

		$cartitems = $this->ModelObj->getcartdetail();

		

		$subtotal = 0;

		$detail = '';$y='Y';$n='N';

		$item = (isset($this->addtocart->cartsession["webshop"]["item"])) ? $this->addtocart->cartsession["webshop"]["item"] : 0 ;

		$price = (isset($this->addtocart->cartsession["webshop"]["price"])) ? number_format($this->addtocart->cartsession["webshop"]["price"],2) : 0 ;

		

		$detail .= '<b class="cartnav" id="cartitem"><dd><img src="'.IMAGE_LINK.'/cartbox1.png"/></dd><a title="View Iteams" onclick="$.showHide()"><strong> '.$item.' Article - &euro;'.$price.'</strong></a>';

		$detail .='<div class="cartview" id="showHideDiv" style="display:none;">';

		$detail .='<div class="carttable">';

		$detail .= '<table width="70%" align="center" cellpadding="0" cellspacing="0">';

		//echo "<pre>";print_r($cartitems);					 

		if(count($cartitems['Records'])>0) {

			foreach($cartitems['Records'] as $key=>$cartitem) {

				$class = ($key%2==0) ? 'odd' : 'even';

				$imgPath = (!empty($cartitem['image']) && file_exists(ROOT_PATH."/public/webshop_product/".$cartitem['image'])) ? $cartitem['image'] :'/no_image.jpg';

				$quantity = 0;

				if(array_key_exists($cartitem['product_id'],$this->addtocart->cartsession['webshop']['token'])) { 

					$quantity = $this->addtocart->cartsession['webshop']['token'][$cartitem['product_id']];

				}

				$total = ($quantity*$cartitem['price']);

				$subtotal += $total;

				$productID = Zend_Encript_Encription:: encode($cartitem['product_id']);

				  

				$detail .= '<tr class="'.$class.'">';

				

				$detail .= '<td align="center"><img src="'.BASE_URL.'/public/webshop_product/'.$imgPath.'" height="30" width="30"/><span style="width:145px">'.substr($cartitem['product_name'],0,22).'</span><span style="width:40px">'. $quantity.'</span> <span style="width:64px; text-align:right"> &euro;'.number_format($total,2).'</span></td>';

				

				$detail .= '</tr>';				

			}

			$detail .= '<tr>

						<td style="text-align:right !important" class="bold_text">Total : '.number_format($subtotal,2).'</td>

						</tr>';

			

			$detail .= '<tr>

						<td align="right"><a style="font-size: 14px; background: #0063be !important; padding: 0px 10px; color: #ffffff !important;" href="'.BASE_URL.'/Supplies/shoppingcart">View Cart</a></td>

					   </tr>';

		}else{

				$detail .= '<tr class="even">';

				

				$detail .= '<td align="center" style="font-size: 14px; background: #0063be !important; padding: 0px 10px; color: #ffffff !important;"><span>No Item Available, Please Add To Cart !!</span></td>';

				

				$detail .= '</tr>';	

		}

		$detail .= '</table>';

		$detail .= '</div>';

		$detail .= '</div>';

		$detail .= '</b>';

		return $detail;

	}

	/**

	 * get order list

	 * Display the list of order

	 * Function : orderlistAction()

	 **/

    public function orderlistAction()

    { 

       $this->view->orderlist = $this->ModelObj->getWebShopOrderDetail();

	   $this->view->customerList = $this->ModelObj->getCustomerList();

    }



	/**

	 * get edit order detail

	 * Display the list of order

	 * Function : editorderAction()

	 **/

    public function editorderAction()

    {  $this->_helper->layout->setLayout('popup');

	   global $objSession;

	   		if (isset($this->Request['editshoporder']) && $this->Request['editshoporder']=='Update' && isset($this->Request['order_id']) && $this->Request['order_id']!='' && isset($this->Request['order_number']) && $this->Request['order_number']!='') {

				if(count($this->Request['product'])>0){

					if($this->ModelObj->editorder()){

						$objSession->successMsg = "Order detail updated !!";

						  echo '<script type="text/javascript">parent.window.location.reload();

						  parent.jQuery.fancybox.close();</script>';

						  exit();

					}

					else{

						$objSession->errorMsg = "Product order should not be empty !!";

						$this->_redirect($this->_request->getControllerName().'/editorder/?order_id='.$this->Request['order_id']);

					}

				}

				else{

					$objSession->errorMsg  = "Product order should not be empty !!";

						$this->_redirect($this->_request->getControllerName().'/editorder/?order_id='.$this->Request['order_id']);

				}

			}

       $this->view->orderlist = $this->ModelObj->GetOrderById();

    }

	/**

     * check order number in system

     * Function : checkorderAction()

     * check order number for add & update order

     **/

	public function checkorderAction(){

		$OrderCount = $this->ModelObj->CheckOrderNumber(); 

		if ($OrderCount['CNT'] > 0) {

			echo $OrderCount['CNT']; exit;

		} else {

			echo 0;	exit;

		}

	}

	/**

	 * get product price 

	 * Function : indexAction()

	 * Date : 21/03/2017

	 **/

	public function getproductpriceAction(){

			$priceData = $this->ModelObj->GetProductPrice($this->Request);

			

			if ($priceData['price'] > 0) {

				echo $priceData['price'];	exit;

			} else {

				echo 0;	exit;

			}

	

	}

	/**

	 * Show All Websshop product list 

	 * Also product details filter by date, name & description is there

	 * Function : indexAction()

	 **/

	public function addtocartAction(){

		if($this->_request->isPost()){

			$this->Request['product_id'] =  Zend_Encript_Encription:: decode($this->Request['token']);

			$this->Request['depot'] =  Zend_Encript_Encription:: decode($this->Request['depot']);

			$this->Request['customer'] =  Zend_Encript_Encription:: decode($this->Request['customer']);

			$priceData = $this->ModelObj->GetProductPrice($this->Request); // Get webshop active product price

			

			$sess['item']  = (isset($this->addtocart->cartsession['webshop']['item']))  ? ($this->addtocart->cartsession['webshop']['item']+1) : '1';

			$sess['price'] = (isset($this->addtocart->cartsession['webshop']['price'])) ? ($this->addtocart->cartsession['webshop']['price']+$priceData['price']) : $priceData['price'];

			$seeToken      = (isset($this->addtocart->cartsession['webshop']['token'])) ? $this->addtocart->cartsession['webshop']['token'] : array();

			$newTokenArr   = array();

			if(count($seeToken)>0) {

				foreach($seeToken as $key=>$prd) {

					if($key == $this->Request['product_id']) {

						$newTokenArr[$key] = ($prd+1);

					}

					else {

						$newTokenArr[$key] = $prd;

					}

				}

			}

			

			if(!array_key_exists($this->Request['product_id'],$newTokenArr)) {

				$newTokenArr[$this->Request['product_id']] = 1;

			}

			

			unset($this->addtocart->cartsession['webshop']['token']);

			$this->addtocart->cartsession['webshop'] = $sess;

			$this->addtocart->cartsession['webshop']['token'] 	= $newTokenArr; //print_r($_SESSION['webshop']);echo '<br>';

			$this->addtocart->cartsession['webshop']['depot'] 	= $this->Request['depot'];

			$this->addtocart->cartsession['webshop']['customer']= $this->Request['customer'];

			

			echo $this->cartsummaryAction();exit;

		}

	}

	/**

	 * Show All Websshop product list 

	 * Also product details filter by date, name & description is there

	 * Function : shoppingcartAction()

	 **/

	public function shoppingcartAction(){

		global $objSession;

		if ($this->getRequest()->isPost()) {

			if($this->ModelObj->addorder()){

				unset($this->addtocart->cartsession['webshop']['item']);

				unset($this->addtocart->cartsession['webshop']['price']);

				unset($this->addtocart->cartsession['webshop']['token']);

				unset($this->addtocart->cartsession['webshop']['depot']);

				unset($this->addtocart->cartsession['webshop']['customer']);

				$objSession->successMsg = "Order added successfully !";

				$this->_redirect($this->_request->getControllerName().'/orderlist');

			}

			else{

				$objSession->errorMsg = "There is some problem in adding order, please try again !";

				$this->_redirect($this->_request->getControllerName().'/addwebshoporder');

			}

		}	

		    

		$this->view->cartitems = $this->getcartAction();

  	}

	/**

	 * Show All Websshop product list 

	 * Also product details filter by date, name & description is there

	 * Function : indexAction()

	 **/

	public function getcartAction() {

		$depot = (isset($this->addtocart->cartsession["webshop"]["depot"])) ? $this->addtocart->cartsession["webshop"]["depot"] : 0 ;

		$customer = (isset($this->addtocart->cartsession["webshop"]["customer"])) ? $this->addtocart->cartsession["webshop"]["customer"] : 0 ;

			$cartitems = $this->ModelObj->getcartdetail();	

		$subtotal = 0;

		$detail = '';$y='Y';$n='N';

		

		$detail .= '<table width="100%" border="0" id="dataTableGridId">

					    <thead>

						<tr style="text-align: center;color: #ffffff;background: #484b68;">

								<th>Image</th>

								<th>Product Name</th>

								<th>Quantity</th>

								<th>Unit Price</th>

								<th>Total Price</th>

					    <input type="hidden" name="depot" value="'.$depot.'"/>

						<input type="hidden" name="customer" value="'.$customer.'"/>

					   </tr>

					  </thead>';

		if(count($cartitems['Records'])>0) {

			foreach($cartitems['Records'] as $key=>$cartitem) {

				$class = ($key%2==0) ? 'odd' : 'even';

				$imgPath = (!empty($cartitem['image']) && file_exists(ROOT_PATH."/public/webshop_product/".$cartitem['image'])) ? $cartitem['image'] :'/no_image.jpg';

				$quantity = 0;

				if(array_key_exists($cartitem['product_id'],$this->addtocart->cartsession['webshop']['token'])) { 

					$quantity = $this->addtocart->cartsession['webshop']['token'][$cartitem['product_id']];

				}

				$total = ($quantity*$cartitem['price']);

				$subtotal += $total;

				$productID = Zend_Encript_Encription:: encode($cartitem['product_id']);

				  

				$detail .= '<tr class="'.$class.'" style="position:relative">';

				

				$detail .= '<input type="hidden" name="token[]" id="token'.$key.'" value="'.$productID.'" />';

				$detail .= '<td align="center"><img src="'.BASE_URL.'/public/webshop_product/'.$imgPath.'" height="50" width="50"/></td>';

				$detail .= '<td align="center" valign="middle">'.$cartitem['product_name'].'</td>';

				$detail .= '<td align="center" id="showquan'.$key.'" style="position::relative">'.$quantity;

				$detail .= '&nbsp;&nbsp;<img src="'.IMAGE_LINK.'/edit-icon.png" title="Change Quantity" alt="Change Quantity" onClick="return editdatashow('."'".$y."'".','."'".$key."'".')" align="absmiddle" border="0" />';

				$detail .= '&nbsp;&nbsp;<img src="'.IMAGE_LINK.'/cancel.png" title="Delete Article" alt="Delete Article" onClick="return deletedata('."'".$key."'".');" align="absmiddle" border="0" />';

				

				$detail .= '</td>';

				$detail .= '<td align="center" id="editquan'.$key.'" style="display:none;">';

				$detail .= '<input type="text" name="qantity[]'.$key.'" id="qantity'.$key.'" value="'.$quantity.'" style="width:25px;" />';

				$detail .= '&nbsp;&nbsp;<img src="'.IMAGE_LINK.'/save.png" title="Update Quantity" alt="Update Quantity" onClick="return savedata('."'".$key."'".');" align="absmiddle" border="0" />';

				$detail .= '&nbsp;&nbsp;<img src="'.IMAGE_LINK.'/cancel.png" title="Cancel" alt="Cancel" onClick="return editdatashow('."'".$n."'".','."'".$key."'".')" align="absmiddle" border="0" />';

				$detail .= '<div class="imgloading" style="display:none;" id="loader_'.$key.'"><img src="'.IMAGE_LINK.'/loader.gif"></div>';

				$detail .= '</td>';				

				$detail .= '<td align="right">&euro;'.number_format($cartitem['price'],2).'</td>';

				$detail .= '<td align="right">&euro;'.number_format($total,2).'</td>';

				$detail .= '</tr>';				

			}

			$detail .= '<tr>

						<td></td>

						<td></td>

						<td></td>

						<td align="right" class="bold_text">Sub-Total : </td>

						<td align="right">&euro;'.number_format($subtotal,2).'</td>

						</tr>';

			

			$detail .= '<tr>

						<td></td>

						<td></td>

						<td></td>

						

						<td align="center"><button class="org_button" id="continue" onClick="return continueshop()" title="Continue Shopping...." type="button" style="float:right">Continue...</span></button></td>

						

						<td align="center"><button class="org_button" title="Place Order" type="submit" style="float:right"><i class="fa fa-shopping-cart"></i><span class="hidden-xs hidden-sm hidden-md">Place Order</span></button></td>

					  </tr>';

		}

		else{

	   		$detail .= '<tr>

						  <td colspan="4" align="center"><font style="color:#0066FF"><b>Please click on continue button and add Item in your cart to place your order !</b></font></td>

						  <td align="center"><button class="org_button" id="continue" onClick="return continueshop()" title="Continue Shopping...." type="button" style="float:right">Continue...</span></button></td>

						   </tr>';

	  	}

		$detail .= '</table>';

		return $detail;

	}

	

	/**

	 * Show All Websshop product list 

	 * Also product details filter by date, name & description is there

	 * Function : indexAction()

	 **/

	public function updatecartAction(){

		if(isset($this->Request['update']) && $this->Request['update']=='order'){

			$product_id = Zend_Encript_Encription:: decode($this->Request['token']);

			$quantity = (isset($this->Request['quantity'])) ? $this->Request['quantity'] : 0;

			

			unset($this->addtocart->cartsession['webshop']['item']);

			unset($this->addtocart->cartsession['webshop']['price']);

			$seeToken      = (isset($this->addtocart->cartsession['webshop']['token'])) ? $this->addtocart->cartsession['webshop']['token'] : array();

			$newTokenArr   = array();

			$item = 0;

			$price = 0;

			if(count($seeToken)>0) {

				foreach($seeToken as $key=>$prd) {

					if($quantity<=0) {

						if($key != $product_id) {

							$this->Request['product_id'] = $key;

							$priceData = $this->ModelObj->GetProductPrice($this->Request); // Get webshop active product price

							$item  += $prd;

							$price += ($prd*$priceData['price']);

							$newTokenArr[$key] = $prd;

						}

					}

					else {

						if($key == $product_id) {

							$newTokenArr[$key] = $quantity;

						}

						else {

							$newTokenArr[$key] = $prd;

						}

						$this->Request['product_id'] = $key;

						$priceData = $this->ModelObj->GetProductPrice($this->Request); // Get webshop active product price

						$item  += $newTokenArr[$key];

						$price += ($newTokenArr[$key]*$priceData['price']);

					}

				}

			}

			

			unset($this->addtocart->cartsession['webshop']['token']);

			$this->addtocart->cartsession['webshop']['item'] = $item;

			$this->addtocart->cartsession['webshop']['price'] = $price;

			$this->addtocart->cartsession['webshop']['token'] = $newTokenArr; //print_r($_SESSION['webshop']);echo '<br>';

			$this->ModelObj->cartsession = $this->addtocart->cartsession;

			$getCart = $this->getcartAction();

			echo $getCart;exit;

		}

	}

}

