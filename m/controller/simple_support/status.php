<?php
    class ControllerSimpleSupportStatus extends Controller {
    	
		private $error = array();
		
    	public function index() {
    		$this->language->load('simple_support/status');

			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/status');
			
			$this->getList();
    	}
		
		public function insert() {
			$this->language->load('simple_support/status');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/status');
	
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_status->addSupportStatus($this->request->post);
	
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
	
				$this->redirect($this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getForm();
		}
	
		public function update() {
			$this->language->load('simple_support/status');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/status');
	
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_status->editSupportStatus($this->request->get['simple_support_status_id'], $this->request->post);
	
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
	
				$this->redirect($this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getForm();
		}
	
		public function delete() {
			$this->language->load('simple_support/status');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/status');
	
			if (isset($this->request->post['selected']) && $this->validateDelete()) {
				foreach ($this->request->post['selected'] as $simple_support_status_id) {
					$this->model_simple_support_status->deleteSupportStatus($simple_support_status_id);
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
	
				$this->redirect($this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getList();
		}
		
		public function getList() {
			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'name';
			}
	
			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'ASC';
			}
	
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
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
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
			);
	
			$this->data['insert'] = $this->url->link('simple_support/status/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
			$this->data['delete'] = $this->url->link('simple_support/status/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	
	
			$this->data['statues'] = array();
			
			$data = array(
				'sort'  => $sort,
				'order' => $order,
				'start' => ($page - 1) * $this->config->get('config_admin_limit'),
				'limit' => $this->config->get('config_admin_limit')
			);
			
			$status_total = $this->model_simple_support_status->getTotalStatues($data);
			
			$results = $this->model_simple_support_status->getStatues($data);
			
			foreach ($results as $result) {
				$action = array();
	
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('simple_support/status/update', 'token=' . $this->session->data['token'] . '&simple_support_status_id=' . $result['simple_support_status_id'] . $url, 'SSL')
				);
	
				$this->data['statues'][] = array(
					'simple_support_status_id'	=> $result['simple_support_status_id'],
					'name'            			=> $result['name'] . (($result['simple_support_status_id'] == $this->config->get('simple_support_status_id')) ? $this->language->get('text_default') : null),
					'selected'        			=> isset($this->request->post['selected']) && in_array($result['simple_support_status_id'], $this->request->post['selected']),
					'action'          			=> $action
				);
			}	
			
			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_no_results'] = $this->language->get('text_no_results');
	
			$this->data['column_name'] = $this->language->get('column_name');
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
			
			$url = '';

			if ($order == 'ASC') {
				$url .= '&order=DESC';
			} else {
				$url .= '&order=ASC';
			}
	
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
	
			$this->data['sort_name'] = $this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
	
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
	
			$pagination = new Pagination();
			$pagination->total = $status_total;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_admin_limit');
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
	
			$this->data['pagination'] = $pagination->render();
			
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
	
			$this->template = 'simple_support/status_list.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
	
			$this->response->setOutput($this->render());
		}

		public function getForm() {
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->data['text_enabled'] = $this->language->get('text_enabled');
			$this->data['text_disabled'] = $this->language->get('text_disabled');
			
			$this->data['entry_name'] = $this->language->get('entry_name');
			$this->data['entry_status'] = $this->language->get('entry_status');
	
			$this->data['button_save'] = $this->language->get('button_save');
			$this->data['button_cancel'] = $this->language->get('button_cancel');
			
			if (isset($this->error['warning'])) {
				$this->data['error_warning'] = $this->error['warning'];
			} else {
				$this->data['error_warning'] = '';
			}
	
			if (isset($this->error['name'])) {
				$this->data['error_name'] = $this->error['name'];
			} else {
				$this->data['error_name'] = array();
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
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
			);
			
			if (!isset($this->request->get['simple_support_status_id'])) {
				$this->data['action'] = $this->url->link('simple_support/status/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
			} else {
				$this->data['action'] = $this->url->link('simple_support/status/update', 'token=' . $this->session->data['token'] . '&simple_support_status_id=' . $this->request->get['simple_support_status_id'] . $url, 'SSL');
			}
			
			$this->data['cancel'] = $this->url->link('simple_support/status', 'token=' . $this->session->data['token'] . $url, 'SSL');
			
			$this->load->model('localisation/language');
	
			$this->data['languages'] = $this->model_localisation_language->getLanguages();
			
			if (isset($this->request->post['status_name'])) {
				$this->data['status_name'] = $this->request->post['status_name'];
			} elseif (isset($this->request->get['simple_support_status_id'])) {
				$this->data['status_name'] = $this->model_simple_support_status->getSupportStatusDescriptions($this->request->get['simple_support_status_id']);
			} else {
				$this->data['status_name'] = array();
			}
			
			$this->template = 'simple_support/status_form.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
	
			$this->response->setOutput($this->render());
			
		}

		public function validateForm() {
			if (!$this->user->hasPermission('modify', 'simple_support/status')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
	
			foreach ($this->request->post['status_name'] as $language_id => $value) {
				if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
					$this->error['name'][$language_id] = $this->language->get('error_name');
				}
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
		
		protected function validateDelete() {
			if (!$this->user->hasPermission('modify', 'simple_support/status')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
			
			foreach ($this->request->post['selected'] as $simple_support_status_id) {
				if($simple_support_status_id == $this->config->get('simple_support_status_id')) {
					$this->error['warning'] = $this->language->get('error_default_status');
				}
			}
			
			if (!$this->error) { 
				return true;
			} else {
				return false;
			}
		}	

    }
?>