<?php

class ModelCheckoutCustomerReferral extends Model {
  public function addCustomerReferral($data) {
    do {
      $code = md5(uniqid('', true));

      $code_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer_referral` WHERE code = '" . $this->db->escape($code) . "'");

      if ($code_query->row) {
        $code = '';
      }
    } while (!$code);

    $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_referral` SET customer_id = '" . (int)$data['customer_id'] . "', code = '" . $this->db->escape($code) . "', email = '" . $this->db->escape($data['email']) . "', single_use = '1', status = '1', date_added = NOW()");

    $customer_referral_id = $this->db->getLastId();

    return $customer_referral_id;
  }

  public function getCustomerReferral($customer_referral_id) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");

    return $query->row;
  }

  public function getCustomerReferralByCode($code) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE code = '" . $this->db->escape($code) . "' AND status = '1'");

    return $query->row;
  }

  public function redeem($customer_referral_id, $credit, $points, $order_info , $order_id='') {
    $customer_referral_info = $this->getCustomerReferral($customer_referral_id);

    if ($customer_referral_info) {
      $language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('checkout/customer_referral');

      $this->load->model('account/customer');

      $customer_info = $this->model_account_customer->getCustomer($customer_referral_info['customer_id']);

      $message_data = array();

      $message_data[] = sprintf($language->get('text_customer_referral'), $customer_referral_info['email']);

      $description = sprintf($language->get('text_customer_referral'), $customer_referral_info['email']);

      if ($credit) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET customer_id = '" . (int)$customer_referral_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$credit . "', date_added = NOW()");

		if($this->config->get('customer_referral_credit_type') == "F"){

			$query = $this->db->query("SELECT credit FROM `my_customer` WHERE customer_id = '" . (int)$customer_referral_info['customer_id'] . "'  ");
			$old_credit = $query->row['credit'];

			$this->db->query("UPDATE `my_customer_referral` SET credit = credit + " . $this->config->get('customer_referral_credit'). " WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");
			$this->db->query("UPDATE `my_customer` SET credit = credit + " . $this->config->get('customer_referral_credit') . " WHERE customer_id = '" . (int)$customer_referral_info['customer_id'] . "'");

			$this->load->model('checkout/pointcredit');
			$this->model_checkout_pointcredit->historyCreditByReferral(0, $this->config->get('customer_referral_credit') , $old_credit , 0, '' , 1 , 'redeem from referral' , $customer_referral_info['customer_id']);
		} 
		

        $query = $this->db->query("SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$customer_referral_info['customer_id'] . "' GROUP BY customer_id");

        //$message_data[] = sprintf($language->get('email_referral_received_credit'), $this->currency->format($credit, $this->config->get('config_currency')), $this->currency->format(($query->row ? $query->row['total'] : 0), $this->config->get('config_currency')));
      }
/*
      if ($points) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET customer_id = '" . (int)$customer_referral_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");

        $this->db->query("UPDATE `" . DB_PREFIX . "customer_referral` SET points = points + " . (int)$points . " WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");

        $query = $this->db->query("SELECT SUM(points) AS total FROM `" . DB_PREFIX . "customer_reward` WHERE customer_id = '" . (int)$customer_referral_info['customer_id'] . "' GROUP BY customer_id");

       // $message_data[] = sprintf($language->get('email_referral_received_points'), $points, ($query->row ? $query->row['total'] : 0));
*/
      if ($customer_referral_info['single_use']) {
        $this->db->query("UPDATE `" . DB_PREFIX . "customer_referral` SET status = '0' WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");
      }

      if ($order_info['customer_id']) {
        $this->db->query("UPDATE `" . DB_PREFIX . "customer` SET referral_customer_id = '" . (int)$customer_referral_info['customer_id'] . "', customer_referral_id = '" . (int)$customer_referral_id . "' WHERE customer_id = '" . (int)$order_info['customer_id'] . "'");
      }

      /*$mail = new Mail();
      $mail->protocol   = $this->config->get('config_mail_protocol');
      $mail->parameter  = $this->config->get('config_mail_parameter');
      $mail->hostname   = $this->config->get('config_smtp_host');
      $mail->username   = $this->config->get('config_smtp_username');
      $mail->password   = $this->config->get('config_smtp_password');
      $mail->port       = $this->config->get('config_smtp_port');
      $mail->timeout    = $this->config->get('config_smtp_timeout');

      $mail->setTo($customer_info['email']);
      $mail->setFrom($this->config->get('config_email'));
      $mail->setSender($this->config->get('config_name'));
      $mail->setSubject(html_entity_decode($language->get('email_referral_subject'), ENT_QUOTES, 'UTF-8'));
      $mail->setText(html_entity_decode(implode("\n\n", $message_data), ENT_QUOTES, 'UTF-8'));
      $mail->send();*/
    }
  }
}

?>
