<?php
	class ModelSimpleSupportFaqGroup extends Model {
		
		public function addFaqGroup($data) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_group` SET sort_order='" . (int)$data['sort_order'] . "', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
			
			$simple_support_faq_group_id = $this->db->getLastId();
			
			foreach ($data['faq_group_name'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_group_description` SET simple_support_faq_group_id = '" . (int)$simple_support_faq_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			}
		}
		
		public function editFaqGroup($simple_support_faq_group_id, $data) {
			$this->db->query("UPDATE `" . DB_PREFIX . "simple_support_faq_group` SET sort_order='" . (int)$data['sort_order'] . "', status='" . (int)$data['status'] . "', date_modified=NOW() WHERE simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
			
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_group_description` WHERE simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
			
			foreach ($data['faq_group_name'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_group_description` SET simple_support_faq_group_id = '" . (int)$simple_support_faq_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			}			
		}
		
		public function deleteFaqGroup($simple_support_faq_group_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_group` WHERE simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_group_description` WHERE simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
		}
		
		public function getFaqGroupInfo($simple_support_faq_group_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq_group` WHERE simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
			
			return $sql->row;
		}
		
		public function getTotalFaqGroups($data = array()) {
			$sql = "SELECT COUNT(DISTINCT(ssfg.simple_support_faq_group_id)) AS total FROM `" . DB_PREFIX . "simple_support_faq_group` ssfg LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON (ssfg.simple_support_faq_group_id = ssfgd.simple_support_faq_group_id) WHERE ssfgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			if(!empty($data['filter_group_name'])) {
				$sql .= " AND LCASE(ssfgd.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_group_name'])) . "%'";
			}
			
			if(isset($data['filter_status']) && $data['filter_status'] != '') {
				$sql .= " AND ssfg.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$query = $this->db->query($sql);
		
			return $query->row['total'];	
		}
		
		public function getFaqGroups($data = array()) {
			$sql = "SELECT ssfg.*, ssfgd.name AS name FROM `" . DB_PREFIX . "simple_support_faq_group` ssfg LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON (ssfg.simple_support_faq_group_id = ssfgd.simple_support_faq_group_id) WHERE ssfgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			if(!empty($data['filter_group_name'])) {
				$sql .= " AND LCASE(ssfgd.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_group_name'])) . "%'";
			}
			
			if(isset($data['filter_status']) && $data['filter_status'] != '') {
				$sql .= " AND ssfg.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sort_data = array(
				'ssfgd.name',
				'ssfg.sort_order',
				'ssfg.status'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY ssfgd.name";	
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
			
			//echo $sql;
			
			$query = $this->db->query($sql);
			
			return $query->rows;
		}	

		public function getFaqGroupDescriptions($simple_support_faq_group_id) {
			$simple_support_faq_group_data = array();	
			
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq_group_description` WHERE simple_support_faq_group_id = '" . (int)$simple_support_faq_group_id . "'");
		
			foreach ($sql->rows as $result) {
				$simple_support_faq_group_data[$result['language_id']] = array('name' => $result['name']);
			}
			
			return $simple_support_faq_group_data;			
		}
		
		public function checkFaqGroupName($name, $language_id, $simple_support_faq_group_id = 0) {
			if(!$simple_support_faq_group_id) {
				$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq_group_description` WHERE LCASE(name)='" . $this->db->escape(utf8_strtolower($name)) . "' AND language_id='" . (int)$language_id . "'");
				
				return $sql->num_rows;
			} else {
				$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq_group_description` WHERE LCASE(name)='" . $this->db->escape(utf8_strtolower($name)) . "' AND language_id='" . (int)$language_id . "' AND simple_support_faq_group_id <> '" . (int)$simple_support_faq_group_id . "'");
				
				return $sql->num_rows;
			}
		}
		
		public function checkFaqGroup($simple_support_faq_group_id) {
			$sql = $this->db->query("SELECT ssfgd.name FROM `" . DB_PREFIX . "simple_support_faq` ssf LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON(ssf.simple_support_faq_group_id = ssfgd.simple_support_faq_group_id) WHERE ssf.simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
			
			return $sql;
		}		
	}
?>