<?php 
class ControllerAccountOrder extends Controller {
	private $error = array();
		
	public function index() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
		
		$this->language->load('account/order');
		$this->load->model('account/order');
 		
		if( isset($this->request->get['my-order']) ) {
			$this->load->model('checkout/order');
			$this->model_checkout_order->checkRollbackCredit( $this->request->get['my-order'] );
			$this->model_account_order->cancelOrder( $this->request->get['my-order'] );
			$this->redirect($this->url->link('account/order'));
		}else{
			// check order expired
			if( $this->model_account_order->checkOrderExpired( ) > 0)
				$this->redirect($this->url->link('account/order'));
		}

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);
			
			if ($order_info) {
				$order_products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);
						
				foreach ($order_products as $order_product) {
					$option_data = array();
							
					$order_options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
							
					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];	
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($order_option['value']);
						}
					}
							
					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->request->get['order_id']);
							
					$this->cart->add($order_product['product_id'], $order_product['quantity'], $option_data);
				}
									
				$this->redirect($this->url->link('checkout/cart'));
			}
		}

    	$this->document->setTitle($this->language->get('heading_title'));
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/order', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_products'] = $this->language->get('text_products');
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_reorder'] = $this->language->get('button_reorder');
		$this->data['button_continue'] = $this->language->get('button_continue');
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		


		$this->data['orders'] = array();
		
		$order_total = $this->model_account_order->getTotalOrders();
		
		$results = $this->model_account_order->getOrders(($page - 1) * 10, 10);
	//print_r($results);
		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
			$date = new DateTime($result['date_added']);
			$original_date = (int)$date->format('d');
			$original_month = (int)$date->format('m') -1; //$date->format('d M Y H:i:s');
			$original_year = (int)$date->format('Y')+543;
			$original_time = $date->format('H:i:s');
			$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;

			$orderdates = $this->model_account_order->getOrderDate($result['order_id']);
			 
			 foreach($orderdates as $tmp){
				$date_add = $tmp['date_added'];
				$order_status_id = $tmp['order_status_id'];
			 }
			$UTC	= new DateTimeZone("UTC");
			$newTZ	= new DateTimeZone("Asia/Bangkok");
			date_default_timezone_set('Asia/Bangkok');
			//$new_date = strtotime('+1 day', strtotime( '2014-09-12 1:11:11' ) )  ;
			$deadline = $this->model_account_order->getDeadLineLimited();
			$new_date = strtotime('+'.$deadline.' hours', strtotime( $date_add   ) )  ;
			$dStart = new DateTime(date('Y-m-d H:i:s', $new_date ) );
			$today	= new DateTime(); 
			$dDiff	= $today->diff( $dStart   );
			//print_r($today); print_r($dStart);print_r($dDiff);echo "<br>";
				
			/*
			1. Order cancel
			2. Order waiting for txn
			3. Order timeout [update status to cancel]
			4. Order finish
			*/
			if($order_status_id == 7){
				$timeout = "รายการถูกยกเลิก ";
				$timeout_status  = "รายการถูกยกเลิก ";
				$my_timepit = "";
				$ostatus="c";

			}elseif($dDiff->invert == 0){
				$timeout = "แจ้งชำระเงิน คลิก";
				$timeout_status = "";
				if((int)$dDiff->format('%h') > 0){
					$my_timepit = "เหลือเวลา ".$dDiff->format('%h')." ชม.  ".$dDiff->format('%i')." นาที";
				}else{
					$my_timepit = "เหลือเวลา ".$dDiff->format('%i')." นาที.  ";
				}
				$ostatus="o";
			}else{
				if($result['status'] == "ส่งของแล้ว"){
					$timeout = "ส่งของแล้ว ";
					$timeout_status  = "รายการถูกยกเลิก ";
					$my_timepit = "";
					$ostatus="c";
				}else{
					$timeout = "รายการถูกยกเลิก ";
					$timeout_status  = "รายการถูกยกเลิก ";
					$my_timepit = "";
					$ostatus="c";

					$this->model_account_order->cancelOrderExpired($result['order_id']);
					//$this->redirect($this->url->link('account/order'));
				}


			}
			$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;


 
			$this->data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'status_id'	 => $result['order_status_id'],
				'ostatus'     => $ostatus,
				'date_added' => $new_date , //date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'timeout'	=> $timeout ,
				'timeout_time' => $my_timepit ,
				'timeout_status' => $timeout_status ,
				'products'   => ($product_total + $voucher_total),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL'),
				'reorder'    => $this->url->link('account/order', 'order_id=' . $result['order_id'], 'SSL')
			);
		}

		// =================== CANCEL ORDER ===================
		if(isset($this->request->get['my-order'])) print_r( $this->request->get['my-order'] ) ;
		

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/order', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/order_list.tpl';
		} else {
			$this->template = 'default/template/account/order_list.tpl';
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
	
	public function info() { 
		$this->language->load('account/order');
		$order_id = (isset($this->request->get['order_id'])) ? $this->request->get['order_id'] : 0 ;

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
			
		$this->load->model('account/order');
		$this->data['order_status'] = $this->model_account_order->getOrderStatus($order_id);
		$order_info = $this->model_account_order->getOrder($order_id);
		$product_info = $this->model_account_order->getProductFromOrder($order_info['order_id']);

		$this->data['error_date'] = 0;
		if( isset($this->request->post['money']) && ($this->request->post['money'] > 0) ){
			if($this->validate() ){

				$txndate = $this->request->post['mydateYear']."-".$this->request->post['mydateMonth']."-".$this->request->post['mydateDay'];
				$txntime =  $this->request->post['mytimeHour'].":".$this->request->post['mytimeMinute'].":".$this->request->post['mytimeSecond'];

				$this->model_account_order->save_banktxn($order_id , $txndate ,$txntime ,$this->request->post['bank'] ,$this->request->post['money'] ,$this->request->post['remark'] );
				
				$this->model_account_order->updateOrder( $order_id ,'2' , $this->request->post['remark'] ); //FOR TEST 5=completed , real 2=processing
				$this->redirect($this->url->link('account/order'));
			} else{
				$this->data['error_date'] = 1;
			}
		}

		$this->data['text_product_id'] = "";
		$this->data['text_product_img'] = "";
		$this->data['product_id'] = $product_info['product_id'];
		$this->data['product_img'] = $product_info['product_img'];
		$this->data['status_id'] = $product_info['status_id'];
		$this->data['product_link'] = $this->url->link('product/product',   '&product_id=' . $product_info['product_id']  );
			
		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));
			
			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),        	
				'separator' => false
			); 
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),        	
				'separator' => $this->language->get('text_separator')
			);
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', $url, 'SSL'),      	
				'separator' => $this->language->get('text_separator')
			);
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
	
      		$this->data['heading_title'] = $this->language->get('text_order');
			
			$this->data['text_order_detail'] = $this->language->get('text_order_detail');
			$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
    		$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
      		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$this->data['text_shipping_address'] = $this->language->get('text_shipping_address');
      		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
      		$this->data['text_payment_address'] = $this->language->get('text_payment_address');
      		$this->data['text_history'] = $this->language->get('text_history');
			$this->data['text_comment'] = $this->language->get('text_comment');

      		$this->data['column_name'] = $this->language->get('column_name');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_price'] = $this->language->get('column_price');
      		$this->data['column_total'] = $this->language->get('column_total');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');
      		$this->data['column_comment'] = $this->language->get('column_comment');
			
			$this->data['button_return'] = $this->language->get('button_return');
      		$this->data['button_continue'] = $this->language->get('button_continue');
		

			$this->data['invoice_no'] = ($order_info['invoice_no']) ? $order_info['invoice_prefix'] . $order_info['invoice_no'] : '';
			$this->data['order_id'] = $this->request->get['order_id'];

			$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
			$date = new DateTime($order_info['date_added']);
			$original_date = (int)$date->format('d');
			$original_month = (int)$date->format('m') - 1; //$date->format('d M Y H:i:s');
			$original_year = (int)$date->format('Y')+543;
			$original_time = $date->format('H:i:s');
			$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;


			$this->data['date_added'] = $new_date; //date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			
			if ($order_info['payment_address_format']) {
      			$format = $order_info['payment_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}' . "\n" . '{sendfrom}';
			}
    		$find = array('{firstname}','{lastname}','{company}','{address_1}','{address_2}','{city}','{postcode}','{zone}','{zone_code}','{country}','{sendfrom}');
			$send_from = ( isset($order_info['send_from']) && ( strlen($order_info['send_from']) > 1  ) ) ? "ส่งในนาม ".$order_info['send_from'] : "";
			$replace = array(
	  			'firstname' => $order_info['payment_firstname'],
	  			'lastname'  => $order_info['payment_lastname'],
	  			'company'   => $order_info['payment_company'],
      			'address_1' => $order_info['payment_address_1'],
      			'address_2' => $order_info['payment_address_2'],
      			'city'      => $order_info['payment_city'],
      			'postcode'  => $order_info['payment_postcode'],
      			'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
      			'country'   => $order_info['payment_country'],
				'sendfrom'  => $send_from
			);
			$this->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
      		$this->data['payment_method'] = $order_info['payment_method'];
			
			if ($order_info['shipping_address_format']) {
      			$format = $order_info['shipping_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}'. "\n" . '{sendfrom}';
			}
    		$find = array('{firstname}','{lastname}','{company}','{address_1}','{address_2}','{city}','{postcode}','{zone}','{zone_code}','{country}','{sendfrom}');
	
			$replace = array(
	  			'firstname' => $order_info['shipping_firstname'],
	  			'lastname'  => $order_info['shipping_lastname'],
	  			'company'   => $order_info['shipping_company'],
      			'address_1' => $order_info['shipping_address_1'],
      			'address_2' => $order_info['shipping_address_2'],
      			'city'      => $order_info['shipping_city'],
      			'postcode'  => $order_info['shipping_postcode'],
      			'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
      			'country'   => $order_info['shipping_country']  ,
				'sendfrom'  => $send_from
			);

			$this->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
			$this->data['shipping_method'] = $order_info['shipping_method'];
			$this->data['link_edit_address'] = $this->url->link('account/order/address',   '&order_id=' . $order_id  );
			$this->data['link_edit_product'] = $this->url->link('account/order/product',   '&order_id=' . $order_id  );

			$this->load->model('tool/image');
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];


			$orderdates = $this->model_account_order->getOrderDate($this->request->get['order_id']);
			 
			 foreach($orderdates as $tmp){
				$order_status_id = $tmp['order_status_id'];
			 }
			$this->data['order_status_id'] =  $order_status_id;

			$this->data['products'] = array();
			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

      		foreach ($products as $product) {
				$option_data = array();
				$p = $this->model_account_order->getProductDetail($product['product_id']);
				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				
				foreach($options as $p2){
					if($p2['name'] == 'Color') 
						$img = $this->model_account_order->getImagefromColorOption($product['product_id'] ,$p2['value']);		
				}
				$image = (strlen($img) > 1) ? $this->model_tool_image->resize($img, 100, 150) : $this->model_tool_image->resize($p[0]['image'], 100, 150);
			 

         		foreach ($options as $option) {
					$value = $option['value'];
				
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);					
        		}
 
        		$this->data['products'][] = array(
					'id'	   => $product['product_id'],
          			'name'     => $product['name'],
          			'model'    => $product['model'],
					'img'	   => $image, //HTTP_SERVER."image/".$p[0]['image'],
					'link'	  =>  $this->url->link('product/product',   '&product_id=' . $product['product_id']  ),
          			'option'   => $option_data,
          			'quantity' => $product['qo'],
          			'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'       => $this->url->link('account/order/info', 'order_id=' . $order_info['order_id'], 'SSL'),
					'return'   => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
        		);
      		}

			// Voucher
			$this->data['vouchers'] = array();
			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);
			foreach ($vouchers as $voucher) {
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}
			
      		$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
			$this->data['comment'] = nl2br($order_info['comment']);
			$this->data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);
      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
		
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/order_info.tpl';
			} else {
				$this->template = 'default/template/account/order_info.tpl';
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
    	} else {
			$this->document->setTitle($this->language->get('text_order'));
			
      		$this->data['heading_title'] = $this->language->get('text_order');
      		$this->data['text_error'] = $this->language->get('text_error');
      		$this->data['button_continue'] = $this->language->get('button_continue');
		
			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
												
      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
			 			
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];

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




	public function product() { 
		$this->language->load('account/order');
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}	

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');
			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
			
		if (!empty($this->request->post['quantity'])) {
			 
			foreach ($this->request->post['quantity'] as $key => $value) {
				print_r($key);echo "///////";print_r($value);echo "<br> ";
				//$this->cart->update($key, $value);
			}
		}
		// Remove
		if (isset($this->request->get['remove'])) {
			/*REMOVE
			== SELECT my_order_product AS op INNER JOIN my_order_option  (find order_product_id)
			//INSERT my_order_history (activities LOGS)
			//UPDATE my_order -> total
			//UPDATE POINT (New)
			//UPDATE CREDIT (New)
			//UPDATE my_order_total -> sub_total, weight, total
			//DELETE my_order_product (quantity) BY order_product_id
			//DELETE my_order_option (quantity) BY order_product_id*/

			list($t_oid, $t_pid, $t_options ) = explode(":", $this->request->get['remove']);
			$t_option = unserialize(base64_decode($t_options));

			//Remove order has only 1 product.
			$this->load->model('account/order');
			$t_p = $this->model_account_order->getOrderProducts($t_oid );
			$count_product = count($t_p);
			if($count_product == 1){ //cancel order
				$this->redirect($this->url->link('account/order', 'my-order='.$t_oid , 'SSL'));
			}


			//Remove order has many products.
			$shipping_methods = $this->model_account_order->getOrdershipping_methods($t_oid );
			$g_product = $this->model_account_order->getOrderProductOption($t_oid, $t_pid, $t_option );
			//print_r($g_product);
			//print_r($g_product['order_product_id']);
			//print_r( "[removed product price]=".$g_product['total'] );


			//TOTAL WEIGHT
			$t_total_product_order = $this->model_account_order->getOrderProducts($t_oid);
			$ttt_weight = 0;
			foreach($t_total_product_order as $key){
				$tt_weight = $this->model_account_order->getProductWeight($key['product_id']);
				$ttt_weight = $ttt_weight + ( $tt_weight * $key['quantity'] ) ;
			}
			$weight = $this->model_account_order->getProductWeight($t_pid );
			$total_weights =  $g_product['quantity'] *  $weight;
			$new_weight = $ttt_weight - $total_weights;

			$this->load->model('catalog/product');
			$pp = $this->model_catalog_product->getProductWeight($new_weight , $shipping_methods);
			$this->data['shipping_price'] = abs($pp)." ฿"; 


			$g_total = $this->model_account_order->getOrderTotals($t_oid );
			foreach($g_total as $key ){
				if ($key['code'] == "sub_total" ){
					$gt_stotal = $key['value'];
				}elseif( $key['code'] == "shipping" ){
					$gt_ship = $key['value'];
				}elseif( $key['code'] == "total" ){
					$gt_total = $key['value'];
				}
			}

			$this->load->model('checkout/order');
			$used_credit_tmp = $this->model_checkout_order->rollbackSomeCredit( $t_oid , $g_product['total']   );
			$used_credit	= $used_credit_tmp[0];
			$remove_credit	= $used_credit_tmp[1];

			$new_sub_total	= $gt_stotal - $g_product['total'] ; //+ $used_credit;
			$new_total		= $gt_total - $gt_ship + $pp - $g_product['total'] + $used_credit;
			$new_credit		= $used_credit - $g_product['total'];

			if($remove_credit)
				$this->model_account_order->deleteOrderTotal($t_oid,"credit_discount" );
			else
				$this->model_account_order->updateOrderTotal($t_oid,"credit_discount", ($new_credit ) , (abs( $new_credit )." ฿") );

			$this->model_account_order->updateOrderTotal($t_oid,"sub_total", ($new_sub_total ) , (abs( $new_sub_total )." ฿") );
			$this->model_account_order->updateOrderTotal($t_oid,"shipping", $pp , (abs( $pp )." ฿") );
			$this->model_account_order->updateOrderTotal($t_oid,"total", ($new_total ) , (abs( ($new_total ) )." ฿") );

			$this->model_account_order->updateOrderminiTotal( $t_oid,  ($new_total )    );


			$this->model_account_order->deleteOrderProduct($g_product['order_product_id']);

			$this->redirect($this->url->link('account/order/product', 'order_id='.$t_oid , 'SSL'));
			//echo ($gt_stotal   )." ".($gt_ship  )." ".($gt_total   )."<br>";
			//echo ($g_product['total']   )." ".($pp  )."<br>";
			//echo ($gt_stotal - $g_product['total'] )." ".( $pp)." ".($gt_total - $gt_ship + $pp - $g_product['total'] );
			//print_r($g_total);
			//echo abs( $gt_stotal - $g_product['total'] )." ฿"; 

			
			//print_r($g_total);
			//$this->redirect($this->url->link('account/order/product', '&order_id=' . $t_oid, 'SSL'));
			//$this->cart->remove($this->request->get['remove']);
			//$this->session->data['success'] = $this->language->get('text_remove');
		}
 

		$this->load->model('account/order');
		$this->data['order_status'] = $this->model_account_order->getOrderStatus($order_id);
		$order_info = $this->model_account_order->getOrder($order_id);
		$product_info = $this->model_account_order->getProductFromOrder($order_info['order_id']);


		//print_r( $product_info  );	
		if( isset($this->request->post['money']) && ($this->request->post['money'] > 0) ){
			if($this->validate() ){

				$txndate = $this->request->post['mydateYear']."-".$this->request->post['mydateMonth']."-".$this->request->post['mydateDay'];
				$txntime =  $this->request->post['mytimeHour'].":".$this->request->post['mytimeMinute'].":".$this->request->post['mytimeSecond'];

				$this->model_account_order->save_banktxn($order_id , $txndate , $txntime , $this->request->post['bank'] , $this->request->post['money'] , $this->request->post['remark'] );
				
				$this->model_account_order->updateOrder( $order_id ,'2'); //FOR TEST 5=completed , real 2=processing
				$this->redirect($this->url->link('account/order'));
			} else{
				//echo "false";
			}
		}

		$this->data['text_product_id'] = "";
		$this->data['text_product_img'] = "";
		$this->data['product_id'] = $product_info['product_id'];
		$this->data['product_img'] = $product_info['product_img'];
		$this->data['status_id'] = $product_info['status_id'];
		$this->data['product_link'] = $this->url->link('product/product',   '&product_id=' . $product_info['product_id']  );
			
		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));
			
			$this->data['breadcrumbs'] = array();
		
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),        	
				'separator' => false
			); 
		
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),        	
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', $url, 'SSL'),      	
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
					
      		$this->data['heading_title'] = $this->language->get('text_order');
			
			$this->data['text_order_detail'] = $this->language->get('text_order_detail');
			$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
    		$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
      		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$this->data['text_shipping_address'] = $this->language->get('text_shipping_address');
      		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
      		$this->data['text_payment_address'] = $this->language->get('text_payment_address');
      		$this->data['text_history'] = $this->language->get('text_history');
			$this->data['text_comment'] = $this->language->get('text_comment');

      		$this->data['column_name'] = $this->language->get('column_name');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_price'] = $this->language->get('column_price');
      		$this->data['column_total'] = $this->language->get('column_total');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');
      		$this->data['column_comment'] = $this->language->get('column_comment');
			
			$this->data['button_return'] = $this->language->get('button_return');
      		$this->data['button_continue'] = $this->language->get('button_continue');
		
			$this->data['return_url'] = $this->url->link('account/order/product', 'order_id=' . $order_info['order_id']  , 'SSL');

			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}
			
			$this->data['order_id'] = $this->request->get['order_id'];

			$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
			$date = new DateTime($order_info['date_added']);
			$original_date = (int)$date->format('d');
			$original_month = (int)$date->format('m') - 1; //$date->format('d M Y H:i:s');
			$original_year = (int)$date->format('Y')+543;
			$original_time = $date->format('H:i:s');
			$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;

			$this->data['date_added'] = $new_date; //date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			$this->data['shipping_method'] = $order_info['shipping_method'];


			$this->data['products'] = array();
			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

      		foreach ($products as $product) {
				$option_data = array();
				//echo $product['order_product_id']."<br><br>";
				$p = $this->model_account_order->getProductDetail($product['product_id']);
				//print_r($p[0]['image']); echo "<br><br>";
				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
					}
					
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);					
        		}

				//Created KEY
				foreach ($options as $option) {
					$option_2[$option['product_option_id']] = $option['product_option_value_id'];
				}
				$key = (int)$product['product_id'] . ':' . base64_encode(serialize($option_2));

				 
        		$this->data['products'][] = array(
					'id'	   => $product['product_id'],
          			'name'     => $product['name'],
					'key'	   => $key,
          			'model'    => $product['model'],
					'img'	   => HTTP_SERVER."image/".$p[0]['image'],
					'link'	  =>  $this->url->link('product/product',   '&product_id=' . $product['product_id']  ),
          			'option'   => $option_data,
          			'quantity' => $product['quantity'],
          			'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'       => $this->url->link('account/order/info', 'order_id=' . $order_info['order_id'], 'SSL'),
					'return'   => $this->url->link('account/return/product', 'order_id=' . $order_info['order_id']  , 'SSL'),
					'remove'   => $this->url->link('account/order/product', 'remove=' . $order_info['order_id'].":".$key)
        		);
      		}


      		$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
		
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_product.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/order_product.tpl';
			} else {
				$this->template = 'default/template/account/order_product.tpl';
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
    	} else {
			$this->document->setTitle($this->language->get('text_order'));
			
      		$this->data['heading_title'] = $this->language->get('text_order');
      		$this->data['text_error'] = $this->language->get('text_error');
      		$this->data['button_continue'] = $this->language->get('button_continue');
		
			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
												
      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
			 	
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];		

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



	public function address() { 
		$this->language->load('account/order');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} elseif (isset($this->request->post['order_id'])) {
			$order_id = $this->request->post['order_id'];
		} else {
			$order_id = 0;
		}	
