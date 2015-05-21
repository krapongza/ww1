<?php
class ModelCatalogText extends Model {
	public function addText($data) {
      	$this->db->query("INSERT INTO my_text SET text = '".$data['text']."' , link = '".$data['link']."'   ");
	}
	
	public function editText($data) {
      	$this->db->query("UPDATE my_text SET text = '".$data['text']."' , link = '".$data['link']."'  ");
	 
	}
	
	public function updateStstus($data) {
      	$this->db->query("UPDATE my_text SET enable = '".$data['enable']."'  ");
	 
	}

	public function deleteText() {
		$this->db->query("DELETE FROM my_text ");
			
		//$this->cache->delete('manufacturer');
	}	
	
	public function getText() {
		$query = $this->db->query("SELECT  *  FROM my_text ") ;
		
		return $query->rows;
	}

}
?>