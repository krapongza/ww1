<?php 
class ControllerAccountCredit extends Controller {
	private $error = array();
		
	public function index() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
 
    	//$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setTitle("Credit History");
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

		$this->data['heading_title'] = "Credit Statement";//$this->language->get('heading_title');
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
		
		$this->language->load('account/order');
		$this->load->model('account/order');
		$this->load->model('account/customer');

		$this->data['orders'] = array();
		
		$order_total = $this->model_account_customer->getTotalCredit();
		
		$results = $this->model_account_customer->getCredit(($page - 1) * 10, 10);
	
		$i=1;
		foreach ($results as $result) {
 
 			$TH_Month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
			$date = new DateTime($result['date_added']);
			$original_date = (int)$date->format('d');
			$original_month = (int)$date->format('m') -1; //$date->format('d M Y H:i:s');
			$original_year = (int)$date->format('Y')+543;
			$original_time = $date->format('H:i:s');
			$new_date = $original_date." ".$TH_Month[$original_month]." ".$original_year;
			$new_time = $original_time;

			$used_or_add =  ( $result['used_or_add'] == "1" )?"credit used":"credit add";
			$admin_add =  ( $result['admin_add'] == "0" )?"":$result['admin_add'];
 
			$balance_credit =  ( $result['used_or_add'] == "1" )?($result['old_credit'] - $result['credit']):($result['old_credit'] + $result['credit']);


			if($result['status'] == 1)
			$this->data['credits'][] = array(
				'id'			=>  $i,
				'order_id'		 => $result['order_id'],
				'used_credit'    => $result['credit'],
				'old_credit'	 => $result['old_credit'],
				'balance_credit'    => $balance_credit,
				'date_added'	=> $new_date , 
				'time_add'		=> $new_time ,
				'used_or_add'	=> $used_or_add ,
				'admin_add'		=> $admin_add ,
				'remark'		=> $result['remark'] 
			);
			$i++;
		 
		}

 
		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/credit', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/credit', '', 'SSL');


		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/credit_history.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/credit_history.tpl';
		} else {
			$this->template = 'default/template/account/credit_history.tpl';
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
?>