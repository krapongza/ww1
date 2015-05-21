<?php
class ModelCatalogText extends Model {		
 

	public function getText() {
		$query = $this->db->query("SELECT link,text from my_text WHERE  enable ='1' ");
	
		echo "<p><a href='".$query->row['link']."'>".$query->row['text']."</a></p>";
	}
}
?>