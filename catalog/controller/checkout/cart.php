<?php 
class ControllerCheckoutCart extends Controller {
	private $error = array();
	
	public function index() {
		$this->language->load('checkout/cart');

		if (!isset($this->session->data['vouchers'])) $this->session->data['vouchers'] = array();
		//my_product_special  discount to xx bath
		//my_product_discount 2@500
		// Update
		if (!empty($this->request->post['quantity'])) {
			foreach ($this->request->post['quantity'] as $key => $value) {
				$this->cart->update($key, $value);
			}
			//unset($this->session->data['vender']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']); 
			unset($this->session->data['reward']);
			//$this->redirect($this->url->link('checkout/cart'));  			
		}
		// Remove
		if (isset($this->request->get['remove'])) {
			$this->cart->remove($this->request->get['remove']);
			unset($this->session->data['vouchers'][$this->request->get['remove']]);
			$this->data['remover'] = '1';
			$this->session->data['success'] = $this->language->get('text_remove');
		
			//unset($this->session->data['vender']);
			unset($this->session->data['shipping_pre_method']);
			unset($this->session->data['shipping_ava_method']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']); 
			unset($this->session->data['reward']);  
		}else{
			$this->data['remover'] = '0';
		}
			
		// Coupon    
		if (isset($this->request->post['coupon']) && $this->validateCoupon()) { 
			$this->session->data['coupon'] = $this->request->post['coupon'];
			$this->session->data['success'] = $this->language->get('text_coupon');
			//$this->redirect($this->url->link('checkout/cart'));
		}
		// Coupon    
		if ( isset($this->request->get['rev']) ) unset($this->session->data['coupon']);
		// Voucher
		if (isset($this->request->post['voucher']) && (strlen($this->request->post['voucher']) > 0)  ) { 
			$this->session->data['voucher'] = $this->request->post['voucher'];
			$this->session->data['success'] = $this->language->get('text_voucher');
		}
		// Reward
		if (isset($this->request->post['reward']) && (strlen($this->request->post['reward']) > 0) ) { 
			$this->session->data['reward'] = abs($this->request->post['reward']);
			$this->session->data['success'] = $this->language->get('text_reward');
		}
		//Vender
		$this->session->data['vender'] = (isset($this->request->post['vender'])  ) ? $this->request->post['vender'] : '';
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('heading_title'),
        	'separator' => $this->language->get('text_separator')
      	);

		// Get Price [PreOrder,Order] , Get Shipping Methods , Get Shipping Prices
    	if ( $this->cart->hasProducts() ) {
			$points = $this->customer->getRewardPoints();
			$points_total = 0;
			foreach ($this->cart->getProducts() as $product) 
				if ($product['points']) $points_total += $product['points'];
			
			if (isset($this->error['warning'])) {
				$this->data['error_warning'] = $this->error['warning'];
			} elseif (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
      			$this->data['error_warning'] = $this->language->get('error_stock');		
			} else {
				$this->data['error_warning'] = '';
			}
			$this->data['attention'] = ($this->config->get('config_customer_price') && !$this->customer->isLogged()) ? sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register')) : '';
			if (isset($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];
				unset($this->session->data['success']);
			} else {
				$this->data['success'] = '';
			}

			$this->load->model('catalog/product');
			$products = $this->cart->getProducts();
			//Change Product Image follow OPtion Image//$products = $this->model_catalog_product->checkOptionImage($products);
			$pre_count = 0;  $preorder_a = array(); 
			$ava_count = 0;  $active_a = array();

			$this->data['force_send']	 = 0;
			foreach($products as $p2){
				$tmp = $this->model_catalog_product->isProductPreOrder($p2['product_id'] );
				if(  $this->model_catalog_product->checkForceSend($p2['product_id'])  ){
					$this->data['force_send']	 = 1;
				}
				if($tmp == 1){
					$pre_count =  $pre_count + 1;
					array_push($preorder_a , $p2['product_id']);
				}else{
					$ava_count =  $ava_count + 1;
					array_push($active_a , $p2['product_id']);
				}
			}

			$this->data['opder_splitted'] = "0";
			$this->session->data['opder_splitted'] = "0";

			/*
			*	Shippinh (EMS/Register) Overweight?
			*/
			$this->load->model('checkout/shipping');

			$shp_md = (isset($this->request->post['shipping_methods']) &&($this->request->post['shipping_methods'] == "2")) ? 2 : 1;
			$shipping_methods =  (  $shp_md  == "2"  )? "REGISTER" : "EMS" ;
			$tmp = $this->model_checkout_shipping->checkShippingOverWeight( $shp_md  );
			$shipping_methods						= $tmp[0];
			$this->session->data['shipping_type']	= $tmp[1];
			$this->data['error_weight']				= $tmp[2];

 			/*
			*	Shippinh Price
			*/
			// SPLIT PRODUCT AVALIABLE & PREORDER
					/*2==0 || 2==2
					F || T
					2==1 || 2==1
					F||F*/
			if((count($products) == $pre_count)||(count($products) == $ava_count) ){
				$this->load->model('catalog/product');
				//print_r($this->cart);
				$pp = $this->model_catalog_product->getProductWeight($this->cart->getWeight() , $shipping_methods);
				$this->session->data['shipping_method'] = Array ( 'title' => 'อัตราค่าจัดส่ง' , 'code' => 'flat.flat' , 'cost' => $pp  , 'tax_class_id' => 9 , 'text' => $pp." ฿" );
				$this->data['shipping_price'] = abs($pp)." ฿";

			}else{
				/*  preorder / avaliable  */
				$this->data['pre_shipping'] = $this->model_catalog_product->getProductWeight( $this->model_checkout_shipping->calWeight($preorder_a, $products) , $shipping_methods);
				$this->data['pre_shipping_price'] = abs($this->data['pre_shipping'])." ฿"  ;
				$this->data['pre_product'] = $preorder_a;
				$this->data['ava_shipping'] = $this->model_catalog_product->getProductWeight( $this->model_checkout_shipping->calWeight($active_a, $products) , $shipping_methods);
				$this->data['shipping_price'] = abs($this->data['ava_shipping'])." ฿"  ;
				$this->data['ava_product'] = $active_a;
				$this->session->data['shipping_pre_method'] = Array ( 'title' => 'อัตราค่าจัดส่ง' , 'code' => 'pre_shipping' , 'cost' => $this->data['pre_shipping']  , 'tax_class_id' => 9 , 'text' => $this->data['pre_shipping']." ฿" );
				$this->session->data['shipping_ava_method'] = Array ( 'title' => 'อัตราค่าจัดส่ง' , 'code' => 'ava_shipping' , 'cost' => $this->data['ava_shipping']  , 'tax_class_id' => 9 , 'text' => $this->data['ava_shipping']." ฿" );

				$this->data['opder_splitted'] = "1";// YES
				$this->session->data['opder_splitted'] = "1";
			}
 

			if (isset($this->request->post['shipping_methods'])) {
				$this->data['shipping_methods'] = $this->request->post['shipping_methods']; 			
			} else {
				$this->data['shipping_methods'] = '1';
			}

			$this->load->model('account/address');
			$this->data['bank'] = $this->model_account_address->getbank();
			$this->data['action'] = $this->url->link('checkout/cart');   
						
			$this->load->model('account/order');
			$this->data['pending_order'] = $this->model_account_order->getPendingOrder();

			$this->load->model('tool/image');
      		$this->data['products'] = array();


			//GET ALL Product Details (PreOrder + AVALIABLE)
      		foreach ($products as $product) {
				if( !count($product['option'])){
					$this->cart->remove($product['key']);
					$this->data['remover'] = '1'; 
					$this->redirect($this->url->link('checkout/cart'));  		
				}

				$product_total = 0;
				foreach ($products as $product_2) 
					if ($product_2['product_id'] == $product['product_id']) $product_total += $product_2['quantity'];
			
				if ($product['minimum'] > $product_total) $this->data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
					

				if ($product['image']) { // 2:3  100:150
					foreach($product['option'] as $p) if($p['option_id'] == 2) $img = $this->model_account_order->getImagefromOption($p['product_option_value_id']);
					$image = (strlen($img) > 1) ? $this->model_tool_image->resize($img, 100, 150) : $this->model_tool_image->resize($product['image'], 100, 150);
				} else {
					$image = '';
				}
			
				$option_data = array();
        		foreach ($product['option'] as $option) {
					$value = $option['option_value'];	
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
        		}
				

				$price_ = ($product['price'] == 0) ? $this->model_account_order->getProductPrice($product['product_id']) : $product['price'];
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($price_, $product['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$total = $this->currency->format($this->tax->calculate($price_, $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
				} else {
					$total = false;
				}
				$preorder = $this->model_catalog_product->isProductPreOrder($product['product_id'] );
				foreach($product['option'] as $p){
					if($p['option_id'] == 2)  $ppc = $p['option_value_id'];
					if($p['option_id'] == 11) $pps = $p['option_value_id'];
				}
				$maxQ = $this->model_account_order->getProductMaxQuantity($product['product_id'] , $ppc , $pps);

        		$this->data['products'][] = array(
          			'key'      => $product['key'],
          			'thumb'    => $image,
					'preorder' => $preorder,
					'id'	   => $product['product_id'],
					'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
					'maxq'	   => $maxQ,
          			'quantity' => $product['quantity'],
          			'stock'    => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'   => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'    => $price,
					'total'    => $total,
					'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'remove'   => $this->url->link('checkout/cart', 'remove=' . $product['key'])
				);
      		}
			
			// Gift Voucher
			$this->data['vouchers'] = array();
			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$this->data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount']),
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)   
					);
				}
			}


			$this->data['next'] = (isset($this->request->post['next'])) ? $this->request->post['next'] : ''; 

			$this->data['coupon_status'] = $this->config->get('coupon_status');
			if (isset($this->request->post['coupon'])) {
				$this->data['coupon'] = $this->request->post['coupon'];			
			} elseif (isset($this->session->data['coupon'])) {
				$this->data['coupon'] = $this->session->data['coupon'];
			} else {
				$this->data['coupon'] = '';
			}
			
			$this->data['voucher_status'] = $this->config->get('voucher_status');
			if (isset($this->request->post['voucher'])) {
				$this->data['voucher'] = $this->request->post['voucher'];				
			} elseif (isset($this->session->data['voucher'])) {
				$this->data['voucher'] = $this->session->data['voucher'];
			} else {
				$this->data['voucher'] = '';
			}
			
			$this->data['reward_status'] = ($points && $points_total && $this->config->get('reward_status'));
			if (isset($this->request->post['reward'])) {
				$this->data['reward'] = $this->request->post['reward'];				
			} elseif (isset($this->session->data['reward'])) {
				$this->data['reward'] = $this->session->data['reward'];
			} else {
				$this->data['reward'] = '';
			}

			$this->data['shipping_status'] = $this->config->get('shipping_status') && $this->config->get('shipping_estimator') && $this->cart->hasShipping();	

			if (isset($this->request->post['country_id'])) {
				$this->data['country_id'] = $this->request->post['country_id'];				
			} elseif (isset($this->session->data['shipping_country_id'])) {
				$this->data['country_id'] = $this->session->data['shipping_country_id'];			  	
			} else {
				$this->data['country_id'] = $this->config->get('config_country_id');
			}
				
			$this->load->model('localisation/country');
			$this->data['countries'] = $this->model_localisation_country->getCountries();
						
			if (isset($this->request->post['zone_id'])) {
				$this->data['zone_id'] = $this->request->post['zone_id'];				
			} elseif (isset($this->session->data['shipping_zone_id'])) {
				$this->data['zone_id'] = $this->session->data['shipping_zone_id'];			
			} else {
				$this->data['zone_id'] = '';
			}
			
			if (isset($this->request->post['postcode'])) {
				$this->data['postcode'] = $this->request->post['postcode'];				
			} elseif (isset($this->session->data['shipping_postcode'])) {
				$this->data['postcode'] = $this->session->data['shipping_postcode'];					
			} else {
				$this->data['postcode'] = '';
			}

						
			// Totals
			$this->load->model('setting/extension');
			$total_data = array();					
			$total = 0;
			$taxes = $this->cart->getTaxes();

			// Display prices
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$sort_order = array(); 
				
				$results = $this->model_setting_extension->getExtensions('total');
				
				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}
				
				array_multisort($sort_order, SORT_ASC, $results);
				
				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('total/' . $result['code']);
			
						$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
					}
					
					$sort_order = array(); 
				  
					foreach ($total_data as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}
		
					array_multisort($sort_order, SORT_ASC, $total_data);	
				}
			}
