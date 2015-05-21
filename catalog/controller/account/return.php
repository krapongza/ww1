<?php 
class ControllerAccountReturn extends Controller { 
	private $error = array();
	
	public function index() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
 		$this->load->model('account/address');
		if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('account/bank')); 
		}
    	$this->language->load('account/return');
    	$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');		
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		$url = '';
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}	
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_return_id'] = $this->language->get('text_return_id');
		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_continue'] = $this->language->get('button_continue');
	
		$this->load->model('account/order');
		$this->load->model('account/return');
		$this->load->model('tool/image'); 

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$this->data['returns'] = array();
		$return_total = $this->model_account_return->getTotalReturnsIndex();
		//$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);
		//(`status` = 'done' or `status` = 'received')  6=done  7=received
		$results = $this->model_account_return->getReturnsNewIndex(($page - 1) * 10, 10);
		
		$oid = "";
		$omoney = 0;
		$odate = "";
		foreach ($results as $result) {
			$img	= $this->model_account_order->getImagefromColorOption($result['product_id'] ,$result['value']);		
			$image	= (strlen($img) > 1) ? $this->model_tool_image->resize($img, 100, 150) : $this->model_tool_image->resize($result['image'], 100, 150);
			$oid	= $result['order_id'];
			$omoney = $omoney + $result['total'];
			$odate	= date($this->language->get('date_format_short'), strtotime($result['order_date']));

			$this->data['returns'][] = array(
				//'return_id'  => $result['return_id'],
				'order_id'		=> $result['order_id'],
				'order_total'   => $this->currency->format($result['order_total']),
				'product_id'    => $result['product_id'],
				'quantity'		=> $result['quantity'],
				'price'			=>  $this->currency->format($result['price']),
				'total'			=> $this->currency->format($result['total']),
				'images'		=>	$image, //$this->model_tool_image->resize($result['image'], 100, 100),
				'order_date'	=> date($this->language->get('date_format_short'), strtotime($result['order_date'])),
				'href'			=> $this->url->link('account/return/info', 'return_id=' . $result['order_id'] . $url, 'SSL')
			);
		}

		$this->data['oid']		= $oid;
		$this->data['omoney']	= $omoney;
		$this->data['odate']	= $odate;

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_catalog_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/history', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];



		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/return_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/return_list.tpl';
		} else {
			$this->template = 'default/template/account/return_list.tpl';
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
	

	public function tracking() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

    	$this->language->load('account/return');

    	$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
						
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		$url = '';
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}	
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_return_id'] = $this->language->get('text_return_id');
		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_continue'] = $this->language->get('button_continue');
		
		$this->data['returns'] = array();
		$this->load->model('account/return');

		$page = (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;

		//Order 3,5,8 <=10days
		$return_total = $this->model_account_return->getTotalRollbackOrders();
		$results = $this->model_account_return->getRollbackOrders(($page - 1) * 10, 10);

		//$return_total = $this->model_account_return->getTotalReturns();
		//$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);
		
		foreach ($results as $result) {
			//print_r($result);
			$this->data['returns'][] = array(
				'return_id'  => $result['return_id'],
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['return_status_id'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'href'       => $this->url->link('account/return/trackinginfo', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
		}

 

		//txn timeout
		//$return_total = $this->model_account_return->getTotalRollbackByAdmin();
		$results = $this->model_account_return->getRollbackCancelList(($page - 1) * 10, 10);
		foreach ($results as $result) {
			$this->data['refundscancel'][] = array(
				'return_id'  => $result['id'],
				'user_id'  => $result['user_id'],
				'username'  => $result['username'],
				'bank_name'   => $result['bank_name'],
				'bank_account'       => $result['bank_account'],
				'bank'       => $result['bank'],
				'date_transfer'       => date($this->language->get('date_format_short'), strtotime($result['date_transfer'])),
				'amount'       => $this->currency->format($result['amount']),
				'message'       => $result['message'],
				'status'       => $result['status'],
				'modify'       => date($this->language->get('date_format_short'), strtotime($result['modify'])),
				'href'       => $this->url->link('account/return/refundinfo', 'order_id=' . $result['order_id'] . $url, 'SSL')
			);
		}





		//wrong_txn
		$return_total = $this->model_account_return->getTotalRollbackByAdmin();
		$results = $this->model_account_return->getRollbackByAdmin(($page - 1) * 10, 10);
		foreach ($results as $result) {
			$this->data['refunds'][] = array(
				'return_id'  => $result['id'],
				'user_id'  => $result['user_id'],
				'username'  => $result['username'],
				'bank_name'   => $result['bank_name'],
				'bank_account'       => $result['bank_account'],
				'bank'       => $result['bank'],
				'date_transfer'       => date($this->language->get('date_format_short'), strtotime($result['date_transfer'])),
				'amount'       => $this->currency->format($result['amount']),
				'message'       => $result['message'],
				'status'       => $result['status'],
				'modify'       => date($this->language->get('date_format_short'), strtotime($result['modify'])),
				'href'       => $this->url->link('account/return/refunddetail', 'id=' . $result['id'] . $url, 'SSL')
			);
		}




 
		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_catalog_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/history', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
	 


		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];




		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/tracking_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/tracking_list.tpl';
		} else {
			$this->template = 'default/template/account/return_list.tpl';
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

	public function upload_image() {

		if (isset($this->request->get['p'])) {
			$product_id = $this->request->get['p'];
		} else {
			$product_id = 0;
		}
		if (isset($this->request->get['o'])) {
			$order_id = $this->request->get['o'];
		} else {
			$order_id = 0;
		}

		$product_option = (isset($this->request->get['op'])) ? $this->request->get['op'] : '' ;

		$file = $_FILES['upload'];
		if (!$file['tmp_name']) return false;
//print_r($file);
		$ext = explode('.',$file['name']);
		$ext = $ext[count($ext)-1];
		$name = md5($file['name']).'_'.date('ymdHis').'.'.$ext;

		//create dir
		$first = substr(md5($file['name']),0,1);
		$path = DIR_IMAGE."rollback_img/".$first;
		if (!file_exists($path)) mkdir($path);

		$p_id = $this->request->get['p'];

		$path .= "/".$name;
		$url = '/rollback_img/'.$first.'/'.$name;
		if (@move_uploaded_file($file['tmp_name'],$path)){
			//return $url;
			$res = HTTP_SERVER."image".$url;
		}else{
			//return false;
			$res = "";
		}

		$this->load->model('account/return');
		$id = $this->model_account_return->saveImageDB($order_id , $product_id , $product_option , $res);

		$json['src'] = $res;
		$json['product_id'] = $p_id;
		$json['id'] = $id;

		//$this->response->setOutput( json_encode(array('error'=>'0','src'=>$res,'id'=>$id,'product_id'=>$product_id)) );
		$this->response->setOutput(json_encode($json));	
	}

	public function delete_image() {

		$this->response->setOutput(json_encode(array('error'=>'1')));

		if (isset($this->request->get['id'])) {
			$id = $this->request->get['id'];
		} else {
			$id = 0;
		}

		$this->load->model('account/return');
		$id = $this->model_account_return->deleteImg($order_id , $product_id , $res);

		if($id) 
			$json['error'] = 0;
		else 
			$json['error'] = 1;
		

		$this->response->setOutput(json_encode($json));	
	}

	public function trackinginfo() {
		$this->language->load('account/return');
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

		$this->load->model('account/return');	
		$id = $return_id= (isset($this->request->get['return_id'])) ? $this->request->get['return_id'] : 0;
		$return_info	= $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));
			$url = '';
			if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];
			$this->data['heading_title'] = $this->language->get('text_return');
			$this->data['text_return_detail'] = $this->language->get('text_return_detail');
			$this->data['text_return_id'] = $this->language->get('text_return_id');
			$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_ordered'] = $this->language->get('text_date_ordered');
			$this->data['text_customer'] = $this->language->get('text_customer');
			$this->data['text_email'] = $this->language->get('text_email');
			$this->data['text_telephone'] = $this->language->get('text_telephone');			
			$this->data['text_status'] = $this->language->get('text_status');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
			$this->data['text_product'] = $this->language->get('text_product');
			$this->data['text_comment'] = $this->language->get('text_comment');
      		$this->data['text_history'] = $this->language->get('text_history');
      		$this->data['column_product'] = $this->language->get('column_product');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_opened'] = $this->language->get('column_opened');
			$this->data['column_reason'] = $this->language->get('column_reason');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');
      		$this->data['column_comment'] = $this->language->get('column_comment');			
			$this->data['button_continue'] = $this->language->get('button_continue');
			
			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
			$this->data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];						
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];
			$this->data['reason'] = $return_info['reason'];
			$this->data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$this->data['comment'] = nl2br($return_info['comment']);
			$this->data['action'] = $return_info['action'];
						
			$this->data['histories'] = array();
			$this->data['reason'] = $return_info['status'];
			//print_r($return_info);

			/*
			* trackinginfo
			* 1. check Admin rollback_status='approved'  and check after 3 days? => update status to sending_deadline...
			* 2. submit send track_ems
			*    update track_ems and status = 'sending'
			*    2.1 insert to my_return 
			*    2.2 insert to my_rollback_item
			* 3. submit reveiced money
			*    3.1 update status to done
			* 4. if transfer success from admin 
			*    4.1 show data from my_rollback_transfer_success
			*/
			//if ($return_info['customer_id'] != $this->customer->getId()) exit;
			$order_approve_deadline = 259200; 
			//Status 4=approved  and after 3 days?
