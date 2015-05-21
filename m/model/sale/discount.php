<?php
class ModelSaleDiscount extends Model {
	public function addText($data) {
      	$this->db->query("INSERT INTO my_text SET text = '".$data['text']."' , link = '".$data['link']."'   ");
	}
	
	public function editText($data) {
      	$this->db->query("UPDATE my_text SET text = '".$data['text']."' , link = '".$data['link']."'  ");
	 
	}

	public function getLevel() {
      	$query = $this->db->query("select * from  my_customer_levels ");
		return $query->rows;
	}
	
	public function getTime() {
      	$query = $this->db->query("select * from  my_discount_time ");
		return $query->rows;
	}
	public function getVIP() {
      	$query = $this->db->query("SELECT vip FROM  my_customer WHERE vip>0 LIMIT 0,1 ");
		return $query->row['vip'];
	}
	public function getOtherDiscount() {
      	$query = $this->db->query("select * from  my_discount ");
		return $query->rows;
	}
	
	public function updateLevel($data) {
		foreach($data['id'] as $k => $v){
			$sql = "UPDATE my_customer_levels SET level_name='".$data['level_name'][$v-1]."', point_min = '".$data['point_min'][$v-1]."', point_max = '".$data['point_max'][$v-1]."', discount = '".$data['discount'][$v-1]."'  where  id='".($v)."' ";

			$this->db->query($sql);
		}
	 
	}
	public function updateTime($data) {
		$this->db->query("UPDATE my_discount SET value = '".$data['time_discount_status']."' where  name='time_discount_status' ");
		foreach($data['id'] as $k => $v){
			$this->db->query("UPDATE my_discount_time SET date_start = '".$data['date_start'][$v-1]."', date_end = '".$data['date_end'][$v-1]."', discount = '".$data['discount'][$v-1]."'  where  id='".($v)."' ");
		}
	}
	
	public function updateGlobal($data) {
		$this->db->query("UPDATE my_discount SET value = '".$data['global_discount_status']."' where  name='global_discount_status' ");
		$this->db->query("UPDATE my_discount SET value = '".$data['global_discount']."' where  name='global_discount' ");
	}
	public function updateVIP($data) {
		$this->db->query("UPDATE my_customer SET vip = '".$data['vip']."' where  vip>0 ");
	}


	public function updatePaysbuy($data) {
      	$this->db->query("UPDATE my_discount SET value = '".$data['paysbuy_discount']."' where  name='paysbuy_discount' ");
	 
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