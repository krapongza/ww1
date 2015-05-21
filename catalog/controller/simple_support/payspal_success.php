<?php
	class Controllersimplesupportpayspalsuccess extends Controller {
		public function index() {

			//update status to suceess
			$this->load->model('checkout/order');
			if(isset($this->request->get['invoice'])){
				$order_id = $this->request->get['invoice'];
			}
			$this->model_checkout_order->completedOrder($order_id , 'paypal');

			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);	
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			
			$this->redirect($this->url->link('account/order'));		
		}




	}
?>