<?php
	class ControllerSimpleSupportPaysbuy extends Controller {
		public function index() {

			$this->load->model('checkout/order');
			if(isset($this->session->data['order_id'])){
				$order_id = $this->session->data['order_id'];
			}
			if(isset($this->request->get['invoice'])){
				$order_id = $this->request->get['invoice'];
			}
			//$this->model_checkout_order->addPaysbuy($order_id);
			$total = $this->model_checkout_order->getPaymentOrderTotal($order_id);
			$fees = $this->model_checkout_order->getPaysbutFee();

			foreach($fees as $fee){
				if($fee['name'] == 'paysbuy_add_fee'){
					$price_fee_add	 = ($total * ($fee['value']/100));
				}elseif($fee['name'] == 'paysbuy_discount_fee'){
					$price_fee_minus = ($total * ($fee['value']/100));
				}
			}

			$youraccount	= "mayroses@hotmail.com";
			$invoice		= $order_id;
			$description	= "คุณได้ทำรายการซื้อขายกับ Mayroses.com";
			$price			= $total; //$totals['discount']+$totals['shipping'];
			$postURL		= $this->url->link('simple_support/paysbuy_success')."&invoice=".$order_id; //"http://mayroses.veerawit.com/index.php?route=simple_support/paysbuy_success";
			$reqURL			= $this->url->link('simple_support/paysbuy_success'); //"http://mayroses.veerawit.com/index.php?route=simple_support/paysbuy_success";
			$price_fee		= ($price_fee_add - $price_fee_minus);

			$this->data['psb']		= 'psb';
			$this->data['biz']		= $youraccount;
			$this->data['inv']		= $invoice;
			$this->data['itm']		= $description;
			$this->data['amt']		= ($price+ ceil($price_fee) );
			$this->data['postURL']	= $postURL;
			$this->data['reqURL']	= $reqURL;

			unset($this->session->data['order_id']);

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/paysbuy.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/paysbuy.tpl';
			} else {
				$this->template = 'default/template/simple_support/paysbuy.tpl';
			}
	
				/*$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'		
			);*/
			$this->response->setOutput($this->render());			
		}




	}
?>