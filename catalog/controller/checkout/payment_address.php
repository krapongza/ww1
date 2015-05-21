<?php 
class ControllerCheckoutPaymentAddress extends Controller {
	public function index() {
		$this->language->load('checkout/checkout');

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
//print_r($this->session->data['payment_address_id']);
//print_r("<br>xxx");
		if (isset($this->session->data['payment_address_id'])) {
			$this->data['address_id'] = $this->session->data['payment_address_id'];
		} else {
			$this->data['address_id'] = $this->customer->getAddressId();
		}
		
		$this->data['addresses'] = array();
		
		$this->load->model('account/address');
		
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
	
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/payment_address.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/checkout/payment_address.tpl';
		} else {
			$this->template = 'default/template/checkout/payment_address.tpl';
		}
	
		$this->response->setOutput($this->render());			
  	}
	

	public function validate() {
		$this->language->load('checkout/checkout');
		$a_error = false;
		$json = array();
		// Validate if customer is logged in.
		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', '', 'SSL');
		}
		
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
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
				$json['redirect'] = $this->url->link('checkout/cart');
				
				break;
			}				
		}
				
		if (!$json) {
			if (isset($this->request->post['payment_address']) && $this->request->post['payment_address'] == 'existing') {
				$this->load->model('account/address');
				
				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->language->get('error_address');
					$a_error = true;
				//} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
				//	$json['error']['warning'] = $this->language->get('error_address');
				} else {
					// Default Payment Address
					$this->load->model('account/address');
	
					$address_info = $this->model_account_address->getAddress($this->request->post['address_id']);
										
					if ($address_info) {				
						$this->load->model('account/customer_group');
				
						$customer_group_info = $this->model_account_customer_group->getCustomerGroup($this->customer->getCustomerGroupId());			
					}					
				}
					
				if (!$json) {	
					$this->session->data['shipping_address_id'] = $this->request->post['address_id'];
					$this->session->data['payment_address_id'] = $this->request->post['address_id'];
					
					if ($address_info) {
						$this->session->data['payment_country_id'] = $address_info['country_id'];
						$this->session->data['payment_zone_id'] = $address_info['zone_id'];
						$this->session->data['shipping_postcode'] = $address_info['postcode'];
					} else {
						unset($this->session->data['payment_country_id']);	
						unset($this->session->data['payment_zone_id']);	
						unset($this->session->data['shipping_postcode']);
					}
										
					unset($this->session->data['payment_method']);	
					unset($this->session->data['payment_methods']);
				}
			} else {
				if($this->request->post['address_id'] == '0'){
					if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
						$json['error']['firstname'] = $this->language->get('error_firstname');
						$a_error = true;
					}
			
					if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
						$json['error']['lastname'] = $this->language->get('error_lastname');
						$a_error = true;
					}
						
					if ((utf8_strlen($this->request->post['address_1']) < 3) || (utf8_strlen($this->request->post['address_1']) > 128)) {
						$json['error']['address_1'] = $this->language->get('error_address_1');
						$a_error = true;
					}
			
					if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32)) {
						$json['error']['city'] = $this->language->get('error_city');
						$a_error = true;
					}
					
					// Customer Group
					$this->load->model('account/customer_group');
					$customer_group_info = $this->model_account_customer_group->getCustomerGroup($this->customer->getCustomerGroupId());
						
					$this->load->model('localisation/country');
					
					$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
					
					if ($country_info) {
						if ($country_info['postcode_required'] && (utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 10)) {
							$json['error']['postcode'] = $this->language->get('error_postcode');
							$a_error = true;
						}				
					}
					
					if ($this->request->post['country_id'] == '') {
						$json['error']['country'] = $this->language->get('error_country');
						$a_error = true;
					}
					
					if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
						$json['error']['zone'] = $this->language->get('error_zone');
						$a_error = true;
					}
					
					if (!$json) {
						// Default Payment Address
						$this->load->model('account/address');
						
						//$this->session->data['payment_address_id'] = $this->model_account_address->addAddress($this->request->post);
						$this->session->data['payment_address_id'] = $this->model_account_address->addAddressNew($this->request->post);
						$this->session->data['payment_country_id'] = $this->request->post['country_id'];
						$this->session->data['payment_zone_id'] = $this->request->post['zone_id'];
						$this->session->data['shipping_postcode'] = $this->request->post['postcode']; //New
																
						unset($this->session->data['payment_method']);	
						unset($this->session->data['payment_methods']);
					}
				}
			}
			

			/*
			////////////////////////////  SHIPPING  ///////////////////////////////////
			$quote_data = array();
			$this->load->model('setting/extension');
			$results = $this->model_setting_extension->getExtensions('shipping');
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);
					
					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address); 
		
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
			*/
			//$this->session->data['shipping_method'] = "flat.flat";
			$this->session->data['shipping_method'] = Array ( 'title' => 'Flat Shipping Rate' , 'code' => 'flat.flat' , 'cost' => '5.00' , 'tax_class_id' => 9 , 'text' => '$5.00' );

 
			// Totals
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

			
			// Payment Methods
			$method_data = array();
			
			$this->load->model('setting/extension');
			
			$results = $this->model_setting_extension->getExtensions('payment');
	
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('payment/' . $result['code']);
					
					$method = $this->{'model_payment_' . $result['code']}->getMethod($address_info, $total); 
					
					if ($method) {
						$method_data[$result['code']] = $method;
					}
				}
			}

			$sort_order = array(); 
		  
			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $method_data);			
			
			$this->session->data['payment_methods'] = $method_data;	
 

			$this->session->data['payment_method'] = Array ( 'code' => 'cod' , 'title' => 'Cash On Delivery' , 'sort_order' => 2 );
 
			if( $a_error  ){

			}else{
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

				if (isset($this->session->data['payment_method']['title'])) {
					$data['payment_method'] = $this->session->data['payment_method']['title'];
				} else {
					$data['payment_method'] = '';
				}
				
				if (isset($this->session->data['payment_method']['code'])) {
					$data['payment_code'] = $this->session->data['payment_method']['code'];
				} else {
					$data['payment_code'] = '';
				}

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

				
				$data['shipping_method'] = $this->session->data['shipping_method']['title'];
				$data['shipping_code'] = $this->session->data['shipping_method']['code'];


				// Product Option
				$product_data = array();
				foreach ($this->cart->getProducts() as $product) {
					$option_data = array();
		
					foreach ($product['option'] as $option) {
						if ($option['type'] != 'file') {
							$value = $option['option_value'];	
						} else {
							$value = $this->encryption->decrypt($option['option_value']);
						}	
						
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
				$data['comment'] = '';// $this->session->data['comment'];
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

				// Save
				$this->load->model('checkout/order');
				$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);


				$this->data['column_name'] = $this->language->get('column_name');
				$this->data['column_model'] = $this->language->get('column_model');
				$this->data['column_quantity'] = $this->language->get('column_quantity');
				$this->data['column_price'] = $this->language->get('column_price');
				$this->data['column_total'] = $this->language->get('column_total');
		
				$this->data['products'] = array();
		
				foreach ($this->cart->getProducts() as $product) {
					$option_data = array();
		
					foreach ($product['option'] as $option) {
						if ($option['type'] != 'file') {
							$value = $option['option_value'];	
						} else {
							$filename = $this->encryption->decrypt($option['option_value']);
							
							$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
						}
											
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
						);
					}  
		 
					$this->data['products'][] = array(
						'product_id' => $product['product_id'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
						'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']),
						'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
					); 
				} 
				
				// Gift Voucher
				$this->data['vouchers'] = array();
				
				if (!empty($this->session->data['vouchers'])) {
					foreach ($this->session->data['vouchers'] as $voucher) {
						$this->data['vouchers'][] = array(
							'description' => $voucher['description'],
							'amount'      => $this->currency->format($voucher['amount'])
						);
					}
				}  
							
				$this->data['totals'] = $total_data;


				//$this->session->data['shipping_method'] = Array ( "code" => "flat.flat" , "title" => "Flat Shipping Rate" , "cost" => "5.00" , "tax_class_id" => "9" , "text" => "$5.00" ) ;


				$this->session->data['payment_method'] = Array ( "code" => "cod" , "title" => "Cash On Delivery" , "sort_order" => "5" ) ;

				$this->data['payment'] = $this->getChild('payment/' . $this->session->data['payment_method']['code']);


				$this->load->model('checkout/order');
				
				$this->model_checkout_order->confirm($this->session->data['order_id'], '0');

				//$this->data['continue'] = $this->url->link('checkout/success');
				//$this->data['redirect'] = $this->url->link('checkout/success');
				$this->redirect($this->url->link('checkout/success'));
			}
			//END IF $json['error']


		}
		
		$this->response->setOutput(json_encode($json));
	}
}
?>