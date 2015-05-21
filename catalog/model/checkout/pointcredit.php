<?php
class ModelCheckoutPointcredit extends Model {

	public function getAva_PreProduct(  ) {
		$this->load->model('catalog/product');
		$products = $this->cart->getProducts();
		$pre_count = 0;$ava_count = 0;
		$preorder_a = array();
		$avaorder_a = array();
		foreach($products as $p2){
			$tmp = $this->model_catalog_product->isProductPreOrder($p2['product_id'] );
			if($tmp == 1){
				$pre_count =  $pre_count + 1;
				array_push($preorder_a , $p2['product_id']);
			}else{
				$ava_count =  $ava_count + 1;
				array_push($avaorder_a , $p2['product_id']);
			}
		}

		$pre_total =0; $ava_total = 0;$tmp_total=0;
		foreach( $products as $product ){
			foreach($preorder_a as $key => $val){
				if(isset($product['product_id']))
				if($product['product_id'] == $val) 
					$pre_total = $pre_total + $product['total'];
			}
		}
		foreach( $products as $product ){
			foreach($avaorder_a as $key => $val){
				if(isset($product['product_id']))
				if($product['product_id'] == $val) {
					$ava_total = $ava_total + $product['total'];
					unset($product);
					$product = array();

					//print_r($product['total']." ".$ava_total);echo "<br><br>";
				}
			}
		}

		return array( $pre_total , $ava_total , $preorder_a , $avaorder_a );

	}

	public function isPreOrderProduct( $id ) {
		$this->load->model('catalog/product');
		return $this->model_catalog_product->isProductPreOrder($id );
		//print_r($tmp );
	}

	public function getLevelDiscountfromVIP($customer,  $total_data , $global_discount , $newsubtotal=0  ) {
		if($global_discount == 0){
			$user_percent = $customer['vip'];
		}else{
			$user_percent = $global_discount;
		}

		$sub_price = 0;
		//Find & Add Level Discount Price
		foreach($total_data as $key => $val)
			if($val['code'] == "sub_total") $sub_price = $val['value'];
 //print_r($newsubtotal);
		//if($newsubtotal) $sub_price = $sub_price - $newsubtotal;
 		if($sub_price == $newsubtotal){
		
		}elseif($newsubtotal) $sub_price = $sub_price - $newsubtotal;

		$level_discount =   ceil(($sub_price * $user_percent)/100)  ;
 		$level_discount_array = array("code"=>"vip" , "title"=> "ส่วนลดลูกค้าพิเศษ" , "text"=> "- ".$level_discount." ฿" , "value" => "-".$level_discount , "sort_order" => 8 );

		$level_discount =   ceil(($sub_price * $user_percent)/100)  ;

		if( count($total_data) > 0 ){ 
			$total_array = array_pop($total_data);
			$total = $total_array['value'];
			$total_array['value'] = $total - $level_discount;
			$total_array['text'] = ($total - $level_discount )." ฿";
		}else{
			$total_array = array('code'=>'total' , 'title'=>'ยอดเงินรวม' , 'text'=> "0 ฿"  , "value" => 0  ); 
		}

		return array( $user_percent, $level_discount_array , $level_discount , $total_array  );

	}

	public function getLevelDiscountfromPoint($customer, $customerLevel , $total_data , $global_discount , $newsubtotal=0 ) {
		$user_percent=0;
		if($global_discount == 0){
			foreach ($customerLevel as $level){
				if ($customer['point'] >= $level['point_min'] && $customer['point'] <= $level['point_max'])
					$user_percent = $level['discount'];
			}
		}else{
			$user_percent = $global_discount;
		}
		//print_r($total_data);
		$sub_price = 0;
		//Find & Add Level Discount Price
		foreach($total_data as $key => $val)
			if($val['code'] == "sub_total") $sub_price = $val['value'];
		if($sub_price == $newsubtotal){
		
		}elseif($newsubtotal) $sub_price = $sub_price - $newsubtotal;

		$level_discount =   ceil(($sub_price * $user_percent)/100)  ;
		$level_discount_array = array("code"=>"level_discount" , "title"=> "ส่วนลดประจำ Level" , "text"=> "- ".$level_discount." ฿" , "value" => "-".$level_discount , "sort_order" => 8 );

		$level_discount =   ceil(($sub_price * $user_percent)/100)  ;

		if( count($total_data) > 0 ){ 
			$total_array = array_pop($total_data);
			$total = $total_array['value'];
			$total_array['value'] = $total - $level_discount;
			$total_array['text'] = ($total - $level_discount )." ฿";
		}else{
			$total_array = array('code'=>'total' , 'title'=>'ยอดเงินรวม' , 'text'=> "0 ฿"  , "value" => 0  ); 
		}

		return array( $user_percent, $level_discount_array , $level_discount , $total_array  );

	}


