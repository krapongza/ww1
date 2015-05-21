<?php
	class ControllerSimpleSupportPaypal extends Controller {
		public function index() {

			$this->load->model('checkout/order');
			if(isset($this->session->data['order_id'])){
				$order_id = $this->session->data['order_id'];
			}
			if(isset($this->request->get['invoice'])){
				$order_id = $this->request->get['invoice'];
			}
			//$this->model_checkout_order->addPaypal($order_id);
			$order = $this->model_checkout_order->getPaymentOrder($order_id);
			$total = $this->model_checkout_order->getPaymentOrderTotal($order_id);
			$fees = $this->model_checkout_order->getPaypalFee();

			foreach($fees as $fee){
				if($fee['name'] == 'paypal_add_fee'){
					$price_fee_add	 = ($total * ($fee['value']/100));
				}elseif($fee['name'] == 'paypal_discount_fee'){
					$price_fee_minus = ($total * ($fee['value']/100));
				}
			}


$total_price	= 0;
$dis_total		= 0;
$dis_array		= array('vender_discount','globaldiscount','vip','credit_discount');
$total_array	= array('sub_total','shipping');

$price			= $total; //$totals['discount']+$totals['shipping'];
$price_fee		= ($price_fee_add - $price_fee_minus);

$this->data['form']				= 'https://www.paypal.com/cgi-bin/webscr';
$this->data['email']			= 'krapongza@gmail.com';

$this->data['item_name_1']		= "มูลค่าสินค้า Mayroses.com";
$this->data['item_number_1']	= '';
$this->data['amount_1']			= ($price+ ceil($price_fee));
$this->data['quantity_1']		= '1';
$this->data['weight_1']			= '0';

$this->data['discount_amount_cart']	= '0'; //15%

$this->data['first_name']		= $order['shipping_firstname'];
$this->data['last_name']		= $order['shipping_lastname'];
$this->data['address1']			= $order['shipping_address_1'];
$this->data['address2']			= $order['shipping_address_2'];
$this->data['city']				= $order['shipping_city'];
$this->data['zip']				= $order['shipping_postcode'];
$this->data['country']			= 'TH';
$this->data['address_override']	= '0';
$this->data['order_email']		= $order['email'];
$this->data['invoice']			= $order_id;

$this->data['lc']				= 'en';
$this->data['rm']				= '2';
$this->data['no_note']			= '1';
$this->data['charset']			= 'utf-8';

$this->data['return']			= $this->url->link('simple_support/payspal_success')."&invoice=".$order_id; //'http://localhost/test5/index.php?route=checkout/success';
$this->data['notify_url']		= $this->url->link('payment/pp_standard/callback'); //'http://localhost/test5/index.php?route=payment/pp_standard/callback';
$this->data['cancel_return']	= $this->url->link('checkout/checkout'); //'http://localhost/test5/index.php?route=checkout/checkout';
$this->data['paymentaction']	= 'authorization';
$this->data['custom']			= '2';

 
unset($this->session->data['order_id']);





			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/simple_support/paypal.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/simple_support/paypal.tpl';
			} else {
				$this->template = 'default/template/simple_support/paypal.tpl';
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