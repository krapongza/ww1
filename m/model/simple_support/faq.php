<?php
	class ModelSimpleSupportFaq extends Model {
		
		public function addFaq($data) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq` SET simple_support_faq_group_id='" . (int)$data['simple_support_faq_group_id'] . "', sort_order='" . (int)$data['sort_order'] . "', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
			
			$simple_support_faq_id = $this->db->getLastId();
			
			foreach($data['faq_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_description` SET simple_support_faq_id='" . (int)$simple_support_faq_id . "', language_id='" . (int)$language_id . "', question='" . $this->db->escape($value['question']) . "', answer='" . $this->db->escape($value['answer']) . "', meta_description='" . $this->db->escape($value['meta_description']) . "', meta_keyword='" . $this->db->escape($value['meta_keyword']) . "'");
			}
			
			foreach($data['faq_to_store'] as $faq) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_to_store` SET simple_support_faq_id='" . (int)$simple_support_faq_id . "', store_id='" . (int)$faq . "'");
			}			
		}
		
		public function editFaq($simple_support_faq_id, $data) {
			$this->db->query("UPDATE `" . DB_PREFIX . "simple_support_faq` SET simple_support_faq_group_id='" . (int)$data['simple_support_faq_group_id'] . "', sort_order='" . (int)$data['sort_order'] . "', status='" . (int)$data['status'] . "', date_modified=NOW() WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
			
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_description` WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
			
			foreach($data['faq_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_description` SET simple_support_faq_id='" . (int)$simple_support_faq_id . "', language_id='" . (int)$language_id . "', question='" . $this->db->escape($value['question']) . "', answer='" . $this->db->escape($value['answer']) . "', meta_description='" . $this->db->escape($value['meta_description']) . "', meta_keyword='" . $this->db->escape($value['meta_keyword']) . "'");
			}
			
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_to_store` WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
			
			foreach($data['faq_to_store'] as $faq) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_faq_to_store` SET simple_support_faq_id='" . (int)$simple_support_faq_id . "', store_id='" . (int)$faq['store_id'] . "'");
			}				
		}
		
		public function deleteFaq($simple_support_faq_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq` WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_description` WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_faq_to_store` WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
		}
		
		public function getFaqInfo($simple_support_faq_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq` WHERE simple_support_faq_id='" . (int)$simple_support_faq_id . "'");
			
			return $sql->row;
		}
		
		public function getTotalFaqs($data = array()) {
			$sql = "SELECT COUNT(DISTINCT(ssf.simple_support_faq_id)) AS total FROM `" . DB_PREFIX . "simple_support_faq` ssf LEFT JOIN `" . DB_PREFIX . "simple_support_faq_description` ssfd ON(ssf.simple_support_faq_id=ssfd.simple_support_faq_id) LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON(ssf.simple_support_faq_group_id=ssfgd.simple_support_faq_group_id) WHERE ssfd.language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			if(!empty($data['filter_question'])) {
				$sql .= " AND LCASE(ssfd.question) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_question'])) . "%'";
			}
			
			if(!empty($data['filter_group_id'])) {
				$sql .= " AND ssf.simple_support_faq_group_id = '" . (int)$data['filter_group_id'] . "'";
			}
			
			if(isset($data['filter_status']) && $data['filter_status'] != '') {
				$sql .= " AND ssf.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$query = $this->db->query($sql);
			
			return $query->row['total'];			
		}
		
		public function getFaqs($data = array()) {
			$sql = "SELECT ssf.*, ssfd.question AS question, ssfgd.name AS group_name FROM `" . DB_PREFIX . "simple_support_faq` ssf LEFT JOIN `" . DB_PREFIX . "simple_support_faq_description` ssfd ON(ssf.simple_support_faq_id=ssfd.simple_support_faq_id) LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON(ssf.simple_support_faq_group_id=ssfgd.simple_support_faq_group_id) WHERE ssfd.language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			if(!empty($data['filter_question'])) {
				$sql .= " AND LCASE(ssfd.question) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_question'])) . "%'";
			}
			
			if(!empty($data['filter_group_id'])) {
				$sql .= " AND ssf.simple_support_faq_group_id = '" . (int)$data['filter_group_id'] . "'";
			}
			
			if(isset($data['filter_status']) && $data['filter_status'] != '') {
				$sql .= " AND ssf.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sort_data = array(
				'ssfd.question',
				'ssfgd.name',
				'ssf.sort_order',
				'ssf.status'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY ssfd.question";	
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
		
		public function getFaqDescriptions($simple_support_faq_id) {
			$simple_support_faq_data = array();
			
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq_description` WHERE simple_support_faq_id = '" . (int)$simple_support_faq_id . "'");
		
			foreach ($sql->rows as $result) {
				$simple_support_faq_data[$result['language_id']] = array(
					'question' 	=> $result['question'],
					'answer'	=> $result['answer'],
					'meta_keyword' => $result['meta_keyword'],
					'meta_description' => $result['meta_description']
				);
			}			
			return $simple_support_faq_data;	
		}
		
		public function getFaqStore($simple_support_faq_id) {
			$simple_support_faq_data = array();
	
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_faq_to_store` WHERE simple_support_faq_id = '" . (int)$simple_support_faq_id . "'");
	
			foreach ($sql->rows as $result) {
				$simple_support_faq_data[] = $result['store_id'];
			}
	
			return $simple_support_faq_data;
		}
		
		
	}
?>