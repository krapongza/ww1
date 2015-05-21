<?php
	class ControllerSimpleSupportCustomerTicket extends Controller {
		private $error = array();
		
		public function index() {
			$this->language->load('simple_support/customer_ticket');

			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/customer_ticket');
	
			$this->getList();
		}
		
		public function insert() {
			$this->language->load('simple_support/customer_ticket');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/customer_ticket');
	
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_customer_ticket->addticket($this->request->post);
	
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
	
				$this->redirect($this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getForm();
		}
	
		public function update() {
			$this->language->load('simple_support/customer_ticket');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/customer_ticket');
	
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_customer_ticket->editTicket($this->request->get['simple_support_ticket_id'], $this->request->post);
	
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
	
				$this->redirect($this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getForm();
		}
	
		public function delete() {
			$this->language->load('simple_support/customer_ticket');
	
			$this->document->setTitle($this->language->get('heading_title'));
	
			$this->load->model('simple_support/customer_ticket');
	
			if (isset($this->request->post['selected']) && $this->validateDelete()) {
				foreach ($this->request->post['selected'] as $simple_support_ticket_id) {
					$this->model_simple_support_customer_ticket->deleteticket($simple_support_ticket_id);
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
	
				$this->redirect($this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
	
			$this->getList();
		}
		
		public function getList() {
			
			if (isset($this->request->get['view_all'])) {
				$view_all = $this->request->get['view_all'];
			} else {
				$view_all = '';
			}
			
			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'sst.date_modified';
			}
	
			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'DESC';
			}
	
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}
	
			$url = '';
			
			if (isset($this->request->get['view_all'])) {
				$url .= '&view_all=' . $this->request->get['view_all'];
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
				'href'      => $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
			);
			
			$simple_support_ticket_permission = array();
			
			if($this->config->get('simple_support_ticket_permission')) {
				$simple_support_ticket_permission = $this->config->get('simple_support_ticket_permission');
				
				if(in_array($this->user->getId(), $simple_support_ticket_permission)) {
					$this->data['found_user_view_all'] = 1;
					$this->data['view_all_ticket'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&view_all=1', 'SSL');
				}	
			}
			
			$this->data['insert'] = $this->url->link('simple_support/customer_ticket/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
			$this->data['delete'] = $this->url->link('simple_support/customer_ticket/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	
			
			$this->load->model('simple_support/status');
			
			$this->data['tickets'] = array();
			
			$data = array(
				'view_all'	=> $view_all,
				'sort'  	=> $sort,
				'order' 	=> $order,
				'start' 	=> ($page - 1) * $this->config->get('config_admin_limit'),
				'limit' 	=> $this->config->get('config_admin_limit')
			);
			
			$ticket_total = $this->model_simple_support_customer_ticket->getTotalTickets($data);
			
			$results = $this->model_simple_support_customer_ticket->getTickets($data);
			
			foreach ($results as $result) {
				$action = array();
	
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('simple_support/customer_ticket/update', 'token=' . $this->session->data['token'] . '&simple_support_ticket_id=' . $result['simple_support_ticket_id'] . $url, 'SSL')
				);
				
				$ticket_status_info = $this->model_simple_support_status->getTicketStatus($result['simple_support_ticket_status_id']);
				
				$this->data['tickets'][] = array(
					'simple_support_ticket_id'		=> $result['simple_support_ticket_id'],
					'customer_name'            		=> $result['customer'],
					'username'            			=> $result['user_name'],
					'useremail'            			=> $result['user_email'],
					'ticket_id'						=> $result['ticket_id'],
					'subject'						=> $result['subject'],
					'ticket_status'					=> $ticket_status_info['name'],
					'status'						=> ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
					'date_added'					=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'date_modified'					=> date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
					'selected'        				=> isset($this->request->post['selected']) && in_array($result['simple_support_ticket_id'], $this->request->post['selected']),
					'action'          				=> $action
				);
			}	
			
			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_no_results'] = $this->language->get('text_no_results');
	
			$this->data['column_ticket'] = $this->language->get('column_ticket');
			$this->data['column_customer'] = $this->language->get('column_customer');
			$this->data['column_username'] = $this->language->get('column_username');
			$this->data['column_useremail'] = $this->language->get('column_useremail');
			$this->data['column_subject'] = $this->language->get('column_subject');
			$this->data['column_ticket_status'] = $this->language->get('column_ticket_status');
			$this->data['column_status'] = $this->language->get('column_status');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
			$this->data['column_date_modified'] = $this->language->get('column_date_modified');
			$this->data['column_action'] = $this->language->get('column_action');		
	
			$this->data['button_insert'] = $this->language->get('button_insert');
			$this->data['button_delete'] = $this->language->get('button_delete');
			$this->data['button_view_all'] = $this->language->get('button_view_all');
			
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
			
			if (isset($this->request->get['view_all'])) {
				$url .= '&view_all=' . $this->request->get['view_all'];
			}
			
			if ($order == 'ASC') {
				$url .= '&order=DESC';
			} else {
				$url .= '&order=ASC';
			}
	
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
	
			$this->data['sort_ticket'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=sst.ticket_id' . $url, 'SSL');
			$this->data['sort_customer'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, 'SSL');
			$this->data['sort_subject'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=sst.subject' . $url, 'SSL');
			$this->data['sort_ticket_status'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=sst.simple_support_ticket_status_id' . $url, 'SSL');
			$this->data['sort_status'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=sst.status' . $url, 'SSL');
			$this->data['sort_date_added'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=sst.date_added' . $url, 'SSL');
			$this->data['sort_date_modified'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . '&sort=sst.date_modified' . $url, 'SSL');
	
			$url = '';
			
			if (isset($this->request->get['view_all'])) {
				$url .= '&view_all=' . $this->request->get['view_all'];
			}
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
	
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
	
			$pagination = new Pagination();
			$pagination->total = $ticket_total;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_admin_limit');
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
	
			$this->data['pagination'] = $pagination->render();
			
			$this->data['view_all'] = $view_all;
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
	
			$this->template = 'simple_support/customer_ticket_list.tpl';
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
			$this->data['text_notify_customer'] = $this->language->get('text_notify_customer');
			$this->data['text_select_department'] = $this->language->get('text_select_department');
			$this->data['text_select_user'] = $this->language->get('text_select_user');
			
			$this->data['entry_customer'] = $this->language->get('entry_customer');
			$this->data['entry_subject'] = $this->language->get('entry_subject');
			$this->data['entry_ticket_id'] = $this->language->get('entry_ticket_id');
			$this->data['entry_ticket_status'] = $this->language->get('entry_ticket_status');
			$this->data['entry_department'] = $this->language->get('entry_department');
			$this->data['entry_date_added'] = $this->language->get('entry_date_added');
			$this->data['entry_date_modified'] = $this->language->get('entry_date_modified');
			$this->data['entry_status'] = $this->language->get('entry_status');
			$this->data['entry_description'] = $this->language->get('entry_description');
			$this->data['entry_file'] = $this->language->get('entry_file');
			$this->data['entry_add_other'] = $this->language->get('entry_add_other');
			$this->data['entry_assign_ticket'] = $this->language->get('entry_assign_ticket');
			$this->data['entry_current_status'] = $this->language->get('entry_current_status');
			$this->data['entry_assign_user'] = $this->language->get('entry_assign_user');
			
			$this->data['tab_general'] = $this->language->get('tab_general');
			$this->data['tab_history'] = $this->language->get('tab_history');	
			
			$this->data['button_save'] = $this->language->get('button_save');
			$this->data['button_cancel'] = $this->language->get('button_cancel');	
			
			$this->data['token'] = $this->session->data['token'];
			
			if(isset($this->request->get['simple_support_ticket_id'])) {
				$this->data['simple_support_ticket_id'] = $this->request->get['simple_support_ticket_id'];
			} else {
				$this->data['simple_support_ticket_id'] = 0;	
			}
			
			if (isset($this->error['warning'])) {
				$this->data['error_warning'] = $this->error['warning'];
			} else {
				$this->data['error_warning'] = '';
			}
			
			if (isset($this->error['customer_name'])) {
				$this->data['error_customer_name'] = $this->error['customer_name'];
			} else {
				$this->data['error_customer_name'] = '';
			}
			
			if (isset($this->error['subject'])) {
				$this->data['error_subject'] = $this->error['subject'];
			} else {
				$this->data['error_subject'] = '';
			}
			
			if (isset($this->error['description'])) {
				$this->data['error_description'] = $this->error['description'];
			} else {
				$this->data['error_description'] = '';
			}
			
			if (isset($this->error['department_id'])) {
				$this->data['error_department_id'] = $this->error['department_id'];
			} else {
				$this->data['error_department_id'] = '';
			}
			
			if (isset($this->error['user_id'])) {
				$this->data['error_user_id'] = $this->error['user_id'];
			} else {
				$this->data['error_user_id'] = '';
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
				'href'      => $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url, 'SSL'),
				'separator' => ' :: '
			);
			
			$this->load->model('sale/customer');
			
			$this->load->model('simple_support/status');
			$this->data['ticket_status'] = $this->model_simple_support_status->getStatues();
			
			$this->load->model('simple_support/department');
			$this->load->model('user/user');
			
			$this->data['departments'] = $this->model_simple_support_customer_ticket->getDepartments();
			
			if (!isset($this->request->get['simple_support_ticket_id'])) {
				$this->data['action'] = $this->url->link('simple_support/customer_ticket/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
			} else {
				$this->data['action'] = $this->url->link('simple_support/customer_ticket/update', 'token=' . $this->session->data['token'] . '&simple_support_ticket_id=' . $this->request->get['simple_support_ticket_id'] . $url, 'SSL');
			}
			
			$this->data['cancel'] = $this->url->link('simple_support/customer_ticket', 'token=' . $this->session->data['token'] . $url, 'SSL');
			
			if (isset($this->request->get['simple_support_ticket_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
				$ticket_info = $this->model_simple_support_customer_ticket->getTicketInfo($this->request->get['simple_support_ticket_id']);
			}
			
			if(isset($this->request->get['simple_support_ticket_id'])) {
				$ticket_info_found = $this->model_simple_support_customer_ticket->getTicketInfo($this->request->get['simple_support_ticket_id']);
				
				$this->data['main_description'] = $ticket_info_found['description'];
				
				$this->data['status_name'] = $this->model_simple_support_status->getTicketStatus($ticket_info_found['simple_support_ticket_status_id']);
				
				$this->data['user'] = $this->model_user_user->getUser($ticket_info_found['user_id']);
			}
			
			if (isset($this->request->post['ticket_id'])) {
				$this->data['ticket_id'] = $this->request->post['ticket_id'];
			} elseif (!empty($ticket_info)) {
				$this->data['ticket_id'] = $ticket_info['ticket_id'];
			} else {
				$this->data['ticket_id'] = '';
			}
			
			if (isset($this->request->post['subject'])) {
				$this->data['subject'] = $this->request->post['subject'];
			} elseif (!empty($ticket_info)) {
				$this->data['subject'] = $ticket_info['subject'];
			} else {
				$this->data['subject'] = '';
			}
			
			if (isset($this->request->post['description'])) {
				$this->data['description'] = $this->request->post['description'];
			} else {
				$this->data['description'] = '';
			}
			
			if (isset($this->request->post['customer_id'])) {
				$this->data['customer_name'] = $this->request->post['customer_name'];
				$this->data['customer_id'] = $this->request->post['customer_id'];
			} elseif (!empty($ticket_info)) {
				$customer_info = $this->model_sale_customer->getCustomer($ticket_info['customer_id']);
				
				$this->data['customer_id'] = $customer_info['customer_id'];
				$this->data['customer_name'] = $customer_info['firstname'] . " " . $customer_info['lastname'];				
			} else {
				$this->data['customer_name'] = '';
				$this->data['customer_id'] = 0;
			}
			
			if (isset($this->request->post['simple_support_department_id'])) {
				$this->data['simple_support_department_id'] = $this->request->post['simple_support_department_id'];
			} elseif (!empty($ticket_info)) {
				$this->data['simple_support_department_id'] = $ticket_info['simple_support_department_id'];
			} else {
				$this->data['simple_support_department_id'] = '';
			}
			
			if (isset($this->request->post['user_id'])) {
				$this->data['user_id'] = $this->request->post['user_id'];
			} elseif (!empty($ticket_info)) {
				$this->data['user_id'] = $ticket_info['user_id'];
			} else {
				$this->data['user_id'] = 0;
			}
			
			if (isset($this->request->post['simple_support_ticket_status_id'])) {
				$this->data['simple_support_ticket_status_id'] = $this->request->post['simple_support_ticket_status_id'];
			} elseif (!empty($ticket_info)) {
				$this->data['simple_support_ticket_status_id'] = $ticket_info['simple_support_ticket_status_id'];
			} else {
				$this->data['simple_support_ticket_status_id'] = '';
			}
			
			if (isset($this->request->post['status'])) {
				$this->data['status'] = $this->request->post['status'];
			} elseif (!empty($ticket_info)) {
				$this->data['status'] = $ticket_info['status'];
			} else {
				$this->data['status'] = 0;
			}
			
			if (isset($this->request->post['date_added'])) {
				$this->data['date_added'] = $this->request->post['date_added'];
			} elseif (!empty($ticket_info)) {
				$this->data['date_added'] = date($this->language->get('date_format_long'), strtotime($ticket_info['date_added']));
			} else {
				$this->data['date_added'] = '';
			}

			if (isset($this->request->post['date_modified'])) {
				$this->data['date_modified'] = $this->request->post['date_modified'];
			} elseif (!empty($ticket_info)) {
				$this->data['date_modified'] = date($this->language->get('date_format_long'), strtotime($ticket_info['date_modified']));;
			} else {
				$this->data['date_modified'] = '';
			}
			
			$this->template = 'simple_support/customer_ticket_form.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
	
			$this->response->setOutput($this->render());			
		}	

		public function validateForm() {
			//return true;
			
			if (!$this->user->hasPermission('modify', 'simple_support/customer_ticket')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
			
			if(utf8_strlen($this->request->post['description']) < 3) {
				$this->error['description'] = $this->language->get('error_description');
				$this->error['warning'] = $this->language->get('error_ticket_description');
			}	
			
			if(!$this->request->post['simple_support_department_id']) {
				$this->error['department_id'] = $this->language->get('error_department_id');
			} else {
				if(!isset($this->request->post['user_id']) || !$this->request->post['user_id']) {
					$this->error['user_id'] = $this->language->get('error_user_id');
				}
			}	
			
			if ($this->error && !isset($this->error['warning'])) {
				$this->error['warning'] = $this->language->get('error_warning');
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
		
		public function validateDelete() {
			if (!$this->user->hasPermission('modify', 'simple_support/customer_ticket')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
			
			if (!$this->error) {
				return true;
			} else {
				return false;
			}	
		}
		
		public function history() {
			$this->language->load('simple_support/customer_ticket');
		
			$this->load->model('simple_support/customer_ticket');
			$this->load->model('user/user');
			$this->load->model('sale/customer');
					
			$this->data['text_no_results'] = $this->language->get('text_no_results');
			
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}  
			
			$this->data['histories'] = array();
				
			$results = $this->model_simple_support_customer_ticket->getTicketHistories($this->request->get['simple_support_ticket_id'], ($page - 1) * 10, 10);
	      		
			foreach ($results as $result) {
				
				if($result['customer_id']) {
					$info = $this->model_sale_customer->getCustomer($result['customer_id']);
					
					$name = $info['firstname'] . " " . $info['lastname'];
					
				} else {
					$info = $this->model_user_user->getUser($result['user_id']);
					
					$name = $info['firstname'] . " " . $info['lastname'];
				}
				
				$images = $this->model_simple_support_customer_ticket->getImages($result['simple_support_ticket_history_id']);
				
				$ticket_images = array();
				
				foreach($images as $image) {
					$ticket_images[] = array(
						'value' => utf8_substr($image['image'], 0, utf8_strrpos($image['image'], '.')),
						'href'  => $this->url->link('simple_support/customer_ticket/download', 'token=' . $this->session->data['token'] . '&simple_support_ticket_images_id=' . $image['simple_support_ticket_images_id'], 'SSL')
					);					
				}
				
	        	$this->data['histories'][] = array(
					'simple_support_ticket_history_id'   	=> $result['simple_support_ticket_history_id'],
					'name'   								=> $name,
					'ticket_images'							=> $ticket_images,
					'description'							=> $result['description'],
	        		'date_added' 							=> date($this->language->get('date_format_long'), strtotime($result['date_added']))
	        	);
	      	}			
			
			//print "<pre>"; print_r($this->data['histories']); print "</pre>";
			
			$history_total = $this->model_simple_support_customer_ticket->getTotalTicketHistories($this->request->get['simple_support_ticket_id']);
				
			$pagination = new Pagination();
			$pagination->total = $history_total;
			$pagination->page = $page;
			$pagination->limit = 10; 
			$pagination->url = $this->url->link('simple_support/customer_ticket/history', 'token=' . $this->session->data['token'] . '&simple_support_ticket_id=' . $this->request->get['simple_support_ticket_id'] . '&page={page}', 'SSL');
				
			$this->data['pagination'] = $pagination->render();
			
			$this->template = 'simple_support/ticket_history.tpl';		
			
			$this->response->setOutput($this->render());
		}
		
		public function upload() {
			$this->language->load('simple_support/customer_ticket');
		
			$json = array();
	    	
			if (!$this->user->hasPermission('modify', 'simple_support/customer_ticket')) {
	      		$json['error'] = $this->language->get('error_permission');
	    	}	

			if (!isset($json['error'])) {
				if (!empty($this->request->files['file']['name'])) {
					$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
		
					if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
						$json['error'] = $this->language->get('error_filename');
					}
		
					// Allowed file extension types
					$allowed = array();
		
					$filetypes = explode("\n", $this->config->get('config_file_extension_allowed'));
		
					foreach ($filetypes as $filetype) {
						$allowed[] = trim($filetype);
					}
		
					if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}
		
					// Allowed file mime types
					$allowed = array();
		
					$filetypes = explode("\n", $this->config->get('config_file_mime_allowed'));
		
					foreach ($filetypes as $filetype) {
						$allowed[] = trim($filetype);
					}
		
					if (!in_array($this->request->files['file']['type'], $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}
		
					// Check to see if any PHP files are trying to be uploaded
					$content = file_get_contents($this->request->files['file']['tmp_name']);
		
					if (preg_match('/\<\?php/i', $content)) {
						$json['error'] = $this->language->get('error_filetype');
					}
		
					if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
						$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
					}
				} else {
					$json['error'] = $this->language->get('error_upload');
				}
		
				if (!$json && is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
					$file = basename($filename) . '.' . md5(mt_rand());
		
					// Hide the uploaded file name so people can not link to it directly.
					//$json['file'] = $this->encryption->encrypt($file);
					
					$json['file'] = $file;
					
					$json['filename'] = $filename;
					
					move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
		
					$json['success'] = $this->language->get('text_upload');
				}
			}
			
			$this->response->setOutput(json_encode($json));
		}
		
		public function departmentUser() {
			$json = array();
			
			$this->load->model('simple_support/customer_ticket');
			
			if(isset($this->request->get['department_id'])) {
				$department_id = $this->request->get['department_id'];
			} else {
				$department_id = '';
			}
			
			$results = $this->model_simple_support_customer_ticket->getDepartmentWiseUser($department_id);
			
			$json = array(
				'users'	=> $results
			);			
			
			$this->response->setOutput(json_encode($json));
		}	
		
		public function download() {
			$this->load->model('simple_support/customer_ticket');
	
			if (isset($this->request->get['simple_support_ticket_images_id'])) {
				$simple_support_ticket_images_id = $this->request->get['simple_support_ticket_images_id'];
			} else {
				$simple_support_ticket_images_id = 0;
			}
	
			$images_info = $this->model_simple_support_customer_ticket->getTicketImage($this->request->get['simple_support_ticket_images_id']);
	
			if ($images_info) {
				$file = DIR_DOWNLOAD . $images_info['image'];
				$mask = basename(utf8_substr($images_info['image'], 0, utf8_strrpos($images_info['image'], '.')));
	
				if (!headers_sent()) {
					if (file_exists($file)) {
						header('Content-Type: application/octet-stream');
						header('Content-Description: File Transfer');
						header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						header('Content-Length: ' . filesize($file));
	
						readfile($file, 'rb');
						exit;
					} else {
						exit('Error: Could not find file ' . $file . '!');
					}
				} else {
					exit('Error: Headers already sent out!');
				}
			} else {
				$this->language->load('error/not_found');
	
				$this->document->setTitle($this->language->get('heading_title'));
	
				$this->data['heading_title'] = $this->language->get('heading_title');
	
				$this->data['text_not_found'] = $this->language->get('text_not_found');
	
				$this->data['breadcrumbs'] = array();
	
				$this->data['breadcrumbs'][] = array(
					'text'      => $this->language->get('text_home'),
					'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
					'separator' => false
				);
	
				$this->data['breadcrumbs'][] = array(
					'text'      => $this->language->get('heading_title'),
					'href'      => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL'),
					'separator' => ' :: '
				);
	
				$this->template = 'error/not_found.tpl';
				$this->children = array(
					'common/header',
					'common/footer'
				);
	
				$this->response->setOutput($this->render());
			}
		}	
	}
?>