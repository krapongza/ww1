<?php
class ControllerAccountTest extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$timeout=0;
		$refund=0;
		$this->data['refund'] = 0;
		if (isset($this->request->get['timeout'])) {
			$this->load->model('account/order');
			if( $this->model_account_order->checkOrderExpired( ) > 0)
				$this->redirect($this->url->link('account/order'));



		}

		if (   isset($this->request->get['refund']) && isset($this->request->get['refundcancel'])   ) {
			$refund = $this->request->get['refundcancel'];
			
			$query = $this->db->query("select * from my_rollback_cancel where id = '" . $refund . "'");
			$results = $query->row;

			$query = $this->db->query("UPDATE my_rollback_cancel SET status='done' WHERE id = '" . $refund  . "'   ");

			$query = $this->db->query("INSERT INTO my_rollback_transfer_success SET  rollback_id = '" . $results['id'] . "', type = 'refund_cancel', bank_account = 'credit',  bank_name = 'credit' ,bank = 'credit' , amount='" . $results['amount'] . "' ,  datetime = NOW() , note='' ");

			$this->load->model('checkout/pointcredit');
			$query = $this->db->query("SELECT credit from `" . DB_PREFIX . "customer` WHERE customer_id = '" . $this->request->get['refund'] . "' ");
			$this->model_checkout_pointcredit->historyCredit(0, $results['amount'] , $query->row['credit'] , 0, 'Veerawit D' , 1 , 'Refund from Cancel' );
	


		}


		if (   isset($this->request->get['refund'])     ) {
			$this->data['refund'] = 1;

			$sql = "SELECT r.return_id , r.firstname , r.lastname , rs.name as status, r.date_added , o.order_id , order_status_id ,o.total AS order_total , o.date_added AS order_date ,  op.name , op.quantity , op.price , op.total , op.product_id  , image , op.product_id FROM my_order AS o INNER JOIN my_order_product AS op ON o.order_id = op.order_id INNER JOIN my_product AS pim ON  op.product_id = pim.product_id INNER JOIN my_return AS r ON r.order_id = o.order_id INNER JOIN my_return_status AS rs ON r.return_status_id = rs.return_status_id WHERE o.customer_id = '" . $this->request->get['refund'] . "'  and o.order_status_id IN ('3','5','18')   AND DATEDIFF(NOW() , o.date_added   ) <= 10  ORDER BY o.order_id DESC   "   ;
			$query = $this->db->query($sql);	
			$results =  $query->rows;
			foreach ($results as $result) {
				$this->data['returns'][] = array(
					'return_id'  => $result['return_id'],
					'order_id'   => $result['order_id'],
					'name'       => $result['firstname'] . ' ' . $result['lastname'],
					'status'     => $result['status'],
					'date_added' => $result['date_added'],
				);
			}

			$query = $this->db->query("select * from my_rollback_cancel where user_id = '" . $this->request->get['refund'] . "'");
			$results = $query->rows;
			foreach ($results as $result) {
				$this->data['refundscancel'][] = array(
					'return_id'		=> $result['id'],
					'user_id'		=> $result['user_id'],
					'username'		=> $result['username'],
					'bank_name'		=> $result['bank_name'],
					'bank_account'  => $result['bank_account'],
					'bank'			=> $result['bank'],
					'date_transfer' => $result['date_transfer'],
					'amount'		=> $this->currency->format($result['amount']),
					'message'       => $result['message'],
					'status'		=> $result['status'],
					'modify'		=> $result['modify'],
				);
			}


			$query = $this->db->query("SELECT id, bank_name, bank_account, bank, date_transfer, amount, message, status, modify, username, user_id FROM `" . DB_PREFIX . "rollback_transfer`    WHERE user_id = '" . $this->customer->getId() . "'  ORDER BY id DESC  ");
			$results = $query->rows;
			foreach ($results as $result) {
				$this->data['refunds'][] = array(
					'return_id'			=> $result['id'],
					'user_id'			=> $result['user_id'],
					'username'			=> $result['username'],
					'bank_name'			=> $result['bank_name'],
					'bank_account'      => $result['bank_account'],
					'bank'				=> $result['bank'],
					'date_transfer'     => $result['date_transfer'],
					'amount'			=> $this->currency->format($result['amount']),
					'message'			=> $result['message'],
					'status'			=> $result['status'],
					'modify'			=> $result['modify'],
				);
			}



		}
	 
 
 

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/test.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/test.tpl';
		} else {
			$this->template = 'default/template/account/test.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
						
		$this->response->setOutput($this->render());	
	}

 
}
?>