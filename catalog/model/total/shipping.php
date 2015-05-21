<?php
class ModelTotalShipping extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		//if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
		if ( $this->cart->hasShipping()  ) {
			

			if(  isset($this->session->data['shipping_pre_method']) ||  isset($this->session->data['shipping_ava_method'])   ){
				$total_data[] = array( 
					'code'       => 'pre_shipping',
					'title'      => 'อัตราค่าจัดส่ง',
					'text'       => $this->currency->format($this->session->data['shipping_pre_method']['cost']),
					'value'      => $this->session->data['shipping_pre_method']['cost'],
					'sort_order' => $this->config->get('shipping_sort_order')
				);
				$total_data[] = array( 
					'code'       => 'ava_shipping',
					'title'      => 'อัตราค่าจัดส่ง',
					'text'       => $this->currency->format($this->session->data['shipping_ava_method']['cost']),
					'value'      => $this->session->data['shipping_ava_method']['cost'],
					'sort_order' => $this->config->get('shipping_sort_order')
				);

				$total += $this->session->data['shipping_pre_method']['cost'];
				$total += $this->session->data['shipping_ava_method']['cost'];
			
			}else{
				if( isset($this->session->data['shipping_method']) ){
					$total_data[] = array( 
						'code'       => 'shipping',
						'title'      => $this->session->data['shipping_method']['title'],
						'text'       => $this->currency->format($this->session->data['shipping_method']['cost']),
						'value'      => $this->session->data['shipping_method']['cost'],
						'sort_order' => $this->config->get('shipping_sort_order')
					);
					$total += $this->session->data['shipping_method']['cost'];
				}
			}
			

		
			//print_r($total_data);echo "<br><br>";


			/*if ($this->session->data['shipping_method']['tax_class_id']) {
				$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);
				
				foreach ($tax_rates as $tax_rate) {
					if (!isset($taxes[$tax_rate['tax_rate_id']])) {
						$taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}*/
			
			
		}			
	}
}
?>