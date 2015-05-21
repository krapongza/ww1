<?php
class ModelReportProduct extends Model {
	public function getProductsViewed($data = array()) {
		$sql = "SELECT pd.name, p.model, p.viewed FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.viewed > 0 ORDER BY p.viewed DESC";
					
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}	
	
	public function getTotalProductsViewed() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE viewed > 0");
		
		return $query->row['total'];
	}
	
	public function getTotalProductViews() {
      	$query = $this->db->query("SELECT SUM(viewed) AS total FROM " . DB_PREFIX . "product");
		
		return $query->row['total'];
	}

	public function getTotalProductAmounts($data = array()) { //getTotalProductViews

		$sql = "  SELECT COUNT(*) as total FROM my_product_option_qty WHERE  amount > 0 ";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
      	$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	public function getTotalProductAmount($data = array()) { //getTotalProductViews
		$sql = " SELECT p.model , (SELECT od.name FROM  my_option_value_description AS od WHERE od.option_value_id = q.property_1   ) AS size, (SELECT od.name FROM  my_option_value_description AS od WHERE od.option_value_id = q.property_2   ) AS color, q.amount FROM my_product_option_qty AS q LEFT JOIN my_product AS p ON p.product_id = q.product_id WHERE  q.amount > 0 ORDER BY q.product_id DESC ";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	

      	$query = $this->db->query($sql);
		
		return $query->rows;
	}

			
	public function reset() {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = '0'");
	}
	
	public function getPurchased($data = array()) {
		$sql = "SELECT op.name, op.model, SUM(op.quantity) AS quantity, SUM(op.total + op.total * op.tax / 100) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";
		
		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}
		
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		$sql .= " GROUP BY op.model ORDER BY total DESC";
					
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	
	public function getTotalPurchased($data) {
      	$sql = "SELECT COUNT(DISTINCT op.model) AS total FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}
		
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		$query = $this->db->query($sql);
				
		return $query->row['total'];
	}


	public function getMoreProductHidding($data = array()) { 
		$sql = "  SELECT * FROM my_product AS p WHERE p.status=0 AND p.quantity > 0 ";
 	
      	$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getSupplierComingSoon($data = array()) { 
		$sql = "SELECT * FROM my_product AS p LEFT JOIN my_manufacturer AS m ON m.manufacturer_id = p.manufacturer_id WHERE p.stock_status_id=10  GROUP BY p.manufacturer_id  "; 
		$suppliers = $this->db->query($sql);
		foreach($suppliers->rows as &$supplier) {
			$sql = "SELECT COUNT(*) as model FROM my_product WHERE manufacturer_id = '".$supplier['manufacturer_id']."' AND stock_status_id = '10'";
			$t1 = $this->db->query($sql);
			$supplier['model'] = $t1->row['model'];
			$sql = "SELECT SUM(amount) AS total FROM my_product_option_qty  WHERE  product_id IN (SELECT product_id FROM my_product WHERE manufacturer_id = '".$supplier['manufacturer_id']."' AND stock_status_id = '10')";
			$t2 = $this->db->query($sql);
			$supplier['total'] = $t2->row['total'];
			unset($supplier['manufacturer_id']);
		}
	 
	 
		return $suppliers->rows;
	}

	public function getSpecialcost($data = array()) { 
		$sql = "  SELECT value FROM my_discount WHERE name='global_price' ";
      	$query = $this->db->query($sql);
		return $query->row['value'];
	}
	public function getSpecialCostInfo($data = array()) { 
		$sql = "  SELECT update_date,item FROM my_deadline_time ";
      	$query = $this->db->query($sql);

		$query1 = $this->db->query("select update_date from my_deadline_time");
		$update_date = $query1->row['update_date'];
		$sql = "select sum(quantity) as total from  my_product where stock_status_id in ('1','5','7') and date_added <= '$update_date' and quantity > '0' ";
		$query2 = $this->db->query($sql);

		return array($query->row , $query2->row['total'] ) ;
	}

}
?>