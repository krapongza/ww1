<?php
class ModelAccountReturn extends Model {
	public function addReturn($data) {			      	
		$this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$this->customer->getId() . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_status_id = '" . (int)$this->config->get('config_return_status_id') . "', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");
	}
	
	public function addReturnOrder($data , $product , $customer ) {	
		foreach($product as $key => $val ){
			$key = str_replace("amp;", "", $key);
		}

		$this->db->query("INSERT INTO `my_return` SET order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$this->customer->getId() . "', firstname = '" . $this->db->escape($customer['firstname']) . "', lastname = '" . $this->db->escape($customer['lastname']) . "', email = '" . $this->db->escape($customer['email']) . "', telephone = '" . $this->db->escape($customer['telephone']) . "', product = '', model = '', quantity = '', opened = '0', return_reason_id = '" . $this->db->escape($data['received']) . "', return_status_id = '1', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");

		$query = $this->db->query("SELECT return_id FROM `" . DB_PREFIX . "return` r  WHERE order_id = '" . (int)$data['order_id'] . "' ");

		//print_r($query->row['return_id']);
		return $query->row['return_id'];
	}
	public function addRollbackOrder($data  , $bank) {
		$year = $_POST['mydateYear'] -543;
		$txndate = $year."-".$data['mydateMonth']."-".$data['mydateDay']." ".$data['mytimeHour'].":".$data['mytimeMinute'].":".$data['mytimeSecond'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "rollback_transfer` SET   user_id = '" . (int)$this->customer->getId() . "', username = '" . $this->customer->getFirstName() . " ". $this->customer->getLastName() . "', bank_name = '" . $this->db->escape($bank['bankname']) . "', bank_account = '" . $this->db->escape($bank['bank_id']) . "', bank = '" . $this->db->escape($bank['bankname']) . "', date_transfer = '" . $txndate . "', amount = '" . $this->db->escape($data['money']) . "', message = '" . $this->db->escape($data['message']) . "', status =  'pending', created=NOW(), modify=NOW(), cashback_type='" . $this->db->escape($data['txn']) . "' ");

		$query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "rollback_transfer`   WHERE user_id = '" . (int)$this->customer->getId() . "' order by id desc limit 0,1 ");

		//print_r($query->row['return_id']);
		return $query->row['id'];
	}

	public function addReturnOrderProduct($data, $id , $product) {	
		//print_r($product);echo "<Br><br>";
		$pps = $data['pp'];
		print_r($pps);
		foreach($product as $key => $val ){
			$key = str_replace("amp;", "", $key);
			$pp = 0;
			foreach($pps as $key2 => $val2){
				$pp = $pps['amp;price_'.$key];
			}
			
			if(!(substr($key,0,3) == "opt")){
				if($val > 0){
					$option = $product['amp;option_'.$key]; // pack('H*', $product['amp;option_'.$key] );
					//echo $key." ".$option." | "."<br>";
					
					$sql = "INSERT INTO `my_rollback_item` SET rollback_id = '" . (int)$id . "', order_id='" . (int)$data['order_id'] . "', product_id = '" . (int)$key . "', unit_price = '$pp', return_qty = '" . (int)$val . "', pass_qty = '0' , `option`='$option' , createdate = NOW() ";
					$this->db->query($sql);
					//echo $sql."<br>";
				}
			}
		}
	}
	public function addCancelOrder( $data,   $bank='' ) {	
	 
		$year = $_POST['mydateYear'] -543;
		$txndate = $year."-".$data['mydateMonth']."-".$data['mydateDay']." ".$data['mytimeHour'].":".$data['mytimeMinute'].":".$data['mytimeSecond'];

			$this->db->query("INSERT INTO `" . DB_PREFIX . "rollback_cancel` SET bank_name = '" . $bank['bankname'] . "', bank_account='" . $bank['bank_id'] . "', bank = '" . $bank['bankname'] . "', order_id = '" . (int)$data['order_id'] . "' , date_transfer = '" . $txndate . "' , amount = '" . (int)$data['money'] . "', message = '" . (int)$data['transfer_text'] . "' , status='pending' , created=NOW() , modify=NOW() , user_id='" . (int)$this->customer->getId() . "' , username='" . $this->customer->getFirstName() ." ". $this->customer->getLastName() . "', cashback_type='" . $this->db->escape($data['txn']) . "'  ");
 

 
	}



