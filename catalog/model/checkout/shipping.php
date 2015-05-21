<?php
class ModelCheckoutShipping extends Model {
 
	public function checkShippingOverWeight($from_shipping_methods='') {
		$shipping_methods =  ( isset($from_shipping_methods)&&($from_shipping_methods == "2")  )?"REGISTER":"EMS";
		$error_weight="";
		// Shipping
		if ( isset($from_shipping_methods)  ) {
			if( ($shipping_methods == 'REGISTER')&&($this->cart->getWeight() > 2) ){
				$shipping_methods="EMS";
				$error_weight="register_overweight";
			}
			if( ($shipping_methods == 'EMS')&&($this->cart->getWeight() > 20) ){
				$error_weight="ems_overweight";
			}
			$shipping_type = $shipping_methods;
		}else{
			$shipping_type = "EMS";
		}

		return array($shipping_methods , $shipping_type , $error_weight);
	}


	public function calWeight($order , $products) {
		$this->load->model('account/order');

		$weight = 0;
		foreach($order as $key => $val ){
			foreach($products as $product)
				if($product['product_id'] == $val)$q = $product['quantity'];
			$weight = $weight + ( $q * $this->model_account_order->getProductWeight($val) );
		}
		return $weight ;
	}

 
	public function getFraud($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_fraud` WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row;
	}
}
?>