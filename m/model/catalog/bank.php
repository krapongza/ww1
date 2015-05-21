<?php
class ModelCatalogBank extends Model {
	public function addBank($data) {
      	$this->db->query("INSERT INTO my_bank SET bankname = '".$data['name']."', bankcode = '" . $data['code'] . "', bank_id='".$data['account']."', bank_type='".$data['type']."', sub_bank='".$data['branch']."',created_date=NOW() , modified_date=NOW() ");
	}
	
	public function editBank($id, $data) {
      	$this->db->query("UPDATE my_bank SET bankname = '".$data['name']."', bankcode = '" . $data['code'] . "', bank_id='".$data['account']."', bank_type='".$data['type']."', sub_bank='".$data['branch']."', modified_date=NOW() WHERE id = '" . (int)$id . "'");
	 
	}
	
	public function deleteBank($id) {
		$this->db->query("DELETE FROM my_bank WHERE id = '" . (int)$id . "'");
			
		//$this->cache->delete('manufacturer');
	}	
	
	public function getBank($id) {
		$query = $this->db->query("SELECT  *  FROM my_bank WHERE id = '" . (int)$id . "'") ;
		
		return $query->row;
	}
	
	public function getBanks() {
		$query = $this->db->query("SELECT  *  FROM my_bank ") ;
		
		return $query->rows;
	}


	public function updateNote($note,$manufacturer_id) {
		$query = $this->db->query("update my_bank set note_icon='" . (int)$note . "' WHERE id = '" . (int)$manufacturer_id . "'");

	}	

}
?>