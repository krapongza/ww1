<?php    
class ControllerSaleDiscount extends Controller { 
	private $error = array();
  
  	public function index() {
		$this->language->load('sale/discount');
		$this->document->setTitle('Discount');
		$this->load->model('sale/discount');
    	$this->getList();
  	}
   
  	public function update() {
		$this->language->load('sale/discount');
    	$this->document->setTitle('Update Discount');
		$this->load->model('sale/discount');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_discount->editText(  $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getForm();
  	}   

  	public function updateLevel() {
		$this->language->load('sale/discount');
    	$this->document->setTitle('Update Discount');
		$this->load->model('sale/discount');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_discount->updateLevel(  $this->request->post  );
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	} 
  	public function updateTime() {
		$this->language->load('sale/discount');
    	$this->document->setTitle('Update Discount');
		$this->load->model('sale/discount');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_discount->updateTime(  $this->request->post  );
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	} 
  	public function updateGlobal() {
		$this->language->load('sale/discount');
    	$this->document->setTitle('Update Discount');
		$this->load->model('sale/discount');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_discount->updateGlobal(  $this->request->post  );
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	} 
  	public function updateVIP() {
		$this->language->load('sale/discount');
    	$this->document->setTitle('Update Discount');
		$this->load->model('sale/discount');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_discount->updateVIP(  $this->request->post  );
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	} 
  	public function updatePaysbuy() {
		$this->language->load('sale/discount');
    	$this->document->setTitle('Update Discount');
		$this->load->model('sale/discount');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_sale_discount->updatePaysbuy(  $this->request->post  );
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	} 

  	protected function getList() {
		$url = '';
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Discount',
			'href'      => $this->url->link('sale/discount', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);	
		
		$levels = $this->model_sale_discount->getLevel();
    	foreach ($levels as $result) {
 					
			$this->data['levels'][] = array(
				'id'				=> $result['id'],
				'level_name'		=> $result['level_name'],
				'point_min'			=> $result['point_min'],
				'point_max'			=> $result['point_max'],
				'discount'			=> $result['discount']
			);  
		}	

		$times = $this->model_sale_discount->getTime();
    	foreach ($times as $result) {
 					
			$this->data['times'][] = array(
				'id'				=> $result['id'],
				'discount'			=> $result['discount'],
				'date_start'		=> $result['date_start'],
				'date_end'			=> $result['date_end']
			);  
		}

		$this->data['vip'] = $this->model_sale_discount->getVIP();


		$this->data['updateLevel'] = $this->url->link('sale/discount/updateLevel', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['updateTime'] = $this->url->link('sale/discount/updateTime', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['updateGlobal'] = $this->url->link('sale/discount/updateGlobal', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['updateVIP'] = $this->url->link('sale/discount/updateVIP', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['updatePaysbuy'] = $this->url->link('sale/discount/updatePaysbuy', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$others = $this->model_sale_discount->getOtherDiscount();
		$this->data['global_discount']			= $others[0]['value'];
		$this->data['global_discount_status']	= $others[1]['value'];
		$this->data['time_discount_status']		= $others[2]['value'];
		$this->data['priority_discount']		= $others[3]['value'];
		$this->data['paysbuy_discount']			= $others[4]['value'];


 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		$this->template = 'sale/discount_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}
	 


 
 	
}
?>