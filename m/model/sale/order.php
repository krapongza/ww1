<?php
class ModelSaleOrder extends Model {
	public function addOrder($data) {
		$this->load->model('setting/store');
		
		$store_info = $this->model_setting_store->getStore($data['store_id']);
		
		if ($store_info) {
			$store_name = $store_info['name'];
			$store_url = $store_info['url'];
		} else {
			$store_name = $this->config->get('config_name');
			$store_url = HTTP_CATALOG;
		}
		
		$this->load->model('setting/setting');
		
		$setting_info = $this->model_setting_setting->getSetting('setting', $data['store_id']);
			
		if (isset($setting_info['invoice_prefix'])) {
			$invoice_prefix = $setting_info['invoice_prefix'];
		} else {
			$invoice_prefix = $this->config->get('config_invoice_prefix');
		}
		
		$this->load->model('localisation/country');
		
		$this->load->model('localisation/zone');
		
		$country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);
		
		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';	
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}	
		
		$zone_info = $this->model_localisation_zone->getZone($data['shipping_zone_id']);
		
		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';			
		}	
					
		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);
		
		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];			
		} else {
			$payment_country = '';	
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';					
		}
	
		$zone_info = $this->model_localisation_zone->getZone($data['payment_zone_id']);
		
		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';			
		}	

		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
		
		if ($currency_info) {
			$currency_id = $currency_info['currency_id'];
			$currency_code = $currency_info['code'];
			$currency_value = $currency_info['value'];
		} else {
			$currency_id = 0;
			$currency_code = $this->config->get('config_currency');
			$currency_value = 1.00000;			
		}
      	
      	$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($invoice_prefix) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->db->escape($store_name) . "',store_url = '" . $this->db->escape($store_url) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_company_id = '" . $this->db->escape($data['payment_company_id']) . "', payment_tax_id = '" . $this->db->escape($data['payment_tax_id']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($payment_country) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($payment_zone) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($payment_address_format) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($shipping_country) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($shipping_zone) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($shipping_address_format) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', order_status_id = '" . (int)$data['order_status_id'] . "', affiliate_id  = '" . (int)$data['affiliate_id'] . "', language_id = '" . (int)$this->config->get('config_language_id') . "', currency_id = '" . (int)$currency_id . "', currency_code = '" . $this->db->escape($currency_code) . "', currency_value = '" . (float)$currency_value . "', date_added = NOW(), date_modified = NOW()");
      	
      	$order_id = $this->db->getLastId();
		
      	if (isset($data['order_product'])) {		
      		foreach ($data['order_product'] as $order_product) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" . (float)$order_product['total'] . "', tax = '" . (float)$order_product['tax'] . "', reward = '" . (int)$order_product['reward'] . "'");
			
				$order_product_id = $this->db->getLastId();
				
				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");
				
				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$order_option['product_option_id'] . "', product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "', name = '" . $this->db->escape($order_option['name']) . "', `value` = '" . $this->db->escape($order_option['value']) . "', `type` = '" . $this->db->escape($order_option['type']) . "'");
						
						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}
				
				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($order_download['name']) . "', filename = '" . $this->db->escape($order_download['filename']) . "', mask = '" . $this->db->escape($order_download['mask']) . "', remaining = '" . (int)$order_download['remaining'] . "'");
					}
				}
			}
		}
		
		if (isset($data['order_voucher'])) {	
			foreach ($data['order_voucher'] as $order_voucher) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', voucher_id = '" . (int)$order_voucher['voucher_id'] . "', description = '" . $this->db->escape($order_voucher['description']) . "', code = '" . $this->db->escape($order_voucher['code']) . "', from_name = '" . $this->db->escape($order_voucher['from_name']) . "', from_email = '" . $this->db->escape($order_voucher['from_email']) . "', to_name = '" . $this->db->escape($order_voucher['to_name']) . "', to_email = '" . $this->db->escape($order_voucher['to_email']) . "', voucher_theme_id = '" . (int)$order_voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($order_voucher['message']) . "', amount = '" . (float)$order_voucher['amount'] . "'");
			
      			$this->db->query("UPDATE " . DB_PREFIX . "voucher SET order_id = '" . (int)$order_id . "' WHERE voucher_id = '" . (int)$order_voucher['voucher_id'] . "'");
			}
		}

		// Get the total
		$total = 0;
		
		if (isset($data['order_total'])) {		
      		foreach ($data['order_total'] as $order_total) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $this->db->escape($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
			}
			
			$total += $order_total['value'];
		}

		// Affiliate
		$affiliate_id = 0;
		$commission = 0;
		
		if (!empty($this->request->post['affiliate_id'])) {
			$this->load->model('sale/affiliate');
			
			$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->post['affiliate_id']);
			
			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id']; 
				$commission = ($total / 100) * $affiliate_info['commission']; 
			}
		}
		
		// Update order total			 
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . (float)$total . "', affiliate_id = '" . (int)$affiliate_id . "', commission = '" . (float)$commission . "' WHERE order_id = '" . (int)$order_id . "'"); 	
	}
	
	public function editOrder($order_id, $data) {
		$this->load->model('localisation/country');
		
		$this->load->model('localisation/zone');
		
		$country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);
		
		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';	
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}	
		
		$zone_info = $this->model_localisation_zone->getZone($data['shipping_zone_id']);
		
		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';			
		}	
					
		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);
		
		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];			
		} else {
			$payment_country = '';	
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';					
		}
	
		$zone_info = $this->model_localisation_zone->getZone($data['payment_zone_id']);
		
		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';			
		}			

		// Restock products before subtracting the stock later on
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach($product_query->rows as $product) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "' AND subtract = '1'");

				$option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

				foreach ($option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}
		}

      	$this->db->query("UPDATE `" . DB_PREFIX . "order` SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_company_id = '" . $this->db->escape($data['payment_company_id']) . "', payment_tax_id = '" . $this->db->escape($data['payment_tax_id']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($payment_country) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($payment_zone) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($payment_address_format) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "',  shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($shipping_country) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($shipping_zone) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($shipping_address_format) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', order_status_id = '" . (int)$data['order_status_id'] . "', affiliate_id  = '" . (int)$data['affiliate_id'] . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
				
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'"); 
       	$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");
		
      	if (isset($data['order_product'])) {		
      		foreach ($data['order_product'] as $order_product) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_product_id = '" . (int)$order_product['order_product_id'] . "', order_id = '" . (int)$order_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" . (float)$order_product['total'] . "', tax = '" . (float)$order_product['tax'] . "', reward = '" . (int)$order_product['reward'] . "'");
			
				$order_product_id = $this->db->getLastId();

				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");
	
				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_option_id = '" . (int)$order_option['order_option_id'] . "', order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$order_option['product_option_id'] . "', product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "', name = '" . $this->db->escape($order_option['name']) . "', `value` = '" . $this->db->escape($order_option['value']) . "', `type` = '" . $this->db->escape($order_option['type']) . "'");
						
						
						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}
				
				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_download_id = '" . (int)$order_download['order_download_id'] . "', order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($order_download['name']) . "', filename = '" . $this->db->escape($order_download['filename']) . "', mask = '" . $this->db->escape($order_download['mask']) . "', remaining = '" . (int)$order_download['remaining'] . "'");
					}
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'"); 
		
		if (isset($data['order_voucher'])) {	
			foreach ($data['order_voucher'] as $order_voucher) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_voucher_id = '" . (int)$order_voucher['order_voucher_id'] . "', order_id = '" . (int)$order_id . "', voucher_id = '" . (int)$order_voucher['voucher_id'] . "', description = '" . $this->db->escape($order_voucher['description']) . "', code = '" . $this->db->escape($order_voucher['code']) . "', from_name = '" . $this->db->escape($order_voucher['from_name']) . "', from_email = '" . $this->db->escape($order_voucher['from_email']) . "', to_name = '" . $this->db->escape($order_voucher['to_name']) . "', to_email = '" . $this->db->escape($order_voucher['to_email']) . "', voucher_theme_id = '" . (int)$order_voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($order_voucher['message']) . "', amount = '" . (float)$order_voucher['amount'] . "'");
			
				$this->db->query("UPDATE " . DB_PREFIX . "voucher SET order_id = '" . (int)$order_id . "' WHERE voucher_id = '" . (int)$order_voucher['voucher_id'] . "'");
			}
		}
		
		// Get the total
		$total = 0;
				
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		
		if (isset($data['order_total'])) {		
      		foreach ($data['order_total'] as $order_total) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_total_id = '" . (int)$order_total['order_total_id'] . "', order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $this->db->escape($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
			}
			
			$total += $order_total['value'];
		}
		
		// Affiliate
		$affiliate_id = 0;
		$commission = 0;
		
		if (!empty($this->request->post['affiliate_id'])) {
			$this->load->model('sale/affiliate');
			
			$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->post['affiliate_id']);
			
			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id']; 
				$commission = ($total / 100) * $affiliate_info['commission']; 
			}
		}
				 
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . (float)$total . "', affiliate_id = '" . (int)$affiliate_id . "', commission = '" . (float)$commission . "' WHERE order_id = '" . (int)$order_id . "'"); 
	}
	
	public function deleteOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach($product_query->rows as $product) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "' AND subtract = '1'");

				$option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

				foreach ($option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
      	$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");
      	$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_fraud WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");
	}

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$reward = 0;
			
			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}			
			
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}
			
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}
		
			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}				
				
			$this->load->model('sale/affiliate');
				
			$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);
				
			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';				
			}

			$this->load->model('localisation/language');
			
			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);
			
			if ($language_info) {
				$language_code = $language_info['code'];
				$language_filename = $language_info['filename'];
				$language_directory = $language_info['directory'];
			} else {
				$language_code = '';
				$language_filename = '';
				$language_directory = '';
			}
			
			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'paysbuy'				  => $order_query->row['paysbuy'],
				'paypal'				  => $order_query->row['paypal'],
				'tack_code'				  => $order_query->row['tack_code'],
				'track_submit'			  => $order_query->row['track_submit'],
				'send_from'				  => $order_query->row['send_from'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_company_id'      => $order_query->row['payment_company_id'],
				'payment_tax_id'          => $order_query->row['payment_tax_id'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'payment_code'            => $order_query->row['payment_code'],				
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,				
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'], 
				'user_agent'              => $order_query->row['user_agent'],	
				'accept_language'         => $order_query->row['accept_language'],					
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified'],
				'send_from'				  => $order_query->row['send_from']
			);
		} else {
			return false;
		}
	}
	
	public function getOrders($data = array()) {
		$sql = "SELECT o.order_id, o.shipping_method ,CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email, payment_city AS province , ip , o.send_from , o.credit , o.point_status , paysbuy , paypal , o.note_icon,  (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status,  bb.bankname AS  banks, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified FROM `" . DB_PREFIX . "order` o  LEFT JOIN my_bank_txn AS b ON o.order_id = b.order_id LEFT JOIN my_bank bb ON b.bank = bb.id ";

		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
	
			$arre = array('ae','me','ate','mte','ar','mr','atr','mtr');
			if( in_array($data['filter_order_status_id'] , $arre)  ){
				$type = $data['filter_order_status_id'];
				$send = "";
				$ss = '4'; //4 Approved  //14 fortest
				//$sql .= ( in_array($type , $arre2) ) ? $sql." and CHAR_LENGTH(o.send_from) > 0 " : $sql." and CHAR_LENGTH(o.send_from) =  0 ";
				//$sql .= ( in_array($type , $arrr2) ) ? $sql." and CHAR_LENGTH(send_from) > 0 " : $sql." and CHAR_LENGTH(send_from) =  0 ";

				if(		$type == 'ae' or $type == 'me' or $type == 'ate' or $type == 'mte' ) { $shipping_type = 'EMS'; }
				elseif(	$type == 'ar' or $type == 'mr' or $type == 'atr' or $type == 'mtr') { $shipping_type = 'REGISTER'; }
				if(		$type == 'ae' or $type == 'ar' or $type == 'ate' or $type == 'atr') { $cmp = '='; }
				elseif(	$type == 'me' or $type == 'mr' or $type == 'mte' or $type == 'mtr') { $cmp = '>'; }
				if(		$type == 'ate'or $type == 'mte'or $type == 'atr' or $type == 'mtr' ) { $send = ' and CHAR_LENGTH(o.send_from) > 0  '; }
				$s = "SELECT o.order_id FROM my_order AS o INNER JOIN ( SELECT customer_id,  send_from FROM my_order WHERE  order_status_id='".$ss."' AND shipping_method = '".$shipping_type."' GROUP BY customer_id, send_from HAVING COUNT(*) ".$cmp." 1 ) AS datas ON o.order_status_id = '".$ss."' AND o.customer_id = datas.customer_id AND o.send_from = datas.send_from AND o.shipping_method = '".$shipping_type."'  ".$send;
				$qs = $this->db->query($s);
				$qst = $qs->rows;
				//echo $s;

				$sql .= " WHERE o.shipping_method = '".$shipping_type."' and o.order_status_id='".$ss."' ".$send;
				
				if(count($qst) == 0){
					$sql .= ' and 1=0 ';
				}else{
					$sql .= ' and ( ';
					foreach($qst as $key => $row) $sql .= ($key < ( count($qst)-1 ) ) ? " o.order_id='".$row['order_id']."' OR"  : " o.order_id='".$row['order_id']."'";
					$sql .= ' ) ';
				}
			}elseif($data['filter_order_status_id']  == 14){
				$sql .= " WHERE o.order_status_id = '1' AND DATEDIFF(NOW() , date_added   ) > 1 ";
			}else{
				$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
			}
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}
		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}
		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
		}
		if (!empty($data['filter_bank'])) {
			$ss = "3"; //3 checking  //14 fortest
			if($data['filter_bank'] == 'pp'){
				$sql .= " AND paypal like 'yes' and o.order_status_id = '$ss' ";
			}elseif($data['filter_bank'] == 'ps'){
				$sql .= " AND paysbuy like 'yes' and o.order_status_id = '$ss' ";
			}elseif($data['filter_bank'] == 'cd'){
				$sql .= " AND credit like 'yes' and o.order_status_id = '$ss' ";
			}else{
				$sql .= " AND b.bank  = '" . $data['filter_bank'] . "'  and o.order_status_id = '$ss'  ";
			}
			
		}
		$sort_data = array(
			'o.order_id',
			'customer',
			'status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		);
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
		}
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		//echo "<br>";echo $sql;echo "<br>";
		$query = $this->db->query($sql);
		return $query->rows;
	}
 
 
	
	public function getOrderOption($order_id, $order_option_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_option_id = '" . (int)$order_option_id . "'");

		return $query->row;
	}
	
 
	public  function getImgOptionName($product_id, $name){
		$sql ="SELECT optionimage FROM (SELECT * FROM my_product_option_value WHERE product_id = '$product_id' ) AS p LEFT JOIN my_option_value_description AS o ON p.option_value_id = o.option_value_id WHERE   o.name = '$name'";
		$query = $this->db->query($sql);
		//echo $sql;
		return $query->row['optionimage'];
	}

	public function getImgOptions($product_id, $product_option_id) {
		$sql ="SELECT optionimage FROM my_product_option_value WHERE product_id = '" . (int)$product_id . "' AND product_option_id = '" . (int)$product_option_id . "'";
		$query = $this->db->query($sql);
		//echo $sql;
		return $query->row['optionimage'];
	}
	public function getImg($product_id) {
		$sql ="SELECT image FROM my_product WHERE product_id = '" . (int)$product_id . "'  ";
		$query = $this->db->query($sql);
		return $query->row['image'];
	}

	public function getOrderDownloads($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}
	
	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
	}
	
	public function getOrderVoucherByVoucherId($voucher_id) {
      	$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row;
	}
				
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` as o LEFT JOIN my_bank_txn AS b ON o.order_id = b.order_id LEFT JOIN my_bank bb ON b.bank = bb.id ";

		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
	
			$arre = array('ae','me','ate','mte','ar','mr','atr','mtr');
			if( in_array($data['filter_order_status_id'] , $arre)  ){
				$type = $data['filter_order_status_id'];
				$send = "";
				$ss = '4'; //4 Approved  //14 fortest
				//$sql .= ( in_array($type , $arre2) ) ? $sql." and CHAR_LENGTH(o.send_from) > 0 " : $sql." and CHAR_LENGTH(o.send_from) =  0 ";
				//$sql .= ( in_array($type , $arrr2) ) ? $sql." and CHAR_LENGTH(send_from) > 0 " : $sql." and CHAR_LENGTH(send_from) =  0 ";

				if(		$type == 'ae' or $type == 'me' or $type == 'ate' or $type == 'mte' ) { $shipping_type = 'EMS'; }
				elseif(	$type == 'ar' or $type == 'mr' or $type == 'atr' or $type == 'mtr') { $shipping_type = 'REGISTER'; }
				if(		$type == 'ae' or $type == 'ar' or $type == 'ate' or $type == 'atr') { $cmp = '='; }
				elseif(	$type == 'me' or $type == 'mr' or $type == 'mte' or $type == 'mtr') { $cmp = '>'; }
				if(		$type == 'ate'or $type == 'mte'or $type == 'atr' or $type == 'mtr' ) { $send = ' and CHAR_LENGTH(o.send_from) > 0  '; }
				$s = "SELECT o.order_id FROM my_order AS o INNER JOIN ( SELECT customer_id,  send_from FROM my_order WHERE  order_status_id='".$ss."' AND shipping_method = '".$shipping_type."' GROUP BY customer_id, send_from HAVING COUNT(*) ".$cmp." 1 ) AS DATA ON o.order_status_id = '".$ss."' AND o.customer_id = data.customer_id AND o.send_from = data.send_from AND o.shipping_method = '".$shipping_type."'  ".$send;
				$qs = $this->db->query($s);
				$qst = $qs->rows;
				//echo $s;

				$sql .= " WHERE o.shipping_method = '".$shipping_type."' and o.order_status_id='".$ss."' ".$send;
				
				if(count($qst) == 0){
					$sql .= ' and 1=0 ';
				}else{
					$sql .= ' and ( ';
					foreach($qst as $key => $row) $sql .= ($key < ( count($qst)-1 ) ) ? " o.order_id='".$row['order_id']."' OR"  : " o.order_id='".$row['order_id']."'";
					$sql .= ' ) ';
				}
			}elseif($data['filter_order_status_id']  == 14){
				$sql .= " WHERE o.order_status_id = '1' AND DATEDIFF(NOW() , date_added   ) > 1 ";
			}else{
				$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
			}
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}
		
		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}
		if (!empty($data['filter_bank'])) {
			$ss = "3"; //3 checking  //14 fortest
			if($data['filter_bank'] == 'pp'){
				$sql .= " AND paypal like 'yes' and o.order_status_id = '$ss' ";
			}elseif($data['filter_bank'] == 'ps'){
				$sql .= " AND paysbuy like 'yes' and o.order_status_id = '$ss' ";
			}elseif($data['filter_bank'] == 'cd'){
				$sql .= " AND credit like 'yes' and o.order_status_id = '$ss' ";
			}else{
				$sql .= " AND b.bank  = '" . $data['filter_bank'] . "'  and o.order_status_id = '$ss'  ";
			}
			
		}
		if (!empty($data['filter_paypal'])) {
			$paypal = ($data['filter_paypal'] == 'yes') ? 'yes' : ( ($data['filter_paypal'] == 'no') ? 'no' : '%'   );
			$sql .= " AND paypal like '" .$paypal. "'";
		}
 
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalOrdersByStoreId($store_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE store_id = '" . (int)$store_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrdersByOrderStatusId($order_status_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id = '" . (int)$order_status_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByLanguageId($language_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE language_id = '" . (int)$language_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId($currency_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE currency_id = '" . (int)$currency_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}
	
	public function getTotalSales() {
      	$query = $this->db->query("SELECT SUM(total) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalSalesByYear($year) {
      	$query = $this->db->query("SELECT SUM(total) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND YEAR(date_added) = '" . (int)$year . "'");

		return $query->row['total'];
	}

	public function createInvoiceNo($order_id) {
		$order_info = $this->getOrder($order_id);
			
		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->db->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "'");
	
			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}
		
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_id . "'");
			
			return $order_info['invoice_prefix'] . $invoice_no;
		}
	}
	
	public function addOrderHistory($order_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$data['order_status_id'] . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$data['order_status_id'] . "', notify = '" . (isset($data['notify']) ? (int)$data['notify'] : 0) . "', comment = '" . $this->db->escape(strip_tags($data['comment'])) . "', date_added = NOW()");

		$order_info = $this->getOrder($order_id);

		// Send out any gift voucher mails
		if ($this->config->get('config_complete_status_id') == $data['order_status_id']) {
			$this->load->model('sale/voucher');

			$results = $this->getOrderVouchers($order_id);
			
			foreach ($results as $result) {
				$this->model_sale_voucher->sendVoucher($result['voucher_id']);
			}
		}

      	if ($data['notify']) {
			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/order');

			$subject = sprintf($language->get('text_subject'), $order_info['store_name'], $order_id);

			$message  = $language->get('text_order') . ' ' . $order_id . "\n";
			$message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";
			
			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$data['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
				
			if ($order_status_query->num_rows) {
				$message .= $language->get('text_order_status') . "\n";
				$message .= $order_status_query->row['name'] . "\n\n";
			}
			
			if ($order_info['customer_id']) {
				$message .= $language->get('text_link') . "\n";
				$message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
			}
			
			if ($data['comment']) {
				$message .= $language->get('text_comment') . "\n\n";
				$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			}

			$message .= $language->get('text_footer');

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($order_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($order_info['store_name']);
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}
		
	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 10;
		}	
				
		$query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}
	
	public function getTotalOrderHistories($order_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}	
		
	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row['total'];
	}	
	
	public function getEmailsByProductsOrdered($products, $start, $end) {
		$implode = array();
		
		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . $product_id . "'";
		}
		
		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0'");
	
		return $query->rows;
	}
	
	public function getTotalEmailsByProductsOrdered($products) {
		$implode = array();
		
		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . $product_id . "'";
		}
				
		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0' LIMIT " . $start . "," . $end);	
		
		return $query->row['total'];
	}	



	public function getRollbackOrders($data = array()) {
		$sql = "SELECT * FROM my_return as o left join my_rollback_item as ri on ri.rollback_id = o.return_id left join my_customer as c on c.customer_id = o.customer_id left join my_bank as b on b.id = c.bank_name  ";

		if (  count($data)>0  ) $sql .= " WHERE  o.order_id > 0 ";

		if (!empty($data['filter_rollbackid'])) $sql .= " AND ri.rollback_id = '" . (int)$data['filter_rollbackid'] . "'";
		if (!empty($data['filter_orderid'])) $sql .= " AND o.order_id = '" . (int)$data['filter_orderid'] . "'";
		if (!empty($data['filter_productid'])) $sql .= " AND ri.product_id = '" . (int)$data['filter_productid'] . "'";
		if (!empty($data['filter_customer'])) $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		if (!empty($data['filter_email'])) $sql .= " AND o.email like '%" . $data['filter_email'] . "%'";
		if (!empty($data['filter_order_status_id'])) $sql .= " AND o.return_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		if (!empty($data['filter_date_added'])) $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		if (!empty($data['filter_bank'])) $sql .= " AND b.bankname like '%" . $this->db->escape($data['filter_bank']) . "%' ";
		
		$sql .= " group by o.order_id desc ";
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) $data['start'] = 0;
			if ($data['limit'] < 1) $data['limit'] = 20;
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		//echo $sql;
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalRollbackOrders($data = array()) {
		$sql = "SELECT count(*) as total FROM my_return as o left join my_rollback_item as ri on ri.rollback_id = o.return_id  LEFT JOIN my_customer AS c ON c.customer_id = o.customer_id LEFT JOIN my_bank AS b ON b.id = c.bank_name  ";

		if (  count($data)>0  ) $sql .= " WHERE  o.order_id > 0 ";

		if (!empty($data['filter_rollbackid'])) $sql .= " AND ri.rollback_id = '" . (int)$data['filter_rollbackid'] . "'";
		if (!empty($data['filter_orderid'])) $sql .= " AND o.order_id = '" . (int)$data['filter_orderid'] . "'";
		if (!empty($data['filter_productid'])) $sql .= " AND ri.product_id = '" . (int)$data['filter_productid'] . "'";
		if (!empty($data['filter_customer'])) $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		if (!empty($data['filter_email'])) $sql .= " AND o.email like '%" . $data['filter_email'] . "%'";
		if (!empty($data['filter_order_status_id'])) $sql .= " AND o.return_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		if (!empty($data['filter_date_added'])) $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		if (!empty($data['filter_bank'])) $sql .= " AND b.bankname like '%" . $this->db->escape($data['filter_bank']) . "%' ";

		$sql .= " group by o.order_id desc ";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) $data['start'] = 0;
			if ($data['limit'] < 1) $data['limit'] = 20;
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getRollbackOrdersDetail($return_id) {
		//$sql = "SELECT r.return_reason_id   , r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM my_return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '1') AS reason, (SELECT ra.name FROM my_return_action ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = '1') AS action, (SELECT rs.name FROM my_return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '1') AS status, r.comment, r.date_ordered, r.date_added, r.date_modified FROM `my_return` r WHERE return_id = '" . (int)$return_id . "' ";
		//echo $sql;
		$sql = "select * , ri.product_id as pid from my_rollback_item as ri left join my_return as r on  ri.rollback_id = r.return_id LEFT JOIN my_return_status AS rs ON r.return_status_id = rs.return_status_id where rollback_id='$return_id' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		return $query->rows;
	}
	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
		return $query->rows;
	}
	public function getProductFromOrder($order_id){
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'   ");

		if ($order_query->num_rows) {
			$product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$order_query->row['product_id'] . "'   ");
			if ($product_query->num_rows) {
				$product_img		= $product_query->row['image'];
				$product_id			= $product_query->row['product_id'];
				$model				= $product_query->row['model'];
				$stock_status_id	= $product_query->row['stock_status_id'];
			} else {
				$product_img = '';
				$model = "";
				$product_id = '';				
			}

			return array(
				'product_img'               => $product_img,
				'product_id'				=> $product_id,
				'product_model'				=> $model,
				'status_id'					=> $stock_status_id
			);
		} else {
			return false;	
		}
	}
	public function getProductImg($product , $color){
		$sql = "SELECT optionimage as img FROM `my_product_option_value` AS p LEFT JOIN my_option_value_description AS o  ON p.option_value_id = o.option_value_id   WHERE  product_id = '$product' AND `name` = '$color'   ";
		$order_query = $this->db->query($sql);

		if((int)$order_query->num_rows < 2){
			$sql = "SELECT image as img FROM `my_product` WHERE  product_id = '$product'   ";
			$order_query = $this->db->query($sql);
		} 
		return $order_query->row['img'];
	}
	public function getProductModel($product ){
		$sql = "SELECT model FROM `my_product` WHERE  product_id = '$product'   ";
		$order_query = $this->db->query($sql);

		return $order_query->row['model'];
	}





	function adminApproveOrder($rollback_id,$post){
		$a_time = time();
		$a_note = $post['note'];
		$a_status = $post['status'];
		$order_id = $post['o'];
		$a_discount_user = 0;
		$a_shipping = 0;
		$sub_total = 0;
		$total = 0;
		$claim_p = 0;
		$pass = 0;
		$item = array();

		if ($post['status'] == '4'){ //4=approved
			//get return item
			$sql	= "select unit_price,id,product_id from my_rollback_item where order_id = '$order_id' ";
			$query	= $this->db->query($sql);
			$rollback = $query->rows;	
			
			//get shipping . customer_id
			$sql1	= "select shipping_method,customer_id  from my_order where order_id = '$order_id' ";
			$query1 = $this->db->query($sql1);
			$order  = $query1->row;
			$shipping_type = $order['shipping_method'];
			$customer_id   = $order['customer_id'];
			

			//get order total
			$sql2	= "select code,value from my_order_total where order_id = '$order_id' ";
			$query2 = $this->db->query($sql2);
			$totals = $query2->rows;
			$total_array = array('globaldiscount','customer_referral_discount','vip','level_discount','credit_discount','coupon','vender_discount','shipping');
			foreach($totals as $name){
				if($name['code'] == 'sub_total'){
					$sub_total = floor($name['value']);
				}elseif($name['code'] == 'shipping'){
					$a_shipping	= floor($name['value']);
				}elseif($name['code'] == 'total'){
					$total = floor($name['value']);
				}elseif($name == 'vip'){
					$a_discount_user = floor($name['value']);
				}elseif($name == 'level_discount'){
					$a_discount_user = floor($name['value']);
				}
			}
			$discount = $sub_total + $a_shipping - $total;

			foreach ($rollback as $row) $item[$row['product_id']] = $row['unit_price'];
			//return total price & amount
			//f=pid , v=amount
			foreach ($post['pass_qty'] as $f => $v){
				
				$claim_p += $item[$f]*$v;
				$pass += $v;
			}

			/*foreach ($post['pass_qty'] as $f => $v){
				$array_p_d		= ($discount == 0) ? array($item[$f]*$v , 0) : $this->getProductAndDiscount($f , $v) ;
				$claim_p		+= $array_p_d[0];
				$new_discount	+= $array_p_d[1];
			}

			array('return_p','discount_p') = getProductAndDiscount();*/
/****** THIS VERSION NOT SUPPORT SALE PRODUCT ******/
			//Check Product cannot used coupon discount? [sale product]
			//discount = sub_total + shipping - total
			//new_discount = ( claim_p / total )*discount
			//refund = claim_p - new_discount + shipping
			//if($discount_product){
			//	$new_discount	= 0;
			//	$total_return	= $claim_p;
			//}else{
				
				$new_discount	= ($claim_p / $total)*$discount;
				$new_discount	= floor($new_discount);
				$total_return	= $claim_p - $new_discount;
			//}



			$sql5	= "select sum(quantity) as item_amount  from my_order_product where order_id = '$order_id' ";
			$query5 = $this->db->query($sql5);
			$amount = $query5->row['item_amount'];

			if (strtolower($shipping_type) == 'ems') {
				$fee = ($amount == $pass) ? $a_shipping : $pass*10;
			}else{
				$fee = ($amount == $pass) ? $a_shipping : $pass*5;
			}
			$total_return += $fee;

			$total_return = ceil($total_return);
			$total_return = ($total_return > $sub_total) ? $sub_total : $total_return;
			//echo "sub_total=$sub_total   a_shipping=$a_shipping  total=$total  claim_p=$claim_p  discount=$discount  new_discount=$new_discount  total_return=$total_return fee=$fee";

			$stmt = " return_status_id='$a_status' , note='$a_note' , cal_total='$claim_p' , cal_discount_user='$a_discount_user' , cal_discount_coupon='$new_discount' , cal_total_return='$total_return' , cal_fee='$fee' , date_modified=NOW() ";
		}else{
			$stmt = " return_status_id='$a_status' , note='$a_note' , date_modified=NOW() ";
		}

		$res = $this->autoExecuteUpdate('return', $stmt ,"return_id = '$rollback_id' ");
		if ($res){
			$this->saveLog($rollback_id,'claim','approve','success');
			foreach ($post['pass_qty'] as $f => $v){
				$this->autoExecuteUpdate('rollback_item', "pass_qty='$v'" ,"product_id = '$f' ");
			}
		}else{
			$this->saveLog($id,'claim','approve','fail');
		}
	}
	function admin_send_wrong($id) {
		$sql = "update my_return set send_wrong='1' , `cal_fee`=cal_fee+30 , `cal_total_return`=cal_total_return+30 where return_id='$id' ";
		$query = $this->db->query($sql);
	}
	function admin_remove_send_wrong($id) {
		$sql = "update my_return set send_wrong='0' , `cal_fee`=cal_fee-30 , `cal_total_return`=cal_total_return-30 where return_id='$id' ";
		$query = $this->db->query($sql);
	}
	function admin_revert_received($id){
		//status 2=checking
		$sql = "update my_return set return_status_id='2'  where return_id='$id' ";
		$query = $this->db->query($sql);
	}
	function updateSendingDeadline() {
		//status 10=sending_deadline , 4=approved
		//3days
		$sql = "SELECT return_id as id FROM my_return WHERE return_status_id='4'  AND    DATEDIFF(NOW() , date_modified   ) > 3 " ;
		$query = $this->db->query($sql);
		$result = $query->rows;
		foreach($result as $rollback)
			$this->db->query("UPDATE my_return SET return_status_id = '10' WHERE return_id = '".$rollback['id']."'");
	}
	

	function getOrder2($id){
		$sql = "select * from my_order where order_id = '$id' ";
		$query = $this->db->query($sql);
		return $query->row;
	}

	function setCheckingOrder($id){
		//status 2=checking
		$a_time = time();
		$stmt = " return_status_id='2' ,  date_modified='$a_time' ";

		$this->saveLog($id,'order','checking','set sending to checking');
		return $this->autoExecuteUpdate('return', $stmt, "return_id='$id' ");
	}

	function setFinalOrder($id,$status,$note=''){
		$a_time = time();
		$stmt = " return_status_id='$status' , note='$note' ,  date_modified='$a_time' ";

		$this->saveLog($id,'order',$status,'set success : '.$status.' - '.$note);
		return $this->autoExecuteUpdate('return', $stmt, "return_id='$id' ");
	}

	function setTransferSuccessOrder($id,$post){
		//status 7=transfer_success
		$a_time = time();
		$stmt = " return_status_id='7' ,  date_modified='$a_time' ";

		$date = new DateTime();
		$post['mydateYear']		= (int)$date->format('Y')+543;
		$post['mydateMonth']	= (int)$date->format('m') -1;
		$post['mydateDay']		= (int)$date->format('d');
		$post['mytimeHour']		= $date->format('H');
		$post['mytimeMinute']	= $date->format('i');
		$post['cashback_type']	= 'money';


		$this->insertTransferSuccess($id,$post);
		$this->saveLog($id,'order','transfer_success','set transfer success ');
		return $this->autoExecuteUpdate('return',$stmt,"return_id='$id' ");
	}

	function getTransferOrder($id,$type='order'){
		$sql = "select * from my_rollback_transfer_success where rollback_id = '$id' and type = '$type' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	function getImageOrder($order_id){
		$sql = "select * from my_return_image where order_id = '$order_id' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}



	public function getTotalRefundOrders($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM `my_rollback_transfer` as r left join my_customer as c on r.user_id = c.customer_id ";

		$sql .= " WHERE  c.customer_id > 0  ";
		if (!empty($data['filter_email'])) $sql .= " AND c.email = '" . $this->db->escape($data['filter_email']) . "'";
		if (!empty($data['filter_firstname'])) $sql .= " AND c.firstname = '" . $this->db->escape($data['filter_firstname']) . "' ";
		if (!empty($data['filter_lastname'])) $sql .= " AND c.lastname = '" . $this->db->escape($data['filter_lastname']) . "' ";
		if (!empty($data['filter_status'])) $sql .= " AND r.status = '" . $this->db->escape($data['filter_status']) . "' ";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function getTotalRefundOrdersHeader($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM `my_rollback_transfer` as r left join my_customer as c on r.user_id = c.customer_id WHERE  c.customer_id > 0 and r.read='0' ";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	public function getRefundOrders($data = array()) {
      	$sql = "SELECT r.id,r.username, c.email,c.firstname,c.lastname,r.bank_name,r.bank,r.amount,r.date_transfer,r.modify,r.message,r.status, r.cashback_type,r.read FROM `my_rollback_transfer` as r left join my_customer as c on r.user_id = c.customer_id ";
		if (  count($data)>0  ) $sql .= " WHERE  c.customer_id > 0 ";
		if (!empty($data['filter_customer'])) $sql .= " AND c.email like '%" . $this->db->escape($data['filter_customer']) . "%'";
		if (!empty($data['filter_firstname'])) $sql .= " AND c.firstname like '%" . $this->db->escape($data['filter_firstname']) . "%' ";
		if (!empty($data['filter_lastname'])) $sql .= " AND c.lastname like '%" . $this->db->escape($data['filter_lastname']) . "%' ";
		if (!empty($data['filter_status'])) $sql .= " AND r.status = '" . $this->db->escape($data['filter_status']) . "' ";

		$sql .= " order by r.modify desc ";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) $data['start'] = 0;
			if ($data['limit'] < 1) $data['limit'] = 20;

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getRefundDetails($data) {
      	$sql = "SELECT r.id,r.username ,r.bank_account,c.email,c.firstname,c.lastname,r.bank_name,r.bank,r.amount,r.date_transfer,r.modify,r.message,r.status, r.cashback_type,r.read FROM `my_rollback_transfer` as r left join my_customer as c on r.user_id = c.customer_id where r.id='".$data."' ";

		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getRefundDetailSuccess($data , $type) {
      	$sql = "SELECT * FROM my_rollback_transfer_success as r where r.rollback_id='".$data."' and type='".$type."' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function adminSetStatusWrongTransfer($id,$status,$post='') {
		$message='';
		if ($post and $status == 'transfer_success'){
			$this->insertTransferSuccess($id,$post,'wrong');
		}else if ($post and $status == 'disapprovel'){
			$message = $post['note'];
		}

      	$sql = "update  my_rollback_transfer set status='".$status."' , message='".$message."' where id='".$id."'  ";
		$query = $this->db->query($sql);
	
		$this->addBacklog($id,'wrong',$status,$message);
	}
	public function addBacklog($id,$type,$status ,$msg='') {
      	$sql = "insert into   my_rollback_log set `rollback_id`='".$id."' , `rollback_type`='".$type."' , `status`='".$status."' , `message`='".$msg."' , `user_id`='".$this->user->getId()."' , `username`='".$this->user->getUserName()."' , `created`=NOW() ";
		$query = $this->db->query($sql);
	}
	public function saveLog($id,$type,$status ,$msg='') {
      	$sql = "insert into   my_rollback_log set `rollback_id`='".$id."' , `rollback_type`='".$type."' , `status`='".$status."' , `message`='".$msg."' , `user_id`='".$this->user->getId()."' , `username`='".$this->user->getUserName()."' , `created`=NOW() ";
		$query = $this->db->query($sql);
	}
	public function insertTransferSuccess($id,$post,$type='order'){
		$datetime = $post['mydateYear'].'-'.$post['mydateMonth'].'-'.$post['mydateDay'].' '.$post['mytimeHour'].':'.$post['mytimeMinute'].':00';

      	$sql = "insert into   my_rollback_transfer_success set `rollback_id`='".$id."' , `type`='".$type."' , bank_account='".$post['bank_account']."' , bank_name='".$post['bank_name']."' , bank='".$post['bank']."' , amount='".$post['amount']."' , datetime='".$datetime."', note='".$post['message']."' , cashback_type='".$post['cashback_type']."'   ";
		$this->db->query($sql);
	}

	public function setBackToPending($id,$type){
		switch ($type){
			case 'order':
				$this->addBacklog($id,'order','pending','setBackToPending');
				return $this->autoExecuteUpdate('return', "return_status_id='1'","return_id = $id");
			break;

			case 'cancel':
				$this->addBacklog($id,'cancel','pending','setBackToPending');
				return $this->autoExecuteUpdate('rollback_cancel',"status='pending'", "id = $id");
			break;

			case 'wrong':
				$this->addBacklog($id,'wrong','pending','setBackToPending');
				return $this->autoExecuteUpdate('rollback_transfer',"status='pending'", "id = $id");
			break;
		}
	}
	public function autoExecuteUpdate($table , $stmt , $cause ) {
      	$sql = "update  my_".$table."  set ".$stmt."  where ".$cause." ";
		return $this->db->query($sql);
	}


	public function refundDetailsRead($data) {
      	$sql = "update  `my_rollback_transfer` as r set r.read='1'  where r.id='".$data."' ";
		$this->db->query($sql);
	}

	public function rollbackDetailsRead($data) {
      	$sql = "update  my_rollback_item as r set r.read='1'  where r.rollback_id='".$data."' ";
		$this->db->query($sql);
	}


	public function adminSetStatusCancel($id,$status,$post='') {
		$message='';
		if ($post and $status == 'transfer_success'){
			$this->insertTransferSuccess($id,$post,'cancel');
		}else if ($post and $status == 'disapprovel'){
			$message = $post['note'];
		}

      	$sql = "update  my_rollback_cancel set status='".$status."' , message='".$message."' where id='".$id."'  ";
		$query = $this->db->query($sql);
	
		$this->addBacklog($id,'cancel',$status,$message);
	}
 



	public function getTotalCancelOrders($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM my_rollback_cancel as r left join my_customer as c on r.user_id = c.customer_id ";

		$sql .= " WHERE  c.customer_id > 0  ";
		if (!empty($data['filter_orderid'])) $sql .= " AND r.order_id = '" . $this->db->escape($data['filter_orderid']) . "'";
		if (!empty($data['filter_email'])) $sql .= " AND c.email = '" . $this->db->escape($data['filter_email']) . "'";
		if (!empty($data['filter_firstname'])) $sql .= " AND c.firstname = '" . $this->db->escape($data['filter_firstname']) . "' ";
		if (!empty($data['filter_lastname'])) $sql .= " AND c.lastname = '" . $this->db->escape($data['filter_lastname']) . "' ";
		if (!empty($data['filter_status'])) $sql .= " AND r.status = '" . $this->db->escape($data['filter_status']) . "' ";
//echo $sql;
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function getTotalCancelOrdersHeader($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM my_rollback_cancel as r left join my_customer as c on r.user_id = c.customer_id WHERE  c.customer_id > 0 and r.read='0'  ";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function getCancelOrders($data = array()) {
      	$sql = "SELECT *,r.status AS rstatus FROM  my_rollback_cancel as r left join my_customer as c on r.user_id = c.customer_id   ";

		if (  count($data)>0  ) $sql .= " WHERE  c.customer_id > 0 ";
		if (!empty($data['filter_orderid'])) $sql .= " AND r.order_id = '" . $this->db->escape($data['filter_orderid']) . "'";
		if (!empty($data['filter_customer'])) $sql .= " AND c.email like '%" . $this->db->escape($data['filter_customer']) . "%'";
		if (!empty($data['filter_firstname'])) $sql .= " AND c.firstname like '%" . $this->db->escape($data['filter_firstname']) . "%' ";
		if (!empty($data['filter_lastname'])) $sql .= " AND c.lastname like '%" . $this->db->escape($data['filter_lastname']) . "%' ";
		if (!empty($data['filter_status'])) $sql .= " AND r.status = '" . $this->db->escape($data['filter_status']) . "' ";
		$sql .= " order by r.modify desc ";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) $data['start'] = 0;
			if ($data['limit'] < 1) $data['limit'] = 20;
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
	//echo $sql;
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getCancelOrderDetails($data) {
      	//$sql = "SELECT *,r.status AS rstatus FROM  my_rollback_cancel as r left join my_customer as c on r.user_id = c.customer_id WHERE  r.id = '" . $data . "'    ";
		$sql = "SELECT * FROM  my_rollback_cancel  WHERE  id = '" . $data . "'    ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function cancelOrderDetailsRead($data) {
      	$sql = "update  my_rollback_cancel as r set r.read='1'  where r.id='".$data."' ";
		$this->db->query($sql);
	}

	public function getCancelOrdersProductDetail($orderid) {
      	$sql = "SELECT p.model,p.image ,op.quantity , op.total FROM my_order_product AS op LEFT JOIN my_product AS p ON p.product_id = op.product_id where op.order_id='".$orderid."'  ";
	//echo $sql;
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getCancelCustomer($data) {
      	//$sql = "SELECT *,r.status AS rstatus FROM  my_rollback_cancel as r left join my_customer as c on r.user_id = c.customer_id WHERE  r.id = '" . $data . "'    ";
		$sql = "SELECT * FROM  my_customer as c left join my_address as a on a.address_id = c.address_id left join my_bank as b on b.id=c.bank_name  WHERE  c.customer_id = '" . $data . "'    ";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function updateOrder( $data  ) {
		$sql = "update   my_order  set shipping_firstname='".$data['shipping_firstname']."',shipping_lastname='',shipping_address_1='".$data['shipping_address_1']."',shipping_address_2='',shipping_zone_id='".$data['shipping_zone_id']."',shipping_postcode='".$data['shipping_postcode']."',telephone='".$data['telephone']."',email='".$data['email']."' where order_id ='".$data['order_id']."' ";
		
		$query = $this->db->query($sql);
		return $query;
	}

	public function updateOrderStatus( $datas  ) {
		$status = $datas['change_type_status'];
		$array_status = array( 1,2,3,4,5,6,7,8,9  );
		$selected_userid = implode(",", $this->request->post['selected']);
		$comment = "";

		if($status == 3){//Checking
			$sql = "update   my_order  set order_status_id='$status' where order_id in ($selected_userid) and  order_status_id ='2' ";
			$comment = " update from admin [".$this->user->getUserName()."]  to order status $status  from ";
		}elseif( in_array($status,$array_status)  ){
			$sql = "update   my_order  set order_status_id='$status' where order_id in ($selected_userid) ";
			$comment = " update from admin [".$this->user->getUserName()."]  to order status $status  from ";
		}elseif($status == 10){//Refunded
			$sql = "update   my_order  set order_status_id='$status' , refund_time = '". strftime('%Y-%m-%d %H:%M:%S', time())."' where order_id in ($selected_userid) ";
		}elseif( ($status == 13)||($status == 14)  ){ //cancel , cancel_deadline
			//add product_qty
			$sql = "SELECT p.order_id, p.product_id , p.quantity , (SELECT product_option_value_id FROM my_order_option AS o WHERE p.order_id = o.order_id AND p.order_id IN ($selected_userid) AND o.name='Size'  ) AS size, (SELECT product_option_value_id FROM my_order_option AS o WHERE p.order_id = o.order_id AND p.order_id IN ($selected_userid) AND o.name='Color'  ) AS color FROM my_order_product AS p  WHERE p.order_id IN ($selected_userid) ";
			$order_array = $this->queryToArray($sql);
			//echo $sql."<br>";
			foreach($order_array as $o_array){
				$tq = $o_array['quantity'];
				$tp = $o_array['product_id'];
				$ts = $o_array['size'];
				$tc = $o_array['color'];
				//update my_product
				$sql01 = "update my_product set quantity=quantity+$tq where product_id='$tp' ";
				$this->db->query($sql01);
				//update my_product_option_value
				$sql02 = "update my_product_option_value set quantity=quantity+$tq where  product_option_value_id='$ts' ";
				$sql03 = "update my_product_option_value set quantity=quantity+$tq where  product_option_value_id='$tc' ";
				$this->db->query($sql02);
				$this->db->query($sql03);

				$sql2 = "SELECT option_value_id FROM my_product_option_value WHERE product_option_value_id IN (".$o_array['size'].",".$o_array['color'].") ";
				$order_array = $this->queryToArray($sql2);
				$option_array = $this->getProductOption($order_array);
				//update my_product_option_qty
				//print_r($option_array);
				$sql04 = "update my_product_option_qty set amount=amount+$tq  WHERE property_1 ='".$option_array[1]."' and  property_2='".$option_array[0]."' AND product_id='".$o_array['product_id']."' ";
				$this->db->query($sql04);
	 
			}
			
			//update status
			$sql = "update   my_order  set order_status_id='$status' where order_id in ($selected_userid)   ";
			$this->db->query($sql);
			$comment = " update from admin [".$this->user->getUserName()."]  to order status $status  from ";
			//del point
			$sql = "select t.order_id,t.value,o.customer_id from my_order_total as t left join my_order as o on t.order_id = o.order_id where t.order_id in ($selected_userid) and t.code='sub_total' ";
			$order_array = $this->queryToArray($sql);
			foreach($order_array as $data){
				$sql = "update   my_customer  set point=point-".($data['value']*2)." ,amount_cancel  = amount_cancel +1 where customer_id = '".$data['customer_id']."' LIMIT 1  ";
				$this->db->query($sql);
			}
			$sql = 'select * from my_customer limit 1';

		}elseif($status == 'done_point'){
			//add point to user
			//update order status_point = yes
		}elseif($status == 'stock'){
			//UPDATE products SET amount=amount-%s remove item from stock
			//UPDATE orders SET stock_status='yes'
		}elseif($status == 'remove'){
			//add product_qty
			$sql = "SELECT p.order_id, p.product_id , p.quantity , (SELECT product_option_value_id FROM my_order_option AS o WHERE p.order_id = o.order_id AND p.order_id IN ($selected_userid) AND o.name='Size'  ) AS size, (SELECT product_option_value_id FROM my_order_option AS o WHERE p.order_id = o.order_id AND p.order_id IN ($selected_userid) AND o.name='Color'  ) AS color FROM my_order_product AS p  WHERE p.order_id IN ($selected_userid) ";
			$order_array = $this->queryToArray($sql);
			//echo $sql."<br>";
			foreach($order_array as $o_array){
				$tq = $o_array['quantity'];
				$tp = $o_array['product_id'];
				$ts = $o_array['size'];
				$tc = $o_array['color'];
				//update my_product
				$sql01 = "update my_product set quantity=quantity+$tq where product_id='$tp' ";
				$this->db->query($sql01);
				//update my_product_option_value
				$sql02 = "update my_product_option_value set quantity=quantity+$tq where  product_option_value_id='$ts' ";
				$sql03 = "update my_product_option_value set quantity=quantity+$tq where  product_option_value_id='$tc' ";
				$this->db->query($sql02);
				$this->db->query($sql03);

				$sql2 = "SELECT option_value_id FROM my_product_option_value WHERE product_option_value_id IN (".$o_array['size'].",".$o_array['color'].") ";
				$order_array = $this->queryToArray($sql2);
				$option_array = $this->getProductOption($order_array);
				//update my_product_option_qty
				//print_r($option_array);
				$sql04 = "update my_product_option_qty set amount=amount+$tq  WHERE property_1 ='".$option_array[1]."' and  property_2='".$option_array[0]."' AND product_id='".$o_array['product_id']."' ";
				$this->db->query($sql04);
	 
			}
			//del order
			$sql0001 = "delete FROM my_order where order_id IN ($selected_userid) ";
			$sql0002 = "delete FROM my_order_option where order_id IN ($selected_userid) ";
			$sql0003 = "delete FROM my_order_product where order_id IN ($selected_userid) ";
			$this->db->query($sql0001);
			$this->db->query($sql0002);
			$this->db->query($sql0003);

			$sql = 'select * from my_customer limit 1';
		}
 
		foreach($this->request->post['selected'] as $k => $v){
			$sql2 = "select order_status_id from my_order where order_id='$v' " ;
			$query = $this->db->query( $sql2 );
			$t_order_status_id = $query->row['order_status_id'];
			$sql3 = "insert into my_order_history set order_id='$v' ,order_status_id='$t_order_status_id' , notify='1' , comment='".$comment.$t_order_status_id."' , date_added=now() ";
			$query = $this->db->query( $sql3 );
		}

		$query = $this->db->query($sql);
		//echo $sql."<br>";
	}
	public function getProductOption($data) {
		$array = array();
		foreach($data as $k => $v){
			array_push($array , $v['option_value_id']);
		}
		return $array;
	}
	public function queryToArray($sql) {
		$array = array();
		$query = $this->db->query($sql);
		foreach( $query->rows as $data ){
			array_push($array , $data);
		}
		return $array;
	}

	public function getBank($order_id) {
		$sql ="SELECT t.order_id,t.date,t.time,t.money,b.bankname,t.remark FROM my_bank_txn AS t LEFT JOIN my_bank AS b ON t.bank = b.id WHERE t.order_id='$order_id' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}	
	public function updateTracking($data , $order_info) {
		$query = $this->db->query("UPDATE my_order SET order_status_id='7',track_submit='".date('Y-m-d H:i:s')."', tack_code='".$data['tack_code']."' WHERE order_id='".$data['order_id']."' LIMIT 1");

		$this->load->model('catalog/mail');
		$this->model_catalog_mail->sendingProduct($order_info);

		return $query;
	}	
	public function updateNote($note,$order_id) {
		$query = $this->db->query("update my_order set note_icon='" . (int)$note . "' WHERE order_id = '" . (int)$order_id . "'");

	}	

	public function getDeadline() {
		$sql = "SELECT hour FROM  my_deadline_time  ";
		$query = $this->db->query($sql);
		return $query->row['hour'];
	}
	public function updateDeadline($data) {
		$sql = "update   my_deadline_time  set hour='$data' ";
		$query = $this->db->query($sql);
	}

	public function print_address() {//status = aproved
		$sql = "SELECT CONCAT(shipping_firstname,' ',shipping_lastname) AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4'  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function print_address_e() {
		$sql = "SELECT CONCAT(shipping_firstname,' ',shipping_lastname) AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4' and shipping_method='EMS' and CHAR_LENGTH(send_from) = 0 ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function print_address_r() {
		$sql = "SELECT CONCAT(shipping_firstname,' ',shipping_lastname) AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4' and shipping_method='REGISTER' and CHAR_LENGTH(send_from) = 0  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function print_address_e_n() {
		$sql = "SELECT CONCAT(shipping_firstname,' ',shipping_lastname) AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4' and shipping_method='EMS' and CHAR_LENGTH(send_from) = 0  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function print_address_r_n() {
		$sql = "SELECT CONCAT(shipping_firstname,' ',shipping_lastname) AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4' and shipping_method='REGISTER' and CHAR_LENGTH(send_from) = 0  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function print_address_e_o() {
		$sql = "SELECT send_from AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4' and shipping_method='EMS' and CHAR_LENGTH(send_from) > 0 ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function print_address_r_o() {
		$sql = "SELECT send_from AS name, CONCAT(shipping_address_1,' ',shipping_address_2) AS address, shipping_zone AS province, shipping_postcode AS postcode  ,shipping_method AS shipping_type , order_id AS id FROM my_order where order_status_id='4' and shipping_method='REGISTER' and CHAR_LENGTH(send_from) > 0  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
}
?>