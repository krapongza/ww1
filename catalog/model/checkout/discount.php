<?php
class ModelCheckoutDiscount extends Model {

 
	public function calGlobalDiscount($subtotal) {
		$query = $this->db->query("SELECT * from `" . DB_PREFIX . "discount`  ");
		foreach($query->rows as $key => $val){
			if($val['name'] == 'global_discount_status') $g_status = $val['value'];
			if($val['name'] == 'time_discount_status') $t_status = $val['value'];
			if($val['name'] == 'priority_discount') $priority = $val['value'];
			if($val['name'] == 'global_discount') $g_discount = $val['value'];
		}
//echo $subtotal;

		$newprice = 0;
		if(($priority == 2) && ($g_status > 0) ){
		 
			$this->load->model('catalog/product');
			$products = $this->cart->getProducts();
			$active_a = array();$product_date = array(); $product_dis = array();
			$price = 0;
			foreach($products as $p2){
				$tmp = $this->model_catalog_product->isProductPreOrder($p2['product_id'] );
				if(!$tmp){ 
					$tmp2 = $this->model_catalog_product->getProductDiscount($p2['product_id'] );
					if(!$tmp2){
						array_push($active_a , $p2['product_id']);
						array_push($product_date , $this->model_catalog_product->getProductDate($p2['product_id'] )  );
					}
				}
			}
			
			foreach($active_a as $ack => $acv){
				foreach($products as $key => $val){
					if($val['product_id'] == $acv){
						$price = $price + $val['total'];
					}
				}
			}
			
			$newprice =   (($price * $g_discount)/100) ;

		}elseif($t_status > 0){
			$this->load->model('catalog/product');
			$products = $this->cart->getProducts();
			$active_a = array();$product_date = array(); $product_dis = array();
			$price = 0;$time_discount = 0;
			foreach($products as $p2){
				$tmp = $this->model_catalog_product->isProductPreOrder($p2['product_id'] );
				if(!$tmp){ 
					$tmp2 = $this->model_catalog_product->getProductDiscount($tmp );
					//if(!$tmp2){
						array_push($active_a , $p2['product_id']);
						array_push($product_date , $this->model_catalog_product->getProductDate($p2['product_id'] )  );
					//}
				}
			}
			
			// print_r($product_date); echo "xxxx<br>";
 
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "discount_time ");
			foreach($product_date as $p => $v){
				$dDiff =  $this->model_catalog_product->diffdate($v[2]);
				foreach($query->rows as $key => $val){
					if( ($dDiff >= $val['date_start'])&& ($dDiff <= $val['date_end'])  ){ 
						$time_discount =  $val['discount'];
						$product_date[$p][3] =  ceil(($v[1] * $time_discount)/100)   ; //ceil( $v[1] - (($v[1] * $time_discount)/100)  );
						break; 
					}
				}
			}
			//print_r($product_date); echo "xxxx<br>";
 
			foreach($product_date as $key => $val){
				$newprice = $newprice + $val[3];
			}

		}

		

		return ceil($newprice);
	}
 
 	public function calTimeDiscount($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_fraud` WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row;
	}
	
	public function getFraud($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_fraud` WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row;
	}
}
?>