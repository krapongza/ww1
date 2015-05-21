<?php
class ModelAccountGallery extends Model {
 
	public function getGalleryList(){
		$address_data = array();

		$querys = $this->db->query("SELECT g.gallimage_id as id, g.name, g.status ,  gi.image FROM my_gallimage AS g INNER JOIN my_gallimage_image AS gi ON g.gallimage_id = gi.gallimage_id  INNER JOIN my_gallimage_image_description AS gid ON  gi.gallimage_image_id = gid.gallimage_image_id GROUP BY g.gallimage_id  ");

		foreach($querys->rows as $result){
			$address_data[$result['id']] = array(
				'id'			=> $result['id'],
				'name'			=> $result['name'],
				'status'        => $result['status'] ,
				'image'			=> $result['image']
			);
		}
		//print_r($address_data);
		return $address_data;
	}


	public function getGallery($id){
		$address_data = array();

		$querys = $this->db->query("SELECT g.gallimage_id as id , g.name, g.status , gi.link , gi.image , gid.title FROM my_gallimage AS g INNER JOIN my_gallimage_image AS gi ON g.gallimage_id = gi.gallimage_id INNER JOIN my_gallimage_image_description AS gid ON  gi.gallimage_image_id = gid.gallimage_image_id WHERE g.gallimage_id = '".$id."'  ");

		return $querys->rows;
	}

	public function getGalleryHome(){


		$querys = $this->db->query("SELECT g.gallimage_id as id , g.name, g.status , gi.link , gi.image , gid.title FROM my_gallimage AS g INNER JOIN my_gallimage_image AS gi ON g.gallimage_id = gi.gallimage_id INNER JOIN my_gallimage_image_description AS gid ON  gi.gallimage_image_id = gid.gallimage_image_id order by id desc");

		return $querys->rows;
	}

 
}
?>