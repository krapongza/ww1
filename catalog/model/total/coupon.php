<?php
class ModelTotalCoupon extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		if (isset($this->session->data['coupon'])) {
			$this->language->load('total/coupon');
			
			$this->load->model('checkout/coupon');
			$this->load->model('checkout/pointcredit');
			/*
			* $coupon_info['type'] = F  => FIX Amount
			*/

			$this->load->model('catalog/product');
			
			$active_a = array();$product_date = array(); $product_dis = array();
			$price = 0;

			$ava_pre = $this->model_checkout_pointcredit->getAva_PreProduct();
			$tmp = $this->model_catalog_product->getNewProductDiscount($ava_pre[3] , $array=1);
			$newsubtotal = $tmp[0];
	
			$ava_array = $ava_pre[3];
			foreach($newsubtotal as $key){
				if (in_array($key['id'], $ava_array)) {
					$nkey = array_keys($ava_array, $key['id']);
					unset( $ava_array[ $nkey[0] ] ) ;
				} 
			}

//print_r($ava_array);
			if( count($ava_array) > 0  )
				$coupon_info = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
			else
				$coupon_info = 0;


			if ($coupon_info) {
				$discount_total = 0;
				
				if (!$coupon_info['product']) {
					$tmp_data = $ava_pre; 
					if($coupon_info['type'] == 'F'){
						$sub_total = $this->cart->getSubTotal();
					}else{
						$sub_total = ($tmp_data[0] > 0)? $tmp_data[1]:$this->cart->getSubTotal();
					}
				} else {
					$sub_total = 0;
					foreach ($this->cart->getProducts() as $product) {
						if (in_array($product['product_id'], $coupon_info['product']))  $sub_total += $product['total'];
					}					
				}
				
				if ($coupon_info['type'] == 'F') {
					$coupon_info['discount'] = ($tmp_data[1] > 0)?min($coupon_info['discount'], $sub_total):0;
				}


				foreach ($this->cart->getProducts() as $product) {
					$discount = 0;
					
					if (!$coupon_info['product']) {
						$status = true;
					} else {
						$status = (in_array($product['product_id'], $coupon_info['product']))?true:false;
					}
					
					if ($status) {
						if ($coupon_info['type'] == 'F') {
							$discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
						} elseif ($coupon_info['type'] == 'P') {
							$p_total = (in_array($product['product_id'], $ava_array)) ? $product['total'] : 0;
							$discount = $p_total / 100 * $coupon_info['discount'];

						}
				
					}
					
					$discount_total += $discount;
				}
				
				if ($coupon_info['shipping'] && isset($this->session->data['shipping_method'])) {
					if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
						$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);
						
						foreach ($tax_rates as $tax_rate) {
							if ($tax_rate['type'] == 'P') {
								$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
							}
						}
					}
					
					$discount_total += $this->session->data['shipping_method']['cost'];				
				}				
      			
				$discount_total = ceil($discount_total);

				//if($discount_total > 0)
				$total_data[] = array(
					'code'       => 'coupon',
        			'title'      => sprintf($this->language->get('text_coupon'), $this->session->data['coupon']),
	    			'text'       => $this->currency->format(-$discount_total),
        			'value'      => -$discount_total,
					'sort_order' => $this->config->get('coupon_sort_order')
      			);

				$total -= $discount_total;
			} 
		}
	}
	
	public function confirm($order_info, $order_total) {
		$code = '';
		
		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');
		
		if ($start && $end) {  
			$code = substr($order_total['title'], $start, $end - $start);
		}	
		
		$this->load->model('checkout/coupon');
		
		$coupon_info = $this->model_checkout_coupon->getCoupon($code);
			
		if ($coupon_info) {
			$this->model_checkout_coupon->redeem($coupon_info['coupon_id'], $order_info['order_id'], $order_info['customer_id'], $order_total['value']);	
		}						
	}
}
?>