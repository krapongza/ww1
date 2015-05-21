<?php
class ModelAccountBank extends Model {
 
	public function getBank(){
		$address_data = array();

		$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bank`  ");

		foreach($zone_query as $tmpresult){
			if(isset($tmpresult[0])){
				//print_r($tmpresult);
				foreach($tmpresult as $result){
					$address_data[$result['id']] = array(
						'id'     => $result['id'],
						'bankname'       => $result['bankname'],
						'bankcode'        => $result['bankcode'] 
					);
				}
			}

		}
		//print_r($address_data);
		return $address_data;
	}
 
}
?>