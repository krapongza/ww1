<?php
class ControllerReportCustomerOrder extends Controller {
	public function index() {     
		$this->language->load('report/customer_order');

		$this->document->setTitle($this->language->get('heading_title'));
		
		if (isset($this->request->get['filter_date_start'])) {
			$filter_date_start = $this->request->get['filter_date_start'];
		} else {
			$filter_date_start = '';
		}

		if (isset($this->request->get['filter_date_end'])) {
			$filter_date_end = $this->request->get['filter_date_end'];
		} else {
			$filter_date_end = '';
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = 0;
		}	
				
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}
		
		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
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
			'href'      => $this->url->link('report/customer_order', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);		
		
		$this->load->model('report/customer');
		
		$this->data['customers'] = array();
		
		$data = array(
			'filter_date_start'	     => $filter_date_start, 
			'filter_date_end'	     => $filter_date_end, 
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);
				
		$customer_total = $this->model_report_customer->getTotalOrders($data); 
		
		$results = $this->model_report_customer->getOrders($data);
		
		foreach ($results as $result) {
			$action = array();
		
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/customer/update', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['customer_id'] . $url, 'SSL')
			);
						
			$this->data['customers'][] = array(
				'customer'       => $result['customer'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'orders'         => $result['orders'],
				'products'       => $result['products'],
				'total'          => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'action'         => $action
			);
		}
		 
