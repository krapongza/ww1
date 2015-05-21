<?php
class ModelTotalCredit extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		if ($this->config->get('credit_status')) {
			$this->language->load('total/credit');
			/*
			$balance = $this->customer->getBalance();
			
			if ((float)$balance) {
				if ($balance > $total) {
					$credit = $total;	
				} else {
					$credit = $balance;	
				}
				
				if ($credit > 0) {
					$total_data[] = array(
						'code'       => 'credit',
						'title'      => $this->language->get('text_credit'),
						'text'       => $this->currency->format(-$credit),
						'value'      => -$credit,
						'sort_order' => $this->config->get('credit_sort_order')
					);
					
					$total -= $credit;
				}
			}*/
		}

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
	  		 
    	}else{
			$this->load->model('checkout/discount');
			$this->load->model('checkout/pointcredit');

			$sub_total = 0;
			$tmp_data = $this->model_checkout_pointcredit->getAva_PreProduct();
			if($tmp_data[0] > 0){
				$sub_total = $tmp_data[1]; //$this->cart->getSubTotal(); //
			}else{ 
				$sub_total = $this->cart->getSubTotal();
			}

			$global_discount = $this->model_checkout_discount->calGlobalDiscount($sub_total);

			if($global_discount > 0){
				$total_data[] = array(
					'code'       => 'globaldiscount',
					'title'      => 'ลดราคาทั้งร้าน',
					'text'       => $this->currency->format(-$global_discount),
					'value'      => -$global_discount,
					'sort_order' => $this->config->get('credit_sort_order')
				);
			 
				$total -= $global_discount;

	 
			}




		}




	}
	
	public function confirm($order_info, $order_total) {
		$this->language->load('total/credit');
		
		if ($order_info['customer_id']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', amount = '" . (float)$order_total['value'] . "', date_added = NOW()");				
		}
	}	
}
?>