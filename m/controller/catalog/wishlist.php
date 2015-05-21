<?php    
class ControllerCatalogWishList extends Controller { 
	private $error = array();
  

  	public function index() {
		$this->language->load('catalog/text');
		$this->document->setTitle('WishList');
		$this->load->model('catalog/wishlist');
		$this->load->model('tool/image');
		
		$this->getList();
  	}


  	public function delete() {
		$this->language->load('catalog/text');
		$this->document->setTitle('WishList');
		$this->load->model('catalog/wishlist');
		$this->load->model('tool/image');
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST'){
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_wishlist->clearWishList($product_id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			/*if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_model'])) $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			if (isset($this->request->get['filter_price'])) $url .= '&filter_price=' . $this->request->get['filter_price'];
			if (isset($this->request->get['filter_quantity'])) $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			if (isset($this->request->get['filter_status'])) $url .= '&filter_status=' . $this->request->get['filter_status'];	
			if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
			if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];*/
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
			
			$this->redirect($this->url->link('catalog/wishlist', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}


		$this->getList();
	}

	 public function getList() {
 		$url = '';
		$page			= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
		if (isset($this->request->get['page']))  $url .= '&page=' . $this->request->get['page'];

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'WishList',
			'href'      => $this->url->link('catalog/wishlist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);	
		$data = array(
			'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit')
		);

		$product_total	= $this->model_catalog_wishlist->getTotalWishList();
		$results		= $this->model_catalog_wishlist->getWishList($data);
    	foreach ($results as $result) {
			//Status 2=Sold Out , 3=Pre-Order , 10=Coming Soon
			$image = $this->model_tool_image->resize($result['image'], 40, 60);
			$this->data['wishlists'][] = array(
				'product_id'		=> $result['product_id'],
				'model'				=> $result['model'],
				'image'				=> $image,
				'wishlist'			=> $result['wishlist'],
				'stock_status_id'	=> $result['stock_status_id'],
				'selected'			=> isset($this->request->post['selected']) && in_array($result['product_id'], $this->request->post['selected']),
				'link'				=> HTTP_CATALOG."product.html?product_id=".$result['product_id'],
				'clink'				=> $this->url->link('catalog/wishlist/customer', 'token=' . $this->session->data['token'] ."&product=".$result['product_id'], 'SSL')
			);  
		}	
		$this->data['delete'] = $this->url->link('catalog/wishList/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/wishList', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->template = 'catalog/wishlist_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());

	}
 	
	 public function customer() {
 		$url			= '';
		$product		= (isset($this->request->get['product'])) ? $this->request->get['product'] : 0;
		$page			= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
		if (isset($this->request->get['product']))  $url .= '&product=' . $this->request->get['product'];
		if (isset($this->request->get['page']))		$url .= '&page=' . $this->request->get['page'];

  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'WishList',
			'href'      => $this->url->link('catalog/wishlist', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);	
		$data = array(
			'product'		  => $product,
			'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit')
		);
		$this->load->model('catalog/wishlist');

		$product_total	= $this->model_catalog_wishlist->getTotalCustomerWishList($data);
		$results		= $this->model_catalog_wishlist->getCustomerWishList($data);
    	foreach ($results as $result) {
			$this->data['wishlists'][] = array(
				'name'				=> $result['firstname']." ".$result['lastname'],
				'email'				=> $result['email'],
				'tel'				=> $result['telephone']
			);  
		}	

		if (isset($this->request->get['product']))  $url .= '&product=' . $this->request->get['product'];
		if (isset($this->request->get['page']))		$url .= '&page=' . $this->request->get['page'];
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/wishList', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->template = 'catalog/wishlist_customer.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());

	}


}
?>