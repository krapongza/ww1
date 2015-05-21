<?php
class ModelCatalogWishList extends Model {


	public function clearWishList($product_id) {
      	$this->db->query("UPDATE my_product SET wishlist = '0' where product_id = '".$product_id."'  ");
	 
	}
	public function getWishList($data = array()) {
		//Status 2=Sold Out , 3=Pre-Order , 10=Coming Soon
		$sql = "SELECT  product_id,model,image,wishlist,stock_status_id  FROM my_product where stock_status_id in ('2','3','5','10') and wishlist > 0 ";
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) $data['start'] = 0;
			if ($data['limit'] < 1) $data['limit'] = 20;

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		$query = $this->db->query($sql) ;
		
		return $query->rows;
	}
	public function getTotalWishList() {
		$query = $this->db->query("SELECT  count(*) as product_total  FROM my_product where stock_status_id in ('2','3','5','10') ") ;
		return $query->row['product_total'];
	}

	public function getCustomerWishList($data = array()) {

		$sql = "SELECT * FROM my_customer WHERE wishlist LIKE '%".$data['product']."%' ";
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) $data['start'] = 0;
			if ($data['limit'] < 1) $data['limit'] = 20;

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		$query = $this->db->query($sql) ;
		
		return $query->rows;
	}
	public function getTotalCustomerWishList($data = array()) {
		$query = $this->db->query("SELECT customer_id as total FROM my_customer WHERE wishlist LIKE '%".$data['product']."%' ") ;
		return $query->row['total'];
	}

}
?>