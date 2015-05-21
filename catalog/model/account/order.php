<?php
class ModelAccountOrder extends Model {

	public function getProductFromOrder($order_id){
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'   ");
//print_r( $order_query  );	

		if ($order_query->num_rows) {
			foreach($order_query->rows as $key){
			//print_r($key);echo "<br><br>";
			}
			$product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$order_query->row['product_id'] . "'   ");
//print_r( $product_query  );
			if ($product_query->num_rows) {
				$product_img = $product_query->row['image'];
				$product_id = $product_query->row['product_id'];
				$stock_status_id = $product_query->row['stock_status_id'];
			} else {
				$product_img = '';
				$product_id = '';				
			}

			return array(
				'product_img'                => $product_img,
				'product_id'              => $product_id,
				'status_id'					=> $stock_status_id
			);


		} else {
			return false;	
		}

	}

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `my_order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");
	
		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");
			
			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';				
			}
			
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}
			
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");
			
			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';				
			}
			
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}
			
			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],				
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],				
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],	
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],				
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],	
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip'],
				'send_from'               => $order_query->row['send_from']
			);
		} else {
			return false;	
		}
	}
	 
	public function getOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 1;
		}	
		
		//$sql = "SELECT o.order_id, o.firstname, o.lastname,os.order_status_id, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit;
		$sql = "SELECT o.order_id, o.firstname, o.lastname,os.order_status_id, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit;


		$query = $this->db->query($sql);	
	
		//echo $sql;
		return $query->rows;
	}


	public function getProductDetail($pid) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$pid . "'");
	
		return $query->rows;
	}
	
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT *,o.quantity AS q,o.price AS p ,o.quantity AS qo FROM my_order_product as o left join my_product as p on o.product_id = p.product_id WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->rows;
	}
	public function getOrderProductOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT value FROM my_order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
		return $query->rows;
	}
	public function getColor($order_id, $order_product_id) {
		$query = $this->db->query("SELECT o.value FROM my_order_option as o WHERE o.order_id = '" . (int)$order_id . "' AND o.order_product_id = '" . (int)$order_product_id . "'  and o.name='Color' ");
		return $query->row['value'];
	}
	public function getProductImg($product , $color){
		$sql = "SELECT optionimage as img FROM `my_product_option_value` AS p LEFT JOIN my_option_value_description AS o  ON p.option_value_id = o.option_value_id   WHERE  product_id = '$product' AND `name` = '$color'   ";
		$order_query = $this->db->query($sql);

		if((int)$order_query->num_rows < 2){
			$sql = "SELECT image as img FROM `my_product` WHERE  product_id = '$product'   ";
			$order_query = $this->db->query($sql);
		} 
		return $order_query->row['img'];
	}
	public function getProductModel($product ){
		$sql = "SELECT model FROM `my_product` WHERE  product_id = '$product'   ";
		$order_query = $this->db->query($sql);

		return $order_query->row['model'];
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
	
		return $query->rows;
	}

	public function getOrderProductOption($order_id, $product_id , $t_option) {
		$i=0;
		foreach($t_option as $key => $val){
			if($i == 0){
				$k1 = $key; $v1 = $val;
			}else{
				$k2 = $key; $v2 = $val;
			}$i++;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product AS op INNER JOIN " . DB_PREFIX . "order_option AS oo ON op.order_product_id = oo.order_product_id  WHERE op.order_id = '" . (int)$order_id . "' AND op.product_id='" . (int)$product_id . "' AND ((  oo.product_option_id = '" . (int)$k1 . "' AND oo.product_option_value_id = '" . (int)$v1 . "' ) or (oo.product_option_id = '" . (int)$k2 . "' AND oo.product_option_value_id = '" . (int)$v2 . "'))    ");

		return $query->rows[0];
	}	
 
	
	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
	}
	
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
	
		return $query->rows;
	}	

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND oh.notify = '1' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");
	
		return $query->rows;
	}	
	public function deleteOrderProduct($order_id) {
		$query = $this->db->query("delete from  `" . DB_PREFIX . "order_product`     WHERE order_product_id = '" . $order_id . "'  ");
	}	


	public function getOrderDownloads($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "' ORDER BY name");
	
		return $query->rows; 
	}	

	public function getTotalOrders() {
		//echo "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'";
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");
		
		return $query->row['total'];
	}
		
	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row['total'];
	}	

	public function getPendingOrder() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' and  order_status_id = '1' ");
		
		return $query->row['total'];
	}	

	public function cancelOrder($order_id) {
		$this->db->query("update   `" . DB_PREFIX . "order`  SET order_status_id ='14' , date_modified = NOW()  WHERE order_id = '" . $order_id . "'  ");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '1', notify = '1', comment = 'order canceled', date_added = NOW()");


		$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
		foreach ($order_product_query->rows as $order_product) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity + " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");
			
			$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");
		
			foreach ($order_option_query->rows as $option) {
				$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
			}
		}

		$this->cache->delete('product');


	}	

	public function cancelOrderExpired($order_id) {
		$query = $this->db->query("update   `" . DB_PREFIX . "order`  SET order_status_id ='14'  WHERE order_id = '" . $order_id . "'  ");
	}	

	public function checkOrderExpired(){
	
		$query = $this->db->query("UPDATE my_order SET order_status_id='14' WHERE customer_id = '" . $this->customer->getId() . "'  and order_status_id  IN ('0','1')   AND DATEDIFF(NOW() , date_added   ) > 10 ");

		//print_r( mysql_affected_rows() );
		return mysql_affected_rows();
	}

	public function processingOrder($order_id) {
		$query = $this->db->query("update   `" . DB_PREFIX . "order`  SET order_status_id ='2'  WHERE order_id = '" . $order_id . "'  ");
	}	
	public function updateOrder($order_id,$status , $remark='') {
		$query = $this->db->query("update   `" . DB_PREFIX . "order`  SET order_status_id ='" . $status . "' , transfer_text='$remark' WHERE order_id = '" . $order_id . "'  ");
	}	
	public function getOrderStatus($order_id) {
		$query = $this->db->query("SELECT order_status_id AS total FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "'  ");

		if(count($query->row) > 0)
			return $query->row['total'];
		else
			return 0;
	}	
	public function save_banktxn($order_id , $date , $time , $bank , $money , $remark) {
		$query = $this->db->query("insert into   `" . DB_PREFIX . "bank_txn`  SET order_id ='" . $order_id . "' , customer_id = '" . (int)$this->customer->getId() . "' , date='" . $date . "' , time = '" . $time . "' , bank='" . $bank . "', money='" . $money . "' , remark='" . $remark . "'   ");
	}	
	public function getOrderDate($order_id) {
		$query = $this->db->query("SELECT date_added, order_status_id FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' and order_id='" . $order_id . "' ");
		
		return $query->rows;
	}	
	public function getDeadLineLimited() {
		$query = $this->db->query("select hour from my_deadline_time; ");
		
		return $query->row['hour'];
	}	
	public function getOrdershipping_methods($order_id) {
		$query = $this->db->query("SELECT shipping_method FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' and order_id='" . $order_id . "' ");
		
		return $query->row['shipping_method'];
	}	
	public function getProductWeight($pid='') {
		$query = $this->db->query("SELECT weight FROM `" . DB_PREFIX . "product` WHERE  product_id='" . $pid . "' ");
		
		return $query->row['weight'];
	}	
	public function updateOrderTotal($order_id, $code , $val , $text) {
		$query = $this->db->query("update  `" . DB_PREFIX . "order_total`  SET text ='".$text."', value ='".$val."'   WHERE order_id = '".$order_id."' and code='".$code."'  ");
	}	
	public function deleteOrderTotal($order_id, $code  ) {
		$query = $this->db->query("DELETE FROM  `" . DB_PREFIX . "order_total`   WHERE order_id = '".$order_id."' and code='".$code."'  ");
	}	

	public function updateOrderminiTotal($order_id , $val  ) {
		$query = $this->db->query("update  `" . DB_PREFIX . "order`  SET total ='".$val."'    WHERE order_id = '".$order_id."'   ");
	}	
	public function getImagefromOption($id) {
		$query = $this->db->query("select optionimage  from my_product_option_value    WHERE product_option_value_id = '".$id."'   ");
		return $query->row['optionimage'];
	}	
	public function getImagefromColorOption($p_id , $color) {
		//Update Product Not effect this statement
		$sql = "SELECT optionimage FROM my_product_option_value AS p LEFT JOIN  my_option_value_description  AS o ON o.option_value_id = p.option_value_id  WHERE  p.product_id='$p_id' AND p.option_id='2' AND `name` = '$color'";
		$query = $this->db->query($sql);
		if($query->num_rows <> 0){
			return $query->row['optionimage'];
		}else{
			return 0;
		}
	}	

	public function getProductPrice($id) {
		$query = $this->db->query("select price  from my_product    WHERE product_id = '".$id."'   ");
		return $query->row['price'];
	}	
	public function getProductMaxQuantity($id , $color , $size) {
		$query = $this->db->query("select amount  from my_product_option_qty    WHERE product_id = '$id' and property_1='$size' and property_2='$color'   ");
		return $query->row['amount'];
	}	


}
?>