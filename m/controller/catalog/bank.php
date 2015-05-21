<?php    
class ControllerCatalogBank extends Controller { 
	private $error = array();
  
  	public function index() {
		$this->language->load('catalog/manufacturer');
		$this->document->setTitle('Bank');
		$this->load->model('catalog/bank');
    	$this->getList();
  	}
  
  	public function insert() {
		$this->language->load('catalog/manufacturer');
    	$this->document->setTitle('Add Bank');
		$this->load->model('catalog/bank');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_bank->addBank($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success2');
			$url = '';
			$this->redirect($this->url->link('catalog/bank', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    
    	$this->getForm();
  	} 
   
  	public function update() {
		$this->language->load('catalog/manufacturer');
    	$this->document->setTitle('Update Bank');
		$this->load->model('catalog/bank');
		$this->load->model('catalog/note');
		

		$note_type = "bank";
    	if (isset($_POST['note_submit']) ){
			
			$this->model_catalog_note->addNote($this->request->post);
			$this->model_catalog_bank->updateNote( $this->request->post['flag'] , $this->request->get['id']);

			$this->redirect($this->url->link('catalog/bank/update', 'token=' . $this->session->data['token'] . $url . "&id=". $this->request->get['id'] , 'SSL'));

		}elseif (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_bank->editBank($this->request->get['id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success2');
			$url = '';
			$this->redirect($this->url->link('catalog/bank', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}elseif( isset($this->request->get['del']) ){

			$this->model_catalog_note->deleteNote( $note_type ,$this->request->get);
			$this->model_catalog_bank->updateNote( 0 , $this->request->get['id']);

			$this->redirect($this->url->link('catalog/bank/update', 'token=' . $this->session->data['token'] . $url . "&id=". $this->request->get['id'], 'SSL'));
		}
    
		$this->data['type_id'] = $this->request->get['id'];
		$this->data['note'] = $this->model_catalog_note->getNote($note_type ,$this->request->get['id']);


    	$this->getForm();
  	}   

  	public function delete() {
		$this->language->load('catalog/manufacturer');
    	$this->document->setTitle('Delete Bank');
		$this->load->model('catalog/bank');
			
		//if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDelete()) {
			$this->model_catalog_bank->deleteBank($this->request->get['id']);
//echo $this->request->get['id'];echo "xxxxxxxxxxxxxxxxxxxx";
			$this->session->data['success'] = $this->language->get('text_success2');
			$url = '';
			$this->redirect($this->url->link('catalog/bank', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	//}
	
    	//$this->getList();
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
       		'text'      => 'Bank',
			'href'      => $this->url->link('catalog/bank', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);	
		$this->data['insert'] = $this->url->link('catalog/bank/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/bank/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	
		$this->data['banks'] = array();
		
		$results = $this->model_catalog_bank->getBanks();
 
    	foreach ($results as $result) {
			$action = array();
			$deletes = array();
			$action[] = array(
				'text' => 'Edit',
				'href' => $this->url->link('catalog/bank/update', 'token=' . $this->session->data['token'] . '&id=' . $result['id'] . $url, 'SSL'),
				'htext' => 'Note',
				'hnote' => $this->url->link('catalog/bank/update', 'token=' . $this->session->data['token'] . '&id=' . $result['id'] . $url."#note", 'SSL')
			);
			$deletes[] = array(
				'text' => 'Delete',
				'href' => $this->url->link('catalog/bank/delete', 'token=' . $this->session->data['token'] . '&id=' . $result['id'] . $url, 'SSL')
			);

			$note_icon = ( $result['note_icon'] > 0) ? $result['note_icon'] : '0';
						
			$this->data['banks'][] = array(
				'id'				=> $result['id'],
				'bankname'			=> $result['bankname'],
				'bankcode'			=> $result['bankcode'],
				'account'			=> $result['bank_id'],
				'bank_type'			=> $result['bank_type'],
				'branch'			=> $result['sub_bank'],
				'flag'			    => $note_icon,
				'selected'			=> isset($this->request->post['selected']) && in_array($result['manufacturer_id'], $this->request->post['selected']),
				'action'			=> $action,
				'delete'			=> $deletes
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');		
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
 
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
 
		$this->template = 'catalog/bank_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
  
  	protected function getForm() {
     	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['error_warning']	= (isset($this->error['warning'])) ? $this->error['warning'] : '';
		$this->data['error_name']		= (isset($this->error['name'])) ? $this->error['name'] : '';

		$url = '';
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Bank',
			'href'      => $this->url->link('catalog/bank', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		if (!isset($this->request->get['id'])) {
			$this->data['action'] = $this->url->link('catalog/bank/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/bank/update', 'token=' . $this->session->data['token'] . '&id=' . $this->request->get['id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('catalog/bank', 'token=' . $this->session->data['token'] . $url, 'SSL');

    	if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$bank_info = $this->model_catalog_bank->getBank($this->request->get['id']);
    	}

		$this->data['token'] = $this->session->data['token'];


    	if (isset($this->request->post['account'])) {
      		$this->data['name'] = $this->request->post['account'];
    	} elseif (!empty($bank_info)) {
			$this->data['account'] = $bank_info['bank_id'];
		} else {	
      		$this->data['account'] = '';
    	}
    	if (isset($this->request->post['code'])) {
      		$this->data['code'] = $this->request->post['code'];
    	} elseif (!empty($bank_info)) {
			$this->data['code'] = $bank_info['bankcode'];
		} else {	
      		$this->data['code'] = '';
    	}
     	if (isset($this->request->post['name'])) {
      		$this->data['name'] = $this->request->post['name'];
    	} elseif (!empty($bank_info)) {
			$this->data['name'] = $bank_info['bankname'];
		} else {	
      		$this->data['name'] = '';
    	}
    	if (isset($this->request->post['branch'])) {
      		$this->data['branch'] = $this->request->post['branch'];
    	} elseif (!empty($bank_info)) {
			$this->data['branch'] = $bank_info['sub_bank'];
		} else {	
      		$this->data['branch'] = '';
    	}
    	if (isset($this->request->post['type'])) {
      		$this->data['type'] = $this->request->post['type'];
    	} elseif (!empty($bank_info)) {
			$this->data['type'] = $bank_info['bank_type'];
		} else {	
      		$this->data['type'] = '';
    	}

 
		$this->template = 'catalog/bank_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}  
	 
  	protected function validateForm() {
    	if (!$this->user->hasPermission('modify', 'catalog/bank')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}
	
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/bank')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}	
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	}
	
	public function autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/manufacturer');
			
			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);
			
			$results = $this->model_catalog_manufacturer->getManufacturers($data);
				
			foreach ($results as $result) {
				$json[] = array(
					'manufacturer_id' => $result['manufacturer_id'], 
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}		
		}

		$sort_order = array();
	  
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->setOutput(json_encode($json));
	}	
}
?>