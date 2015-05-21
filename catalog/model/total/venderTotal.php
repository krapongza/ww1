<?php
class ModelTotalVenderTotal extends Model {
	public function getTotal(&$total_data, &$total='200', &$taxes) {

		if( isset($this->session->data['vender']) ){
			$this->language->load('total/total');
		 
			$total_data[] = array(
				'code'       => 'vender_discount',
				'title'      => 'Vender Charge',
				'text'       => '100',
				'value'      => +100,
				'sort_order' => 5
			);
	 
			
			$total += "100";
		}




	}
}
?>