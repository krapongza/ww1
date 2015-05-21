<?php 
class ControllerInformationInformation extends Controller {
	public function index() {  
    	$this->language->load('information/information');
		
		$this->load->model('catalog/information');
		
		$this->data['breadcrumbs'] = array();
		
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);
		
		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}
		
		$information_info = $this->model_catalog_information->getInformation($information_id);
   		
		if ($information_info) {
	  		$this->document->setTitle($information_info['title']); 

      		$this->data['breadcrumbs'][] = array(
        		'text'      => $information_info['title'],
				'href'      => $this->url->link('information/information', 'information_id=' .  $information_id),      		
        		'separator' => $this->language->get('text_separator')
      		);		
						
      		$this->data['heading_title'] = $information_info['title'];
      		
      		$this->data['button_continue'] = $this->language->get('button_continue');
			
			$this->data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
      		
			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/information.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/information/information.tpl';
			} else {
				$this->template = 'default/template/information/information.tpl';
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
    	} else {
      		$this->data['breadcrumbs'][] = array(
        		'text'      => $this->language->get('text_error'),
				'href'      => $this->url->link('information/information', 'information_id=' . $information_id),
        		'separator' => $this->language->get('text_separator')
      		);
				
	  		$this->document->setTitle($this->language->get('text_error'));
			
      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
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
	
	public function info() {
		$this->load->model('catalog/information');
		
		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}      
		
		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output  = '<html dir="ltr" lang="en">' . "\n";
			$output .= '<head>' . "\n";
			$output .= '  <title>' . $information_info['title'] . '</title>' . "\n";
			$output .= '  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$output .= '  <meta name="robots" content="noindex">' . "\n";
			$output .= '</head>' . "\n";
			$output .= '<body>' . "\n";
			$output .= '  <h1>' . $information_info['title'] . '</h1>' . "\n";
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
			$output .= '  </body>' . "\n";
			$output .= '</html>' . "\n";			

			$this->response->setOutput($output);
		}
	}

	public function gallerylist() {

    	$this->language->load('information/information');
		$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_error'),
			'href'      => $this->url->link('information/information', 'information_id=1'),
			'separator' => $this->language->get('text_separator')
		);	
		$this->document->setTitle("Gallery Page");
		$this->data['heading_title'] = $this->language->get('text_error');
		$this->data['text_error'] = $this->language->get('text_error');
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['continue'] = $this->url->link('common/home');

		$this->load->model('account/gallery');

	 
		$this->data['products'] = array();
		$gallery_list = $this->model_account_gallery->getGalleryList();
		foreach($gallery_list as $key => $product_info){
			$image = ($product_info['image']) ? HTTP_SERVER."image/".$product_info['image'] : false;		
			$this->data['products'][] = array(
				'thumb'      => $image,
				'href'       => $this->url->link('information/information/gallery', 'gallery=' . $product_info['id'])
			);
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/gallery_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/information/gallery_list.tpl';
		} else {
			$this->template = 'default/template/information/gallery_list.tpl';
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

	public function gallery() {

    	$this->language->load('information/information');
		$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_error'),
			'href'      => $this->url->link('information/information', 'information_id=1'),
			'separator' => $this->language->get('text_separator')
		);	
		$this->document->setTitle("Gallery Images");
		$this->data['heading_title'] = "Gallery Page";
		$this->data['text_error'] = $this->language->get('text_error');
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['continue'] = $this->url->link('common/home');

		$this->load->model('account/gallery');

		$id = (isset($this->request->get['gallery'])) ? $this->request->get['gallery'] : '';

		$this->data['products'] = array();
		$gallery_list = $this->model_account_gallery->getGallery((int)$id);
		foreach($gallery_list as $key => $product_info){
			$image = ($product_info['image']) ? HTTP_SERVER."image/".$product_info['image'] : false;		
			$this->data['products'][] = array(
				'id'		=> $product_info['id'],
				'name'		=> $product_info['name'],
				'thumb'     => $image,
				'title'		=> $product_info['title'],
				'href'      =>  $product_info['link']  //$this->url->link('product/product', 'product_id=' . $product_info['link'])
			);
		}
		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/gallery.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/information/gallery.tpl';
		} else {
			$this->template = 'default/template/information/gallery.tpl';
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