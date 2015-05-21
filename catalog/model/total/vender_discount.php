<?php
class ModelTotalVenderDiscount extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
 
		//		$this->load->model('catalog/product');
		//		$p = $this->model_catalog_product->getVender();

		//if( $p > 0 ){
		if( isset($this->session->data['vender'])&&($this->session->data['vender']  == 1)  ){
		 
			$total_data[] = array(
				'code'       => 'vender_discount',
				'title'      => 'ค่าจัดส่งแทน',
				'text'       => '100.00 ฿',
				'value'      => +100,
				'sort_order' => 5
			);
	 
			
			$total += "100";
		}else{
			
		}


	}
}
?>