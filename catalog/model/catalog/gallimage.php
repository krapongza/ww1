<?php
class ModelCatalogGallimage extends Model {	
	public function getGallName($gallimage_id){
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "gallimage where gallimage_id= " . $gallimage_id . " limit 1");
		return $query->row['name'];
	}

	public function getGallimage($gallimage_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallimage_image gi LEFT JOIN " . DB_PREFIX . "gallimage_image_description gid ON (gi.gallimage_image_id  = gid.gallimage_image_id) WHERE gi.gallimage_id = '" . (int)$gallimage_id . "' AND gid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY gi.sort_order ASC");
		
		return $query->rows;
	}
	
}
?>