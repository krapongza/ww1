<?php
class ModelReportSale extends Model {
	public function getOrders($data = array()) {
		$sql = "SELECT MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.order_id) AS `orders`, SUM(tmp.products) AS products, SUM(tmp.tax) AS tax, SUM(tmp.total) AS total FROM (SELECT o.order_id, (SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, (SELECT SUM(ot.value) FROM `" . DB_PREFIX . "order_total` ot WHERE ot.order_id = o.order_id AND ot.code = 'tax' GROUP BY ot.order_id) AS tax, o.total, o.date_added FROM `" . DB_PREFIX . "order` o"; 

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
		
		$sql .= " GROUP BY o.order_id) tmp";
		
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		
		switch($group) {
			case 'day';
				$sql .= " GROUP BY DAY(tmp.date_added)";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY WEEK(tmp.date_added)";
				break;	
			case 'month':
				$sql .= " GROUP BY MONTH(tmp.date_added)";
				break;
			case 'year':
				$sql .= " GROUP BY YEAR(tmp.date_added)";
				break;									
		}
		
		$sql .= " ORDER BY tmp.date_added DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		echo $sql;
		$query = $this->db->query($sql);
		
		return $query->rows;
	}	
	
	public function getTotalOrders($data = array()) {
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		
		switch($group) {
			case 'day';
				$sql = "SELECT COUNT(DISTINCT DAY(date_added)) AS total FROM `" . DB_PREFIX . "order`";
				break;
			default:
			case 'week':
				$sql = "SELECT COUNT(DISTINCT WEEK(date_added)) AS total FROM `" . DB_PREFIX . "order`";
				break;	
			case 'month':
				$sql = "SELECT COUNT(DISTINCT MONTH(date_added)) AS total FROM `" . DB_PREFIX . "order`";
				break;
			case 'year':
				$sql = "SELECT COUNT(DISTINCT YEAR(date_added)) AS total FROM `" . DB_PREFIX . "order`";
				break;									
		}
		
		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE order_status_id > '0'";
		}
				
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];	
	}


	public function getNewOrders($data = array()) {
		$sql = "SELECT MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.order_id) AS `orders`, SUM(tmp.products) AS products,  SUM(tmp.total) AS total ,SUM(tmp.ems)AS ems , SUM(tmp.register) AS register , (SUM(tmp.ems) + SUM(tmp.register)) AS shipping , SUM(tmp.problem) AS problem FROM (SELECT o.order_id, (SELECT SUM(op.quantity) FROM `my_order_product` op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, (SELECT SUM(ot.value)  FROM my_order_total AS ot WHERE ot.order_id = o.order_id AND ot.code='sub_total'  ) AS total , (SELECT SUM(ot.value)  FROM my_order_total AS ot WHERE ot.order_id = o.order_id AND ot.code='sub_total' AND o.order_status_id IN ('8','13')  ) AS problem, o.date_added ,(SELECT SUM(ot.value)  FROM my_order_total AS ot WHERE ot.order_id = o.order_id AND ot.code='shipping' AND o.shipping_method='EMS'   ) AS ems, (SELECT SUM(ot.value)  FROM my_order_total AS ot WHERE ot.order_id = o.order_id AND ot.code='shipping' AND o.shipping_method='REGISTER'   ) AS register FROM `my_order` o"; 

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
		$sql .= " GROUP BY o.order_id) tmp";
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		switch($group) {
			case 'day';
				$sql .= " GROUP BY DAY(tmp.date_added)";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY WEEK(tmp.date_added)";
				break;	
			case 'month':
				$sql .= " GROUP BY MONTH(tmp.date_added)";
				break;
			case 'year':
				$sql .= " GROUP BY YEAR(tmp.date_added)";
				break;									
		}
		
		$sql .= " ORDER BY tmp.date_added DESC";
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		//echo $sql;
		$query = $this->db->query($sql);
	
		return $query->rows;
	}

	public function getWhoBuyThisProduct($product_id = '') {
		$sql = "SELECT  o.date_added, o.order_id,(SELECT oo.value FROM my_order_option AS oo WHERE oo.order_product_id = op.order_product_id AND oo.name='Size') AS size,(SELECT oo.value FROM my_order_option AS oo WHERE oo.order_product_id = op.order_product_id AND oo.name='Color') AS color,op.quantity AS amount, CONCAT(o.firstname,' ', o.lastname ) AS user , os.name AS status FROM   my_order_product AS op  LEFT JOIN  my_order AS o ON o.order_id = op.order_id LEFT JOIN my_order_status AS  os ON o.order_status_id = os.order_status_id LEFT JOIN my_product AS p ON p.product_id = op.product_id  WHERE p.model = '".$product_id ."' ORDER BY o.order_id";
		$query = $this->db->query($sql);
		//print_r($query);
		return array($query->rows , $query->num_rows );
	}

	
	public function getTaxes($data = array()) {
		$sql = "SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'"; 

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
		
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		
		switch($group) {
			case 'day';
				$sql .= " GROUP BY ot.title, DAY(o.date_added)";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY ot.title, WEEK(o.date_added)";
				break;	
			case 'month':
				$sql .= " GROUP BY ot.title, MONTH(o.date_added)";
				break;
			case 'year':
				$sql .= " GROUP BY ot.title, YEAR(o.date_added)";
				break;									
		}
		
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
	
	public function getTotalTaxes($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM (SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";
		
		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND order_status_id > '0'";
		}
				
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		
		switch($group) {
			case 'day';
				$sql .= " GROUP BY DAY(o.date_added), ot.title";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY WEEK(o.date_added), ot.title";
				break;	
			case 'month':
				$sql .= " GROUP BY MONTH(o.date_added), ot.title";
				break;
			case 'year':
				$sql .= " GROUP BY YEAR(o.date_added), ot.title";
				break;									
		}
		
		$sql .= ") tmp";
		
		$query = $this->db->query($sql);

		return $query->row['total'];	
	}	
	
	public function getShipping($data = array()) {
		$sql = "SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'"; 

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
		
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		
		switch($group) {
			case 'day';
				$sql .= " GROUP BY ot.title, DAY(o.date_added)";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY ot.title, WEEK(o.date_added)";
				break;	
			case 'month':
				$sql .= " GROUP BY ot.title, MONTH(o.date_added)";
				break;
			case 'year':
				$sql .= " GROUP BY ot.title, YEAR(o.date_added)";
				break;									
		}
		
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
	
	public function getTotalShipping($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM (SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";
		
		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND order_status_id > '0'";
		}
				
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}
		
		switch($group) {
			case 'day';
				$sql .= " GROUP BY DAY(o.date_added), ot.title";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY WEEK(o.date_added), ot.title";
				break;	
			case 'month':
				$sql .= " GROUP BY MONTH(o.date_added), ot.title";
				break;
			case 'year':
				$sql .= " GROUP BY YEAR(o.date_added), ot.title";
				break;									
		}
		
		$sql .= ") tmp";
		
		$query = $this->db->query($sql);

		return $query->row['total'];	
	}	
	


	public function topHighVolumnBuyer() {
		$sql = " SELECT c.email   ,  COUNT(c.email) AS orders , ott.o AS cancel ,c.point FROM my_customer AS c LEFT JOIN my_order AS o ON c.customer_id = o.customer_id LEFT JOIN  (SELECT COUNT(order_id) AS o ,customer_id  FROM my_order AS ottt WHERE  order_status_id IN ('13','14') GROUP BY customer_id ) AS ott ON c.customer_id = ott.customer_id GROUP BY c.customer_id ORDER BY c.point DESC LIMIT 100"; 
		//echo $sql;
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	public function getPointTable() {
		$sql ="SELECT level_name,point_min , point_max FROM my_customer_levels";
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	public function countPointLevel($point='' , $levels='') {
		$mylevel="member";
		foreach($levels as $level){
			if($point >= $level['point_min'] && $point <= $level['point_max'])  $mylevel = $level['level_name'];
		}

		return $mylevel;
	}
	public function topLowVolumnBuyer() {
		$sql = " SELECT c.email   ,  COUNT(c.email) AS orders , ott.o AS cancel ,c.point , c.status FROM my_customer AS c LEFT JOIN my_order AS o ON c.customer_id = o.customer_id LEFT JOIN  (SELECT COUNT(order_id) AS o ,customer_id  FROM my_order AS ottt WHERE  order_status_id IN ('13','14') GROUP BY customer_id ) AS ott ON c.customer_id = ott.customer_id GROUP BY c.customer_id ORDER BY c.point  ASC LIMIT 100"; 
		//echo $sql;
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	public function newCustomer() {
		$time = time();
		$total_day = date('t',$time);
		$month = array();
		for($i=1; $i<=$total_day; $i++) {
			if($i != $total_day) {
				$start_date = mktime(0,0,0,date('m', $time),$i,date('Y', $time));	
				$end_date = mktime(0,0,0,date('m', $time),$i+1,date('Y', $time));	
			} else {
				$start_date = mktime(0,0,0,date('m', $time),$i,date('Y', $time));	
				$m = date('m', $time);
				$y = date('Y', $time);
				if($m == 12) { $m = 1; $y += 1; }
				$end_date = mktime(0,0,0,$m,1,$y);
			}
			$start_date2 = date('Y-m-d H:i:s', $start_date);
			$end_date2 = date('Y-m-d H:i:s', $end_date);
			$sql = "SELECT COUNT(*) as total FROM my_customer WHERE date_added >= '" . $start_date2 . "' AND date_added <= '" . $end_date2 . "'";
			$query = $this->db->query($sql);
			$month[] = array('timestamp' => date('m/d/Y', $start_date), 'total' => $query->row['total']);
		}

		return $month;
	}
	public function newCustomerMonthly() {
		$time = time();
		$month = array();
		for($i=1; $i<=12; $i++) {
			if($i != 12) {
				$start_date = mktime(0,0,0,$i,1,date('Y', $time));	
				$end_date = mktime(0,0,0,$i+1,1,date('Y', $time));	
			} else {
				$start_date = mktime(0,0,0,$i,1,date('Y', $time));
				$end_date = mktime(0,0,0,1,1,date('Y', $time) + 1);
			}
			$start_date2 = date('Y-m-d H:i:s', $start_date);
			$end_date2 = date('Y-m-d H:i:s', $end_date);
			$sql = "SELECT COUNT(*) as total FROM my_customer WHERE date_added >= '" . $start_date2 . "' AND date_added <= '" . $end_date2 . "'";
			$query = $this->db->query($sql);
			$month[] = array('timestamp' => date('m/d/Y', $start_date), 'total' => $query->row['total']);
		}

		return $month;
	}
	public function getCustomerInfo($keyword) {
		$sql = " SELECT c.email AS USER,CONCAT(c.firstname,' ',c.lastname) AS names, CONCAT(a.address_1,' ',a.address_2,' ',a.city,' ',a.postcode) AS address, a.city,c.email FROM my_customer AS c LEFT JOIN my_address AS a ON c.customer_id = a.customer_id WHERE c.email LIKE '%".$keyword."%' OR c.firstname LIKE '%".$keyword."%' OR c.lastname LIKE '%".$keyword."%' OR a.address_1 LIKE '%".$keyword."%' OR a.address_2 LIKE '%".$keyword."%' OR a.city LIKE '%".$keyword."%'  GROUP BY c.customer_id "; 
		//echo $sql;
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	public function allProduct() {
		$sql = "SELECT COUNT(*) as total FROM my_product WHERE quantity > 0 AND stock_status_id='5' "; 
		$query_entry = $this->db->query($sql);
		$sql = "SELECT SUM(quantity) as total FROM my_product WHERE quantity > 0 AND stock_status_id='5' "; 
		$query_total = $this->db->query($sql);
		$sql = "SELECT SUM(a1 * quantity) as total FROM my_product WHERE quantity > 0 AND stock_status_id='5' "; 
		$query_price = $this->db->query($sql);
		return array('entry'=>$query_entry->row['total'] , 'total'=>$query_total->row['total'] , 'price'=>$query_price->row['total']   );
	}





}
?>