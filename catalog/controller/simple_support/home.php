<?php
	class ControllerSimpleSupportHome extends Controller {
		public function index() {
			$this->language->load('simple_support/home');

			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('simple_support/faq_group');
			
			$this->data['faq_groups'] = array();
			
			$this->document->addStyle('catalog/view/theme/default/stylesheet/simple_support_custom.css');
			
			if(isset($this->request->get['filter_search'])) {
				$filter_search = $this->request->get['filter_search'];
			} else {
				$filter_search = '';
			}

			if(isset($this->request->get['simple_support_faq_group_id'])) {
				$faq_group = $this->model_simple_support_faq_group->getFaqGroup($this->request->get['simple_support_faq_group_id']);
				
				if($faq_group) {
					$faqs = array();
					$results = $this->model_simple_support_faq_group->getFaqsGroupWise($faq_group['simple_support_faq_group_id'] , $filter_search);
					
					foreach($results as $result) {
						$faqs[] = array(
							'simple_support_faq_id'	=> $result['simple_support_faq_id'],
							'question'				=> $result['question'],
							'answer'				=> html_entity_decode($result['answer'], ENT_QUOTES, 'UTF-8')
						);	
					}	
					
					if($faqs) {
						$this->data['faq_groups'][] = array(
							'name'     => $faq_group['name'],
							'faqs' => $faqs,
						);
					}
					
				} 			
			} else {
				$faq_groups = $this->model_simple_support_faq_group->getFaqGroups();
				
				foreach($faq_groups as $faq_group) {
					
					$faq_group_faqs = array();					
					$faqs = array();
			
					$results = $this->model_simple_support_faq_group->getFaqsGroupWise($faq_group['simple_support_faq_group_id'] , $filter_search);
					
					foreach($results as $result) {
						$faqs[] = array(
							'simple_support_faq_id'	=> $result['simple_support_faq_id'],
							'question'				=> $result['question'],
							'answer'				=> html_entity_decode($result['answer'], ENT_QUOTES, 'UTF-8')
						);	
					}	
					
					if($faqs) {
						$this->data['faq_groups'][] = array(
							'name'     => $faq_group['name'],
							'faqs' => $faqs,
						);
					}									
				}
			}
			
			//print "<pre>"; print_r($this->data['faq_groups']); exit;
			
			$this->data['text_no_faq_found'] = $this->language->get('text_no_faq_found');
			$this->data['text_support_ticket'] = $this->language->get('text_support_ticket');
			
			$this->data['support_ticket'] = $this->url->link('simple_support/ticket', '', 'SSL');
			$this->data['login'] = $this->customer->isLogged();

			$this->data['breadcrumbs'] = array();
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/home', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			if (isset($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];
	
				unset($this->session->data['success']);
			} else {
				$this->data['success'] = '';
			}
	
			$this->data['heading_title'] = $this->language->get('heading_title');
			

			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$point_credit['point'] = ( isset($point_credit['point']) )?$point_credit['point']:0;
			$point_credit['credit'] = ( isset($point_credit['credit']) )?$point_credit['credit']:0;
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];


			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/home.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/home.tpl';
			} else {
				$this->template = 'default/template/simple_support/home.tpl';
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



		public function details() {
			$this->language->load('simple_support/home');

			$this->document->setTitle($this->language->get('heading_title'));
			
			$this->load->model('simple_support/faq_group');
			
			$this->data['faq_groups'] = array();
			
			$this->document->addStyle('catalog/view/theme/default/stylesheet/simple_support_custom.css');
			
			if(isset($this->request->get['id'])) {
				$id = $this->request->get['id'];
			} else {
				$id = '';
			}

			 
			//$faq_group = $this->model_simple_support_faq_group->getFaqGroups();
			
			//if($faq_group) {
				$faqs = array();
				$results = $this->model_simple_support_faq_group->getFaqsWise( $id );
				
				foreach($results as $result) {
					$faqs[] = array(
						'simple_support_faq_id'	=> $result['simple_support_faq_id'],
						'question'				=> $result['question'],
						'answer'				=> html_entity_decode($result['answer'], ENT_QUOTES, 'UTF-8')
					);	
				}	
				
				if($faqs) {
					$this->data['faq_groups'][] = array(
						//'name'     => $faq_group['name'],
						'faqs' => $faqs,
					);
				}
				
			//} 			
			 
			
			//print "<pre>"; print_r($this->data['faq_groups']); exit;
			
			$this->data['text_no_faq_found'] = $this->language->get('text_no_faq_found');
			$this->data['text_support_ticket'] = $this->language->get('text_support_ticket');
			
			$this->data['support_ticket'] = $this->url->link('simple_support/ticket', '', 'SSL');
			$this->data['login'] = $this->customer->isLogged();

			$this->data['breadcrumbs'] = array();
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('simple_support/home', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			if (isset($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];
	
				unset($this->session->data['success']);
			} else {
				$this->data['success'] = '';
			}
	
			$this->data['heading_title'] = $this->language->get('heading_title');
			
			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			if(count($point_credit) > 0){
				$this->data['point'] = $point_credit['point'];
				$this->data['credit'] =  $point_credit['credit'];
			}else{
				$this->data['point'] = 0;
				$this->data['credit'] =  0;
			}



			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/home_details.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/home_details.tpl';
			} else {
				$this->template = 'default/template/simple_support/home_details.tpl';
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