	function setOrderSendingDeadline($id){
		//status 10=sending_deadline
		$query = $this->db->query( "update my_return set return_status_id = '10' where return_id ='$id' ");
	}
	function setCancelSending($id,$post){
		//status 3=sending
		$this->addBacklog($id,'order','sending','insert track ems : '.$post['track_ems']);

		$sql = "update my_return set return_status_id='3' , track_ems='".$post['track_ems']."' , date_modified=NOW() where return_id ='$id'   ";
		$query = $this->db->query($sql);
	}
	function setDoneOrder($id){
		//status 8=done
		$this->addBacklog($id,'order','done','set done');

		$sql = "update my_return set return_status_id='8' , date_modified=NOW() where return_id ='$id'   ";
		$query = $this->db->query($sql);
	}


	public function addBacklog($id,$type,$status ,$msg='') {
      	$sql = "insert into   my_rollback_log set `rollback_id`='".$id."' , `rollback_type`='".$type."' , `status`='".$status."' , `message`='".$msg."' , `user_id`='".$this->user->getId()."' , `username`='".$this->customer->getId()."' , `created`=NOW() ";
		$query = $this->db->query($sql);
	}
	public function getRefundDetailSuccess($data , $type='order') {
      	$sql = "SELECT * FROM my_rollback_transfer_success as r where r.rollback_id='".$data."' and type='".$type."' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getReturn($return_id) {
		$sql = "SELECT ri.unit_price, ri.return_qty, ri.pass_qty, r.cal_total, r.cal_discount_user, r.cal_discount_coupon, r.cal_fee, r.cal_total_return, r.track_ems, r.customer_id, r.return_reason_id   , r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM my_return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '1') AS reason, (SELECT ra.name FROM my_return_action ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = '1') AS action, (SELECT rs.name FROM my_return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '1') AS status, r.comment, r.date_ordered, r.date_added, r.date_modified FROM `my_return` r left join my_rollback_item as ri on ri.rollback_id = r.return_id  WHERE return_id = '" . (int)$return_id . "' ";
		//echo $sql;
		$query = $this->db->query($sql);
		//print_r($query->row);
		return $query->row;
	}
	public function getRollbackOrdersDetail($return_id) {
		$sql = "select * , ri.product_id as pid from my_rollback_item as ri left join my_return as r on  ri.rollback_id = r.return_id LEFT JOIN my_return_status AS rs ON r.return_status_id = rs.return_status_id where rollback_id='$return_id' ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getReturns($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}	
				
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added FROM `" . DB_PREFIX . "return` r LEFT JOIN " . DB_PREFIX . "return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . $this->customer->getId() . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . "," . (int)$limit);
		
