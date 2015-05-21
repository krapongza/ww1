<?php
class ModelCatalogGallimage extends Model {
	public function addGallimage($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "gallimage SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "'");

		$gallimage_id = $this->db->getLastId();

		if (isset($data['gallimage_image'])) {
			foreach ($data['gallimage_image'] as $gallimage_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "gallimage_image SET gallimage_id = '" . (int)$gallimage_id . "', link = '" .  $this->db->escape($gallimage_image['link']) . "', image = '" .  $this->db->escape($gallimage_image['image']) . "', sort_order = '" . (int)$gallimage_image['sort_order'] . "'");

				$gallimage_image_id = $this->db->getLastId();

				foreach ($gallimage_image['gallimage_image_description'] as $language_id => $gallimage_image_description) {				
					$this->db->query("INSERT INTO " . DB_PREFIX . "gallimage_image_description SET gallimage_image_id = '" . (int)$gallimage_image_id . "', language_id = '" . (int)$language_id . "', gallimage_id = '" . (int)$gallimage_id . "', title = '" .  $this->db->escape($gallimage_image_description['title']) . "'");
				}
			}
		}		
	}

	public function editGallimage($gallimage_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "gallimage SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "' WHERE gallimage_id = '" . (int)$gallimage_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "gallimage_image WHERE gallimage_id = '" . (int)$gallimage_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallimage_image_description WHERE gallimage_id = '" . (int)$gallimage_id . "'");

		if (isset($data['gallimage_image'])) {
			foreach ($data['gallimage_image'] as $gallimage_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "gallimage_image SET gallimage_id = '" . (int)$gallimage_id . "', link = '" .  $this->db->escape($gallimage_image['link']) . "', image = '" .  $this->db->escape($gallimage_image['image']) . "', sort_order = '" . (int)$gallimage_image['sort_order'] . "'");

				$gallimage_image_id = $this->db->getLastId();

				foreach ($gallimage_image['gallimage_image_description'] as $language_id => $gallimage_image_description) {				
					$this->db->query("INSERT INTO " . DB_PREFIX . "gallimage_image_description SET gallimage_image_id = '" . (int)$gallimage_image_id . "', language_id = '" . (int)$language_id . "', gallimage_id = '" . (int)$gallimage_id . "', title = '" .  $this->db->escape($gallimage_image_description['title']) . "'");
				}
			}
		}			
	}

	public function deleteGallimage($gallimage_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallimage WHERE gallimage_id = '" . (int)$gallimage_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallimage_image WHERE gallimage_id = '" . (int)$gallimage_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallimage_image_description WHERE gallimage_id = '" . (int)$gallimage_id . "'");
	}

	public function getGallimage($gallimage_id) {			
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "gallimage WHERE gallimage_id = '" . (int)$gallimage_id . "'");

		return $query->row;
	}

	public function getGallimages($data = array()) {
		
		
		$sql = "SELECT * FROM " . DB_PREFIX . "gallimage";

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

	public function getGallimageImages($gallimage_id) {
		$gallimage_image_data = array();
		
		$gallimage_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallimage_image WHERE gallimage_id = '" . (int)$gallimage_id . "' ORDER BY sort_order ASC");

		foreach ($gallimage_image_query->rows as $gallimage_image) {
			$gallimage_image_description_data = array();

			$gallimage_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallimage_image_description WHERE gallimage_image_id = '" . (int)$gallimage_image['gallimage_image_id'] . "' AND gallimage_id = '" . (int)$gallimage_id . "'");

			foreach ($gallimage_image_description_query->rows as $gallimage_image_description) {			
				$gallimage_image_description_data[$gallimage_image_description['language_id']] = array('title' => $gallimage_image_description['title']);
			}

			$gallimage_image_data[] = array(
				'gallimage_image_description' => $gallimage_image_description_data,
				'link'                     => $gallimage_image['link'],
				'image'                    => $gallimage_image['image'],
				'sort_order'               => $gallimage_image['sort_order']	
			);
		}

		return $gallimage_image_data;
	}

	public function getTotalGallimages() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "gallimage");

		return $query->row['total'];
	}	
	
}
?>