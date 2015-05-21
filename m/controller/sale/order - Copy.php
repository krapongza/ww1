<?php
class ControllerSaleOrder extends Controller {
	private $error = array();

  	public function index() {
		$this->language->load('sale/order');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/order');
    	$this->getList();
  	}
	public function getURL() {
		$url = '';
		if (isset($this->request->get['filter_order_id'])) $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		if (isset($this->request->get['filter_customer'])) $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		if (isset($this->request->get['filter_total'])) $url .= '&filter_total=' . $this->request->get['filter_total'];
		if (isset($this->request->get['filter_date_added'])) $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		if (isset($this->request->get['filter_date_modified'])) $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
		if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
		return $url;
	}
  	public function insert() {
		$this->language->load('sale/order');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/order');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
      	  	$this->model_sale_order->addOrder($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = $this->getURL();
			$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getForm();
  	}
  	public function updateList() {
		$this->load->model('sale/order');
		//print_r($this->request->post);

		if (  ($this->request->server['REQUEST_METHOD'] == 'POST')&& ($this->validateUpdateList())   ) {
			$this->model_sale_order->updateOrderStatus( $this->request->post  );
			$this->session->data['success'] = $this->language->get('text_success');
			$url = $this->getURL();
			$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
  	}
	public function validateUpdateList(){
    	if (  strlen($this->request->post['change_type_status']) < 1)   $this->error['updatelist'] = "error1";
    	if (  count($this->request->post['selected']) < 1  ) $this->error['updatelist'] = "error2";
		return (!$this->error) ? true : false;
	}
  	public function update() {
		$this->language->load('sale/order');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/order');
    	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_sale_order->editOrder($this->request->get['order_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = $this->getURL();
			$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getForm();
  	}
	
  	public function delete() {
		$this->language->load('sale/order');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/order');

    	if (isset($this->request->post['selected']) && ($this->validateDelete())) {
			foreach ($this->request->post['selected'] as $order_id) $this->model_sale_order->deleteOrder($order_id);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = $this->getURL();
			$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
    	$this->getList();
  	}
	protected function getminiList() {
		$url = '';
		if (isset($this->request->get['filter_customer'])) $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));	
		if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		if (isset($this->request->get['filter_total'])) $url .= '&filter_total=' . $this->request->get['filter_total'];	
		if (isset($this->request->get['filter_bank'])) $url .= '&filter_bank=' . $this->request->get['filter_bank'];	
		if (isset($this->request->get['filter_paypal'])) $url .= '&filter_paypal=' . $this->request->get['filter_paypal'];	
		if (isset($this->request->get['filter_date_added'])) $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		return $url;
	}
  	protected function getList() {

		$filter_customer		= (isset($this->request->get['filter_customer']))  ? $this->request->get['filter_customer'] : null;
		$filter_order_status_id = (isset($this->request->get['filter_order_status_id'])) ? $this->request->get['filter_order_status_id'] : null;
		$filter_total			= (isset($this->request->get['filter_total']))  ? $this->request->get['filter_total'] : null;
		$filter_bank			= (isset($this->request->get['filter_bank']))  ? $this->request->get['filter_bank'] : null;
		$filter_date_added		= (isset($this->request->get['filter_date_added'])) ? $this->request->get['filter_date_added'] : null;
		$sort					= (isset($this->request->get['sort'])) ? $this->request->get['sort'] : 'o.order_id';
		$order					= (isset($this->request->get['order'])) ? $this->request->get['order'] : 'DESC';
		$page					= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
		$url = $this->getminiList();
		if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
		if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
 
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['invoice'] = $this->url->link('sale/order/invoice', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['insert'] = $this->url->link('sale/order/insert', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['update'] = $this->url->link('sale/order/updateList', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['delete'] = $this->url->link('sale/order/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['orders'] = array();
		$data = array(
			'filter_customer'	     => $filter_customer,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_total'           => $filter_total,
			'filter_bank'            => $filter_bank,
			'filter_date_added'      => $filter_date_added,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);

		$order_total = $this->model_sale_order->getTotalOrders($data);
		$results = $this->model_sale_order->getOrders($data);

    	foreach ($results as $result) {
			$action = array();
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL'),
				//'href' => $this->url->link('sale/order/update', 'token=' . $this->session->data['token'] . '&id=' . $result['order_id'] . $url, 'SSL'),
				'htext' => 'Note',
				'hnote' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url."#note", 'SSL'),
				//'hnote' => $this->url->link('sale/order/update', 'token=' . $this->session->data['token'] . '&id=' . $result['order_id'] . $url."#note", 'SSL')
				'atext' => 'Address',
				'ahref' => $this->url->link('sale/order/orderaddress', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL')
			);

			$note_icon = ( $result['note_icon'] > 0) ? $result['note_icon'] : '0';
			$c1 = "<span style='color:red;font-weight:bold;'>";$c2="</span>";
			$this->data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'status'        => $result['status'],
				'email'			=> $result['email'],
				'province'      => $result['province'],
				'ip'			=> $result['ip'],
				'point_status'  => ($result['point_status'] == 'yes') ?  $c1.$result['point_status'].$c2 : $result['point_status'],
				'credit'        => ($result['credit'] == 'yes') ?  $c1.$result['credit'].$c2 : $result['credit'],
				'paysbuy'       => ($result['paysbuy'] == 'yes') ?  $c1.$result['paysbuy'].$c2 : $result['paysbuy'],
				'paypal'        => ($result['paypal'] == 'yes') ?  $c1.$result['paypal'].$c2 : $result['paypal'],
				'flag'			=> $note_icon,
				'send_from'		=> (strlen($result['send_from']) > 0 ) ? $c1.'yes'.$c2 : 'no' ,
				'bank'			=> $result['banks'],
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['order_id'], $this->request->post['selected']),
				'action'        => $action
			);
		}

		$this->data['deadline'] = $this->model_sale_order->getDeadline();

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_missing'] = $this->language->get('text_missing');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_invoice'] = $this->language->get('button_invoice');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');

		$this->data['token'] = $this->session->data['token'];
		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = $this->getminiList();
		$url .= ($order == 'ASC') ? '&order=DESC' : '&order=ASC';
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
		$this->data['sort_status'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_total'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=o.total' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=o.date_added' . $url, 'SSL');

		$url = $this->getminiList();
		if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
		if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
		$this->data['filter_total'] = $filter_total;
		$this->data['filter_bank'] = $filter_bank;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->load->model('localisation/order_status');
    	$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/order_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
  	}

  	public function getForm() {
		$this->load->model('sale/customer');
				
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');  
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_product'] = $this->language->get('text_product');
		$this->data['text_voucher'] = $this->language->get('text_voucher');
		$this->data['text_order'] = $this->language->get('text_order');
		
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_customer'] = $this->language->get('entry_customer');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_fax'] = $this->language->get('entry_fax');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_comment'] = $this->language->get('entry_comment');	
		$this->data['entry_affiliate'] = $this->language->get('entry_affiliate');
		$this->data['entry_address'] = $this->language->get('entry_address');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_company_id'] = $this->language->get('entry_company_id');
		$this->data['entry_tax_id'] = $this->language->get('entry_tax_id');
		$this->data['entry_address_1'] = $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = $this->language->get('entry_address_2');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_zone'] = $this->language->get('entry_zone');
		$this->data['entry_zone_code'] = $this->language->get('entry_zone_code');
		$this->data['entry_country'] = $this->language->get('entry_country');		
		$this->data['entry_product'] = $this->language->get('entry_product');
		$this->data['entry_option'] = $this->language->get('entry_option');
		$this->data['entry_quantity'] = $this->language->get('entry_quantity');
		$this->data['entry_to_name'] = $this->language->get('entry_to_name');
		$this->data['entry_to_email'] = $this->language->get('entry_to_email');
		$this->data['entry_from_name'] = $this->language->get('entry_from_name');
		$this->data['entry_from_email'] = $this->language->get('entry_from_email');
		$this->data['entry_theme'] = $this->language->get('entry_theme');	
		$this->data['entry_message'] = $this->language->get('entry_message');
		$this->data['entry_amount'] = $this->language->get('entry_amount');
		$this->data['entry_shipping'] = $this->language->get('entry_shipping');
		$this->data['entry_payment'] = $this->language->get('entry_payment');
		$this->data['entry_voucher'] = $this->language->get('entry_voucher');
		$this->data['entry_coupon'] = $this->language->get('entry_coupon');
		$this->data['entry_reward'] = $this->language->get('entry_reward');

		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_total'] = $this->language->get('column_total');
			
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_product'] = $this->language->get('button_add_product');
		$this->data['button_add_voucher'] = $this->language->get('button_add_voucher');
		$this->data['button_update_total'] = $this->language->get('button_update_total');
		$this->data['button_remove'] = $this->language->get('button_remove');
		$this->data['button_upload'] = $this->language->get('button_upload');

		$this->data['tab_order'] = $this->language->get('tab_order');
		$this->data['tab_customer'] = $this->language->get('tab_customer');
		$this->data['tab_payment'] = $this->language->get('tab_payment');
		$this->data['tab_shipping'] = $this->language->get('tab_shipping');
		$this->data['tab_product'] = $this->language->get('tab_product');
		$this->data['tab_voucher'] = $this->language->get('tab_voucher');
		$this->data['tab_total'] = $this->language->get('tab_total');

 
		$this->data['error_warning'] = (isset($this->error['warning']))  ? $this->error['warning'] : '' ;
		$this->data['error_firstname'] = (isset($this->error['firstname'])) ? $this->error['firstname'] : '';
		$this->data['error_lastname'] = (isset($this->error['lastname'])) ? $this->error['lastname'] : '';
		$this->data['error_email'] = (isset($this->error['email'])) ? $this->error['email'] : '';
		$this->data['error_telephone'] = (isset($this->error['telephone'])) ? $this->error['telephone'] : '';
		$this->data['error_payment_firstname'] = (isset($this->error['payment_firstname'])) ? $this->error['payment_firstname'] : '';
		$this->data['error_payment_lastname'] = (isset($this->error['payment_lastname'])) ? $this->error['payment_lastname'] : '';
		$this->data['error_payment_address_1'] = (isset($this->error['payment_address_1'])) ? $this->error['payment_address_1'] : '';
		$this->data['error_payment_city'] = (isset($this->error['payment_city'])) ? $this->error['payment_city'] : '';
		$this->data['error_payment_postcode'] = (isset($this->error['payment_postcode'])) ? $this->error['payment_postcode'] : '';
		$this->data['error_payment_tax_id'] = (isset($this->error['payment_tax_id'])) ? $this->error['payment_tax_id'] : '';
		$this->data['error_payment_country'] = (isset($this->error['payment_country'])) ? $this->error['payment_country'] : '';
		$this->data['error_payment_zone'] = (isset($this->error['payment_zone'])) ? $this->error['payment_zone'] : '';
		$this->data['error_payment_method'] = (isset($this->error['payment_method'])) ? $this->error['payment_method'] : '';
		$this->data['error_shipping_firstname'] = (isset($this->error['shipping_firstname'])) ? $this->error['shipping_firstname'] : '';
		$this->data['error_shipping_lastname'] = (isset($this->error['shipping_lastname'])) ? $this->error['shipping_lastname'] : '';
		$this->data['error_shipping_address_1'] = (isset($this->error['shipping_address_1'])) ? $this->error['shipping_address_1'] : '';
		$this->data['error_shipping_city'] = (isset($this->error['shipping_city'])) ? $this->error['shipping_city'] : '';
		$this->data['error_shipping_postcode'] = (isset($this->error['shipping_postcode'])) ? $this->error['shipping_postcode'] : '';
		$this->data['error_shipping_country'] = (isset($this->error['shipping_country'])) ? $this->error['shipping_country'] : '';
		$this->data['error_shipping_zone'] = (isset($this->error['shipping_zone'])) ? $this->error['shipping_zone'] : '';
		$this->data['error_shipping_method'] = (isset($this->error['shipping_method'])) ? $this->error['shipping_method'] : '';

			
								

		$url = $this->getURL();
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'),				
			'separator' => ' :: '
		);

		if (!isset($this->request->get['order_id'])) {
			$this->data['action'] = $this->url->link('sale/order/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/order/update', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'] . $url, 'SSL');
		}
		$this->data['cancel'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL');

    	if (isset($this->request->get['order_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
    	}
		$this->data['token'] = $this->session->data['token'];

		$this->data['order_id'] = (isset($this->request->get['order_id'])) ? $this->request->get['order_id'] : 0 ;
    	if (isset($this->request->post['store_id'])) {
      		$this->data['store_id'] = $this->request->post['store_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['store_id'] = $order_info['store_id'];
		} else {
      		$this->data['store_id'] = '';
    	}
		
		$this->load->model('setting/store');
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['store_url'] = HTTPS_CATALOG;
		} else {
			$this->data['store_url'] = HTTP_CATALOG;
		}
		
		if (isset($this->request->post['customer'])) {
			$this->data['customer'] = $this->request->post['customer'];
		} elseif (!empty($order_info)) {
			$this->data['customer'] = $order_info['customer'];
		} else {
			$this->data['customer'] = '';
		}	
		if (isset($this->request->post['customer_id'])) {
			$this->data['customer_id'] = $this->request->post['customer_id'];
		} elseif (!empty($order_info)) {
			$this->data['customer_id'] = $order_info['customer_id'];
		} else {
			$this->data['customer_id'] = '';
		}
		if (isset($this->request->post['customer_group_id'])) {
			$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
		} elseif (!empty($order_info)) {
			$this->data['customer_group_id'] = $order_info['customer_group_id'];
		} else {
			$this->data['customer_group_id'] = '';
		}
		
		$this->load->model('sale/customer_group');
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
								
    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($order_info)) { 
			$this->data['firstname'] = $order_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}
    	if (isset($this->request->post['lastname'])) {
      		$this->data['lastname'] = $this->request->post['lastname'];
    	} elseif (!empty($order_info)) { 
			$this->data['lastname'] = $order_info['lastname'];
		} else {
      		$this->data['lastname'] = '';
    	}
    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (!empty($order_info)) { 
			$this->data['email'] = $order_info['email'];
		} else {
      		$this->data['email'] = '';
    	}	
    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
    	} elseif (!empty($order_info)) { 
			$this->data['telephone'] = $order_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}
    	if (isset($this->request->post['fax'])) {
      		$this->data['fax'] = $this->request->post['fax'];
    	} elseif (!empty($order_info)) { 
			$this->data['fax'] = $order_info['fax'];
		} else {
      		$this->data['fax'] = '';
    	}	
		if (isset($this->request->post['affiliate_id'])) {
      		$this->data['affiliate_id'] = $this->request->post['affiliate_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['affiliate_id'] = $order_info['affiliate_id'];
		} else {
      		$this->data['affiliate_id'] = '';
    	}
		if (isset($this->request->post['affiliate'])) {
      		$this->data['affiliate'] = $this->request->post['affiliate'];
    	} elseif (!empty($order_info)) { 
			$this->data['affiliate'] = ($order_info['affiliate_id'] ? $order_info['affiliate_firstname'] . ' ' . $order_info['affiliate_lastname'] : '');
		} else {
      		$this->data['affiliate'] = '';
    	}	
		if (isset($this->request->post['order_status_id'])) {
      		$this->data['order_status_id'] = $this->request->post['order_status_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['order_status_id'] = $order_info['order_status_id'];
		} else {
      		$this->data['order_status_id'] = '';
    	}
	
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();	
    	if (isset($this->request->post['comment'])) {
      		$this->data['comment'] = $this->request->post['comment'];
    	} elseif (!empty($order_info)) { 
			$this->data['comment'] = $order_info['comment'];
		} else {
      		$this->data['comment'] = '';
    	}	
				
		$this->load->model('sale/customer');
		if (isset($this->request->post['customer_id'])) {
			$this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->post['customer_id']);
		} elseif (!empty($order_info)) {
			$this->data['addresses'] = $this->model_sale_customer->getAddresses($order_info['customer_id']);
		} else {
			$this->data['addresses'] = array();
		}
    	if (isset($this->request->post['payment_firstname'])) {
      		$this->data['payment_firstname'] = $this->request->post['payment_firstname'];
		} elseif (!empty($order_info)) { 
			$this->data['payment_firstname'] = $order_info['payment_firstname'];
		} else {
      		$this->data['payment_firstname'] = '';
    	}
    	if (isset($this->request->post['payment_lastname'])) {
      		$this->data['payment_lastname'] = $this->request->post['payment_lastname'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_lastname'] = $order_info['payment_lastname'];
		} else {
      		$this->data['payment_lastname'] = '';
    	}
    	if (isset($this->request->post['payment_company'])) {
      		$this->data['payment_company'] = $this->request->post['payment_company'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_company'] = $order_info['payment_company'];
		} else {
      		$this->data['payment_company'] = '';
    	}
    	if (isset($this->request->post['payment_company_id'])) {
      		$this->data['payment_company_id'] = $this->request->post['payment_company_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_company_id'] = $order_info['payment_company_id'];
		} else {
      		$this->data['payment_company_id'] = '';
    	}
    	if (isset($this->request->post['payment_tax_id'])) {
      		$this->data['payment_tax_id'] = $this->request->post['payment_tax_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_tax_id'] = $order_info['payment_tax_id'];
		} else {
      		$this->data['payment_tax_id'] = '';
    	}	
    	if (isset($this->request->post['payment_address_1'])) {
      		$this->data['payment_address_1'] = $this->request->post['payment_address_1'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_address_1'] = $order_info['payment_address_1'];
		} else {
      		$this->data['payment_address_1'] = '';
    	}
    	if (isset($this->request->post['payment_address_2'])) {
      		$this->data['payment_address_2'] = $this->request->post['payment_address_2'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_address_2'] = $order_info['payment_address_2'];
		} else {
      		$this->data['payment_address_2'] = '';
    	}
    	if (isset($this->request->post['payment_city'])) {
      		$this->data['payment_city'] = $this->request->post['payment_city'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_city'] = $order_info['payment_city'];
		} else {
      		$this->data['payment_city'] = '';
    	}
    	if (isset($this->request->post['payment_postcode'])) {
      		$this->data['payment_postcode'] = $this->request->post['payment_postcode'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_postcode'] = $order_info['payment_postcode'];
		} else {
      		$this->data['payment_postcode'] = '';
    	}	
    	if (isset($this->request->post['payment_country_id'])) {
      		$this->data['payment_country_id'] = $this->request->post['payment_country_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_country_id'] = $order_info['payment_country_id'];
		} else {
      		$this->data['payment_country_id'] = '';
    	}		
		if (isset($this->request->post['payment_zone_id'])) {
      		$this->data['payment_zone_id'] = $this->request->post['payment_zone_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_zone_id'] = $order_info['payment_zone_id'];
		} else {
      		$this->data['payment_zone_id'] = '';
    	}				
    	if (isset($this->request->post['payment_method'])) {
      		$this->data['payment_method'] = $this->request->post['payment_method'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_method'] = $order_info['payment_method'];
		} else {
      		$this->data['payment_method'] = '';
    	}
    	if (isset($this->request->post['payment_code'])) {
      		$this->data['payment_code'] = $this->request->post['payment_code'];
    	} elseif (!empty($order_info)) { 
			$this->data['payment_code'] = $order_info['payment_code'];
		} else {
      		$this->data['payment_code'] = '';
    	}				
    	if (isset($this->request->post['shipping_firstname'])) {
      		$this->data['shipping_firstname'] = $this->request->post['shipping_firstname'];
		} elseif (!empty($order_info)) { 
			$this->data['shipping_firstname'] = $order_info['shipping_firstname'];
		} else {
      		$this->data['shipping_firstname'] = '';
    	}
    	if (isset($this->request->post['shipping_lastname'])) {
      		$this->data['shipping_lastname'] = $this->request->post['shipping_lastname'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_lastname'] = $order_info['shipping_lastname'];
		} else {
      		$this->data['shipping_lastname'] = '';
    	}
    	if (isset($this->request->post['shipping_company'])) {
      		$this->data['shipping_company'] = $this->request->post['shipping_company'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_company'] = $order_info['shipping_company'];
		} else {
      		$this->data['shipping_company'] = '';
    	}
    	if (isset($this->request->post['shipping_address_1'])) {
      		$this->data['shipping_address_1'] = $this->request->post['shipping_address_1'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_address_1'] = $order_info['shipping_address_1'];
		} else {
      		$this->data['shipping_address_1'] = '';
    	}
    	if (isset($this->request->post['shipping_address_2'])) {
      		$this->data['shipping_address_2'] = $this->request->post['shipping_address_2'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_address_2'] = $order_info['shipping_address_2'];
		} else {
      		$this->data['shipping_address_2'] = '';
    	}
    	if (isset($this->request->post['shipping_city'])) {
      		$this->data['shipping_city'] = $this->request->post['shipping_city'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_city'] = $order_info['shipping_city'];
		} else {
      		$this->data['shipping_city'] = '';
    	}
    	if (isset($this->request->post['shipping_postcode'])) {
      		$this->data['shipping_postcode'] = $this->request->post['shipping_postcode'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_postcode'] = $order_info['shipping_postcode'];
		} else {
      		$this->data['shipping_postcode'] = '';
    	}	
    	if (isset($this->request->post['shipping_country_id'])) {
      		$this->data['shipping_country_id'] = $this->request->post['shipping_country_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_country_id'] = $order_info['shipping_country_id'];
		} else {
      		$this->data['shipping_country_id'] = '';
    	}		
		if (isset($this->request->post['shipping_zone_id'])) {
      		$this->data['shipping_zone_id'] = $this->request->post['shipping_zone_id'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_zone_id'] = $order_info['shipping_zone_id'];
		} else {
      		$this->data['shipping_zone_id'] = '';
    	}	
						
		$this->load->model('localisation/country');
		$this->data['countries'] = $this->model_localisation_country->getCountries();															
		
    	if (isset($this->request->post['shipping_method'])) {
      		$this->data['shipping_method'] = $this->request->post['shipping_method'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_method'] = $order_info['shipping_method'];
		} else {
      		$this->data['shipping_method'] = '';
    	}	
    	if (isset($this->request->post['shipping_code'])) {
      		$this->data['shipping_code'] = $this->request->post['shipping_code'];
    	} elseif (!empty($order_info)) { 
			$this->data['shipping_code'] = $order_info['shipping_code'];
		} else {
      		$this->data['shipping_code'] = '';
    	}
		if (isset($this->request->post['order_product'])) {
			$order_products = $this->request->post['order_product'];
		} elseif (isset($this->request->get['order_id'])) {
			$order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);			
		} else {
			$order_products = array();
		}
		
		$this->load->model('catalog/product');
		$this->document->addScript('view/javascript/jquery/ajaxupload.js');
		$this->data['order_products'] = array();		
		
		foreach ($order_products as $order_product) {
			if (isset($order_product['order_option'])) {
				$order_option = $order_product['order_option'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_option = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $order_product['order_product_id']);
			} else {
				$order_option = array();
			}

			if (isset($order_product['order_download'])) {
				$order_download = $order_product['order_download'];
			} elseif (isset($this->request->get['order_id'])) {
				$order_download = $this->model_sale_order->getOrderDownloads($this->request->get['order_id'], $order_product['order_product_id']);
			} else {
				$order_download = array();
			}
							
			$this->data['order_products'][] = array(
				'order_product_id' => $order_product['order_product_id'],
				'product_id'       => $order_product['product_id'],
				'name'             => $order_product['name'],
				'model'            => $order_product['model'],
				'option'           => $order_option,
				'download'         => $order_download,
				'quantity'         => $order_product['quantity'],
				'price'            => $order_product['price'],
				'total'            => $order_product['total'],
				'tax'              => $order_product['tax'],
				'reward'           => $order_product['reward']
			);
		}
		
		if (isset($this->request->post['order_voucher'])) {
			$this->data['order_vouchers'] = $this->request->post['order_voucher'];
		} elseif (isset($this->request->get['order_id'])) {
			$this->data['order_vouchers'] = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);			
		} else {
			$this->data['order_vouchers'] = array();
		}
       
		$this->load->model('sale/voucher_theme');
					
		$this->data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();
						
		if (isset($this->request->post['order_total'])) {
      		$this->data['order_totals'] = $this->request->post['order_total'];
    	} elseif (isset($this->request->get['order_id'])) { 
			$this->data['order_totals'] = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);
		} else {
      		$this->data['order_totals'] = array();
    	}	
		
		$this->template = 'sale/order_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
  	}
	
  	protected function validateForm() {
    	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

    	if ((utf8_strlen($this->request->post['email']) > 96) || (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email']))) {
      		$this->error['email'] = $this->language->get('error_email');
    	}
		
    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}
		
    	if ((utf8_strlen($this->request->post['payment_firstname']) < 1) || (utf8_strlen($this->request->post['payment_firstname']) > 32)) {
      		$this->error['payment_firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['payment_lastname']) < 1) || (utf8_strlen($this->request->post['payment_lastname']) > 32)) {
      		$this->error['payment_lastname'] = $this->language->get('error_lastname');
    	}

    	if ((utf8_strlen($this->request->post['payment_address_1']) < 3) || (utf8_strlen($this->request->post['payment_address_1']) > 128)) {
      		$this->error['payment_address_1'] = $this->language->get('error_address_1');
    	}

    	if ((utf8_strlen($this->request->post['payment_city']) < 3) || (utf8_strlen($this->request->post['payment_city']) > 128)) {
      		$this->error['payment_city'] = $this->language->get('error_city');
    	}
		
		$this->load->model('localisation/country');
		
		$country_info = $this->model_localisation_country->getCountry($this->request->post['payment_country_id']);
		
		if ($country_info) {
			if ($country_info['postcode_required'] && (utf8_strlen($this->request->post['payment_postcode']) < 2) || (utf8_strlen($this->request->post['payment_postcode']) > 10)) {
				$this->error['payment_postcode'] = $this->language->get('error_postcode');
			}
			
			// VAT Validation
			$this->load->helper('vat');
			
			if ($this->config->get('config_vat') && $this->request->post['payment_tax_id'] && (vat_validation($country_info['iso_code_2'], $this->request->post['payment_tax_id']) == 'invalid')) {
				$this->error['payment_tax_id'] = $this->language->get('error_vat');
			}				
		}

    	if ($this->request->post['payment_country_id'] == '') {
      		$this->error['payment_country'] = $this->language->get('error_country');
    	}
		
    	if (!isset($this->request->post['payment_zone_id']) || $this->request->post['payment_zone_id'] == '') {
      		$this->error['payment_zone'] = $this->language->get('error_zone');
    	}	
		
    	if ($this->request->post['payment_method'] == '') {
      		$this->error['payment_zone'] = $this->language->get('error_zone');
    	}			
		
		if (!$this->request->post['payment_method']) {
			$this->error['payment_method'] = $this->language->get('error_payment');
		}	
					
		// Check if any products require shipping
		$shipping = false;
		
		if (isset($this->request->post['order_product'])) {
			$this->load->model('catalog/product');
			
			foreach ($this->request->post['order_product'] as $order_product) {
				$product_info = $this->model_catalog_product->getProduct($order_product['product_id']);
			
				if ($product_info && $product_info['shipping']) {
					$shipping = true;
				}
			}
		}
		
		if ($shipping) {
			if ((utf8_strlen($this->request->post['shipping_firstname']) < 1) || (utf8_strlen($this->request->post['shipping_firstname']) > 32)) {
				$this->error['shipping_firstname'] = $this->language->get('error_firstname');
			}
	
			if ((utf8_strlen($this->request->post['shipping_lastname']) < 1) || (utf8_strlen($this->request->post['shipping_lastname']) > 32)) {
				$this->error['shipping_lastname'] = $this->language->get('error_lastname');
			}
			
			if ((utf8_strlen($this->request->post['shipping_address_1']) < 3) || (utf8_strlen($this->request->post['shipping_address_1']) > 128)) {
				$this->error['shipping_address_1'] = $this->language->get('error_address_1');
			}
	
			if ((utf8_strlen($this->request->post['shipping_city']) < 3) || (utf8_strlen($this->request->post['shipping_city']) > 128)) {
				$this->error['shipping_city'] = $this->language->get('error_city');
			}
	
			$this->load->model('localisation/country');
			
			$country_info = $this->model_localisation_country->getCountry($this->request->post['shipping_country_id']);
			
			if ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['shipping_postcode']) < 2) || (utf8_strlen($this->request->post['shipping_postcode']) > 10)) {
				$this->error['shipping_postcode'] = $this->language->get('error_postcode');
			}
	
			if ($this->request->post['shipping_country_id'] == '') {
				$this->error['shipping_country'] = $this->language->get('error_country');
			}
			
			if (!isset($this->request->post['shipping_zone_id']) || $this->request->post['shipping_zone_id'] == '') {
				$this->error['shipping_zone'] = $this->language->get('error_zone');
			}
			
			if (!$this->request->post['shipping_method']) {
				$this->error['shipping_method'] = $this->language->get('error_shipping');
			}			
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    
	
   	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}
  	public function deadlineupdate() {
    	$deadline	= (isset($this->request->get['deadline'])) ? $this->request->get['deadline'] : null ;
		$this->load->model('sale/order');
		$this->model_sale_order->updateDeadline($deadline);
		$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
		
	public function info() {

		$note_type = "order";
		$this->load->model('sale/order');
		$this->load->model('catalog/note');
    	if (isset($_POST['note_submit']) ){
			$this->model_catalog_note->addNote($this->request->post);
			$this->model_sale_order->updateNote( $this->request->post['flag'] , $this->request->get['order_id']);

			$this->redirect($this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . $url . "&order_id=". $this->request->get['order_id'] , 'SSL'));
		}
 
		$this->data['type_id']	= $this->request->get['order_id'];
		$this->data['note']		= $this->model_catalog_note->getNote($note_type ,$this->request->get['order_id']);
		$this->data['action']	= $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'], 'SSL');

		$order_id				= (isset($this->request->get['order_id'])) ? $this->request->get['order_id'] : 0;
		$order_info				= $this->model_sale_order->getOrder($order_id);
		//print_r($order_info);

		$this->data['error_warning'] = ''; $this->data['success']='';
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])){
			$query = $this->model_sale_order->updateTracking( $_POST , $order_info );
            if($query){
                $this->data['success'] = 'Insert Tracking Number';
            }else{
                $this->data['error_warning'] = 'can not do this action!';
            }
        }

		if ($order_info) {
			if($order_info['order_status_id'] <> 0 )
				$this->data['order_status_id'] = $order_info['order_status_id'];
			
			$p1 = ($order_info['paysbuy'] == 'no') ? '1' : '0';
			$p2 = ($order_info['paypal']  == 'no') ? '1' : '0';
			$this->data['paysbuy']		= (  $p1 && $p2 ) ? '1' : '0';

			$this->data['tack_code']	= $order_info['tack_code'];
			$this->data['track_submit'] = $order_info['track_submit'];

			$this->language->load('sale/order');
			$this->document->setTitle($this->language->get('heading_title'));
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_invoice_date'] = $this->language->get('text_invoice_date');
			$this->data['text_customer'] = $this->language->get('text_customer');
			$this->data['text_email'] = $this->language->get('text_email');
			$this->data['text_telephone'] = $this->language->get('text_telephone');
			$this->data['text_total'] = $this->language->get('text_total');	
			$this->data['text_order_status'] = $this->language->get('text_order_status');
			$this->data['text_comment'] = $this->language->get('text_comment');
			$this->data['text_date_modified'] = $this->language->get('text_date_modified');			
			$this->data['text_firstname'] = $this->language->get('text_firstname');
			$this->data['text_lastname'] = $this->language->get('text_lastname');
			$this->data['text_company'] = $this->language->get('text_company');
			$this->data['text_company_id'] = $this->language->get('text_company_id');
			$this->data['text_address_1'] = $this->language->get('text_address_1');
			$this->data['text_address_2'] = $this->language->get('text_address_2');
			$this->data['text_city'] = $this->language->get('text_city');
			$this->data['text_postcode'] = $this->language->get('text_postcode');
			$this->data['text_zone'] = $this->language->get('text_zone');
			$this->data['text_zone_code'] = $this->language->get('text_zone_code');
			$this->data['text_wait'] = $this->language->get('text_wait');
			$this->data['text_credit_add'] = $this->language->get('text_credit_add');
			$this->data['text_credit_remove'] = $this->language->get('text_credit_remove');

			$this->data['button_invoice'] = $this->language->get('button_invoice');
			$this->data['button_cancel'] = $this->language->get('button_cancel');
		
			$this->data['tab_order'] = $this->language->get('tab_order');
			$this->data['tab_payment'] = $this->language->get('tab_payment');
			$this->data['tab_shipping'] = $this->language->get('tab_shipping');
			$this->data['tab_product'] = $this->language->get('tab_product');
			$this->data['tab_history'] = $this->language->get('tab_history');

			$this->data['token'] = $this->session->data['token'];

			$url = '';
			if (isset($this->request->get['filter_order_id'])) $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			if (isset($this->request->get['filter_customer'])) $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
			if (isset($this->request->get['filter_total'])) $url .= '&filter_total=' . $this->request->get['filter_total'];
			if (isset($this->request->get['filter_date_modified'])) $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
			if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
			if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'),				
				'separator' => ' :: '
			);
			$this->data['invoice'] = $this->url->link('sale/order/invoice', 'token=' . $this->session->data['token'] . '&order_id=' . (int)$this->request->get['order_id'], 'SSL');
			$this->data['cancel'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL');
			$this->data['order_id'] = $this->request->get['order_id'];

			$this->data['firstname'] = $order_info['shipping_firstname'];
			$this->data['lastname'] = $order_info['shipping_lastname'];

			$this->data['customer'] = ($order_info['customer_id']) ? $this->url->link('sale/customer/update', 'token=' . $this->session->data['token'] . '&customer_id=' . $order_info['customer_id'], 'SSL') : '';

			$this->data['email'] = $order_info['email'];
			$this->data['telephone'] = $order_info['telephone'];
			$this->data['comment'] = nl2br($order_info['comment']);
			$this->data['shipping_method'] = $order_info['shipping_method'];
			$this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
			
			if ($order_info['total'] < 0) {
				$this->data['credit'] = $order_info['total'];
			} else {
				$this->data['credit'] = 0;
			}
			
			$this->load->model('sale/customer');	
			$this->data['credit_total'] = $this->model_sale_customer->getTotalTransactionsByOrderId($this->request->get['order_id']); 
 
 

			$this->load->model('localisation/order_status');
			$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

			$this->data['order_status'] = ($order_status_info) ? $order_status_info['name'] : '';

 				$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
				$date = new DateTime($order_info['date_modified']);
				$original_date = (int)$date->format('d');
				$original_month = (int)$date->format('m') -1; //$date->format('d M Y H:i:s');
				$original_year = (int)$date->format('Y')+543;
				$original_time = $date->format('H:i:s');
				$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;

			$this->data['date_modified']		= $new_date;		
			$this->data['payment_firstname']	= $order_info['shipping_firstname'];
			$this->data['payment_lastname']		= $order_info['shipping_lastname'];
			$this->data['send_from']			= $order_info['send_from'];	
			$this->data['payment_company']		= $order_info['shipping_company'];
			$this->data['payment_company_id']	= $order_info['payment_company_id'];
			$this->data['payment_address_1']	= $order_info['shipping_address_1'];
			$this->data['payment_address_2']	= $order_info['shipping_address_2'];
			$this->data['payment_city']			= $order_info['shipping_city'];
			$this->data['payment_postcode']		= $order_info['shipping_postcode'];
			$this->data['payment_zone']			= $order_info['shipping_zone'];
			$this->data['payment_zone_code']	= $order_info['shipping_zone_code'];		
			
			$this->data['products'] = array();
			$this->load->model('tool/image');
			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();
				$img=0;
				//print_r($this->request->get['order_id']." ". $product['order_product_id']);echo "<br><br>";
				$options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);
				
				
				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
						if($option['name'] =='Size'){
							$img = $this->model_sale_order->getImgOptionName($product['product_id'] , $option['value']);
							//echo $img."<br>";
						}
					} else {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.')),
							'type'  => $option['type'],
							'href'  => $this->url->link('sale/order/download', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'] . '&order_option_id=' . $option['order_option_id'], 'SSL')
						);						
					}
				}
				if( ($img == 0) || (strlen($img) == 0)){
					$img = $this->model_sale_order->getImg($product['product_id']);
				}

				$this->data['products'][] = array(
					'order_product_id' => $product['order_product_id'],
					'product_id'       => $product['product_id'],
					'name'    	 	   => $product['name'],
					'model'    		   => $product['model'],
					'option'   		   => $option_data,
					'quantity'		   => $product['quantity'],
					'img'			   => $this->model_tool_image->resize($img, 60, 80),
					'link'			   => HTTP_CATALOG."product.html?product_id=".$product['product_id'],
					'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    		   => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'     		   => $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $product['product_id'], 'SSL')
				);
			}

			
			$this->data['banks'] = array();
			$banks = $this->model_sale_order->getBank($this->request->get['order_id']);
			foreach ($banks as $bank) {
				$this->data['banks'][] = array(
					'date'			=> $bank['date'],
					'time'			=> $bank['time'],
					'money'    	 	=> $bank['money'],
					'bankname'		=> $bank['bankname'],
					'remark'   		=> $bank['remark']
				);
			}
		
			$this->data['vouchers'] = array();	
			
			$vouchers = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);
			 
			foreach ($vouchers as $voucher) {
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
					'href'        => $this->url->link('sale/voucher/update', 'token=' . $this->session->data['token'] . '&voucher_id=' . $voucher['voucher_id'], 'SSL')
				);
			}

			$dis=0;
			$array_dis = array('globaldiscount','vip','credit_discount');
			$this->data['total_product'] = count($products);
			$this->data['totals'] = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);
			foreach($this->data['totals'] as $total){
				if($total['code'] == 'total'){
					$this->data['order_total'] = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
				}elseif($total['code'] == 'sub_total'){
					$this->data['order_subtotal'] = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
				}elseif($total['code'] == 'shipping'){
					$this->data['order_shipping'] = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
				}elseif($total['code'] == 'vender_discount'){
					$this->data['order_vender'] = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
				}elseif(in_array($total['code'] , $array_dis)){
					$dis = $dis + $total['value'];
				}
			}
			
			$this->data['order_dis']		=	abs($this->currency->format($dis , "USD", 1) )." ฿";
			$this->data['order_status_id']	= $order_info['order_status_id'];

			// Fraud
			$this->load->model('sale/fraud');
			
 
			
			$this->template = 'sale/order_info.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
			
			$this->response->setOutput($this->render());
		} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			);
		
			$this->template = 'error/not_found.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
		
			$this->response->setOutput($this->render());
		}	
	}

	public function createInvoiceNo() {
		$this->language->load('sale/order');

		$json = array();
		
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
		} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			
			$invoice_no = $this->model_sale_order->createInvoiceNo($this->request->get['order_id']);
			
			if ($invoice_no) {
				$json['invoice_no'] = $invoice_no;
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->setOutput(json_encode($json));
  	}

	public function addCredit() {
		$this->language->load('sale/order');
		
		$json = array();
    	
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
			
			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');
				
				$credit_total = $this->model_sale_customer->getTotalTransactionsByOrderId($this->request->get['order_id']);
				
				if (!$credit_total) {
					$this->model_sale_customer->addTransaction($order_info['customer_id'], $this->language->get('text_order_id') . ' #' . $this->request->get['order_id'], $order_info['total'], $this->request->get['order_id']);
					
					$json['success'] = $this->language->get('text_credit_added');
				} else {
					$json['error'] = $this->language->get('error_action');
				}
			}
		}
		
		$this->response->setOutput(json_encode($json));
  	}
	
	public function removeCredit() {
		$this->language->load('sale/order');
		
		$json = array();
    	
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
			
			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');
				
				$this->model_sale_customer->deleteTransaction($this->request->get['order_id']);
					
				$json['success'] = $this->language->get('text_credit_removed');
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}
		
		$this->response->setOutput(json_encode($json));
  	}
				
