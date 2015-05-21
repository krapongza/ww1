<?php  
class ControllerCheckoutCheckout extends Controller { 
	public function index() {

//$this->data['error']['accept_tos'] = "uncheck";
//print_r(isset($this->data['error']));
//echo $this->config->get('customer_referral_credit_type')." ".$this->config->get('customer_referral_credit');
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
	  		$this->redirect($this->url->link('checkout/cart'));
    	}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('checkout/cart'));
			}				
		}

		if( $this->session->data['vender'] == 1 ){
			$this->data['vender'] = '1';
		}else{
			$this->data['vender'] = '0';
		}
		

		
		$this->language->load('checkout/checkout');
		
		$this->document->setTitle($this->language->get('heading_title')); 
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
					
		$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_cart'),
			'href'      => $this->url->link('checkout/cart'),
        	'separator' => $this->language->get('text_separator')
      	);
		
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
					
	    $this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_checkout_option'] = $this->language->get('text_checkout_option');
		$this->data['text_checkout_account'] = $this->language->get('text_checkout_account');
		$this->data['text_checkout_payment_address'] = $this->language->get('text_checkout_payment_address');
		$this->data['text_checkout_shipping_address'] = $this->language->get('text_checkout_shipping_address');
		$this->data['text_checkout_shipping_method'] = $this->language->get('text_checkout_shipping_method');
		$this->data['text_checkout_payment_method'] = $this->language->get('text_checkout_payment_method');		
		$this->data['text_checkout_confirm'] = $this->language->get('text_checkout_confirm');
		$this->data['text_modify'] = $this->language->get('text_modify');
		
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();	



		$this->data['text_address_existing'] = $this->language->get('text_address_existing');
		$this->data['text_address_new'] = $this->language->get('text_address_new');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');

		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_company_id'] = $this->language->get('entry_company_id');
		$this->data['entry_tax_id'] = $this->language->get('entry_tax_id');			
		$this->data['entry_address_1'] = $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = $this->language->get('entry_address_2');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_country'] = $this->language->get('entry_country');
		$this->data['entry_zone'] = $this->language->get('entry_zone');

		$this->data['button_continue'] = $this->language->get('button_continue');

		if (isset($this->session->data['payment_address_id'])) {
			$this->data['address_id'] = $this->session->data['payment_address_id'];
		} else {
			$this->data['address_id'] = $this->customer->getAddressId();
		}
		
		$this->data['addresses'] = array();
		
		$this->load->model('account/address');
		/*if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('checkout/cart')); 
		}*/

		$this->load->model('account/order');
		if( $this->model_account_order->getPendingOrder() > 0 ){
			$this->redirect($this->url->link('account/order')); 
		}
 

		$this->data['addresses'] = $this->model_account_address->getAddresses();
		
		$this->load->model('account/customer_group');
		
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($this->customer->getCustomerGroupId());
		
		if ($customer_group_info) {
			$this->data['company_id_display'] = $customer_group_info['company_id_display'];
		} else {
			$this->data['company_id_display'] = '';
		}
		
		if ($customer_group_info) {
			$this->data['company_id_required'] = $customer_group_info['company_id_required'];
		} else {
			$this->data['company_id_required'] = '';
		}
				
		if ($customer_group_info) {
			$this->data['tax_id_display'] = $customer_group_info['tax_id_display'];
		} else {
			$this->data['tax_id_display'] = '';
		}
		
		if ($customer_group_info) {
			$this->data['tax_id_required'] = $customer_group_info['tax_id_required'];
		} else {
			$this->data['tax_id_required'] = '';
		}
										
		if (isset($this->session->data['payment_country_id'])) {
			$this->data['country_id'] = $this->session->data['payment_country_id'];		
		} else {
			$this->data['country_id'] = $this->config->get('config_country_id');
		}
				
		if (isset($this->session->data['payment_zone_id'])) {
			$this->data['zone_id'] = $this->session->data['payment_zone_id'];		
		} else {
			$this->data['zone_id'] = '';
		}
		
		$this->load->model('localisation/country');
		
		$this->data['countries'] = $this->model_localisation_country->getCountries();


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/checkout.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/checkout/checkout.tpl';
		} else {
			$this->template = 'default/template/checkout/checkout.tpl';
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


	public function validate() {
		$this->language->load('checkout/checkout');
		$a_error = false;
		$json = array();

		// Validate if customer is logged in.
		if (!$this->customer->isLogged()) {
			$this->redirect( $this->url->link('account/login') );
		}
		
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->redirect( $this->url->link('checkout/cart') );
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$this->redirect( $this->url->link('checkout/cart') );
				break;
			}				
		}
	
		if( !isset($this->request->post['accept_tos']) ){
			$this->data['error']['accept_tos'] = "uncheck";
		}



		// Customer Address Management //Add or Get
		// from payment_address == NEW... other people buy and send by them name........
		if (isset($this->request->post['payment_address']) && $this->request->post['payment_address'] == 'new'  && $this->request->post['address_id'] == 0) {
		
			if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
				$this->data['error']['firstname'] = $this->language->get('error_firstname');
			}else{
				$this->data['data_firstname'] = $this->request->post['firstname'];
			}
			

			if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
				$this->data['error']['lastname'] = $this->language->get('error_lastname');
			}else{
				$this->data['data_lastname'] = $this->request->post['lastname'];
			}
				
			if ((utf8_strlen($this->request->post['address_1']) < 3) || (utf8_strlen($this->request->post['address_1']) > 128)) {
				$this->data['error']['address_1'] = $this->language->get('error_address_1');
			}else{
				$this->data['data_address_1'] = $this->request->post['address_1'];
			}
	
			if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32)) {
				$this->data['error']['city'] = $this->language->get('error_city');
			}else{
				$this->data['data_city'] = $this->request->post['city'];
			}

			// Customer Group
			$this->load->model('account/customer_group');
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($this->customer->getCustomerGroupId());
				
			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
			if ($country_info) {
				if ($country_info['postcode_required'] && (utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 10)) {
					$this->data['error']['postcode'] = $this->language->get('error_postcode');
				}else{
					$this->data['data_postcode'] = $this->request->post['postcode'];
				}			
			}
			
			if ($this->request->post['country_id'] == '') {
				$this->data['error']['country'] = $this->language->get('error_country');
			}else{
				$this->data['data_country'] = $this->request->post['country'];
			}	
			
			if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
				$this->data['error']['zone'] = $this->language->get('error_zone');
			}else{
				$this->data['data_zone'] = $this->request->post['zone'];
			}	

			//Send From ==> other people buy and send by them name........
			
			
			// Default Payment Address
			$this->load->model('account/address');
			
			//$this->session->data['payment_address_id'] = $this->model_account_address->addAddress($this->request->post);
			if( isset($this->data['error'])  ){
				
			}else{
				$this->session->data['payment_address_id'] = $this->model_account_address->addAddressNew($this->request->post);
			}
			$this->session->data['payment_country_id'] = $this->request->post['country_id'];
			$this->session->data['payment_zone_id'] = $this->request->post['zone_id'];
			$this->session->data['shipping_postcode'] = $this->request->post['postcode']; //New
													
			unset($this->session->data['payment_method']);	
			unset($this->session->data['payment_methods']);
		}else{
			$this->load->model('account/address');

			$address_info = $this->model_account_address->getAddress($this->request->post['address_id']);
								
			if ($address_info) {				
				$this->load->model('account/customer_group');
				$customer_group_info = $this->model_account_customer_group->getCustomerGroup($this->customer->getCustomerGroupId());			
			}					

			$this->session->data['shipping_address_id'] = $this->request->post['address_id'];
			$this->session->data['payment_address_id'] = $this->request->post['address_id'];
			
			if ($address_info) {
				$this->session->data['payment_country_id'] = $address_info['country_id'];
				$this->session->data['payment_zone_id'] = $address_info['zone_id'];
				$this->session->data['shipping_postcode'] = $address_info['postcode'];
			}

			unset($this->session->data['payment_method']);	
			unset($this->session->data['payment_methods']);
		}

		$this->data['data_send_from'] = (!isset($this->request->post['send_from']) || $this->request->post['send_from'] <> '') ? $this->request->post['send_from'] : '';





			if( 1==2  ){
			//if( isset($this->data['error'])  ){

			}else{


		//Prepare for Payment.............

		$shipping_methods = $this->session->data['shipping_type'];
		//echo $shipping_methods."xxxxxxxxxxxx";
		if($this->session->data['opder_splitted']==1){

			$this->load->model('checkout/shipping');
			$this->load->model('catalog/product');
			$products = $this->cart->getProducts();
			$pre_count = 0;$ava_count = 0;
			$preorder_a = array();
			$active_a = array();
			foreach($products as $p2){
				$tmp = $this->model_catalog_product->isProductPreOrder($p2['product_id'] );
				if($tmp == 1){
					$pre_count =  $pre_count + 1;
					array_push($preorder_a , $p2['product_id']);
				}else{
					$ava_count =  $ava_count + 1;
					array_push($active_a , $p2['product_id']);
				}
			}
			
			/*  preorder / avaliable  */
			$pre_shipping = $this->model_catalog_product->getProductWeight( $this->model_checkout_shipping->calWeight($preorder_a, $products) , $shipping_methods);
			$pre_shipping_price = abs($pre_shipping)." ฿"  ;
			$pre_product = $preorder_a;
			$ava_shipping = $this->model_catalog_product->getProductWeight( $this->model_checkout_shipping->calWeight($active_a, $products) , $shipping_methods);
			$shipping_price = abs($ava_shipping)." ฿"  ;
			$ava_product = $active_a;
			$this->session->data['shipping_pre_method'] = Array ( 'title' => 'อัตราค่าจัดส่ง' , 'code' => 'pre_shipping' , 'cost' => $pre_shipping  , 'tax_class_id' => 9 , 'text' => $pre_shipping." ฿" );
			$this->session->data['shipping_ava_method'] = Array ( 'title' => 'อัตราค่าจัดส่ง' , 'code' => 'ava_shipping' , 'cost' => $ava_shipping  , 'tax_class_id' => 9 , 'text' => $ava_shipping." ฿" );

		}else{
			$this->load->model('catalog/product');
			$pp = $this->model_catalog_product->getProductWeight($this->cart->getWeight() , $shipping_methods);
			$this->session->data['shipping_method'] = Array ( 'title' => 'อัตราค่าจัดส่ง' , 'code' => 'flat.flat' , 'cost' => $pp  , 'tax_class_id' => 9 , 'text' => $pp." ฿" );
			$shipping_price = abs($pp)." ฿";
		}


		// Totals ================================
		$total_data = array();					
		$total = 0;
		$taxes = $this->cart->getTaxes();
		$this->load->model('setting/extension');
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
		}
		$sort_order = array(); 
		foreach ($total_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $total_data);
 
		


		$data = array();
		$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$data['store_id'] = $this->config->get('config_store_id');
		$data['store_name'] = $this->config->get('config_name');
		if ($data['store_id']) {
			$data['store_url'] = $this->config->get('config_url');		
		} else {
			$data['store_url'] = HTTP_SERVER;	
		}
				




		// CUSTOMER INFO
		if ($this->customer->isLogged()) {
			$data['customer_id'] = $this->customer->getId();
			$data['customer_group_id'] = $this->customer->getCustomerGroupId();
			$data['firstname'] = $this->customer->getFirstName();
			$data['lastname'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();
			$data['fax'] = $this->customer->getFax();
		
			$this->load->model('account/address');
			$shipping_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
		}
				
		$data['payment_firstname'] = $shipping_address['firstname'];
		$data['payment_lastname'] = $shipping_address['lastname'];	
		$data['payment_company'] = $shipping_address['company'];	
		$data['payment_company_id'] = '';//$payment_address['company_id'];	
		$data['payment_tax_id'] = '';//$payment_address['tax_id'];	
		$data['payment_address_1'] = $shipping_address['address_1'];
		$data['payment_address_2'] = $shipping_address['address_2'];
		$data['payment_city'] = $shipping_address['city'];
		$data['payment_postcode'] = $shipping_address['postcode'];
		$data['payment_zone'] = $shipping_address['zone'];
		$data['payment_zone_id'] = $shipping_address['zone_id'];
		$data['payment_country'] = $shipping_address['country'];
		$data['payment_country_id'] = $shipping_address['country_id'];
		$data['payment_address_format'] = $shipping_address['address_format'];

		$data['send_from'] = (isset($this->data['data_send_from'])) ? $this->data['data_send_from'] : '';

		$data['payment_method'] = (isset($this->session->data['payment_method']['title'])) ?  $this->session->data['payment_method']['title'] : '';

		$data['payment_code'] = (isset($this->session->data['payment_method']['code'])) ? $this->session->data['payment_method']['code'] : '';


		$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);	

		$data['shipping_firstname'] = $shipping_address['firstname'];
		$data['shipping_lastname'] = $shipping_address['lastname'];	
		$data['shipping_company'] = $shipping_address['company'];	
		$data['shipping_address_1'] = $shipping_address['address_1'];
		$data['shipping_address_2'] = $shipping_address['address_2'];
		$data['shipping_city'] = $shipping_address['city'];
		$data['shipping_postcode'] = $shipping_address['postcode'];
		$data['shipping_zone'] = $shipping_address['zone'];
		$data['shipping_zone_id'] = $shipping_address['zone_id'];
		$data['shipping_country'] = $shipping_address['country'];
		$data['shipping_country_id'] = $shipping_address['country_id'];
		$data['shipping_address_format'] = $shipping_address['address_format'];
		$data['shipping_code'] = "flat.flat";
		$data['shipping_method'] = $this->session->data['shipping_type']; //"Flat Shipping Rate";
		$data['payment_code'] = "cod";
		$data['payment_method'] = "Cash On Delivery" ;

		// Product Option
		$product_data = array();
		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				$value = $option['option_value'];	
				
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],								   
					'name'                    => $option['name'],
					'value'                   => $value,
					'type'                    => $option['type']
				);					
			}
 
			$product_data[] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			); 
		}


		// Gift Voucher
		$voucher_data = array();
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$voucher_data[] = array(
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],						
					'amount'           => $voucher['amount']
				);
			}
		}  
							
		$data['products'] = $product_data;
		$data['vouchers'] = $voucher_data;
		$data['totals'] = $total_data;
		$data['comment'] = ''; //$this->session->data['comment'];
		$data['total'] = $total;

		// affiliate
		if (isset($this->request->cookie['tracking'])) {
			$this->load->model('affiliate/affiliate');
			
			$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
			$subtotal = $this->cart->getSubTotal();
			
			if ($affiliate_info) {
				$data['affiliate_id'] = $affiliate_info['affiliate_id']; 
				$data['commission'] = ($subtotal / 100) * $affiliate_info['commission']; 
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}
		} else {
			$data['affiliate_id'] = 0;
			$data['commission'] = 0;
		}

		// Web Browser
		$data['language_id'] = $this->config->get('config_language_id');
		$data['currency_id'] = $this->currency->getId();
		$data['currency_code'] = $this->currency->getCode();
		$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];	
		} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];	
		} else {
			$data['forwarded_ip'] = '';
		}
		
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];	
		} else {
			$data['user_agent'] = '';
		}
		
		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];	
		} else {
			$data['accept_language'] = '';
		}




				//print_r($data);
				//245
				//if( isset($this->session->data['opder_splitted']) ){
				if($this->session->data['opder_splitted']==1){
					$pre_data =  $data;
					$ava_data =  $data;
					$data2['invoice_prefix'] = "aaaa";

						
					$this->load->model('account/order');	
					$this->load->model('catalog/product');
					$this->load->model('checkout/pointcredit');

					/*
					*	Setup Product to Avaliable Order & PreOrder
					*/
					$products = $this->cart->getProducts();
					$ava_pre = $this->model_checkout_pointcredit->getAva_PreProduct( );
					$preorder_a = $ava_pre[2];
					$active_a = $ava_pre[3];
					$pre_total =$ava_pre[0];
					$ava_total = $ava_pre[1];

//print_r($ava_data);echo "<br><br>";
					/*
					*	Setup Coupon for Avaliable Order & PreOrder
					*/
					//**********COUPON DISCOUNT preorder + ava show in ava order
					$coupon = 0;$global = 0;
					foreach($ava_data['totals'] as $key => $val){
						if($val['code'] == "coupon") $coupon = $val['value'];
						if($val['code'] == "pre_shipping")  unset($ava_data['totals'][$key]);
						if($val['code'] == "globaldiscount") $global = $val['value'];
					}
					//Remove coupon from preorders
					foreach($pre_data['totals'] as $key => $val){
						if($val['code'] == "coupon") unset($pre_data['totals'][$key]);
						if($val['code'] == "ava_shipping")  unset($pre_data['totals'][$key]);
					}


					/*
					*	Setup New Total Price for Avaliable Order & PreOrder
					*/
					$totals = $total_data;
					$ava_totals = $ava_total + $ava_shipping + $coupon + $global; //$total_data;
					$pre_totals = $pre_total + $pre_shipping ; //$total_data;




					//SET EACH TOTAL
					$pre_data['total'] = $pre_totals;
					$ava_data['total'] = $ava_totals;

					/*
					*	Re-Setup PRODUCT for Avaliable Order & PreOrder
					*/
					//RESETUP PRODUCT IN EACH CART
					$pre_data = $this->model_checkout_pointcredit->resetupProductonCart($preorder_a , $pre_data );
					$ava_data = $this->model_checkout_pointcredit->resetupProductonCart($active_a , $ava_data );
					$pre_data = $this->model_checkout_pointcredit->checkoutReCalTotal($pre_data , $pre_totals , $pre_total , $pre_shipping );
					$ava_data = $this->model_checkout_pointcredit->checkoutReCalTotal($ava_data , $ava_totals , $ava_total , $ava_shipping );

//print_r($data);echo "<br><br>";
//print_r($pre_total);echo "<br>";
//print_r($ava_totals);echo "<br>";
//print_r($pre_data);echo "<br><br>";
//print_r($ava_data);echo "<br><br>";
					
					$pre_totals = $pre_data['totals'];
					$ava_totals = $ava_data['totals'];

					foreach($pre_totals as $key => $val){
						if($val['code'] == 'globaldiscount') unset($pre_totals[$key]) ;
						if($val['code'] == 'vip') unset($pre_totals[$key]) ;
					}


					$tmp = $this->model_catalog_product->getNewProductDiscount($active_a , $array=1);
					$newsubtotal = $tmp[1];

					/*
					*  LOAD POINTS & CREDITS (Point discount only ava product [no preorder])
					*/
					$LevelDiscountfromPoint = 0;
					$this->load->model('account/customer');
					$customer = $this->model_account_customer->geThistCustomer();
					if($customer['vip']){
						$LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromVIP($customer ,  $ava_totals , $global_discount=0 , $newsubtotal );
					}else{
						$LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromPoint($customer , $this->model_account_customer->getCustomerLevel() , $ava_totals , $global_discount=0 , $newsubtotal );
					}
					//  $LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromPoint($customer , $this->model_account_customer->getCustomerLevel() , $ava_totals , $global_discount=0 );
					$level_discount_array	= $LevelDiscountfromPoint[1];    
					$total_array			= array_pop($ava_totals);
					if(count($ava_totals) > 0)$total_array = $LevelDiscountfromPoint[3];
					$total_array			= $LevelDiscountfromPoint[3];
					array_push($ava_totals, $level_discount_array);

					/*
					*  CREDIT DISCOUNT (Credit discount both ava product and preorder)
					*/
					$ava_credit = 0;$pre_credit = 0; $new_total_array['value']=0;
					$CreditAvaDiscount = $this->model_checkout_pointcredit->getCreditDiscount($customer['credit'], ($total_array['value'] )  , $total_array);
					if($CreditAvaDiscount[1]){
						array_push($ava_totals, $CreditAvaDiscount[3]);
						array_push($ava_totals, $CreditAvaDiscount[2]);
						$new_total_array = $total_array; // for cal new credit
						$total_array = $CreditAvaDiscount[2];
						$ava_credit = $CreditAvaDiscount[0];
					}else{
						array_push($ava_totals, $total_array);
					}

					$newCredit = $this->model_checkout_pointcredit->calNewCredit( $customer['credit'], $new_total_array['value'] );
					$total_array_pre			= array_pop($pre_totals);
					$CreditPreDiscount = $this->model_checkout_pointcredit->getCreditDiscount($newCredit, ($total_array_pre['value'] )  , $total_array_pre);
					if($CreditPreDiscount[1]){
						array_push($pre_totals, $CreditPreDiscount[3]);
						array_push($pre_totals, $CreditPreDiscount[2]);
						$total_array_pre = $CreditPreDiscount[2];
						$pre_credit = $CreditPreDiscount[0];
					}else{
						array_push($pre_totals, $total_array_pre);
					}
					$total_credit_used = $ava_credit + $pre_credit;

 
					/*
					*  RE-SET TOTALS
					*/
					$pre_data = $this->model_checkout_pointcredit->reCalTotals($pre_data, $pre_totals , $total_array_pre);
					$ava_data = $this->model_checkout_pointcredit->reCalTotals($ava_data, $ava_totals , $total_array);

 

//print_r($ava_totals);echo "<br><br>";
//print_r($pre_totals);echo "<br><br>";
//print_r($ava_data);echo "<br><br>";
//print_r($pre_data);echo "<br><br>";
					

					/*
					*  Stock Status
					*/
					foreach($ava_totals as $key => $val){
						if($val['code'] == 'total')$ava_result = $val['value'] ;
					}
					foreach($pre_totals as $key => $val){
						if($val['code'] == 'total')$pre_result = $val['value'] ;
					}
					if($ava_result > 0)$ava_stock_status = 1;	//wait
					else $ava_stock_status = 3;					//notify

					if($pre_result > 0)$pre_stock_status = 1;	//wait
					else $pre_stock_status = 3;					//notify


					$pre_data['customer_referral_id']=0;
					$pre_data['customer_referral_credit']=0;
					$pre_data['customer_referral_points']=0;
					$ava_data['customer_referral_id']=0;
					$ava_data['customer_referral_credit']=0;
					$ava_data['customer_referral_points']=0;




					  // Start Customer Referrals
					  if ($this->config->get('customer_referral_status')) {
						$referral_customer_info = array();
						$customer_referral_info = array();

						if ($this->customer->isLogged()) {
						  $referral_customer_info = $this->model_account_customer->getReferralCustomer($this->customer->getId());

						  if ($referral_customer_info) {
							if ($this->config->get('customer_referral_credit_all_orders')) {
							  $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

							  $this->load->model('checkout/customer_referral');
							  $customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferral($customer_info['customer_referral_id']);
							}
						  }
						}

						if (!$referral_customer_info && isset($this->request->cookie['referral'])) {
						  $this->load->model('checkout/customer_referral');

						  $customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferralByCode($this->request->cookie['referral']);

						  if (!$customer_referral_info['single_use']) {
							$customer_referral_id = $this->model_checkout_customer_referral->addCustomerReferral(array(
							  'customer_id' => $customer_referral_info['customer_id'],
							  'email'       => $data['email']
							));

							$customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferral($customer_referral_id);
						  }
						}

						if ($customer_referral_info) {
						  $pre_data['customer_referral_id']     = $customer_referral_info['customer_referral_id'];
						  $pre_data['customer_referral_points'] = $this->config->get('customer_referral_points');
						  $ava_data['customer_referral_id']     = $customer_referral_info['customer_referral_id'];
						  $ava_data['customer_referral_points'] = $this->config->get('customer_referral_points');

						  if ($this->config->get('customer_referral_credit')) {
							if ($this->config->get('customer_referral_credit_type') == 'P') {
							  //$data['customer_referral_credit'] = ($this->cart->getSubTotal() / 100) * $this->config->get('customer_referral_credit');
							  $pre_data['customer_referral_credit'] = ($this->cart->getSubTotal() / 100) * $this->config->get('customer_referral_credit');
							  $ava_data['customer_referral_credit'] = ($this->cart->getSubTotal() / 100) * $this->config->get('customer_referral_credit');

							} else {
							  $pre_data['customer_referral_credit'] = $this->config->get('customer_referral_credit');
							  $ava_data['customer_referral_credit'] = $this->config->get('customer_referral_credit');
							}
						  } else {
							$data['customer_referral_credit'] = 0;
							$pre_data['customer_referral_credit']=0;
							$ava_data['customer_referral_credit']=0;
						  }
						} else {
							$pre_data['customer_referral_id']=0;
							$pre_data['customer_referral_credit']=0;
							$pre_data['customer_referral_points']=0;
							$ava_data['customer_referral_id']=0;
							$ava_data['customer_referral_credit']=0;
							$ava_data['customer_referral_points']=0;
						}
					  } else {

						$pre_data['customer_referral_id']=0;
						$pre_data['customer_referral_credit']=0;
						$pre_data['customer_referral_points']=0;
						$ava_data['customer_referral_id']=0;
						$ava_data['customer_referral_credit']=0;
						$ava_data['customer_referral_points']=0;
					  }
					  // End Customer Referrals



require 'phpmail/PHPMailerAutoload.php';
					// Save
					$this->load->model('checkout/order');
					$preid = $this->model_checkout_order->addOrder($pre_data);
					$this->model_checkout_order->confirm($preid, $pre_stock_status);

					$avaid = $this->model_checkout_order->addOrder($ava_data);
					$this->model_checkout_order->confirm($avaid, $ava_stock_status);
					$old_credit = $this->model_checkout_order->updateCredit($total_credit_used);
					//Order_id, total_credit_used, old_credit, used_or_add[1=used,0=add] , admin_name ='' , status[1=active,0=cancel]
					$this->model_checkout_pointcredit->historyCredit($preid, $pre_credit, $old_credit , 1, '' , 1 );
					$this->model_checkout_pointcredit->historyCredit($avaid, $ava_credit, ($old_credit - $pre_credit) , 1, '' , 1 );

				}else{

					/*
					*  LOAD POINTS & CREDITS
					*/
					$this->load->model('checkout/pointcredit');
					$this->load->model('account/customer');
					$this->load->model('account/order');	
					$this->load->model('catalog/product');
					$customer = $this->model_account_customer->geThistCustomer();
					$ava_pre = $this->model_checkout_pointcredit->getAva_PreProduct( );
					$ava_total = $ava_pre[1];
					$avaorder_a = $ava_pre[3];
					foreach($total_data as $key => $val){
						if($val['code']=="sub_total") $total_data[$key]['value'] = $ava_total;
					}

					if(!$ava_total)
						foreach($total_data as $key => $val){
							if($val['code'] == 'globaldiscount') unset($total_data[$key]) ;
							if($val['code'] == 'vip') unset($total_data[$key]) ;
						}

					$tmp = $this->model_catalog_product->getNewProductDiscount($avaorder_a , $array=1);
					$newsubtotal = $tmp[1];


					# Check if global discount enabled
					//if($global_discount > 0) $user_percent = 0;
					$LevelDiscountfromPoint = 0;
					$customer = $this->model_account_customer->geThistCustomer();
					if($customer['vip']){
						$LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromVIP($customer ,  $total_data , $global_discount=0 , $newsubtotal);
					}else{
						$LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromPoint($customer , $this->model_account_customer->getCustomerLevel() , $total_data , $global_discount=0 , $newsubtotal );
					}

					//  $LevelDiscountfromPoint = $this->model_checkout_pointcredit->getLevelDiscountfromPoint($customer , $this->model_account_customer->getCustomerLevel() , $total_data , $global_discount=0 );
					$level_discount_array			= $LevelDiscountfromPoint[1];    
					$total_array = array_pop($total_data);
					if(count($ava_pre[3]) > 0)$total_array = $LevelDiscountfromPoint[3];
					//$total_array = $LevelDiscountfromPoint[3];
					array_push($total_data, $level_discount_array);

					/*
					*  CREDIT DISCOUNT
					*/
					$total_credit_used = 0;
					$CreditDiscount = $this->model_checkout_pointcredit->getCreditDiscount($customer['credit'], ($total_array['value'] )  , $total_array);
					$total_array = $CreditDiscount[2];
					if($CreditDiscount[1]){
						array_push($total_data, $CreditDiscount[3]);
						array_push($total_data, $CreditDiscount[2]);
						$total_credit_used = $CreditDiscount[0];
					}else{
						array_push($total_data, $total_array);
					}

					/*
					*  RE-SET TOTALS
					*/
					unset($data['totals']);
					$data['totals'] = $total_data;
					$data['total'] = $total_array['value'];

					/*
					*  Stock Status
					*/
					foreach($total_data as $key => $val){
						if($val['code'] == 'total')$tmp_result = $val['value'] ;
					}
					if($tmp_result > 0)$stock_status = 1;	//wait
					else $stock_status = 3;					//notify


					/*
					*  Save
					*/
					$data['customer_referral_id']=0;
					$data['customer_referral_credit']=0;
					$data['customer_referral_points']=0;




					  // Start Customer Referrals
					  if ($this->config->get('customer_referral_status')) {
						$referral_customer_info = array();
						$customer_referral_info = array();

						if ($this->customer->isLogged()) {
						  $referral_customer_info = $this->model_account_customer->getReferralCustomer($this->customer->getId());

						  if ($referral_customer_info) {
							if ($this->config->get('customer_referral_credit_all_orders')) {
							  $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

							  $this->load->model('checkout/customer_referral');
							  $customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferral($customer_info['customer_referral_id']);
							}
						  }
						}

						if (!$referral_customer_info && isset($this->request->cookie['referral'])) {
						  $this->load->model('checkout/customer_referral');

						  $customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferralByCode($this->request->cookie['referral']);

						  if (!$customer_referral_info['single_use']) {
							$customer_referral_id = $this->model_checkout_customer_referral->addCustomerReferral(array(
							  'customer_id' => $customer_referral_info['customer_id'],
							  'email'       => $data['email']
							));

							$customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferral($customer_referral_id);
						  }
						}

						if ($customer_referral_info) {
						  $data['customer_referral_id']     = $customer_referral_info['customer_referral_id'];
						  $data['customer_referral_points'] = $this->config->get('customer_referral_points');

						  if ($this->config->get('customer_referral_credit')) {
							if ($this->config->get('customer_referral_credit_type') == 'P') {
							  $data['customer_referral_credit'] = ($this->cart->getSubTotal() / 100) * $this->config->get('customer_referral_credit');
							} else {
							  $data['customer_referral_credit'] = $this->config->get('customer_referral_credit');
							}
						  } else {
							$data['customer_referral_credit'] = 0;
						  }
						} else {
						  $data['customer_referral_id']     = 0;
						  $data['customer_referral_credit'] = 0;
						  $data['customer_referral_points'] = 0;
						}
					  } else {
						$data['customer_referral_id']     = 0;
						$data['customer_referral_credit'] = 0;
						$data['customer_referral_points'] = 0;
					  }
					  // End Customer Referrals
//print_r($data);echo "<br><br>";



					$this->load->model('checkout/order');
 
 require 'phpmail/PHPMailerAutoload.php';

					$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);
					$this->model_checkout_order->confirm($this->session->data['order_id'], $stock_status);
					$old_credit = $this->model_checkout_order->updateCredit($total_credit_used);
					//Order_id, total_credit_used, old_credit, used_or_add 1=used , admin_name ='' , status[1=active,0=cancel]
					$this->model_checkout_pointcredit->historyCredit($this->session->data['order_id'], $total_credit_used, $old_credit , 1, '' , 1 );
				}
				//} 
			 
			

				$orderid = $this->session->data['order_id'];
				$this->cart->clear();

				unset($this->session->data['shipping_pre_method']);
				unset($this->session->data['shipping_ava_method']);
				unset($this->session->data['shipping_type']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
				unset($this->session->data['guest']);
				unset($this->session->data['comment']);
				unset($this->session->data['order_id']);	
				unset($this->session->data['coupon']);
				unset($this->session->data['reward']);
				unset($this->session->data['voucher']);
				unset($this->session->data['vouchers']);

				unset($this->session->data['opder_splitted']);

				if(isset($_POST['button-paysbuy'])){
					$this->session->data['order_id'] = $orderid;
					$this->redirect($this->url->link('simple_support/paysbuy'));
				}elseif(isset($_POST['button-paypal'])){
					$this->session->data['order_id'] = $orderid;
					$this->redirect($this->url->link('simple_support/paypal'));
				}else{
					$this->redirect($this->url->link('account/order'));
					//$this->redirect($this->url->link('checkout/checkout/validate'));
				}

			
			}

	}


}
?>