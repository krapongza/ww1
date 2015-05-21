<?php 
class ControllerCatalogProduct extends Controller {
	private $error = array(); 
     
  	public function index() {
		$this->language->load('catalog/product');
		$this->document->setTitle($this->language->get('heading_title')); 
		$this->load->model('catalog/product');
		
		$this->getList();
  	}
  
  	public function insert() {
    	$this->language->load('catalog/product');

    	$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('catalog/product');
		
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			//print_r($this->request->post);
			$this->model_catalog_product->addProduct($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
	  
			$url = '';
			if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_model'])) $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_price'])) $url .= '&filter_price=' . $this->request->get['filter_price'];
			if (isset($this->request->get['filter_quantity'])) $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			if (isset($this->request->get['filter_status'])) $url .= '&filter_status=' . $this->request->get['filter_status'];	
			if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
			if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
			
			$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
	
		$this->data['type_id'] = '';
		$this->data['note'] = array();

    	$this->getForm();
  	}

  	public function update() {
    	$this->language->load('catalog/product');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/product');
		$this->load->model('catalog/note');


		$note_type = "product";
    	if (isset($_POST['note_submit']) ){
			
			$this->model_catalog_note->addNote($this->request->post);
			$this->model_catalog_product->updateNote( $this->request->post['flag'] , $this->request->get['product_id']);

			$this->redirect($this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . $url . "&product_id=". $this->request->get['product_id'] , 'SSL'));

		}elseif (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$po_array = array();
			foreach($this->request->post['product_option'][0]['product_option_value'] as $data){
				$arr2 = $data['property_1'].",".$data['property_2'];
				if( in_array($arr2 , $po_array) ) $this->error['warning'] = "Color or Size has duplicated.";
				else array_push($po_array , $arr2);
			}
			if(!$this->error){

				$this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post);

				if(($this->request->post['old_stock_status_id'] == 10)&&($this->request->post['stock_status_id']==5)){
					$this->model_catalog_product->removeWishList($this->request->get['product_id'], $this->request->post);
				}
				if(strlen($this->request->post['old_model']) > 0){
					$this->model_catalog_product->moveWishListToNewProduct($this->request->get['product_id'], $this->request->post['old_model']);
					$this->model_catalog_product->moveReviewToNewProduct($this->request->get['product_id'], $this->request->post['old_model']);
					$this->model_catalog_product->hideProduct( $this->request->post['old_model'] );
				}
				$this->session->data['success'] = $this->language->get('text_success');
				
				$url = '';
				if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
				if (isset($this->request->get['filter_model'])) $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
				if (isset($this->request->get['filter_price'])) $url .= '&filter_price=' . $this->request->get['filter_price'];
				if (isset($this->request->get['filter_quantity'])) $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
				if (isset($this->request->get['filter_status'])) $url .= '&filter_status=' . $this->request->get['filter_status'];	
				if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
				if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
				if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

				$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}elseif( isset($this->request->get['del']) ){

			$this->model_catalog_note->deleteNote( $note_type ,$this->request->get);
			$this->model_catalog_product->updateNote( 0 , $this->request->get['product_id']);

			$this->redirect($this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . $url . "&product_id=". $this->request->get['product_id'], 'SSL'));
		}

		$this->data['type_id'] = $this->request->get['product_id'];
		$this->data['note'] = $this->model_catalog_note->getNote($note_type ,$this->request->get['product_id']);


    	$this->getForm();
  	}

  	public function delete() {
    	$this->language->load('catalog/product');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/product');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_model'])) $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_price'])) $url .= '&filter_price=' . $this->request->get['filter_price'];
			if (isset($this->request->get['filter_quantity'])) $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			if (isset($this->request->get['filter_status'])) $url .= '&filter_status=' . $this->request->get['filter_status'];	
			if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
			if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
			
			$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

    	$this->getList();
  	}

  	public function copy() {
    	$this->language->load('catalog/product');
    	$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
		
		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
	  		}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_model'])) $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_price'])) $url .= '&filter_price=' . $this->request->get['filter_price'];
			if (isset($this->request->get['filter_quantity'])) $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			if (isset($this->request->get['filter_status'])) $url .= '&filter_status=' . $this->request->get['filter_status'];
			if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
			if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
			
			$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	}
	
  	public function repidupdate() {
    	$this->language->load('catalog/product');
    	$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
		
		//print_r($this->request->post);
		if ( isset($this->request->post['selected']) ) {
			
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->changeTypeProduct($product_id , $this->request->post['change_type'] );
	  		}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_model'])) $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_price'])) $url .= '&filter_price=' . $this->request->get['filter_price'];
			if (isset($this->request->get['filter_quantity'])) $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			if (isset($this->request->get['filter_status'])) $url .= '&filter_status=' . $this->request->get['filter_status'];
			if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
			if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
			
			//$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    	$this->getList();
  	}


  	public function table() {
    	$this->language->load('catalog/product');
    	$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
		
 
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_catalog_product->updateTableDetails( $this->request->post );
	  		 
			$this->session->data['success'] = $this->language->get('text_success');
			//$this->redirect($this->url->link('catalog/product/table', 'token=' . $this->session->data['token'] , 'SSL'));
		}
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Product Table Details',
			'href'      => $this->url->link('catalog/product/table', 'token=' . $this->session->data['token'] , 'SSL'),       		
      		'separator' => ' :: '
   		);

		$this->data['token'] = $this->session->data['token'];
		$this->data['update'] = $this->url->link('catalog/product/table', 'token=' . $this->session->data['token']  , 'SSL');

		$results = $this->model_catalog_product->getTableDetails($data='');
		foreach ($results as $result) {
      		$this->data['products'][] = array(
				'id'		=> $result['id'],
				'msg'       => $result['msg'] 
			);
    	}

 		$this->data['error_warning']	= (isset($this->error['warning'])) ? $this->error['warning'] : '';
		$this->data['success']			= (isset($this->session->data['success'])) ? $this->session->data['success'] : '';


		$this->template = 'catalog/product_table.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());

  	}

	
  	protected function getList() {
		
		$filter_name	= (isset($this->request->get['filter_name'])) ? $this->request->get['filter_name'] : null ;
		$filter_model	= (isset($this->request->get['filter_model'])) ? $this->request->get['filter_model'] : null;
		$filter_price	= (isset($this->request->get['filter_price'])) ? $this->request->get['filter_price'] : null;
		$filter_quantity = (isset($this->request->get['filter_quantity'])) ? $this->request->get['filter_quantity'] : null;
		$filter_status	= (isset($this->request->get['filter_status'])) ? $this->request->get['filter_status'] : null;
		$filter_cat	= (isset($this->request->get['filter_cat'])) ? $this->request->get['filter_cat'] : null;
		$filter_supplier	= (isset($this->request->get['filter_supplier'])) ? $this->request->get['filter_supplier'] : null;
		$filter_flag	= (isset($this->request->get['filter_flag'])) ? $this->request->get['filter_flag'] : null;

		$sort			= (isset($this->request->get['sort'])) ? $this->request->get['sort'] : 'p.product_id';
		$order			= (isset($this->request->get['order'])) ? $this->request->get['order'] : 'DESC';
		$page			= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		$url = '';
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}	
		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}	
		if (isset($this->request->get['filter_cat'])) {
			$url .= '&filter_cat=' . $this->request->get['filter_cat'];
		}	
		if (isset($this->request->get['filter_flag'])) {
			$url .= '&filter_flag=' . $this->request->get['filter_flag'];
		}	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'),       		
      		'separator' => ' :: '
   		);
		
		$this->data['update'] = $this->url->link('catalog/product/repidupdate', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['hide'] = $this->url->link('catalog/product/hide', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['insert'] = $this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['copy'] = $this->url->link('catalog/product/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');	
		$this->data['delete'] = $this->url->link('catalog/product/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
    	
		$this->data['products'] = array();
		$data = array(
			'filter_name'	  => $filter_name, 
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'filter_cat'	  => $filter_cat,
			'filter_supplier'   => $filter_supplier,
			'filter_flag'	  => $filter_flag,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit')
		);
		
		$this->load->model('tool/image');
		
		$product_total = $this->model_catalog_product->getNewTotalProducts($data);
		$results = $this->model_catalog_product->getNewProducts($data);
				    	
		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
				'htext' => 'Note',
				'hnote' => $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url."#note", 'SSL')
			);
		
			if ($result['pimage'] && file_exists(DIR_IMAGE . $result['pimage'])) {
				$image = $this->model_tool_image->resize($result['pimage'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
			}
	
			$special = false;
			
			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);
			
			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] < date('Y-m-d')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] > date('Y-m-d'))) {
					$special = $product_special['price'];
			
					break;
				}					
			}

			$note_icon = ( $result['pnote'] > 0) ? $result['pnote'] : '0';
			 
			
			 
      		$this->data['products'][] = array(
				'product_id' => $result['product_id'],
				'name'       => $result['pname'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'special'    => $special,
				'image'      => $image,
				'quantity'   => $result['quantity'],
				'flag'		 => $note_icon,
				'mname'      => $result['mname'],
				'sname'      => $result['sname'],
				'cat'		 => $result['cname'],
				'pstatus'	 => $result['pstatus'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'selected'   => isset($this->request->post['selected']) && in_array($result['product_id'], $this->request->post['selected']),
				'link'		 => HTTP_CATALOG."product.html?product_id=".$result['product_id'],
				'cus_buy'	 => $this->url->link('report/sale_order/whobuy', 'token=' . $this->session->data['token'] . '&html=html&product_id='.$result['model'] . $url, 'SSL'),
				'action'     => $action
			);
    	}
		
		$this->data['costz'] = $this->model_catalog_product->getGlobalPrice();

		
		$this->data['heading_title'] = $this->language->get('heading_title');		
				
		$this->data['text_enabled'] = $this->language->get('text_enabled');		
		$this->data['text_disabled'] = $this->language->get('text_disabled');		
		$this->data['text_no_results'] = $this->language->get('text_no_results');		
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');		
			
		$this->data['column_image'] = $this->language->get('column_image');		
		$this->data['column_name'] = $this->language->get('column_name');		
		$this->data['column_model'] = $this->language->get('column_model');		
		$this->data['column_price'] = $this->language->get('column_price');		
		$this->data['column_quantity'] = $this->language->get('column_quantity');		
		$this->data['column_status'] = $this->language->get('column_status');		
		$this->data['column_action'] = $this->language->get('column_action');		
				
		$this->data['button_copy'] = $this->language->get('button_copy');		
		$this->data['button_insert'] = $this->language->get('button_insert');		
		$this->data['button_delete'] = $this->language->get('button_delete');		
		$this->data['button_filter'] = $this->language->get('button_filter');
		 
 		$this->data['token'] = $this->session->data['token'];
		
 		$this->data['error_warning']	= (isset($this->error['warning'])) ? $this->error['warning'] : '';
		$this->data['success']			= (isset($this->session->data['success'])) ? $this->session->data['success'] : '';

		$url = '';
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}	
		if (isset($this->request->get['filter_cat'])) {
			$url .= '&filter_cat=' . $this->request->get['filter_cat'];
		}	
		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}
		if (isset($this->request->get['filter_flag'])) {
			$url .= '&filter_flag=' . $this->request->get['filter_flag'];
		}	
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
					
		$this->data['sort_name'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.model' . $url, 'SSL');
		$this->data['sort_price'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, 'SSL');
		$this->data['sort_quantity'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.quantity' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=s.name' . $url, 'SSL');
		$this->data['sort_supplier'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=m.name' . $url, 'SSL');
		$this->data['sort_order'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');

		
		$url = '';
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_cat'])) {
			$url .= '&filter_cat=' . $this->request->get['filter_cat'];
		}	
		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}
		if (isset($this->request->get['filter_flag'])) {
			$url .= '&filter_flag=' . $this->request->get['filter_flag'];
		}	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}								
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
	
		$this->data['filter_status']	= $this->model_catalog_product->getProductStatus();
		$this->data['filter_name']		= $filter_name;
		$this->data['filter_model']		= $filter_model;
		$this->data['filter_price']		= $filter_price;
		$this->data['filter_quantity']	= $filter_quantity;
		$this->data['filter_cat']		= $filter_cat;
		$this->data['filter_supplier']	= $filter_supplier;
		$this->data['filter_flag']		= $filter_flag;
		//$this->data['filter_status'] = $filter_status;
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'catalog/product_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	}

  	protected function getForm() {
    	$this->data['heading_title'] = $this->language->get('heading_title');
 
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
    	$this->data['text_none'] = $this->language->get('text_none');
    	$this->data['text_yes'] = $this->language->get('text_yes');
    	$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_plus'] = $this->language->get('text_plus');
		$this->data['text_minus'] = $this->language->get('text_minus');
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');
		$this->data['text_option'] = $this->language->get('text_option');
		$this->data['text_option_value'] = $this->language->get('text_option_value');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_percent'] = $this->language->get('text_percent');
		$this->data['text_amount'] = $this->language->get('text_amount');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$this->data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword');
    	$this->data['entry_model'] = $this->language->get('entry_model');
		$this->data['entry_sku'] = $this->language->get('entry_sku');
		$this->data['entry_upc'] = $this->language->get('entry_upc');
		$this->data['entry_ean'] = $this->language->get('entry_ean');
		$this->data['entry_jan'] = $this->language->get('entry_jan');
		$this->data['entry_isbn'] = $this->language->get('entry_isbn');
		$this->data['entry_mpn'] = $this->language->get('entry_mpn');
		$this->data['entry_location'] = $this->language->get('entry_location');
		$this->data['entry_minimum'] = $this->language->get('entry_minimum');
		$this->data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
    	$this->data['entry_shipping'] = $this->language->get('entry_shipping');
    	$this->data['entry_date_available'] = $this->language->get('entry_date_available');
    	$this->data['entry_quantity'] = $this->language->get('entry_quantity');
		$this->data['entry_stock_status'] = $this->language->get('entry_stock_status');
    	$this->data['entry_price'] = $this->language->get('entry_price');
		$this->data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$this->data['entry_points'] = $this->language->get('entry_points');
		$this->data['entry_option_points'] = $this->language->get('entry_option_points');
		$this->data['entry_subtract'] = $this->language->get('entry_subtract');
    	$this->data['entry_weight_class'] = $this->language->get('entry_weight_class');
    	$this->data['entry_weight'] = $this->language->get('entry_weight');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension');
		$this->data['entry_length'] = $this->language->get('entry_length');
    	$this->data['entry_image'] = $this->language->get('entry_image');
    	$this->data['entry_download'] = $this->language->get('entry_download');
    	$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['entry_filter'] = $this->language->get('entry_filter');
		$this->data['entry_related'] = $this->language->get('entry_related');
		$this->data['entry_attribute'] = $this->language->get('entry_attribute');
		$this->data['entry_text'] = $this->language->get('entry_text');
		$this->data['entry_option'] = $this->language->get('entry_option');
		$this->data['entry_option_value'] = $this->language->get('entry_option_value');
		$this->data['entry_required'] = $this->language->get('entry_required');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['entry_priority'] = $this->language->get('entry_priority');
		$this->data['entry_tag'] = $this->language->get('entry_tag');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_reward'] = $this->language->get('entry_reward');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
				
    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_attribute'] = $this->language->get('button_add_attribute');
		$this->data['button_add_option'] = $this->language->get('button_add_option');
		$this->data['button_add_option_value'] = $this->language->get('button_add_option_value');
		$this->data['button_add_discount'] = $this->language->get('button_add_discount');
		$this->data['button_add_special'] = $this->language->get('button_add_special');
		$this->data['button_add_image'] = $this->language->get('button_add_image');
		$this->data['button_remove'] = $this->language->get('button_remove');
		
    	$this->data['tab_general'] = $this->language->get('tab_general');
    	$this->data['tab_data'] = $this->language->get('tab_data');
		$this->data['tab_attribute'] = $this->language->get('tab_attribute');
		$this->data['tab_option'] = $this->language->get('tab_option');		
		$this->data['tab_discount'] = $this->language->get('tab_discount');
		$this->data['tab_special'] = $this->language->get('tab_special');
    	$this->data['tab_image'] = $this->language->get('tab_image');		
		$this->data['tab_links'] = $this->language->get('tab_links');
		$this->data['tab_reward'] = $this->language->get('tab_reward');
		$this->data['tab_design'] = $this->language->get('tab_design');
		 



 		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';
 		$this->data['error_name'] = (isset($this->error['name'])) ? $this->error['name'] : array();
 		$this->data['error_meta_description'] = (isset($this->error['meta_description'])) ? $this->error['meta_description'] : array();	
   		$this->data['error_description'] = (isset($this->error['description'])) ? $this->error['description'] : array();
   		$this->data['error_model'] = (isset($this->error['model'])) ? $this->error['model'] : '';	
		$this->data['error_date_available'] = (isset($this->error['date_available'])) ? $this->error['date_available'] : '';



		$url = '';
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}	
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}					
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
									
		if (!isset($this->request->get['product_id'])) {
			$this->data['action'] = $this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['token'] = $this->session->data['token'];
		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->data['sku'] = '';
		$this->data['upc'] = '';
		$this->data['ean'] = '';
		$this->data['jan'] = '';
		$this->data['isbn'] = '';
		$this->data['mpn'] = '';							
		$this->data['location'] = '';
		$this->data['product_store'] = array(0);
		$this->data['shipping'] = 1;
		$this->data['tax_class_id'] = 0;
		$this->data['subtract'] = 1;
		
/*
 		$size_info = array();
		if ($data['size_info_f1'] != '')  $size_info[] = $data['size_info_f1'].':'.$data['size_info_v1'];
		if ($data['size_info_f2'] != '')  $size_info[] = $data['size_info_f2'].':'.$data['size_info_v2'];
		if ($data['size_info_f3'] != '')  $size_info[] = $data['size_info_f3'].':'.$data['size_info_v3'];
		if ($data['size_info_f4'] != '')  $size_info[] = $data['size_info_f4'].':'.$data['size_info_v4'];
		if ($data['size_info_f5'] != '')  $size_info[] = $data['size_info_f5'].':'.$data['size_info_v5'];
		if ($data['size_info_f6'] != '')  $size_info[] = $data['size_info_f6'].':'.$data['size_info_v6'];
		$size_info = implode(',',$size_info);

		$size_info_value = @explode(',',$size_info);
		foreach ($size_info_value as $row){
			$t = @explode(':',$row);
			$sf['f'][] = @$t[0];
			$sf['v'][] = @$t[1];
		}



*/
		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
    	}
		$this->data['udate'] = $this->model_catalog_product->getGolbalPriceDate();
		$this->data['costz'] = $this->model_catalog_product->getGlobalPrice();
		if (!empty($product_info)) {
			$this->data['date_added'] = $product_info['date_added'];
		} else {
      		$this->data['date_added'] = '';
    	}

		if (isset($this->request->post['product_description'])) {
			$this->data['product_description'] = $this->request->post['product_description'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$this->data['product_description'] = array();
		}
	 
		/*if($this->request->get['product_id']){
			$this->data['product_id'] = $this->request->post['product_id'];
		}elseif (!empty($product_info)) {
			$this->data['product_id'] = $product_info['product_id'];
		} else {
      		$this->data['product_id'] = '';
    	}*/

		if (!empty($product_info)) {
			$this->data['size_info'] = $product_info['size_info'];
		} else {
      		$this->data['size_info'] = '';
    	}

		if (isset($this->request->post['ch_price'])) {
      		$this->data['ch_price'] = $this->request->post['ch_price'];
    	} elseif (!empty($product_info)) {
			$this->data['ch_price'] = $product_info['ch_price'];
		} else {
      		$this->data['ch_price'] = '';
    	}

		if (isset($this->request->post['ch_discount'])) {
      		$this->data['ch_discount'] = $this->request->post['ch_discount'];
    	} elseif (!empty($product_info)) {
			$this->data['ch_discount'] = $product_info['ch_discount'];
		} else {
      		$this->data['ch_discount'] = '';
    	}
		if (isset($this->request->post['model'])) {
      		$this->data['model'] = $this->request->post['model'];
    	} elseif (!empty($product_info)) {
			$this->data['model'] = $product_info['model'];
		} else {
      		$this->data['model'] = '';
    	}


		if (isset($this->request->post['reference'])) {
      		$this->data['reference'] = $this->request->post['reference'];
    	} elseif (!empty($product_info)) {
			$this->data['reference'] = $product_info['reference'];
		} else {
      		$this->data['reference'] = '';
    	}
		if (isset($this->request->post['reference_link'])) {
      		$this->data['reference_link'] = $this->request->post['reference_link'];
    	} elseif (!empty($product_info)) {
			$this->data['reference_link'] = $product_info['reference_link'];
		} else {
      		$this->data['reference_link'] = '';
    	}

		
		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($product_info)) {
			$this->data['keyword'] = $product_info['keyword'];
		} else {
			$this->data['keyword'] = '';
		}
		
		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($product_info)) {
			$this->data['image'] = $product_info['image'];
		} else {
			$this->data['image'] = '';
		}
		$this->load->model('tool/image');
		if (isset($this->request->post['image']) && file_exists(DIR_IMAGE . $this->request->post['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($product_info) && $product_info['image'] && file_exists(DIR_IMAGE . $product_info['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}
		
    	if (isset($this->request->post['a1'])) {
      		$this->data['a1'] = $this->request->post['a1'];
    	} elseif (!empty($product_info)) {
			$this->data['a1'] = $product_info['a1'];
		} else {
      		$this->data['a1'] = '';
    	}
    	if (isset($this->request->post['a2'])) {
      		$this->data['a2'] = $this->request->post['a2'];
    	} elseif (!empty($product_info)) {
			$this->data['a2'] = $product_info['a2'];
		} else {
      		$this->data['a2'] = '';
    	}
    	if (isset($this->request->post['a3'])) {
      		$this->data['a3'] = $this->request->post['a3'];
    	} elseif (!empty($product_info)) {
			$this->data['a3'] = $product_info['a3'];
		} else {
      		$this->data['a3'] = '';
    	}
    	if (isset($this->request->post['a4'])) {
      		$this->data['a4'] = $this->request->post['a4'];
    	} elseif (!empty($product_info)) {
			$this->data['a4'] = $product_info['a4'];
		} else {
      		$this->data['a4'] = '';
    	}

    	if (isset($this->request->post['force_send'])) {
      		$this->data['force_send'] = $this->request->post['force_send'];
    	} elseif (!empty($product_info)) {
			$this->data['force_send'] = $product_info['force_send'];
		} else {
      		$this->data['force_send'] = '';
    	}

    	if (isset($this->request->post['price'])) {
      		$this->data['price'] = $this->request->post['price'];
    	} elseif (!empty($product_info)) {
			$this->data['price'] = $product_info['price'];
		} else {
      		$this->data['price'] = '';
    	}
  	
		if (isset($this->request->post['date_available'])) {
       		$this->data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($product_info)) {
			$this->data['date_available'] = date('Y-m-d', strtotime($product_info['date_available']));
		} else {
			$this->data['date_available'] = date('Y-m-d', time() - 86400);
		}
											
    	if (isset($this->request->post['quantity'])) {
      		$this->data['quantity'] = $this->request->post['quantity'];
    	} elseif (!empty($product_info)) {
      		$this->data['quantity'] = $product_info['quantity'];
    	} else {
			$this->data['quantity'] = 0;
		}
		
		if (isset($this->request->post['minimum'])) {
      		$this->data['minimum'] = $this->request->post['minimum'];
    	} elseif (!empty($product_info)) {
      		$this->data['minimum'] = $product_info['minimum'];
    	} else {
			$this->data['minimum'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
      		$this->data['sort_order'] = $this->request->post['sort_order'];
    	} elseif (!empty($product_info)) {
      		$this->data['sort_order'] = $product_info['sort_order'];
    	} else {
			$this->data['sort_order'] = 1;
		}

		$this->load->model('localisation/stock_status');
		
		$this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
    	
		if (isset($this->request->post['stock_status_id'])) {
      		$this->data['stock_status_id'] = $this->request->post['stock_status_id'];
    	} elseif (!empty($product_info)) {
      		$this->data['stock_status_id'] = $product_info['stock_status_id'];
    	} else {
			$this->data['stock_status_id'] = 10; //$this->config->get('config_stock_status_id');
		}
				
    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($product_info)) {
			$this->data['status'] = $product_info['status'];
		} else {
      		$this->data['status'] = 1;
    	}

    	if (isset($this->request->post['weight'])) {
      		$this->data['weight'] = ($this->request->post['weight']*1000);
		} elseif (!empty($product_info)) {
			$this->data['weight'] = ($product_info['weight']*1000);
    	} else {
      		$this->data['weight'] = '';
    	} 
		

		$this->load->model('localisation/weight_class');
		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
    	
		if (isset($this->request->post['weight_class_id'])) {
      		$this->data['weight_class_id'] = $this->request->post['weight_class_id'];
    	} elseif (!empty($product_info)) {
      		$this->data['weight_class_id'] = $product_info['weight_class_id'];
		} else {
      		$this->data['weight_class_id'] = $this->config->get('config_weight_class_id');
    	}
		
		if (isset($this->request->post['length'])) {
      		$this->data['length'] = $this->request->post['length'];
    	} elseif (!empty($product_info)) {
			$this->data['length'] = $product_info['length'];
		} else {
      		$this->data['length'] = '';
    	}
		if (isset($this->request->post['width'])) {
      		$this->data['width'] = $this->request->post['width'];
		} elseif (!empty($product_info)) {	
			$this->data['width'] = $product_info['width'];
    	} else {
      		$this->data['width'] = '';
    	}
		if (isset($this->request->post['height'])) {
      		$this->data['height'] = $this->request->post['height'];
		} elseif (!empty($product_info)) {
			$this->data['height'] = $product_info['height'];
    	} else {
      		$this->data['height'] = '';
    	}

		$this->load->model('localisation/length_class');
		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
    	
		if (isset($this->request->post['length_class_id'])) {
      		$this->data['length_class_id'] = $this->request->post['length_class_id'];
    	} elseif (!empty($product_info)) {
      		$this->data['length_class_id'] = $product_info['length_class_id'];
    	} else {
      		$this->data['length_class_id'] = $this->config->get('config_length_class_id');
		}

		$this->load->model('catalog/manufacturer');
    	if (isset($this->request->post['manufacturer_id'])) {
      		$this->data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($product_info)) {
			$this->data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
      		$this->data['manufacturer_id'] = 0;
    	} 		
    	if (isset($this->request->post['manufacturer'])) {
      		$this->data['manufacturer'] = $this->request->post['manufacturer'];
		} elseif (!empty($product_info)) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);
			
			if ($manufacturer_info) {		
				$this->data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$this->data['manufacturer'] = '';
			}	
		} else {
      		$this->data['manufacturer'] = '';
    	} 
		
		// Categories
		$this->load->model('catalog/category');
		
		if (isset($this->request->post['product_category'])) {
			$categories = $this->request->post['product_category'];
		} elseif (isset($this->request->get['product_id'])) {		
			$categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
		} else {
			$categories = array();
		}
	
		$this->data['product_categories'] = array();
		
		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);
			
			if ($category_info) {
				$this->data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path'] ? $category_info['path'] . ' &gt; ' : '') . $category_info['name']
				);
			}
		}
		
		// Filters
		$this->load->model('catalog/filter');
		
		if (isset($this->request->post['product_filter'])) {
			$filters = $this->request->post['product_filter'];
		} elseif (isset($this->request->get['product_id'])) {
			$filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
		} else {
			$filters = array();
		}
		
		$this->data['product_filters'] = array();
		
		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);
			
			if ($filter_info) {
				$this->data['product_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}		
		
		// Attributes
		$this->load->model('catalog/attribute');
		
		if (isset($this->request->post['product_attribute'])) {
			$product_attributes = $this->request->post['product_attribute'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
		} else {
			$product_attributes = array();
		}
		$this->data['product_attributes'] = array();
		
		foreach ($product_attributes as $product_attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);
			
			if ($attribute_info) {
				$this->data['product_attributes'][] = array(
					'attribute_id'                  => $product_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'product_attribute_description' => $product_attribute['product_attribute_description']
				);
			}
		}		
		
		// Options
		$this->load->model('catalog/option');
		
		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $this->model_catalog_product->getNewProductOptions($this->request->get['product_id']);			
		} else {
			$product_options = array();
		}			
		
		$this->load->model('tool/image');
		$this->data['product_options'] = array();
			 
		foreach ($product_options as $product_option) {
			$product_option_value_data = array();
			
			foreach ($product_option['product_option_value'] as $product_option_value) {
			//echo $product_option_value['optionimage']."<br><br>";
		//	echo $this->model_tool_image->resize($product_option_value['optionimage'], 100, 100);
				$product_option_value_data[] = array(
					'product_option_value_id' => '0',
					'property_1'			=> $product_option_value['property_1'],
					'property_2'			=> $product_option_value['property_2'],
					'amount'                => ( isset($product_option_value['amount']) ) ? $product_option_value['amount'] : '',
					'price'                 => $product_option_value['price'],
					'optionimage'           => $product_option_value['optionimage'],
					'optionimagethumb'      => $this->model_tool_image->resize($product_option_value['optionimage'], 100, 100)
				);
			}
			
			$this->data['product_options'][] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'required'             => $product_option['required']
			);				

		}//print_r($product_options);
		
		$this->data['option_values'] = array();
		$this->data['option_values'][11] = $this->model_catalog_option->getOptionValues(11);
		$this->data['option_values'][2] = $this->model_catalog_option->getOptionValues(2);
		foreach ($this->data['product_options'] as $product_option) {
			if (!isset($this->data['option_values'][11])) {
				$this->data['option_values'][11] = $this->model_catalog_option->getOptionValues(11);
			}
			if (!isset($this->data['option_values'][2])) {
				$this->data['option_values'][2] = $this->model_catalog_option->getOptionValues(2);
			}		
		}
		
		$this->load->model('sale/customer_group');
		
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		
		if (isset($this->request->post['product_discount'])) {
			$this->data['product_discounts'] = $this->request->post['product_discount'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_discounts'] = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
		} else {
			$this->data['product_discounts'] = array();
		}

		if (isset($this->request->post['product_special'])) {
			$this->data['product_specials'] = $this->request->post['product_special'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_specials'] = $this->model_catalog_product->getProductSpecials($this->request->get['product_id']);
		} else {
			$this->data['product_specials'] = array();
		}
		
		// Images
		if (isset($this->request->post['product_image'])) {
			$product_images = $this->request->post['product_image'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = array();
		}
		
		$this->data['product_images'] = array();
		
		foreach ($product_images as $product_image) {
			if ($product_image['image'] && file_exists(DIR_IMAGE . $product_image['image'])) {
				$image = $product_image['image'];
			} else {
				$image = 'no_image.jpg';
			}
			
			$this->data['product_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($image, 100, 100),
				'sort_order' => $product_image['sort_order']
			);
		}

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		// Downloads
		$this->load->model('catalog/download');
		
		if (isset($this->request->post['product_download'])) {
			$product_downloads = $this->request->post['product_download'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
		} else {
			$product_downloads = array();
		}
			
		$this->data['product_downloads'] = array();
		
		foreach ($product_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);
			
			if ($download_info) {
				$this->data['product_downloads'][] = array(
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				);
			}
		}
		
		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($this->request->get['product_id'])) {		
			$products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
		} else {
			$products = array();
		}
	
		$this->data['product_related'] = array();
		
		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);
			
			if ($related_info) {
				$this->data['product_related'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

    	if (isset($this->request->post['points'])) {
      		$this->data['points'] = $this->request->post['points'];
    	} elseif (!empty($product_info)) {
			$this->data['points'] = $product_info['points'];
		} else {
      		$this->data['points'] = '';
    	}
						
		if (isset($this->request->post['product_reward'])) {
			$this->data['product_reward'] = $this->request->post['product_reward'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
		} else {
			$this->data['product_reward'] = array();
		}
		
		if (isset($this->request->post['product_layout'])) {
			$this->data['product_layout'] = $this->request->post['product_layout'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
		} else {
			$this->data['product_layout'] = array();
		}

		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
										
		$this->template = 'catalog/product_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	} 
	
  	protected function validateForm() { 
    	if (!$this->user->hasPermission('modify', 'catalog/product')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	/*foreach ($this->request->post['product_description'] as $language_id => $value) {
      		if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
        		$this->error['name'][$language_id] = $this->language->get('error_name');
      		}
    	}*/
		
    	if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
      		$this->error['model'] = $this->language->get('error_model');
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
  	public function globalpriceupdate() {
    	$costz	= (isset($this->request->get['costz'])) ? $this->request->get['costz'] : null ;
		$this->load->model('catalog/product');
		$this->model_catalog_product->updateGlobalPrice($costz);
		$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
  	}

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/product'))  $this->error['warning'] = $this->language->get('error_permission');  
		return (!$this->error) ? true : false ;
  	}
  	
  	protected function validateCopy() {
    	if (!$this->user->hasPermission('modify', 'catalog/product')) $this->error['warning'] = $this->language->get('error_permission');  
		return (!$this->error) ? true : false ;
  	}
		
	public function autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model']) || isset($this->request->get['filter_category_id'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');
			
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			
			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}
			
			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];	
			} else {
				$limit = 20;	
			}			
						
			$data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);
			
			$results = $this->model_catalog_product->getProducts($data);
			
			foreach ($results as $result) {
				$option_data = array();
				
				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);	
				
				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);
					
					if ($option_info) {				
						if ($option_info['type'] == 'select' || $option_info['type'] == 'radio' || $option_info['type'] == 'checkbox' || $option_info['type'] == 'image') {
							$option_value_data = array();
							
							foreach ($product_option['product_option_value'] as $product_option_value) {
								$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);
						
								if ($option_value_info) {
									$option_value_data[] = array(
										'product_option_value_id' => $product_option_value['product_option_value_id'],
										'option_value_id'         => $product_option_value['option_value_id'],
										'name'                    => $option_value_info['name'],
										'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
										'price_prefix'            => $product_option_value['price_prefix']
									);
								}
							}
						
							$option_data[] = array(
								'product_option_id' => $product_option['product_option_id'],
								'option_id'         => $product_option['option_id'],
								'name'              => $option_info['name'],
								'type'              => $option_info['type'],
								'option_value'      => $option_value_data,
								'required'          => $product_option['required']
							);	
						} else {
							$option_data[] = array(
								'product_option_id' => $product_option['product_option_id'],
								'option_id'         => $product_option['option_id'],
								'name'              => $option_info['name'],
								'type'              => $option_info['type'],
								'option_value'      => $product_option['option_value'],
								'required'          => $product_option['required']
							);				
						}
					}
				}
					
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),	
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price'],
					'image'		 => HTTP_CATALOG."image/".$result['image']
				);	
			}
		}

		$this->response->setOutput(json_encode($json));
	}
}
?>
