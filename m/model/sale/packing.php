<?php
class ModelSalePacking extends Model {
	public function getOrder($data) {
		$sql = "select order_id,customer_id,shipping_method ,CONCAT(payment_firstname ,' ', payment_lastname) AS send_to , CONCAT(payment_address_1 ,' ', payment_address_2 , '' ,payment_city) AS address ,payment_postcode from my_order where order_id= '$data' ";
		$query = $this->db->query($sql); 
		return $query->rows;
	}
	public function getName($data) {
		$sql = "select CONCAT(payment_firstname ,' ', payment_lastname) AS send_to from my_order where order_id= '$data' ";
		$query = $this->db->query($sql); 
		return $query->row['send_to'];
	}

	public function getPackingList($date , $type) {
		$sql = "select max(num) as num from my_packing_list where ymd=$date and type='$type' ";
		$query = $this->db->query($sql); 
		return $query->row['num'];
	}
 
	public function addPacking($order_id ,$date , $type , $num , $weight , $weight_bath , $name , $destination , $track_no ) {
		$sql = "insert into my_packing_list set order_id=$order_id , name='$name' , destination='$destination' , track_no='$track_no' ,  weight='$weight' , weight_bath='$weight_bath' , ymd='$date' , type='$type' , num='$num'  ";
		$query = $this->db->query($sql); 
		return $query;
	}
 
	public function deletePacking($order_id ) {
		$sql = "delete from my_packing_list where id = '$order_id' ";
		$query = $this->db->query($sql); 
		return $query;
	}

	public function updateOrderPacking($order_id , $weight , $weight_bath , $tack_code , $track_submit ) {
		$sql = "update my_order set post_weight='$weight' , post_weight_bath='$weight_bath' , tack_code='$tack_code' , track_submit='$track_submit' , order_status_id='6'  where order_id='$order_id' ";
		$query = $this->db->query($sql); 
		return $query;
	}

	public function packing_print( $type , $ymd , $viewtype , $start , $end ) {
		if($start != 0 and $end != 0)
			$sql = sprintf(" LIMIT %d,%d", (int)$start - 1, ((int)$end - (int)$start) + 1);
		else
			$sql = '';
	
		$sql1 = "select * from my_packing_list where type = '$type' and ymd = '$ymd' order by num asc ";
		$query = $this->db->query($sql1); 
		$res = $query->rows;
		
		//print_r($res);
		//echo "<br>";
		$sql2 = "select sum(weight_bath) bath,sum(weight) w from (select * from my_packing_list where type = '$type' and ymd = '$ymd' order by num asc ".$sql." ) as subquery";
		$query = $this->db->query($sql2); 
		$sum = $query->row;
	
		$sql3 = "select count(id) total from (select * from my_packing_list where type = '$type' and ymd = '$ymd' and weight_bath <> '' order by num asc ".$sql." ) as subquery";
		$query = $this->db->query($sql3); 
		$total = $query->row['total'];

		$sum['total'] = $total;
		//merge packing
		if ($viewtype == 'hide'){
			$lists = array();$pre = '';$k=1;
			for ($i=0;$i<count($res);$i++){
				if ($res[$i]['track_no'] == $pre){
					$res[$i-$k]['order_id'] .= ','.$res[$i]['order_id'];
					$res[$i-$k]['num'] .= ','.$res[$i]['num'];
					$res[$i]['merge'] = 'merge';
					$k++;
				}else
					$k=1;
				$pre = $res[$i]['track_no'];
			}
		}

		return array($res , $sum);

	}

	public function testupdate( ) {
		$sql = "update my_order set post_weight=1   where order_id=115 ";
		$query = $this->db->query($sql); 
		print_r($query);
	}



}
?>