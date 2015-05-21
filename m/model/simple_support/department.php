<?php
	class ModelSimpleSupportDepartment extends Model {
		
		public function addDepartment($data) {
			foreach ($data['department_name'] as $language_id => $value) {
				if (isset($simple_support_department_id)) {
					//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', department_head_id='" . (int)$data['department_head_id'] . "', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
					$this->db->query("INSERT INTO `my_simple_support_department` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', language_id = '1', name = '" . $this->db->escape($value['name']) . "', department_head_id='1', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
				} else {
					//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department` SET language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', department_head_id='" . (int)$data['department_head_id'] . "', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
					$this->db->query("INSERT INTO `my_simple_support_department` SET language_id = '1', name = '" . $this->db->escape($value['name']) . "', department_head_id='1', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
	
					$simple_support_department_id = $this->db->getLastId();
				}
			}
			
			$count = count($data['department_for']);
			
			if($count == 2) {
				//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='" . $this->db->escape('customer') . "', admin_department='" . $this->db->escape('user') . "'");
				$this->db->query("INSERT INTO `my_simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='customer', admin_department='admin'");
			} else {
				foreach($data['department_for'] as $department_for) {
					if($department_for == 'customer') {
						//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='" . $this->db->escape($department_for) . "'");
						$this->db->query("INSERT INTO `my_simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='customer'");
					} else {
						//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', admin_department='" . $this->db->escape($department_for) . "'");
						$this->db->query("INSERT INTO `my_simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', admin_department='admin'");
					}
				}
			}			
			
			$this->cache->delete('department_name');		
		}
		
		public function editDepartment($simple_support_department_id, $data) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_department` WHERE simple_support_department_id = '" . (int)$simple_support_department_id . "'");
	
			foreach ($data['department_name'] as $language_id => $value) {
				//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', department_head_id='" . (int)$data['department_head_id'] . "', status='" . (int)$data['status'] . "', date_modified=NOW()");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', language_id = '1', name = '" . $this->db->escape($value['name']) . "', department_head_id='1', status='" . (int)$data['status'] . "', date_modified=NOW()");
			}
			
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_department_employee` WHERE simple_support_department_id = '" . (int)$simple_support_department_id . "'");
			
			$count = count($data['department_for']);
			
			if($count == 2) {
				//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='" . $this->db->escape('customer') . "', admin_department='" . $this->db->escape('user') . "'");
				$this->db->query("INSERT INTO `my_simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='customer', admin_department='admin'");
			} else {
				foreach($data['department_for'] as $department_for) {
					if($department_for == 'customer') {
						//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='" . $this->db->escape($department_for) . "'");
						$this->db->query("INSERT INTO `my_simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', customer_department='customer'");
					} else {
						//$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', admin_department='" . $this->db->escape($department_for) . "'");
						$this->db->query("INSERT INTO `my_simple_support_department_employee` SET simple_support_department_id = '" . (int)$simple_support_department_id . "', admin_department='admin'");
					}
				}
			}	
			
			$this->cache->delete('department_name');	
		}
		
		public function deleteDepartment($simple_support_department_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_department` WHERE simple_support_department_id='" . (int)$simple_support_department_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_department_employee` WHERE simple_support_department_id='" . (int)$simple_support_department_id . "'");
		}
		
		public function getDepartmentInfo($simple_support_department_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_department` WHERE simple_support_department_id='" . (int)$simple_support_department_id . "'");
			
			return $sql->row;
		}
		
		public function getTotalDepartments($data = array()) {
			$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "simple_support_department` WHERE language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			$query = $this->db->query($sql);
			
			return $query->row['total'];
		}
		
		public function getDepartments($data = array()) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "simple_support_department` WHERE language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			$sort_data = array(
				'name',
				'status'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY name";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
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
		
		public function getDepartmentDescriptions($simple_support_department_id) {
			$department_data = array();

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_department` WHERE simple_support_department_id = '" . (int)$simple_support_department_id . "'");
	
			foreach ($query->rows as $result) {
				$department_data[$result['language_id']] = array('name' => $result['name']);
			}
	
			return $department_data;
		}
		
		public function getUserName($username) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE LCASE(username) LIKE '" . $this->db->escape(utf8_strtolower($username)). "%' AND status=1");
			
			return $sql->rows;
		}
		
		public function checkDepartmentHead($data) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape($data['department_head_name']). "' AND status=1");
			
			return $sql;	
		}		
		
		public function getUserInfo($simple_support_department_id) {
			
			$sql = $this->db->query("SELECT u.* FROM `" . DB_PREFIX . "user` u LEFT JOIN `" . DB_PREFIX  . "simple_support_department` ssd ON(ssd.department_head_id=u.user_id) WHERE ssd.simple_support_department_id='" . (int)$simple_support_department_id . "'");
			
			return $sql->row;
		}
		
		public function getDepartmentFor($simple_support_department_id) {
			
			$department_for_data = array();
			
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_department_employee` WHERE simple_support_department_id='" . (int)$simple_support_department_id . "'");
			
			$department_for_data[] = $sql->row['customer_department'];
			$department_for_data[] = $sql->row['admin_department'];

			return $department_for_data;
		}		
		
		public function checkDepartment($simple_support_department_id) {
			$sql = $this->db->query("SELECT sst.*,ssd.name FROM `" . DB_PREFIX . "simple_support_ticket` sst LEFT JOIN `" . DB_PREFIX . "simple_support_department` ssd ON(sst.simple_support_department_id=ssd.simple_support_department_id) WHERE ssd.simple_support_department_id='" . (int)$simple_support_department_id . "' AND ssd.language_id='" . (int)$this->config->get('config_language_id') . "'");
			
			return $sql;
		}
	}
?>