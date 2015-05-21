<?php
class ModelCatalogProduct extends Model {
	public function addProduct($data) {
		//5=arrival
		if((int)$data['stock_status_id'] == 5){
			$master_price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3'] + $data['costz'] );
			$price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3'] + $data['costz']  );
		}else{
			$master_price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3']  );
			$price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3']    );
		}
		

 		$size_info = array();
		if ($data['size_info_f1'] != '')  $size_info[] = $data['size_info_f1'].':'.$data['size_info_v1'];
		if ($data['size_info_f2'] != '')  $size_info[] = $data['size_info_f2'].':'.$data['size_info_v2'];
		if ($data['size_info_f3'] != '')  $size_info[] = $data['size_info_f3'].':'.$data['size_info_v3'];
		if ($data['size_info_f4'] != '')  $size_info[] = $data['size_info_f4'].':'.$data['size_info_v4'];
		if ($data['size_info_f5'] != '')  $size_info[] = $data['size_info_f5'].':'.$data['size_info_v5'];
		if ($data['size_info_f6'] != '')  $size_info[] = $data['size_info_f6'].':'.$data['size_info_v6'];
		$size_info = implode(',',$size_info);

		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$price . "',a1='".(float)$data['a1']."',a2='".(float)$data['a2']."',a3='".(float)$data['a3']."',a4='', points = '" . (int)$data['points'] . "', size_info='" . $size_info . "', ch_price='".$ch_price."',ch_discount='".$ch_discount."',  weight = '" . (float)($data['weight']/1000) . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "', sort_order = '" . (int)$data['sort_order'] . "',force_send='".$data['force_send']."', date_added = NOW()");
		
