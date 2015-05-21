<?php
class ControllerReportProductViewed extends Controller {
	public function index() {     
		$this->language->load('report/product_viewed');
		$this->document->setTitle('Stock Report');
		
		$page = (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		$url = '';
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
		
		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Stock Report', //$this->language->get('heading_title'),
			'href'      => $this->url->link('report/product_viewed', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);		
		
		$this->load->model('report/product');
		$data = array(
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
				
		$product_viewed_total = $this->model_report_product->getTotalProductAmounts($data);  
		$results = $this->model_report_product->getTotalProductAmount($data);
		$this->data['products'] = array();

		foreach ($results as $result) {
			$this->data['products'][] = array(
				'model'		=> $result['model'],
				'size'		=> $result['size'],
				'color'		=> $result['color'],
				'amount'	=> $result['amount']	
			);
		}


		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['products'] , 'stock_report' , 'report_5_stock_report');
		}
 		
		$this->data['heading_title'] = 'สินค้าคงเหลือใน Stock Report'; //$this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_viewed'] = $this->language->get('column_viewed');
		$this->data['column_percent'] = $this->language->get('column_percent');
		
		$this->data['button_reset'] = $this->language->get('button_reset');

		$url = '';			
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
			
		$this->data['reset'] = $this->url->link('report/product_viewed', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
						
		$pagination = new Pagination();
		$pagination->total = $product_viewed_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('report/product_viewed', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
				 
		$this->template = 'report/product_viewed.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	public function reset() {
		$this->language->load('report/product_viewed');
		
		$this->load->model('report/product');
		
		$this->model_report_product->reset();
		
		$this->session->data['success'] = $this->language->get('text_success');
		
		$this->redirect($this->url->link('report/product_viewed', 'token=' . $this->session->data['token'], 'SSL'));
	}




	public function moreproducthiding() {   
		$this->document->setTitle('More Product Hidding Report');
		$page = (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		$url = '';
 		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'More Product Hidding Report',  
			'href'      => $this->url->link('report/product_viewed', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);		
		$this->load->model('report/product');
		$results = $this->model_report_product->getMoreProductHidding();
		$this->data['products'] = array();
		foreach ($results as $result) {
			$this->data['products'][] = array(
				'model'		=> $result['model'],
				'amount'	=> $result['quantity'],
				'status'	=> $result['status'],		
			);
		}
		$this->data['reset'] = $this->url->link('report/product_viewed/moreproducthiding', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['products'] , 'product_hidding_report' , 'report_19_product_hidding');
		}

		$this->data['heading_title'] = 'สินค้าที่ถูกซ่อนและยังคงเหลือมากกว่า 0 Report'; 
		$this->template = 'report/product_hidding.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());

	}

	public function suppliercomingsoon() {   
		$this->document->setTitle('Coming Soon Product from Supplier Report');
		$page = (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		$url = '';
 		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Coming Soon Product from Supplier Report',  
			'href'      => $this->url->link('report/product_viewed/suppliercomingsoon', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);		
		$this->load->model('report/product');
		$results = $this->model_report_product->getSupplierComingSoon();
		$this->data['products'] = array();
		foreach ($results as $result) {
			$this->data['products'][] = array(
				'name'		=> $result['name'],
				'model'		=> $result['model'],
				'total'		=> $result['total']

			);
		}
		$this->data['reset'] = $this->url->link('report/product_viewed/suppliercomingsoon', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['products'] , 'supplier_coming_report' , 'report_21_supplier_coming_soon');
		}

		$this->data['heading_title'] = 'ชื่อ supplier จำนวนแบบ จำนวนรวมทั้งหมด เฉพาะใน comming soon Report'; 
		$this->template = 'report/supplier_comingsoon.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());

	}


	public function specialcost() {   
		$this->document->setTitle('สินค้าที่มีต้นทุนพิเศษ Report');
		$page = (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		$url = '';
 		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'สินค้าที่มีต้นทุนพิเศษ Report',  
			'href'      => $this->url->link('report/product_viewed/specialcost', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);		
		$this->load->model('report/product');
		$this->data['cost'] = $this->model_report_product->getSpecialcost();
		$dps = $this->model_report_product->getSpecialCostInfo();
		$dp  = $dps[0];
		$this->data['product'] = $dps[1];
		$this->data['dates'] = $dp['update_date'];
		$this->data['total'] = $dp['item'];



		$this->data['reset'] = $this->url->link('report/product_viewed/specialcost', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportSpecialCost(array('cost'=>$this->data['cost'] ,'update_date'=> $dp['update_date'] ,'product'=>$dps[1] ,'total'=> $dp['item']) , 'product_special_cost_report' , 'report_25_product_special_cost');
		}

		$this->data['heading_title'] = 'สินค้าที่มีต้นทุนพิเศษ Report'; 
		$this->template = 'report/product_specialcost.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());

	}


}
?>