/*
//echo date('M d, Y', time()-$order_approve_deadline);
$diff = abs( strtotime($return_info['date_modified'])  - (time()-$order_approve_deadline)   );
$years = floor($diff / (365*60*60*24));
$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
echo $days;

if( strtotime("+3 day", strtotime($return_info['date_modified'])) > time() ){
echo "new";
}else{
echo "old";
}*/

			if ( ($return_info['status'] == 'approved') and ( strtotime( "+3 day", strtotime($return_info['date_modified']) ) > time() )     ) {
				$this->data['delay']	= true;
				$this->model_account_return->setOrderSendingDeadline($id);
			}
			
			$submit_sending	= (isset($this->request->post['submit_sending'])) ? $this->request->post['submit_sending'] : null ;
			$track_ems		= (isset($this->request->post['track_ems'])) ? $this->request->post['track_ems'] : null ;
			$success		= (isset($this->request->post['success'])) ? $this->request->post['success'] : null ;
			$order_id		= (isset($this->request->post['order_id'])) ? $this->request->post['order_id'] : null ;
			
			//Status 1= waiting , 4=approved  , 7=transfer_success ,  8=done  ,  10=sending_deadline
			if ($this->request->post && isset($submit_sending)){
				if ($track_ems != '') {
					if (strlen(trim($track_ems)) != 13) $this->data['error_warning'] = "กรุณากรอกเลขพัสดุ 13 ให้ถูกต้อง";
					$this->model_account_return->setCancelSending($id,$this->request->post);
				}
			}else if ($this->request->post && isset($success)){
				$this->model_account_return->setDoneOrder($id);
			}

			$this->data['cal_total']			= ($return_info['cal_total'] > 0) ? $return_info['cal_total'] : 0;
			$this->data['cal_discount_user']	= ($return_info['cal_discount_user']) ? $return_info['cal_discount_user'] : 0;
			$this->data['cal_discount_coupon']	= ($return_info['cal_discount_coupon']) ? $return_info['cal_discount_coupon'] : 0;
			$this->data['cal_fee']				= ($return_info['cal_fee']) ? $return_info['cal_fee'] : 0;
			$this->data['cal_total_return']		= ($return_info['cal_total_return']) ? $return_info['cal_total_return'] : 0;

			$this->load->model('account/order');
			$this->data['products'] = array();
			$res		= $this->model_account_return->getRollbackOrdersDetail($return_id);

      		foreach ($res as $product) {
				$this->data['note'] = $product['note'];
				$ppm	= trim($product['option']);
				$ppm	= explode(' ' , $ppm);
				$img	= $this->model_account_order->getProductImg($product['pid'] , $ppm[1]);
				$model	= $this->model_account_order->getProductModel($product['pid']);

        		$this->data['products'][] = array(
					'product_id'	=> $product['pid'],
					'order_id'		=> $product['order_id'],
					'img'			=> "<img src='image/".$img."' style='width:100px;padding: 5px;'>",
          			'model'			=> $model,
					'option'		=> $product['option'],
					'quantity'		=> $product['quantity'],
					'unit_price'	=> $product['unit_price'],
					'return_qty'	=> $product['return_qty'],
					'pass_qty'		=> $product['pass_qty']
        		);
      		}

			$this->data['totals'] = $this->model_account_order->getOrderTotals($return_info['order_id']);

			//Get success
			$results = $this->model_account_return->getRefundDetailSuccess($id);
      		foreach ($results as $result) {
        		$this->data['transfer_detail'] = array(
					'bank_name'		=> $result['bank_name'],
					'bank'			=> $result['bank'],
					'amount'		=> $result['amount'],
					'cashback_type' => $result['cashback_type'],
          			'date_added'	=> date($this->language->get('date_format_short'), strtotime($result['craeted'])),
          			'note'			=> nl2br($result['note'])
        		);
      		}

			$this->data['track_ems']	= $return_info['track_ems'];
			$this->data['status']		= $return_info['status'];
			$this->data['comment']		= $return_info['comment'];
			$this->data['continue']		= $this->url->link('account/return', $url, 'SSL');


			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/tracking_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/tracking_info.tpl';
			} else {
				$this->template = 'default/template/account/tracking_info.tpl';
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
		} else {
			$this->document->setTitle($this->language->get('text_return'));		
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}					
			$this->data['heading_title'] = $this->language->get('text_return');
			$this->data['text_error'] = $this->language->get('text_error');
			$this->data['button_continue'] = $this->language->get('button_continue');
			$this->data['continue'] = $this->url->link('account/return', '', 'SSL');
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
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










	public function refundlist() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
  		$this->load->model('account/address');
		if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('account/bank')); 
		}
    	$this->language->load('account/return');
    	$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');		
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		$url = '';
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}		
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_return_id'] = $this->language->get('text_return_id');
		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->load->model('account/return');
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$this->data['returns'] = array();
		
		//GET Order Timeout
		$return_total = $this->model_account_return->getTotalReturns();
		$results = $this->model_account_return->getRefundsNew(($page - 1) * 10, 20);
		$returnscount = $this->model_account_return->getRefundsCount(); 

