<?php
	class ControllerSimpleSupportDepartment extends Controller {
		private $error = array();
		
		public function index() {
			$this->language->load('simple_support/department');

			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/department');
	
			$this->getList();
		}
		
		public function insert() {
			$this->language->load('simple_support/department');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/department');
	
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
				
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_department->addDepartment($this->request->post);
	
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
	
				$this->redirect($this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getForm();
		}
	
		public function update() {
			$this->language->load('simple_support/department');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/department');
	
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
				$this->model_simple_support_department->editDepartment($this->request->get['simple_support_department_id'], $this->request->post);
	
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
	
				$this->redirect($this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getForm();
		}
	
		public function delete() {
			$this->language->load('simple_support/department');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/department');
	
			if (isset($this->request->post['selected']) && $this->validateDelete()) {
				foreach ($this->request->post['selected'] as $simple_support_department_id) {
					$this->model_simple_support_department->deleteDepartment($simple_support_department_id);
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
	
				$this->redirect($this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
				'href'      => $this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
			);
	
			$this->data['insert'] = $this->url->link('simple_support/department/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
			$this->data['delete'] = $this->url->link('simple_support/department/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	
	
			$this->data['departments'] = array();
			
			$data = array(
				'sort'  => $sort,
				'order' => $order,
				'start' => ($page - 1) * $this->config->get('config_admin_limit'),
				'limit' => $this->config->get('config_admin_limit')
			);
			
			$department_total = $this->model_simple_support_department->getTotalDepartments($data);
			
			$results = $this->model_simple_support_department->getDepartments($data);
			
			foreach ($results as $result) {
				$action = array();
	
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('simple_support/department/update', 'token=' . $this->session->data['token'] . '&simple_support_department_id=' . $result['simple_support_department_id'] . $url, 'SSL')
				);
				
				$user_info = $this->model_simple_support_department->getUserInfo($result['simple_support_department_id']);
				
				$this->data['departments'][] = array(
					'simple_support_department_id'	=> $result['simple_support_department_id'],
					'name'            				=> $result['name'],
					'username'						=> $user_info['firstname'] . " " . $user_info['lastname'] . " (" . $user_info['email'] .")",
					'status'						=> ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
					'selected'        				=> isset($this->request->post['selected']) && in_array($result['simple_support_department_id'], $this->request->post['selected']),
					'action'          				=> $action
				);
			}	
			
			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_no_results'] = $this->language->get('text_no_results');
	
			$this->data['column_name'] = $this->language->get('column_name');
			$this->data['column_department_head'] = $this->language->get('column_department_head');
			$this->data['column_status'] = $this->language->get('column_status');
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
	
			$this->data['sort_name'] = $this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
			$this->data['sort_status'] = $this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
	
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
	
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
	
			$pagination = new Pagination();
			$pagination->total = $department_total;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_admin_limit');
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
	
			$this->data['pagination'] = $pagination->render();
			
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
	
			$this->template = 'simple_support/department_list.tpl';
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
			$this->data['text_customer'] = $this->language->get('text_customer');
			$this->data['text_admin'] = $this->language->get('text_admin');
			
			$this->data['entry_name'] = $this->language->get('entry_name');
			$this->data['entry_department_head'] = $this->language->get('entry_department_head');
			$this->data['entry_department_for'] = $this->language->get('entry_department_for');
			$this->data['entry_status'] = $this->language->get('entry_status');
	
			$this->data['button_save'] = $this->language->get('button_save');
			$this->data['button_cancel'] = $this->language->get('button_cancel');
			
			$this->data['token'] = $this->session->data['token'];
			
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
			
			if (isset($this->error['department_head_name'])) {
				$this->data['error_department_head_name'] = $this->error['department_head_name'];
			} else {
				$this->data['error_department_head_name'] = '';
			}

			if (isset($this->error['department_for'])) {
				$this->data['error_department_for'] = $this->error['department_for'];
			} else {
				$this->data['error_department_for'] = '';
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
				'href'      => $this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
			);
			
			if (!isset($this->request->get['simple_support_department_id'])) {
				$this->data['action'] = $this->url->link('simple_support/department/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
			} else {
				$this->data['action'] = $this->url->link('simple_support/department/update', 'token=' . $this->session->data['token'] . '&simple_support_department_id=' . $this->request->get['simple_support_department_id'] . $url, 'SSL');
			}
			
			$this->data['cancel'] = $this->url->link('simple_support/department', 'token=' . $this->session->data['token'] . $url, 'SSL');
			
			if (isset($this->request->get['simple_support_department_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
				$department_info = $this->model_simple_support_department->getDepartmentInfo($this->request->get['simple_support_department_id']);
			}
			
			$this->load->model('localisation/language');
	
			$this->data['languages'] = $this->model_localisation_language->getLanguages();

			if (isset($this->request->post['department_name'])) {
				$this->data['department_name'] = $this->request->post['department_name'];
			} elseif (isset($this->request->get['simple_support_department_id'])) {
				$this->data['department_name'] = $this->model_simple_support_department->getDepartmentDescriptions($this->request->get['simple_support_department_id']);
			} else {
				$this->data['department_name'] = array();
			}
			
			$this->data['department_head_name'] = "a@a.com";
			$this->data['department_head_id'] = "1";
			$this->data['department_for'] = array('customer','user');
			/*if (isset($this->request->post['department_head_id'])) {
				$this->data['department_head_name'] = $this->request->post['department_head_name'];
				$this->data['department_head_id'] = $this->request->post['department_head_id'];
			} elseif (isset($this->request->get['simple_support_department_id'])) {
				
				$user_info = $this->model_simple_support_department->getUserInfo($this->request->get['simple_support_department_id']);
				
				$this->data['department_head_name'] = $user_info['firstname'] . " " . $user_info['lastname'] . " (" . $user_info['email'] .")";
				$this->data['department_head_id'] = $user_info['user_id'];
				
			} else {
				$this->data['department_head_name'] = '';
				$this->data['department_head_id'] = 0;
			}
			
			if (isset($this->request->post['department_for'])) {
				$this->data['department_for'] = $this->request->post['department_for'];
			} elseif (isset($this->request->get['simple_support_department_id'])) {
				$this->data['department_for'] = $this->model_simple_support_department->getDepartmentFor($this->request->get['simple_support_department_id']);
			} else {
				$this->data['department_for'] = array();
			}*/
			
			if (isset($this->request->post['status'])) {
				$this->data['status'] = $this->request->post['status'];
			} elseif (!empty($department_info)) {
				$this->data['status'] = $department_info['status'];
			} else {
				$this->data['status'] = 0;
			}
			
			$this->template = 'simple_support/department_form.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
	
			$this->response->setOutput($this->render());			
		}
		
		public function autocomplete() {
			$json = array();
			
			if(isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			
			$this->load->model('simple_support/department');
			
			$results = $this->model_simple_support_department->getUserName($filter_name);
			
			foreach($results as $result) {
				$json[] = array(
					'user_id' 		=> $result['user_id'],
					'username'      => strip_tags(html_entity_decode($result['username'], ENT_QUOTES, 'UTF-8')),
					'name'			=> strip_tags(html_entity_decode($result['firstname'] . " " . $result['lastname'] . " (". $result['email'] . ")", ENT_QUOTES, 'UTF-8'))
				);	
			}
			
			
			$this->response->setOutput(json_encode($json));
		}
		
		public function validateForm() {
			
			//return true;
			
			if (!$this->user->hasPermission('modify', 'simple_support/department')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
	
			foreach ($this->request->post['department_name'] as $language_id => $value) {
				if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
					$this->error['name'][$language_id] = $this->language->get('error_name');
				}
			}
			
			if($this->request->post['department_head_name'] == '') {
				$this->error['department_head_name'] = $this->language->get('error_department_head_name');
			} else {
				if($this->request->post['department_head_id']) {
					
				} else {
					$this->error['department_head_name'] = $this->language->get('error_department_head_name_not_found');
				}
			}
			
			if(!isset($this->request->post['department_for'])) {
				$this->error['department_for'] = $this->language->get('error_department_for');
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
			if (!$this->user->hasPermission('modify', 'simple_support/department')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
			
			foreach ($this->request->post['selected'] as $simple_support_department_id) {
				$found = $this->model_simple_support_department->checkDepartment($simple_support_department_id);
				
				if($found->num_rows) {
					$this->error['warning'] = sprintf($this->language->get('error_department_delete'), $found->row['name'], $found->num_rows);
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