//print_r( $total_data);

			
			/*
			*  LOAD POINTS & CREDITS
			*/
			$this->load->model('checkout/pointcredit');
			$this->load->model('account/customer');
			$this->load->model('catalog/product');
			$customer = $this->model_account_customer->geThistCustomer();

			# Check if global discount enabled
			//if($global_discount > 0) $user_percent = 0;
			$ava_pre = $this->model_checkout_pointcredit->getAva_PreProduct();
			$ava_total = $ava_pre[1];
			$avaorder_a = $ava_pre[3];
			//print_r( $total_data );echo "<br><br>";
			//print_r( $ava_total );  //Price
			//print_r( $avaorder_a ); //Totals
			foreach($total_data as $key => $val){
				if($val['code']=="sub_total") $total_data[$key]['value'] = $ava_total;
			}
 
			$tmp			= $this->model_catalog_product->getNewProductDiscount($avaorder_a , $array=1);
			$newsubtotal	= $tmp[1];
			$aa				= $tmp[0];
			$s_subtotal		= 0;
			$product		= $this->cart->getProducts() ;
			foreach($product as $key => $val){
				foreach($tmp[0] as $k => $v){
					if( $v['id'] == $val['product_id'] ) $s_subtotal = $s_subtotal + $val['total'];
				}
			}
			
			$s_subtotal		= ($s_subtotal == 0)?$newsubtotal:$s_subtotal;

			$LevelDiscountfromPoint = 0;
			$this->data['vip'] = 0;
			 
			if(isset($customer['vip']))
				if($customer['vip']){
					$LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromVIP($customer ,  $total_data , $global_discount=0 , $s_subtotal );
					$this->data['vip'] = 1;
					//print_r($LevelDiscountfromPoint);
				}else{
					$LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromPoint($customer , $this->model_account_customer->getCustomerLevel() , $total_data , $global_discount=0  , $s_subtotal);
				}
			$this->data['user_percent']		= $LevelDiscountfromPoint[0];
			$this->data['level_discount']	= $LevelDiscountfromPoint[2];
			$level_discount_array			= $LevelDiscountfromPoint[1];    
			$total_array = array_pop($total_data);
			if(count($avaorder_a) > 0)$total_array = $LevelDiscountfromPoint[3];
			array_push($total_data, $level_discount_array);

			//print_r($total_data);echo "<br><br>";
			//print_r($total_array);echo "<br><br>";
			//print_r($LevelDiscountfromPoint[3]);echo "<br><br>";
			//echo $this->data['user_percent']." ".$this->data['level_discount'];

			/*
			*  CREDIT DISCOUNT
			*/
			//print_r($total_data);echo "<br><br>";
			if(isset($customer['credit'])) $this->data['credit'] = $customer['credit'];
			else $this->data['credit'] = 0;	
			$CreditDiscount = $this->model_checkout_pointcredit->getCreditDiscount($this->data['credit'], ($total_array['value'] )  , $total_array);
			$this->data['credit_discount'] = $CreditDiscount[0];
			if($CreditDiscount[1]){
				array_push($total_data, $CreditDiscount[3]);
				array_push($total_data, $CreditDiscount[2]);
			}else{
				array_push($total_data, $total_array);
			}
			//print_r($total_data);

			$b = "฿";
			if((count($products) == $pre_count)||(count($products) == $ava_count) ){
				//Only 1 type
				//Vender
				if (isset($this->request->post['vender'])) {
					$this->data['vender'] = $this->request->post['vender'];
					$this->data['p'] = "100 ".$b;
				}else{
					$this->data['vender'] = '';
					$this->data['p'] = "0 ".$b;
				}

				$this->data['totals'] = $total_data;
			}else{
				/*
					GET TOTALs
					- avaliable
					- preorder
				*/

				//Vender
				
				if (isset($this->request->post['vender'])) {
					$this->data['vender'] = $this->request->post['vender'];
					$this->data['p'] = "200 ".$b;
				}else{
					$this->data['vender'] = '';
					$this->data['p'] = "0 ".$b;
				}

				$this->data['totals'] = $total_data;
			} 
			

			

			$this->data['continue'] = $this->url->link('common/home');
			$this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/cart.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/checkout/cart.tpl';
			} else {
				$this->template = 'default/template/checkout/cart.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_bottom',
				'common/content_top',
				'common/footer',
				'common/header'	
			);
						
			$this->response->setOutput($this->render());					
    	} else {
      		$this->data['heading_title'] = $this->language->get('heading_title');

      		$this->data['text_error'] = $this->language->get('text_empty');

      		$this->data['button_continue'] = $this->language->get('button_continue');
			
      		$this->data['continue'] = $this->url->link('common/home');

			unset($this->session->data['success']);

			$this->redirect($this->url->link('common/home'));

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'	
			);
					
			$this->response->setOutput($this->render());			
    	}
  	}
	
	protected function validateCoupon() {
		$this->load->model('checkout/coupon');

		if(strlen($this->request->post['coupon']) > 1 ){
			$coupon_info = $this->model_checkout_coupon->getCoupon($this->request->post['coupon']);			
						$products = $this->cart->getProducts();
						//print_r($products); echo "<br><br>";
			if (!$coupon_info) {			
				$this->error['warning'] = $this->language->get('error_coupon');
			}

			$this->data['empty_coupon'] = "0";
		}else{
			$this->data['empty_coupon'] = "1";
			return false;
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}		
	}
	
	protected function validateVoucher() {
		$this->load->model('checkout/voucher');
				
		$voucher_info = $this->model_checkout_voucher->getVoucher($this->request->post['voucher']);			
		
		if (!$voucher_info) {			
			$this->error['warning'] = $this->language->get('error_voucher');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}		
	}
	
	protected function validateReward() {
		$points = $this->customer->getRewardPoints();
		
		$points_total = 0;
		
		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}	
				
		if (empty($this->request->post['reward'])) {
			$this->error['warning'] = $this->language->get('error_reward');
		}
	
		if ($this->request->post['reward'] > $points) {
			$this->error['warning'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
		}
		
		if ($this->request->post['reward'] > $points_total) {
			$this->error['warning'] = sprintf($this->language->get('error_maximum'), $points_total);
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}		
	}
	
	protected function validateShipping() {
		if (!empty($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);
					
			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {			
				$this->error['warning'] = $this->language->get('error_shipping');
			}
		} else {
			$this->error['warning'] = $this->language->get('error_shipping');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}		
	}
								
	public function add() {
		$this->language->load('checkout/cart');
		
		$json = array();
		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
						
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		if ($product_info) {			
			if (isset($this->request->post['quantity'])) {
				$quantity = $this->request->post['quantity'];
			} else {
				$quantity = 1;
			}
														
			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();	
			}
			
			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
			
			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}
			
			if (!$json) {
				$this->cart->add($this->request->post['product_id'], $quantity, $option);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));
				
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
				
				// Totals
				$this->load->model('setting/extension');
				
				$total_data = array();					
				$total = 0;
				$taxes = $this->cart->getTaxes();
				
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$sort_order = array(); 
					
					$results = $this->model_setting_extension->getExtensions('total');
					
					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}
					
					array_multisort($sort_order, SORT_ASC, $results);
					
					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('total/' . $result['code']);
				
							$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
						}
						
						$sort_order = array(); 
					  
						foreach ($total_data as $key => $value) {
							$sort_order[$key] = $value['sort_order'];
						}
			
						array_multisort($sort_order, SORT_ASC, $total_data);	
						
					}
				}
				
				$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total));
			} else {
				//$this->redirect( $this->url->link('product/product', 'product_id=' . $this->request->post['product_id'])   );
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}
		//$this->redirect($this->url->link('checkout/cart'));
		$this->response->setOutput(json_encode($json));		
	}
	
	public function quote() {
		$this->language->load('checkout/cart');
		
		$json = array();	
		
		if (!$this->cart->hasProducts()) {
			$json['error']['warning'] = $this->language->get('error_product');				
		}				

		if (!$this->cart->hasShipping()) {
			$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));				
		}				
		
		if ($this->request->post['country_id'] == '') {
			$json['error']['country'] = $this->language->get('error_country');
		}
		
		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
			$json['error']['zone'] = $this->language->get('error_zone');
		}
			
		$this->load->model('localisation/country');
		
		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
		
		if ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 10)) {
			$json['error']['postcode'] = $this->language->get('error_postcode');
		}
						
		if (!$json) {		
			$this->tax->setShippingAddress($this->request->post['country_id'], $this->request->post['zone_id']);
		
			// Default Shipping Address
			$this->session->data['shipping_country_id'] = $this->request->post['country_id'];
			$this->session->data['shipping_zone_id'] = $this->request->post['zone_id'];
			$this->session->data['shipping_postcode'] = $this->request->post['postcode'];
		
			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}
			
			$this->load->model('localisation/zone');
		
			$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);
			
			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}	
		 
			$address_data = array(
				'firstname'      => '',
				'lastname'       => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city'           => '',
				'zone_id'        => $this->request->post['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $this->request->post['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
		
			$quote_data = array();
			
			$this->load->model('setting/extension');
			
			$results = $this->model_setting_extension->getExtensions('shipping');
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);
					
					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($address_data); 
		
					if ($quote) {
						$quote_data[$result['code']] = array( 
							'title'      => $quote['title'],
							'quote'      => $quote['quote'], 
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}
	
			$sort_order = array();
		  
			foreach ($quote_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $quote_data);
			
			$this->session->data['shipping_methods'] = $quote_data;
			
			if ($this->session->data['shipping_methods']) {
				$json['shipping_method'] = $this->session->data['shipping_methods']; 
			} else {
				$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			}				
		}	
		
		$this->response->setOutput(json_encode($json));						
	}
	
	public function country() {
		$json = array();
		
		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);
		
		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']		
			);
		}
		
		$this->response->setOutput(json_encode($json));
	}
}
?>
