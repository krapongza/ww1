<?php
class ModelCatalogManufacturer extends Model {
	public function addManufacturer($data) {
      	//$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' , catalog='" . (int)$data['catalog'] . "'  ");
      	$this->db->query("INSERT INTO my_manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' , address='".$data['address']."',city='".$data['city']."',country='".$data['country']."',zipcode='".$data['zipcode']."',website='".$data['website']."',tel='".$data['tel']."',email='".$data['email']."',skype='".$data['skype']."',reference='".$data['reference']."',reference_link='".$data['reference_link']."' , catalog='" . $data['catalog_id'] . "'  ");

		$manufacturer_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}
		
		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
				
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
		
		$this->cache->delete('manufacturer');
	}
	
	public function editManufacturer($manufacturer_id, $data) {
      	$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' , address='".$data['address']."',city='".$data['city']."',country='".$data['country']."',zipcode='".$data['zipcode']."',website='".$data['website']."',tel='".$data['tel']."',email='".$data['email']."',skype='".$data['skype']."',reference='".$data['reference']."',reference_link='".$data['reference_link']."' , catalog='" . $data['catalog_id'] . "'  WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
			
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id. "'");
		
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
		
		$this->cache->delete('manufacturer');
	}
	
	public function deleteManufacturer($manufacturer_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");
			
		$this->cache->delete('manufacturer');
	}	
	
	public function getManufacturer($manufacturer_id) {
		$sql ="SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "') AS keyword FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'";

		$query = $this->db->query($sql);
		
		return $query->row;
	}

	public function getCatalog() {
		$sql ="SELECT  manufacturer_id as id, name FROM my_manufacturer WHERE catalog ='PRIMARY' ";
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	public function getCatalogByID($id) {
		$sql ="SELECT  name FROM my_manufacturer WHERE manufacturer_id ='".$id."' ";
		$query = $this->db->query($sql);

		return $query->row['name'];
	}

	public function validateWWW($data , $data2='') {
		$sql ="SELECT  count(*) as total FROM my_manufacturer WHERE website like '%".$data['website']."%'  ";
		if(isset($data2)) $sql .= " and manufacturer_id <> '".$data2['manufacturer_id']."' ";
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getManufacturers($data = array()) {
		$sql = "SELECT * , (select mm.name from my_manufacturer as mm where m.catalog = mm.manufacturer_id and mm.catalog = 'PRIMARY' ) as mname FROM " . DB_PREFIX . "manufacturer as m";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'   ";
		} 
		if (!empty($data['primary'])) {
			$sql .= " and m.catalog = 'PRIMARY'   ";
		} 
		$sort_data = array(
			'mname',
			'sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY manufacturer_id";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " DESC";
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

	public function getNewManufacturers($data = array()) {
		$sql = "SELECT * , (select mm.name from my_manufacturer as mm where m.catalog = mm.manufacturer_id and mm.catalog = 'PRIMARY' ) as mname FROM " . DB_PREFIX . "manufacturer as m";

		if (!empty($data['filter_supplier'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_supplier']) . "%'   and catalog='PRIMARY'  ";
		} 
		
		if (!empty($data['filter_order'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_order']) . "%'   and catalog<>'PRIMARY'  ";
		} 

		$sort_data = array(
			'mname',
			'sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY manufacturer_id";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " DESC";
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


	
	public function getManufacturerStores($manufacturer_id) {
		$manufacturer_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}
		
		return $manufacturer_store_data;
	}
	
	public function getTotalManufacturersByImageId($image_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer WHERE image_id = '" . (int)$image_id . "'");

		return $query->row['total'];
	}

	public function getTotalManufacturers() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer");
		
		return $query->row['total'];
	}
	

	public function getNewTotalManufacturers($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer";

		$sql .= " WHERE manufacturer_id > 0 "; 

		if (!empty($data['filter_supplier'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_supplier']) . "%'  and catalog='PRIMARY' ";
		}

		if (!empty($data['filter_order'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_order']) . "%' and catalog <> 'PRIMARY' ";
		}
		
	
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}	


	public function updateNote($note,$manufacturer_id) {
		$query = $this->db->query("update my_manufacturer set note_icon='" . (int)$note . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

	}	

}
?>