	public function getCreditDiscount($credit , $total , $total_array) {
		$i=0;$credit_discount = 0;$credit_discount_array = array();
			if($credit > 0){
				if($credit >= $total  ){
					$total_array['value'] = 0;
					$total_array['text'] = "0 ฿";
					$credit_discount = $total;
				}else{
					$total_array['value'] = $total - $credit;
					$total_array['text'] = ($total - $credit )." ฿";
					$credit_discount = $credit;
				}
				$credit_discount_array = array("code"=>"credit_discount" , "title"=> "ส่วนลดประจำ credit" , "text"=> "- ".$credit_discount." ฿" , "value" => "-".$credit_discount , "sort_order" => 8 );
				$i=1;
			}
			//print_r($credit_discount_array );
			return array($credit_discount ,  $i , $total_array  , $credit_discount_array );
	} 

	public function calNewCredit($credit , $total) {
		if($credit > $total){
			return ($credit - $total);
		}else{
			return 0;
		}

	}


	public function resetupProductonCart($order , $data) {

		foreach($data['products'] as $p => $v ){
			if (!in_array($v['product_id'], $order)) unset($data['products'][$p]);
		}
 
		return $data;
	}

	public function checkoutReCalTotal($data , $totals, $total , $shipping ) {

		foreach($data['totals'] as $p => $v ){
			if($v['code'] == "sub_total"){
				$data['totals'][$p]['text'] = abs($total)." ";
				$data['totals'][$p]['value'] = $total;
			}
			if($v['code'] == "shipping"){
				$data['totals'][$p]['text'] = abs($shipping)." ";
				$data['totals'][$p]['value'] = $shipping;
			}
			if($v['code'] == "total"){
				$data['totals'][$p]['text'] = abs($totals)." ";
				$data['totals'][$p]['value'] = $totals;
			}
		}

		return $data;
	}

	public function historyCredit($order_id, $credit, $old_credit , $used_or_add='1', $admin_add='' , $status='1' , $remark='') {
		//Order_id, total_credit_used, old_credit, used_or_add[1=used,0=add] , admin_name ='' , status[1=active,0=cancel]

		if($credit){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_credit` SET  order_id = '" . (int)$order_id . "', customer = '" . (int)$this->customer->getId() . "', credit = '" . $credit . "',  old_credit = '" . $old_credit . "', date_added = NOW() ,used_or_add = '" . (int)$used_or_add . "', admin_add ='" . $admin_add . "' , status ='" . (int)$status . "' , remark ='" . $remark . "'  ");
		}
	}

	public function historyCreditByReferral($order_id, $credit, $old_credit , $used_or_add='1', $admin_add='' , $status='1' , $remark='' , $customer) {
		//Order_id, total_credit_used, old_credit, used_or_add[1=used,0=add] , admin_name ='' , status[1=active,0=cancel]

		if($credit){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_credit` SET  order_id = '" . (int)$order_id . "', customer = '" . (int)$customer . "', credit = '" . $credit . "',  old_credit = '" . $old_credit . "', date_added = NOW() ,used_or_add = '" . (int)$used_or_add . "', admin_add ='" . (int)$admin_add . "' , status ='" . (int)$status . "' , remark ='" . $remark . "'  ");
		}
	}

	public function reCalTotals($data , $total_data, $total_array) {
		unset($data['totals']);
		$data['totals'] = $total_data;
		$data['total'] = $total_array['value'];
		return $data;
	}
	
	public function getFraud($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_fraud` WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row;
	}
}
?>