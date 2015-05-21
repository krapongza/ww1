<?php
	class ControllerSimpleSupportTicket extends Controller {
		
		private $error = array();
		
		public function index() {
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('simple_support/ticket', '', 'SSL');
	
				$this->redirect($this->url->link('account/login', '', 'SSL'));
			}
			
			$this->language->load('simple_support/ticket');

			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('simple_support/ticket');
			
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->data['text_search_ticket'] = $this->language->get('text_search_ticket');
			$this->data['text_no_results'] = $this->language->get('text_no_results');
			$this->data['text_view'] = $this->language->get('text_view');
			
			$this->data['button_search'] = $this->language->get('button_search');
			$this->data['button_create_ticket'] = $this->language->get('button_create_ticket');
			
			$this->data['column_ticket'] = $this->language->get('column_ticket');
			$this->data['column_department'] = $this->language->get('column_department');
			$this->data['column_subject'] = $this->language->get('column_subject');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
			$this->data['column_date_update'] = $this->language->get('column_date_update');
			$this->data['column_ticket_status'] = $this->language->get('column_ticket_status');
			$this->data['column_action'] = $this->language->get('column_action');
			
			$this->data['create_new_ticket'] = $this->url->link('simple_support/ticket/new_ticket', '', 'SSL');
			
			
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}	
			
			if(isset($this->request->get['filter_search'])) {
				$filter_search = $this->request->get['filter_search'];
			} else {
				$filter_search = '';
			}
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/ticket', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['tickets'] = array();
			
			$data = array(		
				'filter_search'	=> $filter_search,		  
				'sort'  => 'date_added',
				'order' => 'DESC',
				'start' => ($page - 1) * 10,
				'limit' => 10
			);
			
			$ticket_total = $this->model_simple_support_ticket->getTotalTicket($data);
			
			$results = $this->model_simple_support_ticket->getTickets($data);
			
			foreach ($results as $result) {
				$this->data['tickets'][] = array(
					'simple_support_ticket_id'	=> $result['simple_support_ticket_id'],
					'ticket_id'      			=> $result['ticket_id'],
					'subject'      				=> $result['subject'],
					'department_name'			=> $result['department_name'],
					'status_name'				=> $result['status_name'],
					'date_added'  				=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'date_modified'  			=> date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
					'href'        				=> $this->url->link('simple_support/ticket/info', 'simple_support_ticket_id=' . $result['simple_support_ticket_id'], 'SSL')
				);
			}	
			
			if (isset($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];
	
				unset($this->session->data['success']);
			} else {
				$this->data['success'] = '';
			}
			
			$pagination = new Pagination();
			$pagination->total = $ticket_total;
			$pagination->page = $page;
			$pagination->limit = 10; 
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('simple_support/ticket', 'page={page}', 'SSL');
	
			$this->data['pagination'] = $pagination->render();
			
			$this->data['filter_search'] = $filter_search;
			
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];


			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/ticket_list.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/ticket_list.tpl';
			} else {
				$this->template = 'default/template/simple_support/ticket_list.tpl';
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

		public function new_ticket() {
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('simple_support/ticket/new_ticket', '', 'SSL');
	
				$this->redirect($this->url->link('account/login', '', 'SSL'));
			}
			
			$this->language->load('simple_support/ticket');

			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('simple_support/ticket');
			
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->data['text_add_file'] = $this->language->get('text_add_file');
			$this->data['text_new_ticket'] = $this->language->get('text_new_ticket');
			$this->data['text_select_department'] = $this->language->get('text_select_department');
			
			$this->data['entry_department'] = $this->language->get('entry_department');
			$this->data['entry_subject'] = $this->language->get('entry_subject');
			$this->data['entry_description'] = $this->language->get('entry_description');
			$this->data['entry_file'] = $this->language->get('entry_file');
			
			$this->data['button_cancel'] = $this->language->get('button_cancel');
			$this->data['button_submit'] = $this->language->get('button_submit');
			
			$this->data['departments'] = $this->model_simple_support_ticket->getDepartments();
			
			$this->data['action'] = $this->url->link('simple_support/ticket/new_ticket', '', 'SSL');
			
			$this->data['cancel'] = $this->url->link('simple_support/ticket', '' , 'SSL');
			
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_ticket->addTicket($this->request->post);
	
				$this->session->data['success'] = $this->language->get('text_success');
	
				$this->redirect($this->url->link('simple_support/ticket', '', 'SSL'));
			}
			
			if (isset($this->error['department_id'])) {
				$this->data['error_department_id'] = $this->error['department_id'];
			} else {
				$this->data['error_department_id'] = '';
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
			
			if(isset($this->request->post['department_id'])) {
				$this->data['department_id'] = $this->request->post['department_id'];
			} else {
				$this->data['department_id'] = '';
			}

			if(isset($this->request->post['subject'])) {
				$this->data['subject'] = $this->request->post['subject'];
			} else {
				$this->data['subject'] = '';
			}
			
			if(isset($this->request->post['description'])) {
				$this->data['description'] = $this->request->post['description'];
			} else {
				$this->data['description'] = '';
			}			
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/ticket', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];


			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/ticket_form.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/ticket_form.tpl';
			} else {
				$this->template = 'default/template/simple_support/ticket_form.tpl';
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
		
		public function info() {
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('simple_support/ticket/info', 'simple_support_ticket_id=' . $this->request->get['simple_support_ticket_id'], 'SSL');
	
				$this->redirect($this->url->link('account/login', '', 'SSL'));
			}
			
			$this->language->load('simple_support/ticket');

			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('simple_support/ticket');
			
			if(isset($this->request->get['simple_support_ticket_id'])) {
				$simple_support_ticket_id = $this->request->get['simple_support_ticket_id'];
			} else {
				$simple_support_ticket_id = 0;
			}
			
			$this->data['simple_support_ticket_id'] = $simple_support_ticket_id;
			
			$this->data['text_add_file'] = $this->language->get('text_add_file');
			$this->data['text_info_not_found'] = $this->language->get('text_info_not_found');
			
			$this->data['entry_department'] = $this->language->get('entry_department');
			$this->data['entry_subject'] = $this->language->get('entry_subject');
			$this->data['entry_description'] = $this->language->get('entry_description');
			$this->data['entry_file'] = $this->language->get('entry_file');
			$this->data['entry_status'] = $this->language->get('entry_status');
			
			$this->data['button_cancel'] = $this->language->get('button_cancel');
			$this->data['button_submit'] = $this->language->get('button_submit');
			
			$this->data['action'] = $this->url->link('simple_support/ticket/info', 'simple_support_ticket_id=' . $simple_support_ticket_id , 'SSL');
			
			$this->data['cancel'] = $this->url->link('simple_support/ticket', '' , 'SSL');
			
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				//print "<pre>"; print_r($this->request->post); exit;
				$this->model_simple_support_ticket->addHistory($simple_support_ticket_id, $this->request->post);
	
				$this->session->data['success'] = $this->language->get('text_update_ticket');
	
				$this->redirect($this->url->link('simple_support/ticket', '', 'SSL'));
			}
			
			$simple_ticket_info = $this->model_simple_support_ticket->getTicketInfo($simple_support_ticket_id);
			
			if($simple_ticket_info) {
				$this->data['simple_ticket_info'] = $simple_ticket_info;
				
				$this->data['heading_title'] = $this->language->get('heading_title') . " - " . $simple_ticket_info['ticket_id'];
				
			} else {
				$this->data['simple_ticket_info'] = '';
			}			
			
			if (isset($this->error['description'])) {
				$this->data['error_description'] = $this->error['description'];
			} else {
				$this->data['error_description'] = '';
			}
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/ticket', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];


			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/ticket_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/ticket_info.tpl';
			} else {
				$this->template = 'default/template/simple_support/ticket_info.tpl';
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
		
		public function validate() {
			if(!isset($this->request->get['simple_support_ticket_id'])) {
				if (!$this->request->post['department_id']) {
					$this->error['department_id'] = $this->language->get('error_department_id');
				}
		
				if ((utf8_strlen($this->request->post['subject']) < 3) || (utf8_strlen($this->request->post['subject']) > 256)) {
					$this->error['subject'] = $this->language->get('error_subject');
				}
			}			
	
			if (utf8_strlen($this->request->post['description']) < 3) {
				$this->error['description'] = $this->language->get('error_description');
			}	
			
			if (!$this->error) {
				return true;
			} else {
				return false;
			}
		}
		
		public function history() {
			$this->language->load('simple_support/ticket');
		
			$this->load->model('simple_support/ticket');
					
			$this->data['text_no_results'] = $this->language->get('text_no_results');
			
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}  
			
			$this->data['histories'] = array();
				
			$results = $this->model_simple_support_ticket->getTicketHistories($this->request->get['simple_support_ticket_id'], ($page - 1) * 10, 10);
	      		
			foreach ($results as $result) {
				
				if($result['customer_id']) {
					$name = $this->customer->getFirstName() . " " . $this->customer->getLastName();
					
				} else {
					$info = $this->model_simple_support_ticket->getUser($result['user_id']);
					
					$name = $info['firstname'] . " " . $info['lastname'];
				}
				
				$images = $this->model_simple_support_ticket->getImages($result['simple_support_ticket_history_id']);
				
				$ticket_images = array();
				
				foreach($images as $image) {
					$ticket_images[] = array(
						'value' => utf8_substr($image['image'], 0, utf8_strrpos($image['image'], '.')),
						'href'  => $this->url->link('simple_support/ticket/download', 'simple_support_ticket_images_id=' . $image['simple_support_ticket_images_id'], 'SSL')
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
			
			$history_total = $this->model_simple_support_ticket->getTotalTicketHistories($this->request->get['simple_support_ticket_id']);
				
			$pagination = new Pagination();
			$pagination->total = $history_total;
			$pagination->page = $page;
			$pagination->limit = 10; 
			$pagination->url = $this->url->link('simple_support/ticket/history', 'simple_support_ticket_id=' . $this->request->get['simple_support_ticket_id'] . '&page={page}', 'SSL');
				
			$this->data['pagination'] = $pagination->render();
			
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];



			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/ticket_history.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/ticket_history.tpl';
			} else {
				$this->template = 'default/template/simple_support/ticket_history.tpl';
			}		
			
			$this->response->setOutput($this->render());
		}
		
		public function upload() {
			$this->language->load('simple_support/ticket');
		
			$json = array();
	    	
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
		
		public function download() {
			$this->load->model('simple_support/ticket');
	
			if (isset($this->request->get['simple_support_ticket_images_id'])) {
				$simple_support_ticket_images_id = $this->request->get['simple_support_ticket_images_id'];
			} else {
				$simple_support_ticket_images_id = 0;
			}
	
			$images_info = $this->model_simple_support_ticket->getTicketImage($this->request->get['simple_support_ticket_images_id']);
	
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