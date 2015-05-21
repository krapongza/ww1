<?php
class ModelCatalogNote extends Model {
	public function addNote($data) {
		$this->db->query("INSERT INTO my_note SET type_note = '" . $data['type_note'] . "', type_id = '" . $data['type_id'] . "', detail = '" . $data['detail'] . "', flag = '" . (int)$data['flag'] . "', user_id = '" . $this->user->getId() . "', username = '" . $this->user->getUserName() . "', created = NOW()");

	}
	
	
	public function deleteNote($cat, $data) {
		$sql = "DELETE FROM my_note WHERE type_note = '" . $cat . "' and id = '" . $data['del'] . "' and user_id = '" . $this->user->getId() . "' ";
		
		$this->db->query($sql);

	} 

	public function getNote($cat, $category_id) {
		$sql = "select * FROM my_note WHERE type_note = '" . $cat . "' and type_id = '" . $category_id . "' and user_id = '" . $this->user->getId() . "'   ";

		$query = $this->db->query($sql);
		return $query->rows;
	} 



}
?>