		$product_id = $this->db->getLastId();
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}
		
		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
					
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {				
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}
	
		/*
		P1 L red	5
		P2 L yellow 2
		P3 S red	1
		L		7
		S		1
		red		6
		yellow	2
		1. insert my_product_option
		$option_id = $this->db->getLastId();
		2. insert my_product_option_value
		$option_value_id = $this->db->getLastId();
		*/
		//Preorder && force_sold_out
		$forcestock = ( ($data['stock_status_id'] ==3 )&& ($data['force_sold_out'] == 1)  ) ? 0 : 1;
		
		$array_color	= array();
		$array_size		= array();
		$order_item = 0;
		$total_item = 0;
		$grandtotal = 0;
		//----Size and Colour:----
		if (isset($data['product_option'])) { 

			//**********************************************************************
			//use for option=2 only [color]
			//$price = Price - product_option[$option_row][product_option_value][$option_value_row][price]
			//if $price > 0 $prefix = "+" else $prefix = "-" 
			//**********************************************************************
			if((int)$data['stock_status_id'] == 5){
				$master_price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3'] + $data['costz'] );
				$price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3'] + $data['costz']  );
			}else{
				$master_price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3']  );
				$price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3']    );
			}
			//$tmp_status = array( 'PreOrder_received' , 'add/update' , 'pre_add_or_received');
			$tmp_status = array( 'PreOrder_received' , 'add' , 'add');
			$grandtotal = $this->adjustProductOptionItem( $data['product_option'] , $forcestock , $array_color,$array_size ,$order_item ,$total_item,$grandtotal ,$product_id , $master_price ,$price ,$data['stock_status_id'] ,  $tmp_status );

		}

		$prestmt = $this->getPreorderStmt($data['stock_status_id'],$tmp_status,$grandtotal);
		$this->db->query("UPDATE my_product SET quantity = '" . (int)$grandtotal . "' $prestmt WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
		
		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
		
		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}
		
		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}
		
		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
						
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
						
		$this->cache->delete('product');
	}
	

	public function getPreorderStmt($stock_status_id,$tmp_status,$qty){
		$prestmt = "";
		if($stock_status_id == 3){ //status 3=preorder , 5=arrival
			//Add or Received PreOrder-Product from Supplier
			$stmt	 = ($tmp_status[2] == 'add') ? " , preorder = $qty  " : " , preorder_received = $qty  ";
			//Add or Edit Product
			$prestmt = ($tmp_status[1] == 'add') ? " , preorder = $qty  "  : $stmt;
		}
		return $prestmt;
	}
	public function adjustProductOptionItem( $product_options , $forcestock ,$array_color,$array_size ,$order_item ,$total_item,$grandtotal , $product_id , $master_price ,$price , $stock_status_id , $tmp_status  ){

		foreach( $product_options as $product_option ){
			foreach( $product_option['product_option_value'] as $product_option_value ){
				
				$optionimage = (isset($product_option_value['optionimage'])) ? $product_option_value['optionimage'] : '';
				//Preorder + force out of stock

				if($forcestock){
					if( array_key_exists( $product_option_value['property_2'], $array_color) ){
						$total_item = $total_item + $product_option_value['quantity'] + $product_option_value['add'];
						$grandtotal += $product_option_value['quantity'] + $product_option_value['add'];
						//echo $total_item."|1<br>";
						$array_color[ $product_option_value['property_2'] ][3] = $array_color[ $product_option_value['property_2'] ][3] + $product_option_value['quantity'] + $product_option_value['add'];
					}else{
						$total_item = $product_option_value['quantity'] + $product_option_value['add'];
						$grandtotal += $product_option_value['quantity'] + $product_option_value['add'];
						//echo $total_item."|2<br>";
						$array_color[ $product_option_value['property_2'] ] = array( $product_id , 2, $product_option_value['property_2'] ,$total_item , $optionimage , $product_option_value['price']);
					}

					if( array_key_exists( $product_option_value['property_1'], $array_size) ){
						$array_size[ $product_option_value['property_1'] ][3] = $array_size[ $product_option_value['property_1'] ][3] + $product_option_value['quantity'] + $product_option_value['add'];
					}else{
						$array_size[ $product_option_value['property_1'] ] = array( $product_id , 11, $product_option_value['property_1'] ,$product_option_value['quantity'] + $product_option_value['add'] , $optionimage , $product_option_value['price']);
					}
				}else{
					if( array_key_exists( $product_option_value['property_2'], $array_color) ){
						$array_color[ $product_option_value['property_2'] ][3] = 0;
					}else{
						$array_color[ $product_option_value['property_2'] ] = array( $product_id , 2, $product_option_value['property_2'] ,0 , $optionimage , $product_option_value['price']);
					}

					if( array_key_exists( $product_option_value['property_1'], $array_size) ){
						$array_size[ $product_option_value['property_1'] ][3] = 0;
					}else{
						$array_size[ $product_option_value['property_1'] ] = array( $product_id , 11, $product_option_value['property_1'] ,0 , $optionimage , $product_option_value['price']);
					}
					$total_item = 0;
				}
			}
		}
/*echo "<br><br>";
print_r($product_options);
echo "<br><br>";
print_r($array_color);echo "<br><br>";
print_r($array_size);echo "<br><br>";
echo $total_item ."<br><br>";
echo $grandtotal."<br><br>";
echo $product_id."<br><br>";*/

		$this->db->query("DELETE FROM my_product_option_qty WHERE product_id = '" . (int)$product_id . "'");
	 
	 //Case Preorder [$stock_status_id=5] add qty to preorder all table
	 //$tmp_status = array( 'PreOrder_received' , 'update' , 'pre_add_or_received');
		foreach( $product_options as $product_option ){
			foreach( $product_option['product_option_value'] as $product_option_value ){
				//Preorder + force out of stock
				$qty = (int)$product_option_value['quantity'] + (int)$product_option_value['add'];
				$prestmt = $this->getPreorderStmt($stock_status_id,$tmp_status,$qty);

				if($forcestock){
					$sql = " insert into my_product_option_qty set order_item='".(int)$order_item."' , product_id='".(int)$product_id."' , property_1='".(int)$product_option_value['property_1']."' , property_2='".(int)$product_option_value['property_2']."' , amount='".$qty."' ,price='".$product_option_value['price']."', modified=NOW(), created=NOW()  $prestmt ";
					
					$this->db->query( $sql );
				}else{
					$sql = " insert into my_product_option_qty set order_item='".(int)$order_item."' , product_id='".(int)$product_id."' , property_1='".(int)$product_option_value['property_1']."' , property_2='".(int)$product_option_value['property_2']."' , amount='0' , price='".$product_option_value['price']."', modified=NOW(), created=NOW()    ";
					
					$this->db->query( $sql );
				}
			}
			$order_item ++;
		}

		//**********************************************************************
		//use for option=2 only [color]
		//$price = Price - product_option[$option_row][product_option_value][$option_value_row][price]
		//if $price > 0 $prefix = "+" else $prefix = "-" 
		//**********************************************************************
		

		if ( count($array_color) > 0 ) {
			$this->db->query("INSERT INTO my_product_option SET product_id = '".(int)$product_id."', option_id = '2', required = '1'");
			$product_option_id = $this->db->getLastId();
			foreach ($array_color as $a) {
			//foreach ($product_option['product_option_value'] as $product_option_value) {
				$prestmt = $this->getPreorderStmt($stock_status_id,$tmp_status,$a[3]);

				$new_price = $a[5] - $master_price;
				$prefixed = ($new_price > 0) ? "+" : "-";
				$new_price = ($a[5] == 0) ? 0 : abs($new_price);
				$this->db->query("INSERT INTO my_product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '".(int)$product_id."', option_id = '2', option_value_id = '" . (int)$a[2] . "', quantity = '" . (int)$a[3] . "', subtract = '1', price = '".$new_price."', price_prefix = '".$prefixed."', points = '0', points_prefix = '+', weight = '0.00000000', weight_prefix = '+' , optionimage = '" . $a[4] . "' $prestmt ");

			} 
		
		}

		if ( count($array_size) > 0 ) {
			$this->db->query("INSERT INTO my_product_option SET product_id = '".(int)$product_id."', option_id = '11',required = '1'");
			$product_option_id = $this->db->getLastId();
			foreach ($array_size as $b) {
				$prestmt = $this->getPreorderStmt($stock_status_id,$tmp_status,$b[3]);

				$this->db->query("INSERT INTO my_product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '".(int)$product_id."', option_id = '11', option_value_id = '" . (int)$b[2] . "', quantity = '" . (int)$b[3] . "', subtract = '1', price = '0.0000', price_prefix = '+', points = '0', points_prefix = '+', weight = '0.00000000' , weight_prefix = '+' , optionimage = '" . $b[4] . "' $prestmt ");
			} 

		}
		return $grandtotal;
	}



	public function editProduct($product_id, $data) {

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
 
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
 
		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
					
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {				
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}


/**
*	END Update Order Option when update Product Option
**/
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		
		/*" update my_order_option ";
		P1 L red	5
		P2 L yellow 2
		P3 S red	1
		L		7
		S		1
		red		6
		yellow	2
		1. insert my_product_option
		$option_id = $this->db->getLastId();
		2. insert my_product_option_value
		$option_value_id = $this->db->getLastId();
		*/
		//Preorder + force out of stock
		$forcestock = ( ($data['stock_status_id'] ==3 )&& ($data['force_sold_out'] == 1)  ) ? 0 : 1;
		$array_color	= array();
		$array_size		= array();
		$order_item = 0;
		$total_item = 0;
		$grandtotal = 0;
		if (isset($data['product_option'])) { 

			//**********************************************************************
			//use for option=2 only [color]
			//$price = Price - product_option[$option_row][product_option_value][$option_value_row][price]
			//if $price > 0 $prefix = "+" else $prefix = "-" 
			//**********************************************************************
			if((int)$data['stock_status_id'] == 5){
				$master_price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3'] + $data['costz'] );
				$price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3'] + $data['costz']  );
			}else{
				$master_price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3']  );
				$price = ceil( ( $data['a1'] * $data['a2'] ) + $data['a3']    );
			}
			//$tmp_status = array( 'PreOrder_received' , 'add/update' , 'pre_add_or_received');
			$tmp_status = array( 'PreOrder_received' , 'update' , 'add');
			$grandtotal = $this->adjustProductOptionItem( $data['product_option'] , $forcestock , $array_color,$array_size ,$order_item ,$total_item,$grandtotal ,$product_id , $master_price ,$price ,$data['stock_status_id'] , $tmp_status  );
		}


 		$size_info = array();
		if ($data['size_info_f1'] != '')  $size_info[] = $data['size_info_f1'].':'.$data['size_info_v1'];
		if ($data['size_info_f2'] != '')  $size_info[] = $data['size_info_f2'].':'.$data['size_info_v2'];
		if ($data['size_info_f3'] != '')  $size_info[] = $data['size_info_f3'].':'.$data['size_info_v3'];
		if ($data['size_info_f4'] != '')  $size_info[] = $data['size_info_f4'].':'.$data['size_info_v4'];
		if ($data['size_info_f5'] != '')  $size_info[] = $data['size_info_f5'].':'.$data['size_info_v5'];
		if ($data['size_info_f6'] != '')  $size_info[] = $data['size_info_f6'].':'.$data['size_info_v6'];
		$size_info = implode(',',$size_info);

		//status,tmp_status,quantity
		$prestmt = $this->getPreorderStmt($data['stock_status_id'],$tmp_status,$grandtotal);

		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$grandtotal . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$price . "',a1='".(float)$data['a1']."',a2='".(float)$data['a2']."',a3='".(float)$data['a3']."',a4='', points = '" . (int)$data['points'] . "', size_info='" . $size_info . "' , ch_price='".$data['ch_price']."',ch_discount='".$data['ch_discount']."' , weight = '" . (float)($data['weight']/1000) . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "', sort_order = '" . (int)$data['sort_order'] . "',force_send='".$data['force_send']."', date_modified = NOW() $prestmt WHERE product_id = '" . (int)$product_id . "'");

		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
 
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}		
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}		
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
						
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
		
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
						
		$this->cache->delete('product');
	}
	
	public function copyProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		if ($query->num_rows) {
			$data = array();
			
			$data = $query->row;
			
			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';
						
			$data = array_merge($data, array('product_attribute' => $this->getProductAttributes($product_id)));
			$data = array_merge($data, array('product_description' => $this->getProductDescriptions($product_id)));			
			$data = array_merge($data, array('product_discount' => $this->getProductDiscounts($product_id)));
			$data = array_merge($data, array('product_filter' => $this->getProductFilters($product_id)));
			$data = array_merge($data, array('product_image' => $this->getProductImages($product_id)));		
			$data = array_merge($data, array('product_option' => $this->getProductOptions($product_id)));
			$data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
			$data = array_merge($data, array('product_reward' => $this->getProductRewards($product_id)));
			$data = array_merge($data, array('product_special' => $this->getProductSpecials($product_id)));
			$data = array_merge($data, array('product_category' => $this->getProductCategories($product_id)));
			$data = array_merge($data, array('product_download' => $this->getProductDownloads($product_id)));
			$data = array_merge($data, array('product_layout' => $this->getProductLayouts($product_id)));
			$data = array_merge($data, array('product_store' => $this->getProductStores($product_id)));
			
			$this->addProduct($data);
		}
	}

	public function changeTypeProduct($product_id , $change_type ) {
		if($change_type == 99){
			$query = $this->db->query("update my_product  set status='0'  WHERE product_id = '" . (int)$product_id . "' ");
		}elseif(strlen($change_type) == 0){

		}else{
			$query = $this->db->query("update my_product  set stock_status_id='".$change_type."' , status='1'   WHERE product_id = '" . (int)$product_id . "' ");
		}
		
	}
	
	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
		
		$this->cache->delete('product');
	}
	
	public function getProduct($product_id) {
		$sql = "SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
			
				//echo $sql;
		return $query->row;
	}
	
	public function getProducts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		
		if (!empty($data['filter_category_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
		}
				
		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'"; 
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		if (!empty($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}
		
		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		$sql .= " GROUP BY p.product_id";
					
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY pd.name";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

	public function getNewProducts($data = array()) {
		$sql = "SELECT * ,cd.name as cname, p.note_icon AS pnote, m.name as mname , pd.name as pname , s.name as sname , p.image as pimage , p.status as pstatus FROM  my_product p LEFT JOIN  my_product_description pd ON (p.product_id = pd.product_id)";

		$sql .= " LEFT JOIN my_product_to_category p2c ON (p.product_id = p2c.product_id) left join my_category_description as cd on cd.category_id =  p2c.category_id ";			


		if (!empty($data['filter_flag'])) {
			$sql .= " LEFT JOIN my_note n ON (p.product_id = n.type_id and n.type_note='product')  ";			
		}

		$sql .= " LEFT JOIN  my_manufacturer m on  p.manufacturer_id = m.manufacturer_id ";
		$sql .= " LEFT JOIN  my_stock_status s on  p.stock_status_id = s.stock_status_id ";
				
		
		
		if (isset($data['filter_status']) && ($data['filter_status'] == 99)) {
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.status ='0' "; 
		}else{
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.status ='1' "; 
		}
		if (!empty($data['filter_flag'])) {
			$sql .= " and n.flag='" . $this->db->escape($data['filter_flag']) . "'  ";			
		}
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		if (!empty($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}
		
		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		if (!empty($data['filter_cat'])) {
			$sql .= " AND cd.name like '%" . $this->db->escape($data['filter_cat']) . "%'";		
		}
		if (isset($data['filter_status']) && ($data['filter_status'] == 99)) {

		}elseif (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND s.stock_status_id = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_supplier']) && !is_null($data['filter_supplier'])) {
			$sql .= " AND m.name like '%" . $data['filter_supplier'] . "%'";
		}

		$sql .= " GROUP BY p.product_id";
					 
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			//'p.status',
			's.name',
			'm.name',
			'p.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY p.product_id ";	
		}
		 
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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
	public function getProductStatus() {
		$query = $this->db->query("SELECT stock_status_id as id, name FROM my_stock_status WHERE status <> 0 ORDER BY status ASC");
								  
		return $query->rows;
	} 

	
	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");
								  
		return $query->rows;
	} 
	
	public function getProductDescriptions($product_id) {
		$product_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'tag'              => $result['tag']
			);
		}
		
		return $product_description_data;
	}
		
	public function getProductCategories($product_id) {
		$product_category_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}
	
	public function getProductFilters($product_id) {
		$product_filter_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}
				
		return $product_filter_data;
	}
	
	public function getProductAttributes($product_id) {
		$product_attribute_data = array();
		
		$product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");
		
		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();
			
			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
			
			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}
			
			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}
		
		return $product_attribute_data;
	}
	
	public function getProductOptions($product_id) {
		$product_option_data = array();
		

		$sql = "SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$product_option_query = $this->db->query($sql);
		 
		
		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();	
				
			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");
				
			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],						
					'weight'                  => $product_option_value['weight'],
					'optionimage'             => $product_option_value['optionimage'],
					'weight_prefix'           => $product_option_value['weight_prefix']					
				);
			}
				
			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],			
				'product_option_value' => $product_option_value_data,
				'option_value'         => $product_option['option_value'],
				'required'             => $product_option['required']				
			);
		}
		
		return $product_option_data;
	}

	public function getNewProductOptions($product_id) {
		$product_option_data = array();
		

		$sql = "SELECT *  FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id)      WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$product_option_query = $this->db->query($sql);
		 //echo $sql;
		
		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();	
				
			//$product_qtys = $this->db->query(" select * from my_product_option_qty as q  LEFT JOIN my_product_option_value AS v ON q.product_id = v.product_id AND v.option_id='11' where q.product_id = '" . (int)$product_id . "' order by order_item ");
			$product_qtys = $this->db->query(" select * from my_product_option_qty as q  where q.product_id = '" . (int)$product_id . "' order by order_item ");

			foreach($product_qtys->rows as $product_qty){
				if(isset($product_qty['property_1'])) $p1	= $product_qty['property_1'];
				if(isset($product_qty['property_2'])) $p2	= $product_qty['property_2'];
				if(isset($product_qty['amount'])) $p3		= $product_qty['amount'];
				//if(isset($product_qty['price']))  $p4		= $product_qty['price'];
				$imgs = $this->db->query(" select optionimage from my_product_option_value   where product_id = '".(int)$product_qty['product_id']. "'and option_value_id='" . (int)$product_qty['property_2']. "' ");
				$product_option_value_data[] = array(
					'property_1'	=>	$p1,
					'property_2'	=>	$p2,
					'amount'		=>	$p3,
					'price'			=>	$product_qty['price'],
					'optionimage'   =>  $imgs->row['optionimage']
				);
			}  
 
			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],			
				'product_option_value' => $product_option_value_data,
				'option_value'         => $product_option['option_value'],
				'required'             => $product_option['required']				
			);
			break;
		}

		//print_r($product_option_data);
		
		return $product_option_data;
	}

			
	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		
		return $query->rows;
	}
	
	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");
		
		return $query->rows;
	}
	
	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");
		
		return $query->rows;
	}
	
	public function getProductRewards($product_id) {
		$product_reward_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}
		
		return $product_reward_data;
	}
		
	public function getProductDownloads($product_id) {
		$product_download_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}
		
		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}
		
		return $product_store_data;
	}

	public function getProductLayouts($product_id) {
		$product_layout_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}
		
		return $product_layout_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}
		
		return $product_related_data;
	}
	
	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

		if (!empty($data['filter_category_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
		}
		 
		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		 			
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		if (!empty($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}
		
		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}	

	public function getNewTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

		if (!empty($data['filter_cat'])) {
			$sql .= " LEFT JOIN my_product_to_category p2c ON (p.product_id = p2c.product_id) left join my_category_description as cd on cd.category_id =  p2c.category_id ";			
		}
		if (!empty($data['filter_flag'])) {
			$sql .= " LEFT JOIN my_note n ON (p.product_id = n.type_id and n.type_note='product')  ";			
		}
		if (isset($data['filter_status']) && ($data['filter_status'] == 99)) {
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.status ='0' "; 
		}else{
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.status ='1' "; 
		}		
		if (!empty($data['filter_flag'])) {
			$sql .= " and n.flag='" . $this->db->escape($data['filter_flag']) . "'  ";			
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.product_id LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		if (!empty($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}
		if (!empty($data['filter_cat'])) {
			$sql .= " AND cd.name like '%" . $this->db->escape($data['filter_cat']) . "%'";		
		}
		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		

		if (isset($data['filter_status']) && ($data['filter_status'] == 99)) {
			
		}elseif (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}	

	
	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}
		
	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}
	
	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}
	
	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}
	
	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}	
	
	public function getTotalProductsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}	
	
	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}


	public function getTableDetails( $data='') {
		$query = $this->db->query("select * from my_product_table   ");
		return $query->rows;
	}	
	public function updateTableDetails( $data) {
		$query = $this->db->query("update my_product_table set msg='" . $this->db->escape($data['msg']) . "'  ");

	}	
	public function updateNote($note,$product_id) {
		$query = $this->db->query("update my_product set note_icon='" . (int)$note . "' WHERE product_id = '" . (int)$product_id . "'");

	}		

	public function hideProduct( $model  ) {
		$query = $this->db->query("update my_product set status='0'  where model ='" .$model. "'  ");
	}
	public function moveReviewToNewProduct( $new , $old) {
		$query = $this->db->query("update my_review set product_id='" .$new. "'  where product_id ='" .$old. "'  ");
	}	
	public function moveWishListToNewProduct( $new , $old) {
		$query = $this->db->query("select customer_id, wishlist from my_customer where  wishlist like '%" .$old. "%'    ");
		foreach($query->rows as $wishlist){
			$tmp = unserialize($wishlist['wishlist']);
			if(($key = array_search($old, $tmp)) !== false) unset($tmp[$key]);
			array_push($tmp , $new );
			$tmp = serialize($tmp);
			$query = $this->db->query("update my_customer set wishlist='" .$tmp. "'  where customer_id ='" .$wishlist['customer_id']. "'  ");
		}
		$query = $this->db->query("update my_product set status='0'  where product_id ='" .$old. "'  ");
		echo $old;
	}	
	public function removeWishList( $product_id , $data) {
		$arr = array();
		$query = $this->db->query("select customer_id, wishlist from my_customer where  wishlist like '%" .$product_id. "%'    ");
		foreach($query->rows as $wishlist){
			$tmp = unserialize($wishlist['wishlist']);
			if(($key = array_search($product_id, $tmp)) !== false) unset($tmp[$key]);
			array_push($arr , array($wishlist['customer_id'],$product_id) );
			$tmp = serialize($tmp);
			$query = $this->db->query("update my_customer set wishlist='" .$tmp. "'  where customer_id ='" .$wishlist['customer_id']. "'  ");
		}
		//print_r($arr );
		//Send Mail
	}
	public function updateGlobalPrice( $data) {
		$query = $this->db->query("update my_discount set value='$data' where  name='global_price'   ");
		$query = $this->db->query("select update_date from my_deadline_time");
		$update_date = $query->row['update_date'];
		$query = $this->db->query("select sum(quantity) as total from  my_product where stock_status_id in ('1','5','7') and date_added <= '$update_date' and quantity > '0' ");
		$ps = $query->row['total'];
		$query = $this->db->query("update my_deadline_time set update_date=NOW() ,  item='$ps'   ");
		$query = $this->db->query("update my_product set price=CEIL((a1*a2)+a3+$data) where  stock_status_id='5'   ");
	}
	public function getGolbalPriceDate() {
		$query = $this->db->query("select update_date from my_deadline_time  ");
		return $query->row['update_date'];
	}
	public function getGlobalPrice() {
		$query = $this->db->query("select value from my_discount where  name='global_price'   ");
		return $query->row['value'];
	}
}
?>
