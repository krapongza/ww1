<?php
	class ModelSimpleSupportFaqGroup extends Model {
		public function getFaqGroups() {
			$sql = $this->db->query("SELECT ssfg.*, ssfgd.name AS name FROM `" . DB_PREFIX . "simple_support_faq_group` ssfg LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON(ssfg.simple_support_faq_group_id=ssfgd.simple_support_faq_group_id) WHERE ssfg.status=1 ORDER BY ssfg.sort_order");
			
			return $sql->rows;
		}
		
		public function getFaqsGroupWise($simple_support_faq_group_id , $key='') {
			
			$sql = $this->db->query("SELECT ssf.*, ssfd.* FROM `" . DB_PREFIX . "simple_support_faq` ssf LEFT JOIN `" . DB_PREFIX . "simple_support_faq_description` ssfd ON(ssf.simple_support_faq_id=ssfd.simple_support_faq_id) LEFT JOIN `" . DB_PREFIX . "simple_support_faq_to_store` ssfs ON(ssf.simple_support_faq_id=ssfs.simple_support_faq_id) WHERE ssfd.language_id='" . (int)$this->config->get('config_language_id') . "' AND ssfs.store_id='" . (int)$this->config->get('config_store_id') . "' AND ssf.status=1 AND ssf.simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'  AND ( answer LIKE '%".$key."%' or  question LIKE '%".$key."%'  ) ORDER BY ssf.sort_order");
			
			return $sql->rows;			
		}	
		
		public function getFaqsWise(  $id='') {
			
			$sql = $this->db->query("SELECT ssf.*, ssfd.* FROM `" . DB_PREFIX . "simple_support_faq` ssf LEFT JOIN `" . DB_PREFIX . "simple_support_faq_description` ssfd ON(ssf.simple_support_faq_id=ssfd.simple_support_faq_id) LEFT JOIN `" . DB_PREFIX . "simple_support_faq_to_store` ssfs ON(ssf.simple_support_faq_id=ssfs.simple_support_faq_id) WHERE ssfd.language_id='" . (int)$this->config->get('config_language_id') . "' AND ssfs.store_id='" . (int)$this->config->get('config_store_id') . "' and ssf.simple_support_faq_id='".$id."' AND ssf.status=1    ORDER BY ssf.sort_order");
			
			return $sql->rows;			
		}
		
		public function getFaqGroup($simple_support_faq_group_id) {
			$sql = $this->db->query("SELECT ssfg.*, ssfgd.name AS name FROM `" . DB_PREFIX . "simple_support_faq_group` ssfg LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group_description` ssfgd ON(ssfg.simple_support_faq_group_id=ssfgd.simple_support_faq_group_id) WHERE ssfg.status=1 AND ssfg.simple_support_faq_group_id='" . (int)$simple_support_faq_group_id . "'");
			
			return $sql->row;
		}
		
		public function getFaqs($faq_group_id, $faq_search) {
			$sql = "SELECT ssf.*,ssfd.* FROM `" . DB_PREFIX . "simple_support_faq` ssf LEFT JOIN `" . DB_PREFIX . "simple_support_faq_description` ssfd ON(ssf.simple_support_faq_id=ssfd.simple_support_faq_id) LEFT JOIN `" . DB_PREFIX . "simple_support_faq_group` ssfg ON(ssf.simple_support_faq_group_id=ssfg.simple_support_faq_group_id) LEFT JOIN `" . DB_PREFIX . "simple_support_faq_to_store` ssfs ON(ssf.simple_support_faq_id=ssfs.simple_support_faq_id)  WHERE ssfd.language_id='" . (int)$this->config->get('config_language_id') . "' AND ssf.status=1 AND ssf.simple_support_faq_group_id='" . (int)$faq_group_id . "' AND ssfs.store_id='" . (int)$this->config->get('config_store_id') . "'";
			
			if(!empty($faq_search)) {
				$sql .= " AND LCASE(ssfd.question) LIKE '" . $this->db->escape(utf8_strtolower($faq_search)) . "%'";
			}
			
			$sql .= " ORDER BY ssf.sort_order";
			//echo $sql; exit;
			$query = $this->db->query($sql);
			
			return $query->rows;			
		}
		
	}
?>