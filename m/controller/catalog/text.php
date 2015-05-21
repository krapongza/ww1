<?php    
class ControllerCatalogText extends Controller { 
	private $error = array();
  
  	public function index() {
		$this->language->load('catalog/manufacturer');
		$this->document->setTitle('Text');
		$this->load->model('catalog/text');
    	$this->getList();
  	}
   
  	public function update() {
		$this->language->load('catalog/manufacturer');
    	$this->document->setTitle('Update text');
		$this->load->model('catalog/text');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_catalog_text->editText(  $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success3');
			$url = '';
			$this->redirect($this->url->link('catalog/text', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getForm();
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
       		'text'      => 'Text',
			'href'      => $this->url->link('catalog/text', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);	
		$this->data['insert'] = $this->url->link('catalog/text/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['update'] = $this->url->link('catalog/text/update', 'token=' . $this->session->data['token'] . $url, 'SSL');	
		$this->data['texts'] = array();
		
		$results = $this->model_catalog_text->getText();
    	foreach ($results as $result) {
			$action = array();
			$deletes = array();
			$action[] = array(
				'text' => 'Edit',
				'href' => $this->url->link('catalog/text/update', 'token=' . $this->session->data['token'] . '&id=' . $result['id'] . $url, 'SSL')
			);
 					
			$this->data['texts'][] = array(
				'id'				=> $result['id'],
				'text'				=> $result['text'],
				'link'				=> $result['link'],
				'status'			=> $result['enable'],
				'action'			=> $action 
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
		$this->template = 'catalog/text_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
	}
	 


 
 	
}
?>