<?php
class ControllerAccountEdit extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/edit');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('account/customer');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_account_customer->editCustomer($this->request->post);
			
			$this->load->model('account/address');
			if($this->model_account_address->getTotalAddresses() > 0){
				$this->model_account_address->editHomeAddress($this->request->post['address_id'],    $this->request->post);
			}else{
				$this->model_account_address->addAddress($this->request->post);
			}
			

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('account/account', '', 'SSL'));
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

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_edit'),
			'href'      => $this->url->link('account/edit', '', 'SSL'),       	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_details'] = $this->language->get('text_your_details');

		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_fax'] = $this->language->get('entry_fax');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}
		
		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}	
		
		if (isset($this->error['gender'])) {
			$this->data['error_gender'] = $this->error['gender'];
		} else {
			$this->data['error_gender'] = '';
		}


		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}	

		if (isset($this->error['mobile'])) {
			$this->data['error_mobile'] = $this->error['mobile'];
		} else {
			$this->data['error_mobile'] = '';
		}	

		$this->data['action'] = $this->url->link('account/edit', '', 'SSL');

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		}
 $this->load->model('checkout/order');
 $r =$this->model_checkout_order->reorderSizeColor('134');
//print_r($r);
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];

		if (isset($this->request->post['firstname'])) {
			$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($customer_info)) {
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (isset($customer_info)) {
			$this->data['lastname'] = $customer_info['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} elseif (isset($customer_info)) {
			$this->data['email'] = $customer_info['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['gender'])) {
			$this->data['gender'] = $this->request->post['gender'];
		} elseif (isset($customer_info)) {
			$this->data['gender'] =  $customer_info['gender'];
		} else {
			$this->data['gender'] = '';
		}


		if (isset($customer_info))
			if( strlen( $customer_info['birthday'] ) > 0  ){
				$peace = explode(  '_' , $customer_info['birthday'] );
			}


		if (isset($this->request->post['day'])) {
			$this->data['day'] = $this->request->post['day'];
		} elseif (isset($customer_info)) {
			if( strlen( $customer_info['birthday'] ) > 0  ){
				$this->data['day'] =  $peace[0];
			}else{
				$this->data['day'] =  '';
			}
		} else {
			$this->data['day'] = '';
		}

		if (isset($this->request->post['month'])) {
			$this->data['month'] = $this->request->post['month'];
		} elseif (isset($customer_info)) {
			if( strlen( $customer_info['birthday'] ) > 0  ){
				$this->data['month'] =  $peace[1];
			}else{
				$this->data['month'] =  '';
			}
		} else {
			$this->data['month'] = '';
		}

		if (isset($this->request->post['year'])) {
			$this->data['year'] = $this->request->post['year'];
		} elseif (isset($customer_info)) {
			if( strlen( $customer_info['birthday'] ) > 0  ){
				$this->data['year'] =  $peace[2];
			}else{
				$this->data['year'] =  '';
			}
		} else {
			$this->data['year'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$this->data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($customer_info)) {
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->request->post['mobile'])) {
			$this->data['mobile'] = $this->request->post['mobile'];
		} elseif (isset($customer_info)) {
			$this->data['mobile'] =  $customer_info['mobile'];
		} else {
			$this->data['mobile'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$this->data['fax'] = $this->request->post['fax'];
		} elseif (isset($customer_info)) {
			$this->data['fax'] = $customer_info['fax'];
		} else {
			$this->data['fax'] = '';
		}

		if (isset($this->request->post['facebook'])) {
			$this->data['facebook'] = $this->request->post['facebook'];
		} elseif (isset($customer_info)) {
			$this->data['facebook'] = $customer_info['facebook'];
		} else {
			$this->data['facebook'] = '';
		}

		if (isset($this->request->post['line'])) {
			$this->data['line'] = $this->request->post['line'];
		} elseif (isset($customer_info)) {
			$this->data['line'] = $customer_info['line'];
		} else {
			$this->data['line'] = '';
		}

		$this->load->model('account/address');
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$results_address = $this->model_account_address->getHomeAddress();
		}
		$results_zone = $this->model_account_address->getProvince();
		//print_r( $results_address );

		if (isset($this->request->post['address_1'])) {
			$this->data['address_1'] = $this->request->post['address_1'];
		} elseif (isset($customer_info)) {
			$this->data['address_1'] = $results_address['address_1'];
			$this->data['address_id'] = $results_address['address_id'];
		} else {
			$this->data['address_1'] = '';
		}
		

		if (isset($this->request->post['postcode'])) {
			$this->data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($customer_info)) {
			$this->data['postcode'] = $results_address['postcode'];
		} else {
			$this->data['postcode'] = '';
		}

		if (isset($this->request->post['zone_id'])) {
			$this->data['zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($customer_info)) {
			$this->data['zone_id'] = $results_address['zone_id'];
		} else {
			$this->data['zone_id'] = '';
		}
		$this->data['zonecode'] = $results_zone;


		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/edit.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/edit.tpl';
		} else {
			$this->template = 'default/template/account/edit.tpl';
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

	protected function validate() {
		if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
		if (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		/*if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen($this->request->post['mobile']) < 3) || (utf8_strlen($this->request->post['mobile']) > 32)) {
			$this->error['mobile'] = $this->language->get('error_telephone');
		}*/

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>