<?php
class ControllerModuleFacebookComments extends Controller {
	protected function index($setting) {				
		$this->load->language('module/facebook_comments');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/facebook_comments.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/facebook_comments.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/facebook_comments.css');
		}
		
      	$this->data['heading_title'] = $this->language->get('heading_title');
		
		// add fb tag for APP ID if Facebook Open Graph Meta Tags extension is installed
		if (method_exists($this->document, 'addOpenGraphMetaTags')) {
			$this->document->addOpenGraphMetaTags('fb:app_id', $this->config->get('facebook_comments_app_id'));
		}
				
		$this->data['app_id'] = $this->config->get('facebook_comments_app_id');		
		$this->data['url'] = $this->getCurrentURL();
		$this->data['color_scheme'] = $this->config->get('facebook_comments_color_scheme');
		$this->data['num_posts'] = $this->config->get('facebook_comments_num_posts');
		$this->data['order_by'] = $this->config->get('facebook_comments_order_by'); 
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/facebook_comments.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/facebook_comments.tpl';
		} else {
			$this->template = 'default/template/module/facebook_comments.tpl';
		}

		$this->render();
	}
	
	private function getCurrentURL() {
		$url = '';
		
		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
		} else {
			$route = 'common/home';
		}
		
		if ($route == 'common/home') {
			$url = $this->url->link('common/home');
		} elseif ($route == 'product/product' && isset($this->request->get['product_id'])) {
			$url = $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']);
		} elseif ($route == 'product/category' && isset($this->request->get['path'])) {
			$url = $this->url->link('product/category', 'path=' . $this->request->get['path']);
		} elseif ($route == 'information/information' && isset($this->request->get['information_id'])) {
			$url = $this->url->link('information/information', 'information_id=' . $this->request->get['information_id']);
		} else {
			if ($this->request->server['HTTPS']) {
				$url = $this->config->get('config_ssl');
			} else {
				$url = $this->config->get('config_url');
			}
			
			$url .= $this->request->server["REQUEST_URI"];
		}
		
		return $url;
	}
}
?>