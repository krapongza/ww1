<?php 
class ControllerAccountBank extends Controller { 
	public function index() {
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/bank', '', 'SSL');
	  
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	} 
	
		$this->language->load('account/bank');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')  ) {
			$this->model_account_customer->editBank($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('account/bank', '', 'SSL'));
		}

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
		
		if (isset($this->session->data['success'])) {
    		$this->data['success'] = $this->session->data['success'];
			
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
    	$this->data['heading_title'] = $this->language->get('heading_title');


		$this->data['bank_name'] = $this->language->get('bank_name');
		$this->data['bank_acc'] = $this->language->get('bank_acc');
		$this->data['bank_bran'] = $this->language->get('bank_bran');
		$this->data['acc_name'] = $this->language->get('acc_name');


		$this->data['bank_nam'] = '';
		$this->data['bank_account'] = '';
		$this->data['bank_branch'] = '';
		$this->data['account_name'] = '';

    	$this->data['text_my_account'] = $this->language->get('text_my_account');
 


		$this->data['action'] = $this->url->link('account/bank', '', 'SSL');

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

		}

		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];


		if (isset($this->request->post['bank_name'])) {
			$this->data['bank_name'] = $this->request->post['bank_name'];
		} elseif (isset($customer_info)) {
			$this->data['bank_name'] = $customer_info['bank_name'];
		} else {
			$this->data['bank_name'] = '';
		}

		if (isset($this->request->post['bank_account'])) {
			$this->data['bank_account'] = $this->request->post['bank_account'];
		} elseif (isset($customer_info)) {
			$this->data['bank_account'] = $customer_info['bank_account'];
		} else {
			$this->data['bank_account'] = '';
		}

		if (isset($this->request->post['bank_branch'])) {
			$this->data['bank_branch'] = $this->request->post['bank_branch'];
		} elseif (isset($customer_info)) {
			$this->data['bank_branch'] = $customer_info['bank_branch'];
		} else {
			$this->data['bank_branch'] = '';
		}

		if (isset($this->request->post['bank_account_name'])) {
			$this->data['bank_account_name'] = $this->request->post['bank_account_name'];
		} elseif (isset($customer_info)) {
			$this->data['bank_account_name'] = $customer_info['bank_account_name'];
		} else {
			$this->data['bank_account_name'] = '';
		}


		$this->load->model('account/bank');
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$results_bank = $this->model_account_bank->getBank( );
		}
		$this->data['results_bank'] = $results_bank;
		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/bank.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/bank.tpl';
		} else {
			$this->template = 'default/template/account/bank.tpl';
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