 		$this->data['heading_title'] = $this->language->get('heading_title');
		 
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_all_status'] = $this->language->get('text_all_status');
		
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_customer_group'] = $this->language->get('column_customer_group');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_orders'] = $this->language->get('column_orders');
		$this->data['column_products'] = $this->language->get('column_products');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['button_filter'] = $this->language->get('button_filter');
		
		$this->data['token'] = $this->session->data['token'];
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
			
		$url = '';
						
		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}
		
		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
				
		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('report/customer_order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;		
		$this->data['filter_order_status_id'] = $filter_order_status_id;
				 
		$this->template = 'report/customer_order.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}



	public function topbestbuy() {  
		$this->language->load('report/sale_order');
		$this->document->setTitle('Top 100 Best Buyer Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Top 100 Best Buyer Report',
			'href'      => $this->url->link('report/customer_order/topbestbuy', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "เช็คลูกค้าที่ซื้อเยอะ Report";
		$this->load->model('report/sale');

		$results		= $this->model_report_sale->topHighVolumnBuyer();
		$pointTable		= $this->model_report_sale->getPointTable();
		foreach ($results as $result) {
			$this->data['orders'][] = array(

				'user'		=> $result['email'],
				'orders'	=> $result['orders'],
				'cancel'	=> $result['cancel'] ,
				'level'		=> $this->model_report_sale->countPointLevel( $result['point'] , $pointTable ),
				'point'		=> $result['point']
			);
		}
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportTopBestBuy($this->data['orders'] , 'topbestbuy' , 'report_10_best_customer');
		}

		$this->data['reset'] = $this->url->link('report/customer_order/topbestbuy', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		$this->template = 'report/sale_topbestbuy.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	public function topworstbuy() {  
		$this->language->load('report/sale_order');
		$this->document->setTitle('Top 100 Worst Buyer Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Top 100 Worst Buyer Report',
			'href'      => $this->url->link('report/customer_order/topbuy', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "เช็คลูกค้าที่ยกเลิกเยอะ Report";
		$this->load->model('report/sale');

		$results		= $this->model_report_sale->topLowVolumnBuyer();
		$pointTable		= $this->model_report_sale->getPointTable();
		foreach ($results as $result) {
			$this->data['orders'][] = array(

				'user'		=> $result['email'],
				'orders'	=> $result['orders'],
				'cancel'	=> $result['cancel'] ,
				'level'		=> $this->model_report_sale->countPointLevel( $result['point'] , $pointTable ),
				'point'		=> $result['point'],
				'status'	=> ($result['status']) ? 'User' : 'Banned'
			);
		}
		$this->data['reset'] = $this->url->link('report/customer_order/topworstbuy', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['orders'] , 'worst_customer_report' , 'report_11_worst_customer');
		}
		$this->template = 'report/sale_topworstbuy.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	public function registerdaily() {  
		$this->document->setTitle('New Customer Daily Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'New Customer Daily Report',
			'href'      => $this->url->link('report/customer_order/topbuy', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "สมาชิกใหม่รายวัน Report";
		$this->load->model('report/sale');

		$this->data['months']	= $this->model_report_sale->newCustomer();
		$this->data['reset'] = $this->url->link('report/customer_order/registerdaily', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['months'] , 'customer_register_daily_report' , 'report_16_new_daily_customer');
		}

		$this->template			= 'report/customer_newdaily.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	public function registermonthly() {  
		$this->document->setTitle('New Customer Monthly Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'New Customer Monthly Report',
			'href'      => $this->url->link('report/customer_order/registermonthly', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "สมาชิกใหม่รายเดือน Report";
		$this->load->model('report/sale');

		$this->data['months']	= $this->model_report_sale->newCustomerMonthly();
		$this->data['reset'] = $this->url->link('report/customer_order/registermonthly', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['months'] , 'customer_register_monthly_report' , 'report_18_new_monthly_customer');
		}


		$this->template			= 'report/customer_newmonthly.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	public function customerinfo() {  
		$keyword = (isset($this->request->get['keyword'])) ? $this->request->get['keyword'] : '';
		$this->language->load('report/sale_order');
		$this->document->setTitle('Customer Infomation Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Customer Infomation Report',
			'href'      => $this->url->link('report/customer_order/customerinfo', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "ค้นหาข้อมูลลูกค้า Report";
		$this->data['token']	= $this->session->data['token'];
		$this->data['keyword']	= $keyword;
		$this->load->model('report/sale');

		if($keyword){
			$results		= $this->model_report_sale->getCustomerInfo($keyword);
			foreach ($results as $result) {
				$this->data['cusomers'][] = array(
					'user'		=> $result['email'],
					'name'		=> $result['names'],
					'address'	=> $result['address'] ,
					'city'		=> $result['city'],
					'email'		=> $result['email']
				);
			}
		}else{
				$this->data['cusomers'][] = array('user'		=> '','name'		=> '','address'	=> '' ,'city'	=> '','email'		=> '');
		}
		$this->data['reset'] = $this->url->link('report/customer_order/customerinfo', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if((isset($_GET['report']))&&($_GET['report'] == 'excel')){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['cusomers'] , 'customer_info_report' , 'report_13_customer');
		}

		$this->template = 'report/customer_info.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	public function allProduct() {  
		$this->document->setTitle('All Product Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'All Product Report',
			'href'      => $this->url->link('report/customer_order/allProduct', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "มูลค่าของสินค้าทั้งหมด Report";
		$this->load->model('report/sale');
		$this->data['reset'] = $this->url->link('report/customer_order/allProduct', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');

		$p	= $this->model_report_sale->allProduct();
		$this->data['entry'] = $p['entry'];
		$this->data['total'] = $p['total'];
		$this->data['price'] = $this->currency->format($p['price'], $this->config->get('config_currency'));
		if(isset($_GET['export'])){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($p , 'all_product_report' , 'report_14_total_product_summary');
		}

		$this->template		 = 'report/product_all.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	public function gettopwishlist() {  
		$this->document->setTitle('Wishlist Top 100 Report');
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Wishlist Top 100 Report',
			'href'      => $this->url->link('report/customer_order/getTopWishList', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title'] = "Wishlist Top 100 Report";
		$this->load->model('report/customer');

		$results	= $this->model_report_customer->getTopWishList();
		foreach ($results as $result) {
			$this->data['products'][] = array(
				'product_id'=> $result['product_id'],
				'model'		=> $result['model'],
				'wishlist'	=> $result['wishlist'] ,
				'image'		=> $result['image']
			);
		}

		$this->template		 = 'report/product_topwishlist.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}


}
?>