//print_r($returnscount);
		foreach ($results as $result) {

			foreach ($returnscount as $tmp) {
				if($tmp['order_id'] == $result['order_id']){
					$count = $tmp['counts'];
					$this->data['returns'][] = array(
						'count'   => $count,
						'order_id'   => $result['order_id'],
						'order_total'       => $this->currency->format($result['order_total']),
						'product_id'     => $result['product_id'],
						'quantity'     => $result['quantity'],
						'price'     =>  $this->currency->format($result['price']),
						'total'     => $this->currency->format($result['total']),
						'images'	=>	$this->model_tool_image->resize($result['image'], 100, 100),
						'order_date' => date($this->language->get('date_format_short'), strtotime($result['order_date'])),
						'href'       => $this->url->link('account/return/info', 'return_id=' . $result['order_id'] . $url, 'SSL')
					);
				}

			}
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_catalog_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/history', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		

		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/refund_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/refund_list.tpl';
		} else {
			$this->template = 'default/template/account/refund_list.tpl';
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
	


	public function refundinfo() {
		$this->language->load('account/return');
		$this->load->model('account/return');


    	if (isset($this->request->post['order_id'])) {
      		$this->data['order_id'] = $this->request->post['order_id']; 	
		} elseif (!empty($order_info)) {
			$this->data['order_id'] = $order_info['order_id'];
		} elseif ( $this->request->get['order_id'] ) {
			$this->data['order_id'] = $this->request->get['order_id'];
		} else {
      		$this->data['order_id'] = ''; 
    	}
		if ($this->model_account_return->thisOrderCanced( $this->data['order_id'] ))
			$this->redirect($this->url->link('account/return/refundview','id='.$this->data['order_id'])); //Redirect to view page

		$this->load->model('account/customer');
		$this->data['bank'] = $this->model_account_return->getBank();

    	//if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			//print_r($this->request->post);
			$bank = $this->model_account_return->getBankAccount($this->request->post['bank_name']);
			$this->model_account_return->addCancelOrder($this->request->post ,   $bank[0]);
	  		
			$this->load->model('checkout/order');
			$this->model_checkout_order->update($this->request->post['order_id'] , '19' , $message='', false);
			$this->redirect($this->url->link('account/return/tracking', '', 'SSL'));
    	} 
							
		$this->document->setTitle($this->language->get('heading_title'));
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return/insert', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
    	$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_order'] = $this->language->get('text_order');
		$this->data['text_product'] = $this->language->get('text_product');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		
		$this->data['entry_order_id'] = $this->language->get('entry_order_id');	
		$this->data['entry_date_ordered'] = $this->language->get('entry_date_ordered');	    	
		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
    	$this->data['entry_lastname'] = $this->language->get('entry_lastname');
    	$this->data['entry_email'] = $this->language->get('entry_email');
    	$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_product'] = $this->language->get('entry_product');	
		$this->data['entry_model'] = $this->language->get('entry_model');			
		$this->data['entry_quantity'] = $this->language->get('entry_quantity');				
		$this->data['entry_reason'] = $this->language->get('entry_reason');	
		$this->data['entry_opened'] = $this->language->get('entry_opened');	
		$this->data['entry_fault_detail'] = $this->language->get('entry_fault_detail');	
		$this->data['entry_captcha'] = $this->language->get('entry_captcha');
				
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');
		    
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['order_id'])) {
			$this->data['error_order_id'] = $this->error['order_id'];
		} else {
			$this->data['error_order_id'] = '';
		}
				
		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}	
		
		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}		
	
		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}
		
		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}
				
		if (isset($this->error['product'])) {
			$this->data['error_product'] = $this->error['product'];
		} else {
			$this->data['error_product'] = '';
		}
		
		if (isset($this->error['model'])) {
			$this->data['error_model'] = $this->error['model'];
		} else {
			$this->data['error_model'] = '';
		}
						
		if (isset($this->error['reason'])) {
			$this->data['error_reason'] = $this->error['reason'];
		} else {
			$this->data['error_reason'] = '';
		}
		
 		if (isset($this->error['captcha'])) {
			$this->data['error_captcha'] = $this->error['captcha'];
		} else {
			$this->data['error_captcha'] = '';
		}	

		$this->data['action'] = $this->url->link('account/return/insert', '', 'SSL');
	
		/*$this->load->model('account/order');
		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);
		}
		
		$this->load->model('catalog/product');
		if (isset($this->request->get['product_id'])) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}*/

    	if (isset($this->request->get['order_id'])) {
      		$this->data['order_id'] = $this->request->get['order_id']; 	
		} elseif (isset($this->request->post['order_id'])) {
			$this->data['order_id'] = $this->request->post['order_id'];
		} else {
      		$this->data['order_id'] = ''; 
    	}
				

		// getOrderDeadline


		$this->load->model('account/return');
		$this->load->model('tool/image'); 

		$this->data['returns'] = array();
		$results = $this->model_account_return->getRefundsNew(0, 10 , $this->data['order_id']);
		$order_date = "";
		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'order_id'   => $result['order_id'],
				'order_total'       => $result['order_total'] ,
				'product_id'     => $result['product_id'],
				'quantity'     => $result['quantity'],
				'price'     => $this->currency->format($result['price']),
				'total'     => $result['total'],
				'images'	=>	$this->model_tool_image->resize($result['image'], 100, 100),
				'order_date' => date($this->language->get('date_format_short'), strtotime($result['order_date']))
			);
			$order_date = $result['order_date'];
		}
		$this->data['order_date'] = date($this->language->get('date_format_short'), strtotime($order_date));

		$total = $this->model_account_return->returnordertotal($this->data['order_id']);
		$this->data['option'] = $this->model_account_return->returnorderOption($this->data['order_id']);
		foreach($total as $key => $val){
			$total[$key]['value'] = $this->currency->format($val['value']);
		}
		$this->data['total'] = $total;




		

    	if (isset($this->request->post['date_ordered'])) {
      		$this->data['date_ordered'] = $this->request->post['date_ordered']; 	
		} elseif (!empty($order_info)) {
			$this->data['date_ordered'] = date('Y-m-d', strtotime($order_info['date_added']));
		} else {
      		$this->data['date_ordered'] = '';
    	}
				
		if (isset($this->request->post['firstname'])) {
    		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($order_info)) {
			$this->data['firstname'] = $order_info['firstname'];	
		} else {
			$this->data['firstname'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['lastname'])) {
    		$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($order_info)) {
			$this->data['lastname'] = $order_info['lastname'];			
		} else {
			$this->data['lastname'] = $this->customer->getLastName();
		}
		
		if (isset($this->request->post['email'])) {
    		$this->data['email'] = $this->request->post['email'];
		} elseif (!empty($order_info)) {
			$this->data['email'] = $order_info['email'];				
		} else {
			$this->data['email'] = $this->customer->getEmail();
		}
		
		if (isset($this->request->post['telephone'])) {
    		$this->data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($order_info)) {
			$this->data['telephone'] = $order_info['telephone'];				
		} else {
			$this->data['telephone'] = $this->customer->getTelephone();
		}
		
		if (isset($this->request->post['product'])) {
    		$this->data['product'] = $this->request->post['product'];
		} elseif (!empty($product_info)) {
			$this->data['product'] = $product_info['name'];				
		} else {
			$this->data['product'] = '';
		}
		
		if (isset($this->request->post['model'])) {
    		$this->data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$this->data['model'] = $product_info['model'];				
		} else {
			$this->data['model'] = '';
		}
			
		if (isset($this->request->post['quantity'])) {
    		$this->data['quantity'] = $this->request->post['quantity'];
		} else {
			$this->data['quantity'] = 1;
		}	
 
 		if (isset($this->request->post['txn'])) {
    		$this->data['txn'] = $this->request->post['txn'];
		} elseif (!empty($product_info)) {
			$this->data['txn'] = $product_info['cashback_type'];				
		} else {
			$this->data['txn'] = '';
		}

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');
				
		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/refund_info.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/refund_info.tpl';
		} else {
			$this->template = 'default/template/account/refund_info.tpl';
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


	public function refundview() {

    	if (isset($this->request->get['id'])) {
      		$this->data['id'] = $this->request->get['id']; 
			$this->data['order_id'] = $this->request->get['id']; 
		} elseif (!empty($order_info)) {
			$this->data['id'] = $order_info['id'];
			$this->data['order_id'] = $this->request->post['id'];
		} else {
      		$this->data['id'] = ''; 
			$this->data['order_id'] = ''; 
    	}
 
  		$this->language->load('account/return');
		$this->load->model('account/return');
		$this->data['bank'] = $this->model_account_return->getBank();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			
			$this->model_account_return->adminSetStatusCancel($this->data['id'] , 'done' );
			//update rollback_cancel to done
			//$this->redirect($this->url->link('account/return/success', '', 'SSL'));
    	} 
							
		$this->document->setTitle($this->language->get('heading_title'));
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return/insert', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
    	$this->data['heading_title'] = $this->language->get('heading_title');
 

		// getOrderDeadline
		$this->load->model('tool/image');
		$this->load->model('account/order');
		$this->data['products'] = array();
		$products = $this->model_account_order->getOrderProducts($this->data['order_id']);
		foreach ($products as $product) {
			$option_data = array();
			//echo $product['order_product_id']."<br><br>";
			$p = $this->model_account_order->getProductDetail($product['product_id']);
			//print_r($p[0]['image']); echo "<br><br>";
			$options = $this->model_account_order->getOrderOptions($this->data['order_id'], $product['order_product_id']);

			foreach($options as $p2){
				if($p2['name'] == 'Color') 
					$img = $this->model_account_order->getImagefromColorOption($product['product_id'] ,$p2['value']);		
			}
			$image = (strlen($img) > 1) ? $this->model_tool_image->resize($img, 100, 150) : $this->model_tool_image->resize($p[0]['image'], 100, 150);

			foreach ($options as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
				}
				
				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);					
			}

			$this->data['products'][] = array(
				'product_id'	=> $product['product_id'],
				'name'			=> $product['name'],
				'model'			=> $product['model'],
				'images'		=> $image, //HTTP_SERVER."image/".$p[0]['image'],
				'link'			=>  $this->url->link('product/product',   '&product_id=' . $product['product_id']  ),
				'option'		=> $option_data,
				'quantity'		=> $product['quantity'],
				'price'			=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), 'USD', '1.00000000'),
				'total'			=> $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), 'USD', '1.00000000')
			);
		}







		$status = $this->model_account_return->getRefundViewByID($this->data['id']);
		$this->data['status'] = $status[0]['status'];
		//$this->data['order_date'] = date($this->language->get('date_format_short'), strtotime($order_date));

		$total = $this->model_account_return->returnordertotal($this->data['order_id']);
		$this->data['option'] = $this->model_account_return->returnorderOption($this->data['order_id']);
		foreach($total as $key => $val){
			$total[$key]['value'] = $this->currency->format($val['value']);
		}
		$this->data['total'] = $total;
		$this->data['back'] = $this->url->link('account/account', '', 'SSL');
				
		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/refund_view.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/refund_view.tpl';
		} else {
			$this->template = 'default/template/account/return_form.tpl';
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




	public function refundform() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
  		$this->load->model('account/address');
		if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('account/bank')); 
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$year = $_POST['mydateYear'] -543;
			$datetime = $year .'-'.$_POST['mydateMonth'].'-'.$_POST['mydateDay'].' '.$_POST['mytimeHour'].':'.$_POST['mytimeMinute'].':'.$_POST['mytimeSecond'];
			$datetime = strtotime($datetime);
			if ($datetime < time()){
				$this->load->model('account/return');
				$bankAccount = $this->model_account_return->getBankAccount($this->request->post['bank_name'] );
				$return_id = $this->model_account_return->addRollbackOrder($this->request->post   ,$bankAccount[0] );
				$this->redirect($this->url->link('account/return/refunddetail',  'id=' . $return_id  , 'SSL'));
			}else{
				$this->data['error_time'] = "กรุณาระบุวันเวลาให้ถูกต้องกับความเป็นจริง";
				//$this->msgAlert(array('notice','','/rollback/wrong_form'));
			}


    	} 

 

    	$this->language->load('account/return');

    	$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
						
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		$url = '';
		if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];	
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_return_id'] = $this->language->get('text_return_id');
		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_continue'] = $this->language->get('button_continue');
		
		$this->load->model('account/return');
		

		$return_bank = $this->model_account_return->getBank();
		$this->data['return_bank'] = $return_bank;
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$this->data['returns'] = array();
		$return_total = $this->model_account_return->getTotalRollbackByAdmin();
		$results = $this->model_account_return->getRollbackByAdmin(($page - 1) * 10, 10);
		
		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'return_id'  => $result['id'],
				'user_id'  => $result['user_id'],
				'username'  => $result['username'],
				'bank_name'   => $result['bank_name'],
				'bank_account'       => $result['bank_account'],
				'bank'       => $result['bank'],
				'date_transfer'       => date($this->language->get('date_format_short'), strtotime($result['date_transfer'])),
				'amount'       => $result['amount'],
				'message'       => $result['message'],
				'status'       => $result['status'],
				'modify'       => date($this->language->get('date_format_short'), strtotime($result['modify'])),
				'href'       => $this->url->link('account/return/refunddetail', 'return_id=' . $result['id'] . $url, 'SSL')
			);
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_catalog_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/history', 'page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');
		
		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];



		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/refund_form.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/refund_form.tpl';
		} else {
			$this->template = 'default/template/account/refund_list.tpl';
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



	public function refunddetail() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
  		$this->load->model('account/address');
		if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('account/bank')); 
		}
 
    	$this->language->load('account/return');

    	$this->document->setTitle($this->language->get('heading_title'));
 	
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->load->model('account/return');
		if (isset($this->request->get['id'])) {
    		$this->data['return_id'] = $this->request->get['id'];
		} else {
			$this->data['return_id'] = '';
		}	
 
		$this->data['returns'] = array();
		$results = $this->model_account_return->getRollbackDetail( $this->data['return_id'] );
		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'bank'			=> $result['bank'],
				'username'		=> $result['username'],
				'bank_account'  => $result['bank_account'],
				'amount'		=> $result['amount'],
				'message'		=> $result['message'],
				'status'		=> $result['status'],
				'cashback_type' => $result['cashback_type'],
				'date_transfer' => date($this->language->get('date_format_short'), strtotime($result['date_transfer']))
				//'href'       => $this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
		}
 
		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];



		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/refund_detail.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/refund_detail.tpl';
		} else {
			$this->template = 'default/template/account/refund_detail.tpl';
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
		$this->language->load('account/return');
		
		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}
    	
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');
			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
		
		$this->load->model('account/return');
						
		$return_info = $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));

			$this->data['breadcrumbs'] = array();
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);
	
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	
					
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/return', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);			
			
			$this->data['heading_title'] = $this->language->get('text_return');
			
			$this->data['text_return_detail'] = $this->language->get('text_return_detail');
			$this->data['text_return_id'] = $this->language->get('text_return_id');
			$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_ordered'] = $this->language->get('text_date_ordered');
			$this->data['text_customer'] = $this->language->get('text_customer');
			$this->data['text_email'] = $this->language->get('text_email');
			$this->data['text_telephone'] = $this->language->get('text_telephone');			
			$this->data['text_status'] = $this->language->get('text_status');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
			$this->data['text_product'] = $this->language->get('text_product');
			$this->data['text_comment'] = $this->language->get('text_comment');
      		$this->data['text_history'] = $this->language->get('text_history');
			
      		$this->data['column_product'] = $this->language->get('column_product');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_opened'] = $this->language->get('column_opened');
			$this->data['column_reason'] = $this->language->get('column_reason');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');
      		$this->data['column_comment'] = $this->language->get('column_comment');
							
			$this->data['button_continue'] = $this->language->get('button_continue');
			
			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
			$this->data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];						
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];
			$this->data['reason'] = $return_info['reason'];
			$this->data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$this->data['comment'] = nl2br($return_info['comment']);
			$this->data['action'] = $return_info['action'];
						
			$this->data['histories'] = array();
			
			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);
			
      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}
			
			$this->data['continue'] = $this->url->link('account/return', $url, 'SSL');


			$this->load->model('account/customer');
			$point_credit = $this->model_account_customer->getPointCredit();
			$this->data['point'] = $point_credit['point'];
			$this->data['credit'] =  $point_credit['credit'];



			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/return_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/return_info.tpl';
			} else {
				$this->template = 'default/template/account/return_info.tpl';
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
		} else {
			$this->document->setTitle($this->language->get('text_return'));
						
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/return', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
									
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/info', 'return_id=' . $return_id . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['heading_title'] = $this->language->get('text_return');

			$this->data['text_error'] = $this->language->get('text_error');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('account/return', '', 'SSL');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
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
		
 

	public function insert() {
		$this->language->load('account/return');

 		$this->load->model('account/address');
		if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('account/bank')); 
		}
		
		//Check this order isClaim?
 		$this->load->model('account/address');
		if( !$this->model_account_address->getbank() ){
			$this->redirect($this->url->link('account/bank')); 
		}
		//Check this order already save Claim?
		$this->load->model('account/return');
		if(isset($this->request->post['order_id'])){  //REDIRECT TO VIEW
			if( $this->model_account_return->thisOrderClaimed($this->request->post['order_id']) ) {
				$this->redirect($this->url->link('account/return', '', 'SSL'));
			}
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {

			$this->load->model('account/customer');
			$cus = $this->model_account_customer->getCustomer($this->customer->getId());
			parse_str( $this->request->post['product'],$this->request->post['product'] );
			parse_str( $this->request->post['pp'],$this->request->post['pp'] );
 
			$return_id = $this->model_account_return->addReturnOrder($this->request->post , $this->request->post['product'] , $cus );
	  		$this->model_account_return->addReturnOrderProduct($this->request->post , $return_id , $this->request->post['product'] );


			$this->load->model('checkout/order');
			//$this->model_checkout_order->update($this->request->get['order_id'] , '14' , $message='', false);
			// Change Reditect
			$this->redirect($this->url->link('account/return/tracking', '', 'SSL'));
    	} 
							
		$this->document->setTitle($this->language->get('heading_title'));
		
      	$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return/insert', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
    	$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';
		$this->load->model('account/order');
		
		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);
		}
 
    	if (isset($this->request->post['order_id'])) {
      		$this->data['order_id'] = $this->request->post['order_id']; 	
		} elseif (!empty($order_info)) {
			$this->data['order_id'] = $order_info['order_id'];
		} else {
      		$this->data['order_id'] = ''; 
    	}
 
		$this->load->model('account/return');
		$this->load->model('account/order');
		$this->load->model('tool/image'); 

		$o_date = "";
		$this->data['returns'] = array();
		//(`status` = 'done' or `status` = 'received') 6=done  7=received
		$results = $this->model_account_return->getReturnsNew(0, 10 , $this->data['order_id']);
		foreach ($results as $result) {
			//print_r($result);echo "<br><br>";
			$img = $this->model_account_order->getImagefromColorOption($result['product_id'] ,$result['value']);		
			$image = (strlen($img) > 1) ? $this->model_tool_image->resize($img, 100, 150) : $this->model_tool_image->resize($result['image'], 100, 150);
			$option = $this->model_account_return->getReturnOption(  $this->data['order_id'] , $result['product_id'] ,$result['value']);
			$o_date = date($this->language->get('date_format_short'), strtotime($result['order_date']));
			$this->data['order_id']." ". $result['product_id']." ".$option." ";
			$this->data['returns'][] = array(
				'order_id'		=> $result['order_id'],
				'order_total'   => $result['order_total'] ,
				'product_id'    => $result['product_id'],
				'quantity'		=> $result['quantity'],
				'option'		=> $option,
				'price'			=> $this->currency->format($result['price']),
				'pp'			=> $result['price'],
				'total'			=> $result['total'],
				'images'		=> $image,
				'order_date'	=> date($this->language->get('date_format_short'), strtotime($result['order_date']))
			);
		}

		$this->data['o_date'] = $o_date;
		$this->data['option'] = $this->model_account_return->returnorderOption($this->data['order_id']);
		//print_r($this->data['option']);


		$total = $this->model_account_return->returnordertotal($this->data['order_id']);
		foreach($total as $key => $val){
			$total[$key]['value'] = $this->currency->format($val['value']);
		}
		$this->data['total'] = $total;
 								
		$this->load->model('localisation/return_reason');
    	$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();
		if (isset($this->request->post['comment'])) {
    		$this->data['comment'] = $this->request->post['comment'];
		} else {
			$this->data['comment'] = '';
		}	
		
 


		// Totals
		$this->load->model('setting/extension');
		
		$total_data = array();					
		$total = 0;
		$taxes = $this->cart->getTaxes();
		$sort_order = array(); 
		$results = $this->model_setting_extension->getExtensions('total');
		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}
		array_multisort($sort_order, SORT_ASC, $results);
		
		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);
	
				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}
			
			$sort_order = array(); 
			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);	
		}
		$this->data['totals'] = $total_data;
 
 

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');
		$this->data['action'] = $this->url->link('account/return/insert', '', 'SSL');

		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/return_form.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/return_form.tpl';
		} else {
			$this->template = 'default/template/account/return_form.tpl';
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
	
  	public function success() {
		$this->language->load('account/return');

		$this->document->setTitle($this->language->get('heading_title')); 
      
	  	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);	
				
    	$this->data['heading_title'] = $this->language->get('heading_title');

    	$this->data['text_message'] = $this->language->get('text_message');

    	$this->data['button_continue'] = $this->language->get('button_continue');
	
    	$this->data['continue'] = $this->url->link('common/home');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/success.tpl';
		} else {
			$this->template = 'default/template/common/success.tpl';
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
		
  	protected function validate() {
    	if (!$this->request->post['order_id']) {
      		$this->error['order_id'] = $this->language->get('error_order_id');
    	}
		
		//if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      	//	$this->error['firstname'] = $this->language->get('error_firstname');
    	//}

    	//if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      //		$this->error['lastname'] = $this->language->get('error_lastname');
    	//}

    	//if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      	//	$this->error['email'] = $this->language->get('error_email');
    	//}
		
    	//if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      	//	$this->error['telephone'] = $this->language->get('error_telephone');
    	//}		
		if(isset($this->request->post['product']) )
		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}	
		
		//if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
		//	$this->error['model'] = $this->language->get('error_model');
		//}							

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}	
				
    	//if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
      	//	$this->error['captcha'] = $this->language->get('error_captcha');
    	//}
		
		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));
			
			if ($information_info && !isset($this->request->post['agree'])) {
      			$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}
	
	public function captcha() {
		$this->load->library('captcha');
		
		$captcha = new Captcha();
		
		$this->session->data['captcha'] = $captcha->getCode();
		
		$captcha->showImage();
	}	
}
?>
