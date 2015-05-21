<?php
    class ModelSimpleSupportStatus extends Model {
    	
		public function addSupportStatus($data) {
			foreach ($data['status_name'] as $language_id => $value) {
				if (isset($simple_support_status_id)) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_status` SET simple_support_status_id = '" . (int)$simple_support_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', date_added=NOW(), date_modified=NOW()");
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_status` SET language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', date_added=NOW(), date_modified=NOW()");
	
					$simple_support_status_id = $this->db->getLastId();
				}
			}
			
			$this->cache->delete('status_name');
		}
		
		public function editSupportStatus($simple_support_status_id, $data) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_status` WHERE simple_support_status_id = '" . (int)$simple_support_status_id . "'");
	
			foreach ($data['status_name'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_status` SET simple_support_status_id = '" . (int)$simple_support_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', date_modified=NOW()");
			}
	
			$this->cache->delete('status_name');
		}
		
		public function deleteSupportStatus($simple_support_status_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_status` WHERE simple_support_status_id = '" . (int)$simple_support_status_id . "'");
		}
		
    	public function getTotalStatues($data = array()) {
    		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "simple_support_status` WHERE language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			$query = $this->db->query($sql);
			
			return $query->row['total'];
    	}
		
		public function getStatues($data = array()) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "simple_support_status` WHERE language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			$sql .= " ORDER BY name";
			
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

		public function getSupportStatusDescriptions($simple_support_status_id) {
			$simple_support_status_data = array();
	
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_status` WHERE simple_support_status_id = '" . (int)$simple_support_status_id . "'");
	
			foreach ($query->rows as $result) {
				$simple_support_status_data[$result['language_id']] = array('name' => $result['name']);
			}
	
			return $simple_support_status_data;
		}
		
		public function getTicketStatus($simple_support_status_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_status` WHERE simple_support_status_id='" . (int)$simple_support_status_id . "' AND language_id='" . (int)$this->config->get('config_language_id') . "'");
			
			return $sql->row;
		}
		
    }
?>