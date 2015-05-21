<?php
class ControllerSalePacking extends Controller {
	private $error = array();

  	public function index() {
		$this->language->load('sale/order');
		$this->document->setTitle("Packing");
		$this->load->model('sale/packing');
    	$this->getList();
  	}
	
    
  	protected function getList() {
		$url = '';
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => "Packing",
			'href'      => $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['orders']		= array();
		$this->data['token']		=  $this->session->data['token'];
		$this->data['ajax_get']		= $this->url->link('sale/packing/ajax_get', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['ajax_save']	= $this->url->link('sale/packing/ajax_save', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['packing_print']= $this->url->link('sale/packing/packing_print', 'token=' . $this->session->data['token'], 'SSL');

		//$this->model_sale_packing->testupdate();

		$this->data['token'] = $this->session->data['token'];

		$this->template = 'sale/packing_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());
  	}

	function ajax_get(){
		$json = array();

		if (   isset($this->request->get['order_id'])  ) {
			$this->load->model('sale/packing');

			$order_id = (isset($this->request->get['order_id'])) ? $this->request->get['order_id'] : '';
			$results = $this->model_sale_packing->getOrder($order_id);

			$order = array();
			$address = array();
			foreach ($results as $result) {
				$order = array('shipping_type' => $result['shipping_method']);
				$address = array(
					'name'		=> $result['send_to'],
					'address'	=> $result['address'],
					'post_code' => $result['payment_postcode']
				);
				$result['order_id'];
			}

			$json = array(
				'order'			=> $order,
				'address'       => $address,
				'error'			=> '0',
				'msg'			=> ''
			);	
		}
		$this->response->setOutput(json_encode($json));
	}

	function ajax_save(){
		$json		= array();
		$type		= (isset($this->request->post['type'])) ? $this->request->post['type'] : ''; 
		$order_id	= (isset($this->request->post['order_id'])) ? $this->request->post['order_id'] : '';  
		$track_no	= (isset($this->request->post['track'])) ? $this->request->post['track'] : '';   
		$weight		= (isset($this->request->post['weight'])) ? $this->request->post['weight'] : '';  
		$weight_bath = (isset($this->request->post['weight_bath'])) ? $this->request->post['weight_bath'] : ''; 
		if ($weight > 1980 and $type == 'registered') $type='P';
		//[Order ประเภท P คือ Order แบบลงทะเบียนที่น้ำหนักเกิน 1980g]

		$this->load->model('sale/packing');
		$num = $this->model_sale_packing->getPackingList( date('Ymd') , $type );
		$num++;

		$name	= $this->model_sale_packing->getName($order_id);
		$q		= $this->model_sale_packing->addPacking($order_id , date('Ymd') , $type , $num , $weight , $weight_bath , $name , '' , $track_no );

		if ($q) {
			$r = $this->model_sale_packing->updateOrderPacking($order_id , $weight , $weight_bath , $track_no , date('Y-m-d H:i:s')  );
			if ($r){
				$json = array('error' => '0', 'msg' => '' ,'data'=>$num,'type'=>$type);
			}else{
				$json = array('error' => '3', 'msg' => 'error insert order');	
			}
		}else{
			$json = array('error' => '2', 'msg' => 'error insert packing list');	
		}	

		$this->response->setOutput(json_encode($json));
	}
 
 	function ajax_delete(){
		$json		= array();
		$id			= (isset($this->request->post['id'])) ? $this->request->post['id'] : ''; 
		if ($id == '') {
			$json = array('error' => '5', 'msg' => 'error id');
		}else{
			$this->load->model('sale/packing');
			$res = $this->model_sale_packing->deletePacking( $id );

			$json = ($res) ? array('error' => '0', 'msg' => 'success') : array('error' => '2', 'msg' => 'delete error');
		}
		$this->response->setOutput(json_encode($json));
	}

	function packing_print(){

		$type		= (isset($this->request->get['type'])) ? $this->request->get['type'] : ''; 
		$ymd		= (isset($this->request->get['ymd'])) ? $this->request->get['ymd'] : '';  
		$viewtype	= (isset($this->request->get['viewtype'])) ? $this->request->get['viewtype'] : '';   
		$start		= (isset($this->request->get['start'])) ? $this->request->get['start'] : '';  
		$end		= (isset($this->request->get['end'])) ? $this->request->get['end'] : ''; 

		$this->data['packing_print']= $this->url->link('sale/packing/packing_print', 'type='.$type.'&ymd='.$ymd.'&viewtype='.$viewtype.'&token=' . $this->session->data['token'], 'SSL');
		$this->data['packing_print_e']= $this->url->link('sale/packing/packing_print', 'type='.$type.'&ymd='.$ymd.'&viewtype=edit&token=' . $this->session->data['token'], 'SSL');
		$this->data['ajax_delete']		= $this->url->link('sale/packing/ajax_delete', 'token=' . $this->session->data['token'], 'SSL');
		if ($ymd == ''){
			$ymd = date('Ymd');
		}else{
			$ymd = (substr($ymd,0,4)-543).substr($ymd,4,2).substr($ymd,6,2);
		}
		$date = substr($ymd,6,2).'/'.substr($ymd,4,2).'/'.substr($ymd,0,4);

		$this->load->model('sale/packing');
		$array_p = $this->model_sale_packing->packing_print( $type , $ymd , $viewtype , $start , $end );
		
		$this->data['lists']	= $array_p[0];
		$this->data['summary']	= $array_p[1];
		$this->data['viewtype']	= $viewtype;
		$this->data['ymd']		= $ymd;
		$this->data['date']		= $date;
		$this->data['type']		= $type;



		$this->data['token'] = $this->session->data['token'];

		$this->template = 'sale/packing_print.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->response->setOutput($this->render());


	}





}
?>
