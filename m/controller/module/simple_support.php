<?php
	class ControllerModuleSimpleSupport extends Controller {
		
		private $error = array();
		
		public function index() {
			$this->language->load('module/simple_support');

			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('setting/setting');
			
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				$this->model_setting_setting->editSetting('simple_support', $this->request->post);		
	
				$this->session->data['success'] = $this->language->get('text_success');
	
				$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
			}
			
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->data['text_enabled'] = $this->language->get('text_enabled');
			$this->data['text_disabled'] = $this->language->get('text_disabled');
			$this->data['text_select_all'] = $this->language->get('text_select_all');
			$this->data['text_unselect_all'] = $this->language->get('text_unselect_all');
			$this->data['text_content_top'] = $this->language->get('text_content_top');
			$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
			$this->data['text_column_left'] = $this->language->get('text_column_left');
			$this->data['text_column_right'] = $this->language->get('text_column_right');
			$this->data['text_faq_module'] = $this->language->get('text_faq_module');
	
			$this->data['entry_layout'] = $this->language->get('entry_layout');
			$this->data['entry_position'] = $this->language->get('entry_position');
			$this->data['entry_status'] = $this->language->get('entry_status');
			$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
			$this->data['entry_simple_support_status'] = $this->language->get('entry_simple_support_status');			
			$this->data['entry_ticket_prefix'] = $this->language->get('entry_ticket_prefix');
			$this->data['entry_search_faq'] = $this->language->get('entry_search_faq');
			$this->data['entry_show_ticket_all'] = $this->language->get('entry_show_ticket_all');
			
			
			$this->data['button_save'] = $this->language->get('button_save');
			$this->data['button_cancel'] = $this->language->get('button_cancel');
			$this->data['button_add_module'] = $this->language->get('button_add_module');
			$this->data['button_remove'] = $this->language->get('button_remove');
			
			if (isset($this->error['warning'])) {
				$this->data['error_warning'] = $this->error['warning'];
			} else {
				$this->data['error_warning'] = '';
			}
	
			$this->data['breadcrumbs'] = array();
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_module'),
				'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('module/simple_support', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			);
			
			$this->data['action'] = $this->url->link('module/simple_support', 'token=' . $this->session->data['token'], 'SSL');

			$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
			
			$this->data['simple_support_status'] = '';
			$this->data['simple_support_ticket_prefix'] = '';
			$this->data['simple_support_search_faq'] = '';
			$this->data['simple_support_status_id'] = '';
			$this->data['simple_support_ticket_permission'] = array();
			
			$this->load->model('simple_support/status');
				
			$this->data['support_statues'] = $this->model_simple_support_status->getStatues();
			
			if (isset($this->request->post['simple_support_status_id'])) {
				$this->data['simple_support_status_id'] = $this->request->post['simple_support_status_id'];
			} else {
				$this->data['simple_support_status_id'] = $this->config->get('simple_support_status_id');
			}	
			
			if (isset($this->request->post['simple_support_status'])) {
				$this->data['simple_support_status'] = $this->request->post['simple_support_status'];
			} else if ($this->config->get('simple_support_status')) {
				$this->data['simple_support_status'] = $this->config->get('simple_support_status');
			}	
			
			if (isset($this->request->post['simple_support_ticket_prefix'])) {
				$this->data['simple_support_ticket_prefix'] = $this->request->post['simple_support_ticket_prefix'];
			} else if ($this->config->get('simple_support_ticket_prefix')) {
				$this->data['simple_support_ticket_prefix'] = $this->config->get('simple_support_ticket_prefix');
			}	

			if (isset($this->request->post['simple_support_search_faq'])) {
				$this->data['simple_support_search_faq'] = $this->request->post['simple_support_search_faq'];
			} else if ($this->config->get('simple_support_search_faq')) {
				$this->data['simple_support_search_faq'] = $this->config->get('simple_support_search_faq');
			}	
			
			$this->load->model('user/user_group');
			
			$this->data['user_groups'] = $this->model_user_user_group->getUserGroups();
			
			if (isset($this->request->post['simple_support_ticket_permission'])) {
				$this->data['simple_support_ticket_permission'] = $this->request->post['simple_support_ticket_permission'];
			} else if ($this->config->get('simple_support_ticket_permission')) {
				$this->data['simple_support_ticket_permission'] = $this->config->get('simple_support_ticket_permission');
			}	
			
			$this->data['modules'] = array();
			
			if (isset($this->request->post['simple_support_module'])) {
				$this->data['modules'] = $this->request->post['simple_support_module'];
			} elseif ($this->config->get('simple_support_module')) { 
				$this->data['modules'] = $this->config->get('simple_support_module');
			}	
			
			$this->load->model('design/layout');

			$this->data['layouts'] = $this->model_design_layout->getLayouts();
	
			$this->template = 'module/simple_support.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
	
			$this->response->setOutput($this->render());			
		}

		protected function validate() {
			if (!$this->user->hasPermission('modify', 'module/simple_support')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
			
			if(utf8_strlen($this->request->post['simple_support_ticket_prefix']) > 5) {
				$this->error['warning'] = $this->language->get('error_ticket_prefix_length');
			}
			
			if(!isset($this->request->post['simple_support_ticket_permission'])) {
				$this->error['warning'] = $this->language->get('error_select_atleast_one_group');
			}
					
			if (!$this->error) {
				return true;
			} else {
				return false;
			}	
		}
	}
?>