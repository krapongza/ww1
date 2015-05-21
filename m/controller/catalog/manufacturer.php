<?php    
class ControllerCatalogManufacturer extends Controller { 
	private $error = array();
  
  	public function index() {
		$this->language->load('catalog/manufacturer');
		
		$this->document->setTitle($this->language->get('heading_title'));
		 
		$this->load->model('catalog/manufacturer');
		
    	$this->getList();
  	}
  
  	public function insert() {
		$this->language->load('catalog/manufacturer');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/manufacturer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')&&( strlen($_POST['website']) > 0 )){
			$total = $this->model_catalog_manufacturer->validateWWW($this->request->post);
			if($total > 0)$this->error['warning'] = "website duplicate.";
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm() && ($total < 1)) {
			$this->model_catalog_manufacturer->addManufacturer($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    
		$this->data['type_id'] = '';
		$this->data['note'] = array();

    	$this->getForm();
  	} 
   
  	public function update() {
		$this->language->load('catalog/manufacturer');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/note');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')&&( strlen($_POST['website']) > 0 )){
			$total = $this->model_catalog_manufacturer->validateWWW($this->request->post , $this->request->get);
			if($total > 0)$this->error['warning'] = "website duplicate.";
		}


		$note_type = "supplier";
    	if (isset($_POST['note_submit']) ){
			
			$this->model_catalog_note->addNote($this->request->post);
			$this->model_catalog_manufacturer->updateNote( $this->request->post['flag'] , $this->request->get['manufacturer_id']);

			$this->redirect($this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . $url . "&manufacturer_id=". $this->request->get['manufacturer_id'] , 'SSL'));

		}elseif (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm() && ($total < 1)) {
			//print_r($this->request->post);
			$this->model_catalog_manufacturer->editManufacturer($this->request->get['manufacturer_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->redirect($this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}elseif( isset($this->request->get['del']) ){

			$this->model_catalog_note->deleteNote( $note_type ,$this->request->get);
			$this->model_catalog_manufacturer->updateNote( 0 , $this->request->get['manufacturer_id']);

			$this->redirect($this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . $url . "&manufacturer_id=". $this->request->get['manufacturer_id'], 'SSL'));
		}


		$this->data['type_id'] = $this->request->get['manufacturer_id'];
		$this->data['note'] = $this->model_catalog_note->getNote($note_type ,$this->request->get['manufacturer_id']);
    
    	$this->getForm();
  	}   

  	public function delete() {
		$this->language->load('catalog/manufacturer');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/manufacturer');
			
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $manufacturer_id) {
				$this->model_catalog_manufacturer->deleteManufacturer($manufacturer_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
	
    	$this->getList();
  	}  
    
  	protected function getList() {

		$filter_supplier = (isset($this->request->get['filter_supplier'])) ? $this->request->get['filter_supplier'] : null;
		$filter_order	= (isset($this->request->get['filter_order'])) ? $this->request->get['filter_order'] : null;

		$sort			= (isset($this->request->get['sort'])) ? $this->request->get['sort'] : 'name';
		$order			= (isset($this->request->get['order'])) ? $this->request->get['order'] : 'ASC';
		$page			= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
 
				
		$url = '';
		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}	
		if (isset($this->request->get['filter_order'])) {
			$url .= '&filter_order=' . $this->request->get['filter_order'];
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
       		'text'      => 'Supplier',
			'href'      => $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('catalog/manufacturer/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/manufacturer/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	

		$this->data['manufacturers'] = array();

		$data = array(
			'filter_supplier'   => $filter_supplier,
			'filter_order'		=> $filter_order,
			'sort'				=> $sort,
			'order'				=> $order,
			'start'				=> ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'				=> $this->config->get('config_admin_limit')
		);
		
		$manufacturer_total = $this->model_catalog_manufacturer->getNewTotalManufacturers($data);
	
		$results = $this->model_catalog_manufacturer->getNewManufacturers($data);

    	foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . '&manufacturer_id=' . $result['manufacturer_id'] . $url, 'SSL'),
				'htext' => 'Note',
				'hnote' => $this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . '&manufacturer_id=' . $result['manufacturer_id'] . $url."#note", 'SSL')
			);

			$note_icon = ( $result['note_icon'] > 0) ? $result['note_icon'] : '0';
						
			$this->data['manufacturers'][] = array(
				'mname'			  => $result['mname'],
				'manufacturer_id' => $result['manufacturer_id'],
				'name'            => $result['name'],
				'sort_order'      => $result['sort_order'],
				'address'		  => $result['address'],
				'country'		  => $result['country'],
				'tel'			  => $result['tel'],
				'website'		  => $result['website'],
				'email'			  => $result['email'],
				'flag'			  => $note_icon,
				'selected'        => isset($this->request->post['selected']) && in_array($result['manufacturer_id'], $this->request->post['selected']),
				'action'          => $action
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');		
		
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['token'] = $this->session->data['token'];

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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_name'] = $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . '&sort=mname' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . '&sort=sort_order' . $url, 'SSL');
		
		$url = '';
		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}	
		if (isset($this->request->get['filter_order'])) {
			$url .= '&filter_order=' . $this->request->get['filter_order'];
		}	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $manufacturer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['filter_supplier']	= $filter_supplier;
		$this->data['filter_order']		= $filter_order;
		$this->data['sort']				= $sort;
		$this->data['order']			= $order;

		$this->template = 'catalog/manufacturer_list.tpl';
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
		$this->data['text_default'] = $this->language->get('text_default');
    	$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');			
		$this->data['text_percent'] = $this->language->get('text_percent');
		$this->data['text_amount'] = $this->language->get('text_amount');
				
		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword');
    	$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		  
    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
			  

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}
		    
		$url = '';
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
       		'text'      => 'Supplier',
			'href'      => $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		if (!isset($this->request->get['manufacturer_id'])) {
			$this->data['action'] = $this->url->link('catalog/manufacturer/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . '&manufacturer_id=' . $this->request->get['manufacturer_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'] . $url, 'SSL');

    	if (isset($this->request->get['manufacturer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);
    	}

		//$this->data['catalog_info'] = $this->model_catalog_manufacturer->getCatalog();

		$this->data['token'] = $this->session->data['token'];

    	if (isset($this->request->post['name'])) {
      		$this->data['name'] = $this->request->post['name'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['name'] = $manufacturer_info['name'];
		} else {	
      		$this->data['name'] = '';
    	}

    	if ( isset($this->request->post['catalog_id'])&&($this->request->post['catalog_id'] > 0)    ) {
      		$this->data['catalog_id']	= $this->request->post['catalog_id'];
			$this->data['catalog']		= $this->model_catalog_manufacturer->getCatalogByID($this->request->post['manufacturer_id']);
    	} elseif (!empty($manufacturer_info)) {
			$this->data['catalog_id']	= $manufacturer_info['manufacturer_id'];
			$this->data['catalog']		= $this->model_catalog_manufacturer->getCatalogByID($manufacturer_info['catalog']);
		} else {	
      		$this->data['catalog_id']	= '0';
			$this->data['catalog']		= '';
    	}

		
		$this->load->model('setting/store');
		
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		if (isset($this->request->post['manufacturer_store'])) {
			$this->data['manufacturer_store'] = $this->request->post['manufacturer_store'];
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$this->data['manufacturer_store'] = $this->model_catalog_manufacturer->getManufacturerStores($this->request->get['manufacturer_id']);
		} else {
			$this->data['manufacturer_store'] = array(0);
		}	
		
		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($manufacturer_info)) {
			$this->data['keyword'] = $manufacturer_info['keyword'];
		} else {
			$this->data['keyword'] = '';
		}

		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($manufacturer_info)) {
			$this->data['image'] = $manufacturer_info['image'];
		} else {
			$this->data['image'] = '';
		}
		
		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && file_exists(DIR_IMAGE . $this->request->post['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($manufacturer_info) && $manufacturer_info['image'] && file_exists(DIR_IMAGE . $manufacturer_info['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($manufacturer_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}
		
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		
		if (isset($this->request->post['sort_order'])) {
      		$this->data['sort_order'] = $this->request->post['sort_order'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['sort_order'] = $manufacturer_info['sort_order'];
		} else {
      		$this->data['sort_order'] = '';
    	}

		if (isset($this->request->post['reference'])) {
      		$this->data['reference'] = $this->request->post['reference'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['reference'] = $manufacturer_info['reference'];
		} else {
      		$this->data['reference'] = '';
    	}
		if (isset($this->request->post['reference_link'])) {
      		$this->data['reference_link'] = $this->request->post['reference_link'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['reference_link'] = $manufacturer_info['reference_link'];
		} else {
      		$this->data['reference_link'] = '';
    	}
		if (isset($this->request->post['address'])) {
      		$this->data['address'] = $this->request->post['address'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['address'] = $manufacturer_info['address'];
		} else {
      		$this->data['address'] = '';
    	}
		if (isset($this->request->post['city'])) {
      		$this->data['city'] = $this->request->post['city'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['city'] = $manufacturer_info['city'];
		} else {
      		$this->data['city'] = '';
    	}
		if (isset($this->request->post['country'])) {
      		$this->data['country'] = $this->request->post['country'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['country'] = $manufacturer_info['country'];
		} else {
      		$this->data['country'] = '';
    	}
		if (isset($this->request->post['zipcode'])) {
      		$this->data['zipcode'] = $this->request->post['zipcode'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['zipcode'] = $manufacturer_info['zipcode'];
		} else {
      		$this->data['zipcode'] = '';
    	}
		if (isset($this->request->post['website'])) {
      		$this->data['website'] = $this->request->post['website'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['website'] = $manufacturer_info['website'];
		} else {
      		$this->data['website'] = '';
    	}
		if (isset($this->request->post['tel'])) {
      		$this->data['tel'] = $this->request->post['tel'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['tel'] = $manufacturer_info['tel'];
		} else {
      		$this->data['tel'] = '';
    	}
		if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['email'] = $manufacturer_info['email'];
		} else {
      		$this->data['email'] = '';
    	}
		if (isset($this->request->post['skype'])) {
      		$this->data['skype'] = $this->request->post['skype'];
    	} elseif (!empty($manufacturer_info)) {
			$this->data['skype'] = $manufacturer_info['skype'];
		} else {
      		$this->data['skype'] = '';
    	}

		
		$this->template = 'catalog/manufacturer_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}  
	 
  	protected function validateForm() {
    	if (!$this->user->hasPermission('modify', 'catalog/manufacturer')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
      		$this->error['name'] = $this->language->get('error_name');
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/manufacturer')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}	
		
		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $manufacturer_id) {
  			$product_total = $this->model_catalog_product->getTotalProductsByManufacturerId($manufacturer_id);
    
			if ($product_total) {
	  			$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);	
			}	
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
				'primary'	  => 'y',
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