//print_r($this->request->get);
//print_r($this->request->post['order_id']);
//exit;
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');
			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
			
		//if( $order_id == 0 ) $this->redirect($this->url->link('account/order', '', 'SSL'));
 
		if ( isset($this->request->post['firstname']) && (strlen($this->request->post['firstname']) > 1) ) {
		
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

			if ($this->request->post['country_id'] == '') {
				$this->data['error']['country'] = $this->language->get('error_country');
			}else{
				$this->data['data_country'] = $this->request->post['country_id'];
			}	
			
			if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
				$this->data['error']['zone'] = $this->language->get('error_zone');
			}else{
				$this->data['data_zone'] = $this->request->post['zone_id'];
			}	

			//Send From ==> other people buy and send by them name........
			
			if( isset($this->data['error'])  ){
	
			}else{
				// Default Payment Address
				$this->load->model('account/address');
				 $this->model_account_address->updatePaymentAddress($this->request->post);
				$this->redirect($this->url->link('account/order/info', 'order_id='.$order_id, 'SSL'));
				//$this->redirect($this->url->link('account/order/address', 'order_id='.$order_id, 'SSL'));
				//$this->redirect($this->url->link('account/order', '', 'SSL'));
			}

			
		}


		$this->load->model('account/order');

		$this->data['order_status'] = $this->model_account_order->getOrderStatus($order_id);
		$order_info = $this->model_account_order->getOrder($order_id);
		$product_info = $this->model_account_order->getProductFromOrder($order_info['order_id']);
 
		$this->data['text_product_id'] = "";
		$this->data['text_product_img'] = "";
		$this->data['product_id'] = $product_info['product_id'];
		$this->data['product_img'] = $product_info['product_img'];
		$this->data['status_id'] = $product_info['status_id'];
		$this->data['product_link'] = $this->url->link('product/product',   '&product_id=' . $product_info['product_id']  );
			
 
			$this->document->setTitle($this->language->get('text_order'));
			
			$this->data['breadcrumbs'] = array();
		
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),        	
				'separator' => false
			); 
		
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),        	
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', $url, 'SSL'),      	
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
					
      		$this->data['heading_title'] = $this->language->get('text_order');
			
			$this->data['text_order_detail'] = $this->language->get('text_order_detail');
			$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
    		$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
      		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$this->data['text_shipping_address'] = $this->language->get('text_shipping_address');
      		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
      		$this->data['text_payment_address'] = $this->language->get('text_payment_address');
      		$this->data['text_history'] = $this->language->get('text_history');
			$this->data['text_comment'] = $this->language->get('text_comment');
			$this->data['text_select'] = "";
			$this->data['text_none'] = "";

      		$this->data['column_name'] = $this->language->get('column_name');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_price'] = $this->language->get('column_price');
      		$this->data['column_total'] = $this->language->get('column_total');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');
      		$this->data['column_comment'] = $this->language->get('column_comment');
			
			$this->data['button_return'] = $this->language->get('button_return');
      		$this->data['button_continue'] = $this->language->get('button_continue');
		
			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			if (isset($order_info['payment_zone_id'])) {
				$this->data['zone_id'] = $order_info['payment_zone_id'];		
			} else {
				$this->data['zone_id'] = '';
			}
			if( $this->session->data['vender'] == 1 ){
				$this->data['vender'] = '1';
			}else{
				$this->data['vender'] = '0';
			}

			//$this->data['order_id'] = $this->request->get['order_id'];
			if (isset($this->request->get['order_id'])) {
				$this->data['order_id'] = $this->request->get['order_id'];
			} elseif (isset($this->request->post['order_id'])) {
				$this->data['order_id'] = $this->request->post['order_id'];
			} else {
				$this->data['order_id'] = 0;
			}	

			$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
			$date = new DateTime($order_info['date_added']);
			$original_date = (int)$date->format('d');
			$original_month = (int)$date->format('m') - 1; //$date->format('d M Y H:i:s');
			$original_year = (int)$date->format('Y')+543;
			$original_time = $date->format('H:i:s');
			$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;


			$this->data['date_added'] = $new_date; //date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

      		$this->data['payment_method'] = $order_info['payment_method'];
			$this->data['shipping_method'] = $order_info['shipping_method'];

			$this->data['firstname'] = $order_info['payment_firstname'];
			$this->data['lastname'] = $order_info['payment_lastname'];
			$this->data['company'] = $order_info['payment_company'];		
			$this->data['address_1'] = $order_info['payment_address_1'];
			$this->data['address_2'] = $order_info['payment_address_2'];
			$this->data['city'] = $order_info['payment_city'];
			$this->data['postcode'] = $order_info['payment_postcode'];
			$this->data['zone'] = $order_info['payment_zone'];
			$this->data['zone_code'] = $order_info['payment_zone_code'];
			$this->data['country'] = $order_info['payment_country'];
			$this->data['send_from'] = $order_info['send_from'];




			$this->data['products'] = array();
			
			$products = $this->model_account_order->getOrderProducts($order_id);

      		foreach ($products as $product) {
				$option_data = array();
				$p = $this->model_account_order->getProductDetail($product['product_id']);
				$options = $this->model_account_order->getOrderOptions($this->data['order_id'], $product['order_product_id']);

         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
					}
					
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);					
        		}

        		$this->data['products'][] = array(
					'id'	   => $product['product_id'],
          			'name'     => $product['name'],
          			'model'    => $product['model'],
					'img'	   => HTTP_SERVER."image/".$p[0]['image'],
					'link'	  =>  $this->url->link('product/product',   '&product_id=' . $product['product_id']  ),
          			'option'   => $option_data,
          			'quantity' => $product['quantity'],
          			'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'       => $this->url->link('account/order/info', 'order_id=' . $order_info['order_id'], 'SSL'),
					'return'   => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
        		);
      		}

			// Voucher
			$this->data['vouchers'] = array();
			
			$vouchers = $this->model_account_order->getOrderVouchers($order_id);
			
			foreach ($vouchers as $voucher) {
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}
			
      		$this->data['totals'] = $this->model_account_order->getOrderTotals($order_id);
			$this->data['comment'] = nl2br($order_info['comment']);
			$this->data['histories'] = array();
			$results = $this->model_account_order->getOrderHistories($order_id);

      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
		
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_address.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/order_address.tpl';
			} else {
				$this->template = 'default/template/account/order_address.tpl';
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




	public function validate(){
		if (   !empty($this->request->post['money']) && !empty($this->request->post['bank'])   ) {
			
			$txndate = ($this->request->post['mydateYear']-543)."-".$this->request->post['mydateMonth']."-".$this->request->post['mydateDay']." ".$this->request->post['mytimeHour'].":".$this->request->post['mytimeMinute'].":".$this->request->post['mytimeSecond'];
			$now = date('Y-m-d H:i:s');
		
			$ts1 = strtotime($txndate);
			$ts2 = strtotime($now);
			$seconds_diff = $ts2 - $ts1;
			$diffdate =  $seconds_diff/3600 ;
			
			if( ($txndate < $now)&&($diffdate < 5)  ){
				//echo $txndate." ".$now." ".$diffdate;
				return true;
			}else{
				//echo $txndate." ".$now." ".$diffdate;
				return false;
			}

			
		} else {
			return false;
		}
	}
}
?>