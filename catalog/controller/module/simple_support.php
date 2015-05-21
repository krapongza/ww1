<?php
	class ControllerModuleSimpleSupport extends Controller {
		protected function index($setting) {
			$this->language->load('module/simple_support');
			
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->load->model('simple_support/faq_group');
			
			$this->data['faq_groups'] = array();
			
			if(isset($this->request->get['simple_support_faq_group_id'])) {
				$this->data['simple_support_faq_group_id'] = $this->request->get['simple_support_faq_group_id'];
			} else {
				$this->data['simple_support_faq_group_id'] = '';
			} 
			
			if (isset($this->request->get['faq_search'])) {
				$this->data['faq_search'] = $this->request->get['faq_search'];
			} else {
				$this->data['faq_search'] = '';
			}
			
			$this->data['text_search_faq'] = $this->language->get('text_search_faq');
						
			$results = $this->model_simple_support_faq_group->getFaqGroups();
			
			foreach($results as $result) {
				$this->data['faq_groups'][] = array(
					'simple_support_faq_group_id'	=> $result['simple_support_faq_group_id'],
					'name' => $result['name'],
					'href'	=> $this->url->link('simple_support/home', 'simple_support_faq_group_id=' . $result['simple_support_faq_group_id'], 'SSL')
				);
			}
			
			$this->data['text_no_found'] = $this->language->get('text_no_result');
			$this->data['button_search'] = $this->language->get('button_search');
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/simple_support.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/module/simple_support.tpl';
			} else {
				$this->template = 'default/template/module/simple_support.tpl';
			}
			
			$this->render();
		}
	}
?>