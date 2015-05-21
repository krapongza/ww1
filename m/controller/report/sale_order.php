<?php
class ControllerReportSaleOrder extends Controller { 
	public function index() {  
		$this->language->load('report/sale_order');
		$this->document->setTitle($this->language->get('heading_title'));

		$filter_date_start	= (isset($this->request->get['filter_date_start'])) ? $this->request->get['filter_date_start'] : date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
		$filter_date_end	= (isset($this->request->get['filter_date_end'])) ? $this->request->get['filter_date_end'] : date('Y-m-d');
		$filter_group		= (isset($this->request->get['filter_group'])) ? $this->request->get['filter_group'] : 'week';
		$filter_order_status_id = (isset($this->request->get['filter_order_status_id'])) ? $this->request->get['filter_order_status_id'] : 0;
		$page				= (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
		
		$url = '';
		if (isset($this->request->get['filter_date_start']))	$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		if (isset($this->request->get['filter_date_end']))		$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		if (isset($this->request->get['filter_group']))			$url .= '&filter_group=' . $this->request->get['filter_group'];
		if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];						
		if (isset($this->request->get['page']))					$url .= '&page=' . $this->request->get['page'];
		

   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/sale_order', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->load->model('report/sale');
		$this->data['orders'] = array();
		$data = array(
			'filter_date_start'	     => $filter_date_start, 
			'filter_date_end'	     => $filter_date_end, 
			'filter_group'           => $filter_group,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);
		
		$tmp_t=0;$tmp_p=0;$tmp_e=0;$tmp_r=0;$tmp_s=0;$tmp_pp=0;
		$order_total	= $this->model_report_sale->getTotalOrders($data);
		$results		= $this->model_report_sale->getNewOrders($data);
		foreach ($results as $result) {
			$total = ( ($result['total'] - $result['problem'] - $result['shipping']) > 0 ) ? ($result['total'] - $result['problem'] - $result['shipping']) : 0;
			$this->data['orders'][] = array(
				'date_start' => date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end'   => date($this->language->get('date_format_short'), strtotime($result['date_end'])),
				'total'      => $this->currency->format($total, $this->config->get('config_currency')),
				'problem'	 => $this->currency->format($result['problem'], $this->config->get('config_currency')),
				'shipping'	 => $this->currency->format(($result['ems']+$result['register']), $this->config->get('config_currency')),
				'ems'		 => $this->currency->format($result['ems'], $this->config->get('config_currency')),
				'register'	 => $this->currency->format($result['register'], $this->config->get('config_currency')),
				'products'   => $result['products']
				//'orders'     => $result['orders']
				//'tax'        => $this->currency->format($result['tax'], $this->config->get('config_currency')),
				
			);
				$tmp_t = $tmp_t + $total ;
				$tmp_p = $tmp_p + $result['problem'] ;
				$tmp_e = $tmp_e + $result['ems'] ;
				$tmp_r = $tmp_r + $result['register'] ;
				$tmp_s = $tmp_s + $result['ems'] + $result['register'] ;
				$tmp_pp = $tmp_pp + $result['products'];
		}
		$this->data['sum'] = array(
			'sum_total'		=> $this->currency->format($tmp_t, $this->config->get('config_currency')),
			'sum_problem'	=> $this->currency->format($tmp_p, $this->config->get('config_currency')),
			'sum_shipping'	=> $this->currency->format($tmp_s, $this->config->get('config_currency')),
			'sum_ems'		=> $this->currency->format($tmp_e, $this->config->get('config_currency')),
			'sum_register'	=> $this->currency->format($tmp_r, $this->config->get('config_currency')),
			'sum_product'	=> $tmp_pp
		);
		if((isset($_GET['filter_report']))&&($_GET['filter_report'] == 'excel')){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStockSum($this->data['orders'] , 'sale_report' , 'report_1_daily_sale',$this->data['sum']);
		}


		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_all_status'] = $this->language->get('text_all_status');
		$this->data['column_date_start'] = $this->language->get('column_date_start');
		$this->data['column_date_end'] = $this->language->get('column_date_end');
    	$this->data['column_orders'] = $this->language->get('column_orders');
		$this->data['column_products'] = $this->language->get('column_products');
		$this->data['column_tax'] = $this->language->get('column_tax');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['entry_group'] = $this->language->get('entry_group');	
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['button_filter'] = $this->language->get('button_filter');
		$this->data['token'] = $this->session->data['token'];
		
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['groups'] = array();
		$this->data['groups'][] = array(
			'text'  => $this->language->get('text_year'),
			'value' => 'year',
		);
		$this->data['groups'][] = array(
			'text'  => $this->language->get('text_month'),
			'value' => 'month',
		);
		$this->data['groups'][] = array(
			'text'  => $this->language->get('text_week'),
			'value' => 'week',
		);
		$this->data['groups'][] = array(
			'text'  => $this->language->get('text_day'),
			'value' => 'day',
		);

		$url = '';			
		if (isset($this->request->get['filter_date_start']))	$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		if (isset($this->request->get['filter_date_end']))		$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		if (isset($this->request->get['filter_group']))			$url .= '&filter_group=' . $this->request->get['filter_group'];
		if (isset($this->request->get['filter_order_status_id'])) $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		
		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('report/sale_order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();		
		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;		
		$this->data['filter_group'] = $filter_group;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
				 
		$this->template = 'report/sale_order.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}


	public function whobuy() {  
		$this->language->load('report/sale_order');
		$this->document->setTitle($this->language->get('Who Buy Report'));

		$html		= (isset($this->request->get['html'])) ? $this->request->get['html'] : 0;
		$product_id = (isset($this->request->get['product_id'])) ? $this->request->get['product_id'] : 0;
		$url = '';
   		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => "Who buy This Product Report ",
			'href'      => $this->url->link('report/sale_order/whobuy', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['heading_title']	= "Who buy This Product Report ";
		$this->data['token']			= $this->session->data['token'];
		$this->load->model('report/sale');
		$this->data['orders'] = array();
		$resultstmp		= $this->model_report_sale->getWhoBuyThisProduct($product_id);
		$results		= $resultstmp[0];
		foreach ($results as $result) {
			$this->data['orders'][] = array(
				'date_added'	=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'order_id'		=> $result['order_id'],
				'size'			=> $result['size'],
				'color'			=> $result['color'],
				'amount'		=> $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'user'			=> $result['user'],
				'status'		=> $result['status']
			);
		}
		$this->data['num_rows'] =  $resultstmp[1];
		$this->data['reset'] = $this->url->link('report/sale_order/whobuy', 'export=y&token=' . $this->session->data['token'] . $url, 'SSL');
		if((isset($_GET['html']))&&($_GET['html'] == 'excel')){
			$this->load->model('common/excel');
			$this->model_common_excel->exportStock($this->data['orders'] , 'who_buy_report' , 'report_8_who_buy_product_report');
		}


		$this->template = 'report/sale_whobuy.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());




	}








}
?>