		return $query->rows;
	}

	public function getRollbackByAdmin($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}	
				
		$query = $this->db->query("SELECT id, bank_name, bank_account, bank, date_transfer, amount, message, status, modify, username, user_id FROM `" . DB_PREFIX . "rollback_transfer`    WHERE user_id = '" . $this->customer->getId() . "'  ORDER BY id DESC LIMIT " . (int)$start . "," . (int)$limit);
		
		return $query->rows;
	}

	public function getRollbackDetail($id='') {
				
		$query = $this->db->query("SELECT id, bank_name, bank_account, bank, date_transfer, amount, message, status, modify, username, user_id , cashback_type FROM `" . DB_PREFIX . "rollback_transfer`    WHERE id = '" . $id . "'  and user_id = '" . $this->customer->getId() . "'   " );
		
		return $query->rows;
	}
	public function getTotalRollbackDetail($id='') {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "rollback_transfer` WHERE id = '" . $id . "'");
		
		return $query->row['total'];
	}


	public function getRefundsNew($start = 0, $limit = 20 , $orderid='') {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}	
	 
		if( strlen($orderid) > 0 ){
 
			$sql = "SELECT o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_id='" . $orderid . "' and o.order_status_id IN ('7')    ";
			
		}else{
		
			$sql = " select o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_status_id IN ('7')   AND DATEDIFF(NOW() , o.date_added   ) < 10  and o.order_id not in (select order_id from my_rollback_cancel )  ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit ;
			 
		}
		//echo $sql; //strlen($orderid);
		$query = $this->db->query($sql);	

		return $query->rows;
	}

	public function getRefundsCount() {

		$sql = "SELECT COUNT(o.order_id) AS counts , o.order_id  FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id  WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_status_id IN ('7')   AND DATEDIFF(NOW() , o.date_added   ) < 10  GROUP BY o.order_id ORDER BY o.order_id DESC   "  ;

		$query = $this->db->query($sql);	

		return $query->rows;
	}



	public function thisOrderCanced($order) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM my_rollback_cancel WHERE order_id = '" . $order . "'");
		
		return $query->row['total'];
	}



	function getRollbackCancelList(){
		$query = $this->db->query("select * from my_rollback_cancel where user_id = '" . $this->customer->getId() . "' order by id desc ");

		return $query->rows;
	}

	function getRefundViewByID($id){
		$query = $this->db->query("select * from my_rollback_cancel where order_id = '" . $id . "'");

		return $query->rows;
	}

	function adminSetStatusCancel($id,$status,$post=''){

		if ($post and $status == 'transfer_success'){  // for Admin insert to transfer_success
			//$this->insertTransferSuccess($id,$post,'cancel');
		}else if ($post and $status == 'disapprovel'){
			//$array['note'] = $post['note'];
		}

		$this->db->query("update `" . DB_PREFIX . "rollback_cancel` SET status = '". $status ."' WHERE order_id = '". $id ."' ");
	}


	public function getReturnOption( $orderid='' , $product='' , $value='') {
		//Order_id , Product_id , Color_Name
		$q = $this->db->query("SELECT op.order_product_id FROM  my_order_product AS op  INNER JOIN my_order_option AS oo ON  oo.order_product_id =op.order_product_id   WHERE op.order_id='$orderid'   AND op.product_id='$product' AND oo.name='Color' AND oo.value = '".$value."'   ");
		$order_product_id = $q->row['order_product_id'];

		$sql = "SELECT oo.value FROM  my_order_product AS op  INNER JOIN my_order_option AS oo ON  oo.order_product_id =op.order_product_id   WHERE op.order_id='$orderid'   AND op.product_id='$product' and op.order_product_id='$order_product_id'  ";
		$query = $this->db->query($sql);
		$value = $query->rows;
		$v="";
		foreach($value as $tk=>$tv){
			$v = $v." ".implode(" ", $tv);
		}
		 
		return $v;
	}

	public function getReturnsNewIndex($start = 0, $limit = 20 , $orderid='') { 
		//(`status` = 'done' or `status` = 'received')  6=done  7=received
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}	
	 
		if( strlen($orderid) > 0 ){
 
			$sql = "SELECT o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id ,oo.name , oo.value FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id INNER JOIN my_order_option AS oo ON oo.order_id = o.order_id AND oo.order_product_id =op.order_product_id WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_id='" . $orderid . "' and o.order_status_id IN ('6','7')   AND DATEDIFF(NOW() , o.date_added   ) <= 10 AND oo.name='Color' ";
			
		}else{
		
			$sql = "SELECT o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id ,oo.name , oo.value FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id INNER JOIN my_order_option AS oo ON oo.order_id = o.order_id AND oo.order_product_id =op.order_product_id WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_status_id IN ('6','7')   AND DATEDIFF(NOW() , o.date_added   ) <= 10  and o.order_id not in (select order_id from my_rollback_item ) AND oo.name='Color'  ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit ;
			 
		}
		//echo $sql;
		$query = $this->db->query($sql);	

		return $query->rows;
	}

	public function getReturnsNew($start = 0, $limit = 20 , $orderid='') {
		if ($start < 0) {$start = 0;}
		if ($limit < 1) {$limit = 20;}	
		//(`status` = 'done' or `status` = 'received')  6=done  7=received

		if( strlen($orderid) > 0 ){
			$sql = "SELECT o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id , oo.value  FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id INNER JOIN my_order_option AS oo ON oo.order_id = o.order_id AND oo.order_product_id =op.order_product_id  WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_id='" . $orderid . "' and o.order_status_id IN ('6','7')   AND DATEDIFF(NOW() , o.date_added   ) <= 10 AND oo.name='Color' ";
		}else{
			$sql = "SELECT o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id , oo.value  FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id INNER JOIN my_order_option AS oo ON oo.order_id = o.order_id AND oo.order_product_id =op.order_product_id WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_status_id IN ('6','7')   AND DATEDIFF(NOW() , o.date_added   ) <= 10 AND oo.name='Color'  ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit ;
		}
		//echo $sql;
		$query = $this->db->query($sql);	

		return $query->rows;
	}
	

	public function getReturnsProduct($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}	

	
		$sql = "SELECT r.return_id , r.firstname , r.lastname , rs.name as status, r.date_added , o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id INNER JOIN my_return AS r ON r.order_id = o.order_id INNER JOIN my_return_status AS rs ON r.return_status_id = rs.return_status_id WHERE o.customer_id = '" . $this->customer->getId() . "'  and o.order_status_id IN ('3','5','18')   AND DATEDIFF(NOW() , o.date_added   ) <= 10  ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit ;
		//echo $sql;

		$query = $this->db->query($sql);	

		return $query->rows;
	}

	public function getRollbackOrders($start = 0, $limit = 20) {
		$sql = "SELECT * FROM my_return as o left join my_rollback_item as ri on ri.rollback_id = o.return_id left join my_customer as c on c.customer_id = o.customer_id left join my_bank as b on b.id = c.bank_name GROUP BY o.return_id   order by o.return_id desc LIMIT " . (int)$start . "," . (int)$limit ;

		//echo $sql;
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getTotalRollbackOrders() {
		$sql = "SELECT count(*) as total FROM my_return as o left join my_rollback_item as ri on ri.rollback_id = o.return_id  LEFT JOIN my_customer AS c ON c.customer_id = o.customer_id LEFT JOIN my_bank AS b ON b.id = c.bank_name GROUP BY o.return_id   ";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	public function getTotalReturnsIndex() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . $this->customer->getId() . "'  AND DATEDIFF(NOW() , date_added   ) <= 10  and order_id not in (select order_id from my_rollback_item ) ");
		
		return $query->row['total'];
	}

	public function getTotalReturns() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return` WHERE customer_id = '" . $this->customer->getId() . "' and order_id not in (select order_id from my_rollback_cancel ) ");
		
		return $query->row['total'];
	}

	public function getTotalRollbackByAdmin() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "rollback_transfer` WHERE user_id = '" . $this->customer->getId() . "'");
		
		return $query->row['total'];
	}

			
	public function thisOrderClaimed($order) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM my_return WHERE order_id = '" . $order . "'");
		
		return $query->row['total'];
	}

	public function returnordertotal($order){
			$query = $this->db->query("SELECT ot.* FROM  my_order AS o INNER JOIN my_order_total AS ot ON  o.order_id = ot.order_id WHERE o.order_id ='" . $order . "'");

			return $query->rows;
	}

	public function returnorderOption($order){
			$query = $this->db->query("SELECT ot.* FROM  my_order AS o INNER JOIN my_order_option AS ot ON  o.order_id = ot.order_id WHERE o.order_id ='" . $order . "'");

			return $query->rows;
	}
	
	public function getReturnHistories($return_id) {
		$query = $this->db->query("SELECT rh.date_added, rs.name AS status, rh.comment, rh.notify FROM my_return_history rh LEFT JOIN my_return_status rs ON rh.return_status_id = rs.return_status_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '1' ORDER BY rh.date_added ASC");

		return $query->rows;
	}	
	

	public function getBank() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bank   ORDER BY id ASC");

		return $query->rows;
	}	
 
	public function getBankAccount($id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bank   where id='$id' ");

		return $query->rows;
	}	
 

	public function saveImageDB($order_id , $product_id , $product_option ,  $fullpath ) {

		$option = pack('H*', $product_option);
		$username = $this->customer->getFirstName(). " " . $this->customer->getLastName();
		$query = $this->db->query("INSERT INTO `" . DB_PREFIX . "return_image` SET  fullpath = '" . $fullpath . "', user_id = '" . (int)$this->customer->getId() . "', username = '" . $username . "',  order_id = '" . $order_id . "' ,product_id = '" . $product_id . "' , `option` ='$option' , created = NOW()  ");

		$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "return_image   order by id desc ");

		return $query->row['id'];
		//print_r($query);
	}

	public function deleteImg($id ) {
		$id = $this->db->query("DELETE FROM " . DB_PREFIX . "return_image WHERE id = '" . (int)$id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
 
		if($id) 
			return true;
		else 
			return false;
	}

}
?>