	public function addReward() {
		$this->language->load('sale/order');
		
		$json = array();
    	
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
						
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
			
			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');

				$reward_total = $this->model_sale_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);
				
				if (!$reward_total) {
					$this->model_sale_customer->addReward($order_info['customer_id'], $this->language->get('text_order_id') . ' #' . $this->request->get['order_id'], $order_info['reward'], $this->request->get['order_id']);
					
					$json['success'] = $this->language->get('text_reward_added');
				} else {
					$json['error'] = $this->language->get('error_action'); 
				}
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}
		
		$this->response->setOutput(json_encode($json));
  	}
	
	public function removeReward() {
		$this->language->load('sale/order');
		
		$json = array();
    	
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
			
			if ($order_info && $order_info['customer_id']) {
				$this->load->model('sale/customer');

				$this->model_sale_customer->deleteReward($this->request->get['order_id']);
				
				$json['success'] = $this->language->get('text_reward_removed');
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}
		
		$this->response->setOutput(json_encode($json));
  	}
		
	public function addCommission() {
		$this->language->load('sale/order');
		
		$json = array();
    	
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
			
			if ($order_info && $order_info['affiliate_id']) {
				$this->load->model('sale/affiliate');
				
				$affiliate_total = $this->model_sale_affiliate->getTotalTransactionsByOrderId($this->request->get['order_id']);
				
				if (!$affiliate_total) {
					$this->model_sale_affiliate->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $this->request->get['order_id'], $order_info['commission'], $this->request->get['order_id']);
					
					$json['success'] = $this->language->get('text_commission_added');
				} else {
					$json['error'] = $this->language->get('error_action'); 
				}
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}
		
		$this->response->setOutput(json_encode($json));
  	}
	
	public function removeCommission() {
		$this->language->load('sale/order');
		
		$json = array(); 
    	
     	if (!$this->user->hasPermission('modify', 'sale/order')) {
      		$json['error'] = $this->language->get('error_permission'); 
    	} elseif (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
			
			if ($order_info && $order_info['affiliate_id']) {
				$this->load->model('sale/affiliate');

				$this->model_sale_affiliate->deleteTransaction($this->request->get['order_id']);
				
				$json['success'] = $this->language->get('text_commission_removed');
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}
		
		$this->response->setOutput(json_encode($json));
  	}

	public function history() {
    	$this->language->load('sale/order');
		
		$this->data['error'] = '';
		$this->data['success'] = '';
		
		$this->load->model('sale/order');
	
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->user->hasPermission('modify', 'sale/order')) { 
				$this->data['error'] = $this->language->get('error_permission');
			}
			
			if (!$this->data['error']) { 
				$this->model_sale_order->addOrderHistory($this->request->get['order_id'], $this->request->post);
				
				$this->data['success'] = $this->language->get('text_success');
			}
		}
				
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_notify'] = $this->language->get('column_notify');
		$this->data['column_comment'] = $this->language->get('column_comment');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}  
		
		$this->data['histories'] = array();
			
		$results = $this->model_sale_order->getOrderHistories($this->request->get['order_id'], ($page - 1) * 10, 10);
      		
		foreach ($results as $result) {
        	$this->data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
        		'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
      	}			
		
		$history_total = $this->model_sale_order->getTotalOrderHistories($this->request->get['order_id']);
			
		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order/history', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'] . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->template = 'sale/order_history.tpl';		
		
		$this->response->setOutput($this->render());
  	}
	
	public function download() {
		$this->load->model('sale/order');
		
		if (isset($this->request->get['order_option_id'])) {
			$order_option_id = $this->request->get['order_option_id'];
		} else {
			$order_option_id = 0;
		}
		
		$option_info = $this->model_sale_order->getOrderOption($this->request->get['order_id'], $order_option_id);
		
		if ($option_info && $option_info['type'] == 'file') {
			$file = DIR_DOWNLOAD . $option_info['value'];
			$mask = basename(utf8_substr($option_info['value'], 0, utf8_strrpos($option_info['value'], '.')));

			if (!headers_sent()) {
				if (file_exists($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));
					
					readfile($file, 'rb');
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->language->load('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			);
		
			$this->template = 'error/not_found.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
		
			$this->response->setOutput($this->render());
		}	
	}

	public function upload() {
		$this->language->load('sale/order');
		
		$json = array();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!empty($this->request->files['file']['name'])) {
				$filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');
				
				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}	  	
				
				// Allowed file extension types
				$allowed = array();
				
				$filetypes = explode("\n", $this->config->get('config_file_extension_allowed'));
				
				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}
				
				if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}	
				
				// Allowed file mime types		
				$allowed = array();
				
				$filetypes = explode("\n", $this->config->get('config_file_mime_allowed'));
				
				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}
								
				if (!in_array($this->request->files['file']['type'], $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}
							
				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		
			if (!isset($json['error'])) {
				if (is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
					$file = basename($filename) . '.' . md5(mt_rand());
					
					$json['file'] = $file;
					
					move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
				}
							
				$json['success'] = $this->language->get('text_upload');
			}	
		}
		
		$this->response->setOutput(json_encode($json));
	}
			
  	public function invoice() {

		$this->language->load('sale/order');

		$this->data['title'] = $this->language->get('heading_title');
		$this->data['base'] = (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) ? HTTPS_SERVER : HTTP_SERVER;
		$this->data['direction'] = $this->language->get('direction');
		$this->data['language'] = $this->language->get('code');

		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
		$this->data['text_invoice_date'] = $this->language->get('text_invoice_date');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_telephone'] = $this->language->get('text_telephone');
		$this->data['text_fax'] = $this->language->get('text_fax');
		$this->data['text_to'] = $this->language->get('text_to');
		$this->data['text_company_id'] = $this->language->get('text_company_id');
		$this->data['text_tax_id'] = $this->language->get('text_tax_id');		
		$this->data['text_ship_to'] = $this->language->get('text_ship_to');
		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');

		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_comment'] = $this->language->get('column_comment');

		$this->load->model('tool/image');
		$this->load->model('sale/order');
		$this->load->model('setting/setting');
		$this->data['orders'] = array();
		$orders		= array();
		$my_order	= array();

		if (isset($this->request->post['selected'])) {
			$orders[] = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		} else{
			$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		//print_r($orders);
		
		
		if(!is_array($orders[0])){
		 $orders[0] = array($orders[0]);
		}

		foreach ($orders[0] as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);
			//print_r($order_id);echo "<br>";
			if ($order_info) {
				$img = 0;
				$product_array = array();
				$product_data = array();
				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_data = array();
					$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

					foreach ($options as $option) {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value']
						);		

						if($option['name'] =='Size'){
							$img = $this->model_sale_order->getImgOptionName($product['product_id'] , $option['value']);
							//echo $img."<br>";
						}
					}
					if( ($img == 0) || (strlen($img) == 0)){
						$img = $this->model_sale_order->getImg($product['product_id']);
					}
					array_push($product_array , $product['quantity']);

					$product_data[] = array(
						'name'		=> $product['name'],
						'model'		=> $product['model'],
						'option'	=> $option_data,
						'quantity'	=> $product['quantity'],
						'img'		=> $this->model_tool_image->resize($img, 100, 100),
						'link'		=> HTTP_CATALOG."product.html?product_id=".$product['product_id'],
						'price'		=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
						'total'		=> $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
					);
				}
				
				$voucher_data = array();
				
				$vouchers = $this->model_sale_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])			
					);
				}
					
				//$total_data = $this->model_sale_order->getOrderTotals($order_id);
				$dis=0;$order_vender=0;$order_total=0;$order_subtotal=0;$order_shipping=0;
				$array_dis = array('globaldiscount','vip','credit_discount');
				$total_product = count($products);
				$totals = $this->model_sale_order->getOrderTotals($order_id);
				foreach($totals as $total){
					if($total['code'] == 'total'){
						$order_total = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
					}elseif($total['code'] == 'sub_total'){
						$order_subtotal = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
					}elseif($total['code'] == 'shipping'){
						$order_shipping = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
					}elseif($total['code'] == 'vender_discount'){
						$order_vender = abs($this->currency->format($total['value'] , "USD", 1))." ฿";
					}elseif(in_array($total['code'] , $array_dis)){
						$dis = $dis + $total['value'];
					}
				}
				
				$order_dis		=	abs($this->currency->format($dis , "USD", 1) )." ฿";

				$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
				$date = new DateTime($order_info['date_added']);
				$original_date = (int)$date->format('d');
				$original_month = (int)$date->format('m') -1; //$date->format('d M Y H:i:s');
				$original_year = (int)$date->format('Y')+543;
				$original_time = $date->format('H:i:s');
				$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year." ".$original_time;

				$myorder = array();
				$myorder = array(
					'order_id'	         => $order_id,
					'date_added'         => $new_date,
					'email'              => $order_info['email'],
					'telephone'          => $order_info['telephone'],
					'firstname'			 => $order_info['shipping_firstname']." ".$order_info['shipping_lastname'],
					'company'			 => $order_info['shipping_company'],
					'address'			 => $order_info['shipping_address_1']." ".$order_info['shipping_address_2']." ".$order_info['shipping_city']." ".$order_info['shipping_zone'],
					'postcode'			 => $order_info['shipping_postcode'],
					'zone_code'			 => $order_info['shipping_zone_code'],
					'shipping_method'    => $order_info['shipping_method'],
					'send_from'			 => $order_info['send_from'],
					'product'            => $product_data,
					'voucher'            => $voucher_data,
					'order_total'		 => $order_total,
					'order_subtotal'	 => $order_subtotal,
					'order_shipping'	 => $order_shipping,
					'order_vender'		 => $order_vender,
					'order_dis'			 => $order_dis,
					'total_product'		 => $total_product,
					'comment'            => nl2br($order_info['comment'])
				);

				array_push( $my_order ,  array("order"=>$myorder ,"tack_code"=>$order_info['tack_code'] , "shipping_method"=>$order_info['shipping_method'] , "product_array"=>$product_array )  );
			}
			
		}
		$this->data['orders'] = $my_order;

		$this->template = 'sale/order_invoice.tpl';

		$this->response->setOutput($this->render());
	}


  	public function rollbacklist() {
		$this->language->load('sale/order');
		$this->document->setTitle('ดูคืนสินค้า');
		$this->load->model('sale/order');

		$filter_rollbackid		= (isset($this->request->get['filter_rollbackid'])) ? $this->request->get['filter_rollbackid'] : null;
		$filter_orderid			= (isset($this->request->get['filter_orderid'])) ? $this->request->get['filter_orderid'] : null;
		$filter_productid		= (isset($this->request->get['filter_productid'])) ? $this->request->get['filter_productid'] : null;
		$filter_customer		= (isset($this->request->get['filter_customer']))  ? $this->request->get['filter_customer'] : null;
		$filter_email			= (isset($this->request->get['filter_email']))  ? $this->request->get['filter_email'] : null;
		
		$filter_order_status_id = (isset($this->request->get['filter_order_status_id'])) ? $this->request->get['filter_order_status_id'] : null;
		$filter_date_added		= (isset($this->request->get['filter_date_added'])) ? $this->request->get['filter_date_added'] : null;
		$filter_bank			= (isset($this->request->get['filter_bank'])) ? $this->request->get['filter_bank'] : null;
		$page					= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		$url = '';
		if (isset($this->request->get['filter_rollbackid'])) $url .= '&filter_rollbackid=' . $this->request->get['filter_rollbackid'];
		if (isset($this->request->get['filter_orderid'])) $url .= '&filter_orderid=' . $this->request->get['filter_orderid'];
		if (isset($this->request->get['filter_productid'])) $url .= '&filter_productid=' . $this->request->get['filter_productid'];
		if (isset($this->request->get['filter_customer'])) $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));	
		if (isset($this->request->get['filter_email'])) $url .= '&filter_email=' . $this->request->get['filter_email'];
		if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		if (isset($this->request->get['filter_date_added'])) $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		if (isset($this->request->get['filter_bank'])) $url .= '&filter_bank=' . $this->request->get['filter_bank'];
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'ดูคืนสินค้า',
			'href'      => $this->url->link('sale/order/rollbacklist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['orders'] = array();
		$data = array(
			'filter_rollbackid'      => $filter_rollbackid,
			'filter_orderid'         => $filter_orderid,
			'filter_productid'       => $filter_productid,
			'filter_customer'	     => $filter_customer,
			'filter_email'			 => $filter_email,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_date_added'      => $filter_date_added,
			'filter_bank'			 => $filter_bank,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);

		$order_total = $this->model_sale_order->getTotalRollbackOrders($data);
		$results = $this->model_sale_order->getRollbackOrders($data);
    	foreach ($results as $result) {
			$action = array();
						
			$action[] = array(
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/order/rollbackdetail', 'token=' . $this->session->data['token'] . '&id=' . $result['rollback_id'] . $url, 'SSL')
			);
			
			if (strtotime($result['date_added']) > strtotime('-' . (int)$this->config->get('config_order_edit') . ' day')) {
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('sale/order/update', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL')
				);
			}
			
			$this->data['orders'][] = array(
				'return_id'     => $result['rollback_id'],
				'order_id'      => $result['order_id'],
				'product_id'    => $result['product_id'],
				'customer'      => $result['firstname']." ".$result['lastname'],
				'email'			=> $result['email'],
				'status'        => $result['return_status_id'],
				'bank'			=> $result['bankname'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'order_link'	=> $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] , 'SSL'),
				'action'        => $action
			);
		}


		$this->data['heading_title'] = 'ดูคืนสินค้า';
		$this->data['text_missing'] = $this->language->get('text_missing');
		$this->data['column_order_id'] = $this->language->get('column_order_id');

		$this->data['token'] = $this->session->data['token'];
		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';
		if (isset($this->request->get['filter_rollbackid'])) $url .= '&filter_rollbackid=' . $this->request->get['filter_rollbackid'];
		if (isset($this->request->get['filter_orderid'])) $url .= '&filter_orderid=' . $this->request->get['filter_orderid'];
		if (isset($this->request->get['filter_productid'])) $url .= '&filter_productid=' . $this->request->get['filter_productid'];
		if (isset($this->request->get['filter_customer'])) $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));		
		if (isset($this->request->get['filter_email'])) $url .= '&filter_email=' . $this->request->get['filter_email'];
		if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		if (isset($this->request->get['filter_date_added'])) $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		if (isset($this->request->get['filter_bank'])) $url .= '&filter_bank=' . $this->request->get['filter_bank'];
		if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
		if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		$this->data['filter_rollbackid'] = $filter_rollbackid;
		$this->data['filter_orderid'] = $filter_orderid;
		$this->data['filter_productid'] = $filter_productid;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_email'] = $filter_email;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_bank'] = $filter_bank;

		$this->load->model('localisation/order_status');
    	$this->data['order_statuses'] = $this->model_localisation_order_status->getReturnStatuses();

		$this->template = 'sale/order_rollback.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());

  	}
  	public function rollbackdetail() {
		$this->language->load('sale/order');
		$this->document->setTitle('ตรวจสอบสินค้า');
		$this->load->model('sale/order');
		$id					= (isset($this->request->get['id']))  ? $this->request->get['id'] : '';
		$rollback_id		= (isset($this->request->get['id']))  ? $this->request->get['id'] : '';

		$url = '';
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'ตรวจสอบสินค้า',
			'href'      => $this->url->link('sale/order/refundlist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		/*if(isset($_POST)){
			if (isset($_POST['submit_no'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'disapprovel',$_POST);
			} elseif (isset($_POST['submit_yes'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'approved');
			} elseif (isset($_POST['transfer_success'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'transfer_success',$_POST);
			} elseif (isset($_POST['submit_done'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'done');
			}
		}*/
print_r($_POST);
		if ($_POST && isset($_POST['save_order_yes'])){
			$_POST['status'] = '4'; //4=approved
			foreach ($_POST['amount'] as $f => $v)	$pass += $v;
			if (!$pass) $this->msgAlert(array('notice','ควรอนุมัติอย่างน้อย 1 ชิ้น','/admin/rollback/order_detail/'.$rollback_id));
			$this->model_sale_order->adminApproveOrder($rollback_id,$_POST);
			$mail = new sendmail();
			$mail->order_approve($rollback_id);

		}else if ($_POST && isset($_POST['save_order_no'])){
			$_POST['status'] = '5'; //5=disapprovel
			$this->model_sale_order->adminApproveOrder($rollback_id,$_POST);

		}else if ($_POST && isset($_POST['save_checking'])){
			$this->model_sale_order->setCheckingOrder($rollback_id);

		}else if ($_POST && isset($_POST['save_done'])){
			$this->model_sale_order->setFinalOrder($rollback_id,'pass',$_POST['note']);

		}else if ($_POST && isset($_POST['save_fail'])){
			$this->model_sale_order->setFinalOrder($rollback_id,'fail',$_POST['note']);

		}else if ($_POST && isset($_POST['transfer_success'])){
			$this->model_sale_order->setTransferSuccessOrder($rollback_id,$_POST);
		}

 
		$customerid = 1;
		$order_id	= "";
		$comment	= "";
		$res		= $this->model_sale_order->getRollbackOrdersDetail($id);
		$results	= $this->model_sale_order->rollbackDetailsRead($id);

		$this->data['products'] = array();
		foreach ($res as $product) {

			$this->data['status']			= $product['name'];
			$this->data['comment']			= $product['comment'];
			$this->data['track_ems']		= $product['track_ems'];
			$ppm = trim($product['option']);
			$ppm = explode(' ' , $ppm);
			$img = $this->model_sale_order->getProductImg($product['pid'] , $ppm[1]);
			$model = $this->model_sale_order->getProductModel($product['pid']);
			$customerid = $product['customer_id'];
			$this->data['products'][] = array(
				'product_id'	=> $product['pid'],
				'img'			=> HTTP_CATALOG."/image/".$img,
				'model'			=> $model,
				'option'		=> $product['option'],
				'quantity'		=> $product['quantity'],
				'unit_price'	=> $product['unit_price'],
				'return_qty'	=> $product['return_qty'],
				'pass_qty'		=> $product['pass_qty'],
				'price'			=> $this->currency->format($product['unit_price'] + ($this->config->get('config_tax') ? 0 : 0), 'USD', '1.00000000')
			);
		}

		/*if(floor($res['order']['money']) == floor($res['cal_total_return']))
			$res['cal_total_return'] = $res['order']['money'];

		if($res['send_wrong'] == 1) {
			$res['cal_fee'] -= 30;
		}
		$this->data['transfer_detail']	= $this->model_sale_order->getTransferOrder($rollback_id);*/
		$this->data['img']				= $this->model_sale_order->getImageOrder($product['order_id']);
		//$this->data['lists']			= $res;
		//$this->data['user']			= $info;

 

	 
		$this->data['transfer_detail'] = $this->model_sale_order->getRefundDetailSuccess($id , 'wrong');

	 
		$this->data['custoemrs'] = array();
		$results = $this->model_sale_order->getCancelCustomer($customerid);
    	foreach ($results as $result) {
			$this->data['custoemrs'][] = array(
				'email'			=> $result['email'],
				'name'			=> $result['firstname']." ".$result['lastname'],
				'gender'		=> $result['gender'],
				'address'		=> $result['address_1']." ".$result['address_2'],
				'city'			=> $result['city'],
				'postcode'		=> $result['postcode'],
				'mobile'		=> $result['mobile'],
				'bank_acc'		=> $result['bank_account_name'],
				'bank_account'	=> $result['bank_account'],
				'bankname'		=> $result['bankname'],
				'bank_branch'	=> $result['bank_branch']

			);
		}


		$this->data['success'] = '';
		$this->data['error_warning'] = '';
		$this->data['heading_title'] = 'ตรวจสอบสินค้า';
		$this->data['token'] = $this->session->data['token'];
		$this->data['backtopending'] = $this->url->link('sale/order/admin_setback', 'token='.$this->session->data['token']."&id=".$id."&type=order" . $url, 'SSL');

		$this->template = 'sale/order_rollbackdetail.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}


  	public function refundlist() {
		$this->language->load('sale/order');
		$this->document->setTitle('ดูแจ้งโอนเงินผิด');
		$this->load->model('sale/order');

		$filter_customer		= (isset($this->request->get['filter_customer']))  ? $this->request->get['filter_customer'] : '';
		$filter_firstname		= (isset($this->request->get['filter_firstname'])) ? $this->request->get['filter_firstname'] : '';
		$filter_lastname		= (isset($this->request->get['filter_lastname'])) ? $this->request->get['filter_lastname'] : '';
		$filter_status			= (isset($this->request->get['filter_status'])) ? $this->request->get['filter_status'] : '';
		$page					= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
		$url = '';
		if (strlen($filter_customer)>0) $url .= '&filter_customer=' . $this->request->get['filter_customer'];
		if (strlen($filter_firstname)>0) $url .= '&filter_firstname=' . urlencode(html_entity_decode($this->request->get['filter_firstname'], ENT_QUOTES, 'UTF-8'));							
		if (strlen($filter_lastname)>0) $url .= '&filter_lastname=' . urlencode(html_entity_decode($this->request->get['filter_lastname'], ENT_QUOTES, 'UTF-8'));	
		if (strlen($filter_status)>1) $url .= '&filter_status=' . $this->request->get['filter_status'];
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

		$filter_status = ( strlen($filter_status) > 1) ? $filter_status : '';

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'ดูแจ้งโอนเงินผิด',
			'href'      => $this->url->link('sale/order/refundlist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['orders'] = array();
		$data = array(
			'filter_customer'	    => $filter_customer,
			'filter_firstname'		=> $filter_firstname,
			'filter_lastname'		=> $filter_lastname,
			'filter_status'			=> $filter_status,
			'start'                 => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                 => $this->config->get('config_admin_limit')
		);

		$order_total = $this->model_sale_order->getTotalRefundOrders($data);
		$results = $this->model_sale_order->getRefundOrders($data);
    	foreach ($results as $result) {
			$this->data['orders'][] = array(
				'id'			=> $result['id'],
				'email'			=> $result['email'],
				'firstname'     => $result['firstname'],
				'lastname'      => $result['lastname'],
				'bank_name'     => $result['username'],
				'bank'			=> $result['bank'],
				'message'		=> $result['message'],
				'status'        => $result['status'],
				'cashback_type' => $result['cashback_type'],
				'read'			=> $result['read'],
				'amount'        => $this->currency->format($result['amount']),
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['date_transfer'])),
				'modify'		=> date($this->language->get('date_format_short'), strtotime($result['modify'])),
				'action'        => $this->url->link('sale/order/refunddetail', 'token=' . $this->session->data['token'] . '&id=' . $result['id'] . $url, 'SSL')
			);
		}


		$this->data['heading_title'] = 'ดูแจ้งโอนเงินผิด';
		$this->data['token'] = $this->session->data['token'];

		$url = '';
		if (strlen($filter_customer)>0) $url .= '&filter_customer=' . $this->request->get['filter_customer'];
		if (strlen($filter_firstname)>0) $url .= '&filter_firstname=' . urlencode(html_entity_decode($this->request->get['filter_firstname'], ENT_QUOTES, 'UTF-8'));							
		if (strlen($filter_lastname)>0) $url .= '&filter_lastname=' . urlencode(html_entity_decode($this->request->get['filter_lastname'], ENT_QUOTES, 'UTF-8'));	
		if (strlen($filter_status)>1) $url .= '&filter_status=' . $this->request->get['filter_status'];

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination']		= $pagination->render();
		$this->data['filter_firstname'] = $filter_firstname;
		$this->data['filter_lastname']	= $filter_lastname;
		$this->data['filter_customer']	= $filter_customer;
		$this->data['filter_status']	= $filter_status;

		$this->template = 'sale/order_refundlist.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}

  	public function refunddetail() {
		$this->language->load('sale/order');
		$this->document->setTitle('รายละเอียดการขอคืนเงิน');
		$this->load->model('sale/order');
		$id		= (isset($this->request->get['id']))  ? $this->request->get['id'] : '';

		$url = '';
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'รายละเอียดการขอคืนเงิน',
			'href'      => $this->url->link('sale/order/refundlist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$results = $this->model_sale_order->refundDetailsRead($id);

		if(isset($_POST)){
			if (isset($_POST['submit_no'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'disapprovel',$_POST);
			} elseif (isset($_POST['submit_yes'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'approved');
			} elseif (isset($_POST['transfer_success'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'transfer_success',$_POST);
			} elseif (isset($_POST['submit_done'])){
				$this->model_sale_order->adminSetStatusWrongTransfer($id,'done');
			}
		}

		$this->data['status'] = '';
		$this->data['orders'] = array();
		$results = $this->model_sale_order->getRefundDetails($id);
    	foreach ($results as $result) {
			$this->data['status'] = $result['status'];
			$this->data['orders'][] = array(
				'id'			=> $result['id'],
				'email'			=> $result['email'],
				'firstname'     => $result['firstname'],
				'lastname'      => $result['lastname'],
				'bank_name'     => $result['username'],
				'bank'			=> $result['bank'],
				'bank_account'	=> $result['bank_account'],
				'message'		=> $result['message'],
				'status'        => $result['status'],
				'cashback_type' => $result['cashback_type'],
				'read'			=> $result['read'],
				'amount'        => $this->currency->format($result['amount']),
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['date_transfer'])),
				'modify'		=> date($this->language->get('date_format_short'), strtotime($result['modify']))
			);
		}
		$results = $this->model_sale_order->getRefundDetailSuccess($id , 'wrong');

		$this->data['orderc'] = array();
		foreach ($results as $result) {
			$this->data['orderc'][] = array(
				'id'			=> $result['id'],
				'bank_name'     => $result['bank_name'],
				'bank'			=> $result['bank'],
				'bank_account'	=> $result['bank_account'],
				'message'		=> $result['note'],
				'cashback_type'			=> $result['cashback_type'],
				'amount'        => $this->currency->format($result['amount']),
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['datetime']))
			);
		}

		$this->data['heading_title'] = 'รายละเอียดการขอคืนเงิน';
		$this->data['token'] = $this->session->data['token'];
		$this->data['backtopending'] = $this->url->link('sale/order/admin_setback', 'token='.$this->session->data['token']."&id=".$id."&type=wrong" . $url, 'SSL');

		$this->template = 'sale/order_refunddetail.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}

	public function admin_setback(){
		$this->load->model('sale/order');
		$id		= (isset($this->request->get['id']))  ? $this->request->get['id'] : '';
		$type	= (isset($this->request->get['type']))  ? $this->request->get['type'] : '';
		$res = $this->model_sale_order->setBackToPending($id,$type);

		if ($type == 'order') $url = 'rollbackdetail';
		if ($type == 'wrong') $url = 'refunddetail';
		if ($type == 'cancel') $url = 'canceldetail';

		$this->redirect( $this->url->link('sale/order/'.$url, 'token='.$this->session->data['token']."&id=".$id  , 'SSL') );

	}



  	public function cancellist() {
		$this->language->load('sale/order');
		$this->document->setTitle('ดูOrder ถูกระบบยกเลิก');
		$this->load->model('sale/order');

		$filter_orderid			= (isset($this->request->get['filter_orderid']))  ? $this->request->get['filter_orderid'] : '';
		$filter_customer		= (isset($this->request->get['filter_customer']))  ? $this->request->get['filter_customer'] : '';
		$filter_firstname		= (isset($this->request->get['filter_firstname'])) ? $this->request->get['filter_firstname'] : '';
		$filter_lastname		= (isset($this->request->get['filter_lastname'])) ? $this->request->get['filter_lastname'] : '';
		$filter_status			= (isset($this->request->get['filter_status'])) ? $this->request->get['filter_status'] : '';
		$page					= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
		$url = '';
		if (strlen($filter_orderid)>0) $url .= '&filter_orderid=' . $this->request->get['filter_orderid'];
		if (strlen($filter_customer)>0) $url .= '&filter_customer=' . $this->request->get['filter_customer'];
		if (strlen($filter_firstname)>0) $url .= '&filter_firstname=' . urlencode(html_entity_decode($this->request->get['filter_firstname'], ENT_QUOTES, 'UTF-8'));							
		if (strlen($filter_lastname)>0) $url .= '&filter_lastname=' . urlencode(html_entity_decode($this->request->get['filter_lastname'], ENT_QUOTES, 'UTF-8'));	
		if (strlen($filter_status)>1) $url .= '&filter_status=' . $this->request->get['filter_status'];
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

		$filter_status = ( strlen($filter_status) > 1) ? $filter_status : '';

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'ดูOrder ถูกระบบยกเลิก',
			'href'      => $this->url->link('sale/order/cancellist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['orders'] = array();
		$data = array(
			'filter_orderid'	    => $filter_orderid,
			'filter_customer'	    => $filter_customer,
			'filter_firstname'		=> $filter_firstname,
			'filter_lastname'		=> $filter_lastname,
			'filter_status'			=> $filter_status,
			'start'                 => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                 => $this->config->get('config_admin_limit')
		);

		$order_total = $this->model_sale_order->getTotalCancelOrders($data);
		$results = $this->model_sale_order->getCancelOrders($data);
    	foreach ($results as $result) {
			$action = array();
						
			$action[] = array(
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/order/canceldetail', 'token=' . $this->session->data['token'] . '&id=' . $result['id'] . $url, 'SSL')
			);
			
			$this->data['orders'][] = array(
				'id'			=> $result['id'],
				'email'			=> $result['email'],
				'firstname'     => $result['firstname'],
				'lastname'      => $result['lastname'],
				'order_id'      => $result['order_id'],
				'bank'			=> $result['bank'],
				'message'		=> $result['message'],
				'status'        => $result['rstatus'],
				'cashback_type' => $result['cashback_type'],
				'read'			=> $result['read'],
				'amount'        => $this->currency->format($result['amount']),
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['date_transfer'])),
				'modify'		=> date($this->language->get('date_format_short'), strtotime($result['modify'])),
				'action'        => $action
			);
		}


		$this->data['heading_title'] = 'ดูOrder ถูกระบบยกเลิก';
		$this->data['text_missing'] = $this->language->get('text_missing');
		$this->data['token'] = $this->session->data['token'];

		$url = '';
		if (strlen($filter_orderid)>0) $url .= '&filter_orderid=' . $this->request->get['filter_orderid'];
		if (strlen($filter_customer)>0) $url .= '&filter_customer=' . $this->request->get['filter_customer'];
		if (strlen($filter_firstname)>0) $url .= '&filter_firstname=' . urlencode(html_entity_decode($this->request->get['filter_firstname'], ENT_QUOTES, 'UTF-8'));							
		if (strlen($filter_lastname)>0) $url .= '&filter_lastname=' . urlencode(html_entity_decode($this->request->get['filter_lastname'], ENT_QUOTES, 'UTF-8'));	
		if (strlen($filter_status)>1) $url .= '&filter_status=' . $this->request->get['filter_status'];

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination']		= $pagination->render();
		$this->data['filter_orderid']	= $filter_orderid;
		$this->data['filter_firstname'] = $filter_firstname;
		$this->data['filter_lastname']	= $filter_lastname;
		$this->data['filter_customer']	= $filter_customer;
		$this->data['filter_status']	= $filter_status;

		$this->load->model('localisation/order_status');
    	$this->data['order_statuses']	= $this->model_localisation_order_status->getOrderStatuses();

		$this->template = 'sale/order_cancellist.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}

  	public function canceldetail() {
		$this->language->load('sale/order');
		$this->document->setTitle('รายการสินค้า order');
		$this->load->model('sale/order');
		$id		= (isset($this->request->get['id']))  ? $this->request->get['id'] : '';

		$url = '';
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'รายการสินค้า order ',
			'href'      => $this->url->link('sale/order/cancellist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$results = $this->model_sale_order->cancelOrderDetailsRead($id);


		if(isset($_POST)){
			if (isset($_POST['submit_no'])){
				$this->model_sale_order->adminSetStatusCancel($id,'disapprovel',$_POST);
			} elseif (isset($_POST['submit_yes'])){
				$this->model_sale_order->adminSetStatusCancel($id,'approved');
			} elseif (isset($_POST['transfer_success'])){
				$this->model_sale_order->adminSetStatusCancel($id,'transfer_success',$_POST);
			} elseif (isset($_POST['submit_done'])){
				$this->model_sale_order->adminSetStatusCancel($id,'done');
			}
		}

 


		$id			= (isset($this->request->get['id']))  ? $this->request->get['id'] : '';
		$results = $this->model_sale_order->cancelOrderDetailsRead($id);

		$this->data['status'] = "";
		$this->data['orders'] = array();
		$results = $this->model_sale_order->getCancelOrderDetails($id);
		$orderid = "";$customerid = "";
    	foreach ($results as $result) {
			$orderid			= $result['order_id'];
			$customerid			= $result['user_id'];
			$this->data['status'] = $result['status'];

			$this->data['orders'][] = array(
				'id'			=> $result['id'],
				'bank'			=> $result['bank'],
				'bank_name'     => $result['username'],
				'bank_account'  => $result['bank_account'],
				'amount'		=> $result['amount'],
				'bank'			=> $result['bank'],
				'message'		=> $result['message'],
				'status'        => $result['status'],
				'cashback_type' => $result['cashback_type'],
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['date_transfer']))
			);
		}
		$this->data['products'] = array();
		$this->data['totals'] = 0;
		$results = $this->model_sale_order->getCancelOrdersProductDetail($orderid);
    	foreach ($results as $result) {
			$this->data['totals'] = $this->data['totals'] + $result['total'];
			$this->data['products'][] = array(
				'model'			=> $result['model'],
				'image'			=> HTTP_CATALOG."image/".$result['image'],
				'quantity'		=> $result['quantity'],
				'total'			=> $this->currency->format($result['total']) 
			);
		}

		$this->data['custoemrs'] = array();
		$results = $this->model_sale_order->getCancelCustomer($customerid);
    	foreach ($results as $result) {
			$this->data['custoemrs'][] = array(
				'email'			=> $result['email'],
				'name'			=> $result['firstname']." ".$result['lastname'],
				'gender'		=> $result['gender'],
				'address'		=> $result['address_1']." ".$result['address_2'],
				'city'			=> $result['city'],
				'postcode'		=> $result['postcode'],
				'mobile'		=> $result['mobile'],
				'bank_acc'		=> $result['bank_account_name'],
				'bank_account'	=> $result['bank_account'],
				'bankname'		=> $result['bankname'],
				'bank_branch'	=> $result['bank_branch']

			);
		}

		$results = $this->model_sale_order->getRefundDetailSuccess($id,'cancel');
		$this->data['orderc'] = array();
		foreach ($results as $result) {
			$this->data['orderc'][] = array(
				'id'			=> $result['id'],
				'bank_name'     => $result['bank_name'],
				'bank'			=> $result['bank'],
				'bank_account'	=> $result['bank_account'],
				'message'		=> $result['note'],
				'cashback_type'			=> $result['type'],
				'amount'        => $this->currency->format($result['amount']),
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['datetime']))
			);
		}

		$this->data['heading_title'] = 'รายการสินค้า order ';
		$this->data['token'] = $this->session->data['token'];
		$this->data['backtopending'] = $this->url->link('sale/order/admin_setback', 'token='.$this->session->data['token']."&id=".$id."&type=cancel" . $url, 'SSL');

		$this->template = 'sale/order_canceldetail.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}

	public function orderaddress() {
		$order_id = (isset($this->request->get['order_id']))  ? $this->request->get['order_id'] : null;

		$this->load->model('sale/order');

		if (  ($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_order->updateOrder( $this->request->post  );
		
			$this->session->data['success'] = $this->language->get('text_success');
			$url = $this->getURL();
			
			//$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$url = '';
		if (isset($this->request->get['order_id'])) $url .= '&order_id=' . $this->request->get['order_id'];	
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Edit Order',
			'href'      => $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "Edit Order";
		 
		$this->data['order'] = $this->model_sale_order->getOrder($order_id);
		$this->data['update'] = $this->url->link('sale/order/orderaddress', 'token=' . $this->session->data['token'] . '&order_id='.$order_id, 'SSL');

		//print_r($this->data['order']);

		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}


		$this->template = 'sale/order_address.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
	}



  	public function print_address() {
		$this->document->setTitle('Mayroses - print รายชื่อที่อยู่ลูกค้า');
		$this->data['heading_title'] = 'Mayroses - print รายชื่อที่อยู่ลูกค้า';
		$this->load->model('sale/order');
		$this->data['orders'] = array();
		$results = $this->model_sale_order->print_address();

    	foreach ($results as $result) {
			$this->data['orders'][] = array(
				'name'			=> $result['name'],
				'address'		=> $result['address'],
				'province'		=> $result['province'],
				'postcode'		=> $result['postcode'],
				'shipping_type'	=> $result['shipping_type'],
				'id'			=> $result['id']
			);
		}

		$this->print_address_footer();
/*
			$this->data['orders'][] = array(
				'name'			=> "จรีรัตน์ คุ้มสา",
				'address'		=> "55/95 หมู่ 2 ถนนกำแพงเพชร 6 แขวงลาดยาว เขตจตุจักร กทม ",
				'province'		=> "กรุงเทพมหานคร",
				'postcode'		=> "10900",
				'shipping_type'	=> "EMS",
				'id'			=> "266633"
			);*/
	}
  	public function print_address_e() {
		$this->document->setTitle('Mayroses - print รายชื่อที่อยู่ลูกค้า');
		$this->data['heading_title'] = 'Mayroses - print รายชื่อที่อยู่ลูกค้า';
		$this->load->model('sale/order');
		$this->data['orders'] = array();
		$results = $this->model_sale_order->print_address_e();
    	foreach ($results as $result) {
			$this->data['orders'][] = array(
				'name'			=> $result['name'],
				'address'		=> $result['address'],
				'province'		=> $result['province'],
				'postcode'		=> $result['postcode'],
				'shipping_type'	=> $result['shipping_type'],
				'id'			=> $result['id']
			);
		}
		$this->print_address_footer();
	}
  	public function print_address_r() {
		$this->document->setTitle('Mayroses - print รายชื่อที่อยู่ลูกค้า');
		$this->data['heading_title'] = 'Mayroses - print รายชื่อที่อยู่ลูกค้า';
		$this->load->model('sale/order');
		$this->data['orders'] = array();
		$results = $this->model_sale_order->print_address_r();
    	foreach ($results as $result) {
			$this->data['orders'][] = array(
				'name'			=> $result['name'],
				'address'		=> $result['address'],
				'province'		=> $result['province'],
				'postcode'		=> $result['postcode'],
				'shipping_type'	=> $result['shipping_type'],
				'id'			=> $result['id']
			);
		}
		$this->print_address_footer();
	}
  	public function print_address_e_n() {
		$this->document->setTitle('Mayroses - print รายชื่อที่อยู่ลูกค้า');
		$this->data['heading_title'] = 'Mayroses - print รายชื่อที่อยู่ลูกค้า';
		$this->load->model('sale/order');
		$this->data['orders'] = array();
		$results = $this->model_sale_order->print_address_e_n();
    	foreach ($results as $result) {
			$this->data['orders'][] = array(
				'name'			=> $result['name'],
				'address'		=> $result['address'],
				'province'		=> $result['province'],
				'postcode'		=> $result['postcode'],
				'shipping_type'	=> $result['shipping_type'],
				'id'			=> $result['id']
			);
		}
	
		
		for($i=0; $i<count($this->data['orders']); $i++){
			if(preg_match('/[^ กขฃคฅฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรลวศษสหฬอฮabcdefghijklmnopqrstuvwxyz]/', strtolower($this->data['orders'][$i]['name']))) {
				$this->data['orders'][$i]['original_name'] = $this->data['orders'][$i]['name'];
				$this->data['orders'][$i]['name'] = preg_replace('/[^ กขฃคฅฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรลวศษสหฬอฮ]/u', '', $this->data['orders'][$i]['name']);
			}
		}

		for($i=0; $i<count($this->data['orders']); $i++)
			for($j=$i; $j<count($this->data['orders']); $j++)
				if(strtolower($this->data['orders'][$j]['name']) < strtolower($this->data['orders'][$i]['name'])) {
					$tmp = $this->data['orders'][$i];
					$this->data['orders'][$i] = $this->data['orders'][$j];
					$this->data['orders'][$j] = $tmp;
				}

		for($i=0; $i<count($this->data['orders']); $i++)
			if(isset($this->data['orders'][$i]['original_name']))
				$this->data['orders'][$i]['name'] = $this->data['orders'][$i]['original_name'];

		$this->print_address_footer();
	}
  	public function print_address_r_n() {
		$this->document->setTitle('Mayroses - print รายชื่อที่อยู่ลูกค้า');
		$this->data['heading_title'] = 'Mayroses - print รายชื่อที่อยู่ลูกค้า';
		$this->load->model('sale/order');
		$this->data['orders'] = array();
		$results = $this->model_sale_order->print_address_r_n();
    	foreach ($results as $result) {
			$this->data['orders'][] = array(
				'name'			=> $result['name'],
				'address'		=> $result['address'],
				'province'		=> $result['province'],
				'postcode'		=> $result['postcode'],
				'shipping_type'	=> $result['shipping_type'],
				'id'			=> $result['id']
			);
		}

		
		for($i=0; $i<count($this->data['orders']); $i++){
			if(preg_match('/[^ กขฃคฅฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรลวศษสหฬอฮabcdefghijklmnopqrstuvwxyz]/', strtolower($this->data['orders'][$i]['name']))) {
				$this->data['orders'][$i]['original_name'] = $this->data['orders'][$i]['name'];
				$this->data['orders'][$i]['name'] = preg_replace('/[^ กขฃคฅฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรลวศษสหฬอฮ]/u', '', $this->data['orders'][$i]['name']);
			}
		}

		for($i=0; $i<count($this->data['orders']); $i++)
			for($j=$i; $j<count($this->data['orders']); $j++)
				if(strtolower($this->data['orders'][$j]['name']) < strtolower($this->data['orders'][$i]['name'])) {
					$tmp = $this->data['orders'][$i];
					$this->data['orders'][$i] = $this->data['orders'][$j];
					$this->data['orders'][$j] = $tmp;
				}

		for($i=0; $i<count($this->data['orders']); $i++)
			if(isset($this->data['orders'][$i]['original_name']))
				$this->data['orders'][$i]['name'] = $this->data['orders'][$i]['original_name'];

		$this->print_address_footer();
	}
	public function print_address_footer(){
		$this->template = 'sale/print_address.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}
}
?>
