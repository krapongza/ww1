<?php

class ModelSaleCustomerReferral extends Model {
  public function getCustomerReferral($customer_referral_id) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");

    return $query->row;
  }

  public function redeem($customer_referral_id, $credit, $points, $order_info) {
    $customer_referral_info = $this->getCustomerReferral($customer_referral_id);

    if ($customer_referral_info) {
      $language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/customer_referral');

      $this->load->model('sale/customer');

      $customer_info = $this->model_sale_customer->getCustomer($customer_referral_info['customer_id']);

      $message_data = array();

      $message_data[] = sprintf($language->get('text_customer_referral'), $customer_referral_info['email']);

      $description = sprintf($language->get('text_customer_referral'), $customer_referral_info['email']);

      if ($credit) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET customer_id = '" . (int)$customer_referral_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$credit . "', date_added = NOW()");

        $this->db->query("UPDATE `" . DB_PREFIX . "customer_referral` SET credit = credit + " . (float)$credit . " WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");

        $query = $this->db->query("SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$customer_referral_info['customer_id'] . "' GROUP BY customer_id");

        $message_data[] = sprintf($language->get('email_referral_received_credit'), $this->currency->format($credit, $this->config->get('config_currency')), $this->currency->format(($query->row ? $query->row['total'] : 0), $this->config->get('config_currency')));
      }

      if ($points) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET customer_id = '" . (int)$customer_referral_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");

        $this->db->query("UPDATE `" . DB_PREFIX . "customer_referral` SET points = points + " . (int)$points . " WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");

        $query = $this->db->query("SELECT SUM(points) AS total FROM `" . DB_PREFIX . "customer_reward` WHERE customer_id = '" . (int)$customer_referral_info['customer_id'] . "' GROUP BY customer_id");

        $message_data[] = sprintf($language->get('email_referral_received_points'), $points, ($query->row ? $query->row['total'] : 0));
      }

      if ($customer_referral_info['single_use']) {
        $this->db->query("UPDATE `" . DB_PREFIX . "customer_referral` SET status = '0' WHERE customer_referral_id = '" . (int)$customer_referral_id . "'");
      }

      if ($order_info['customer_id']) {
        $this->db->query("UPDATE `" . DB_PREFIX . "customer` SET referral_customer_id = '" . (int)$customer_referral_info['customer_id'] . "', customer_referral_id = '" . (int)$customer_referral_id . "' WHERE customer_id = '" . (int)$order_info['customer_id'] . "'");
      }

      $mail = new Mail();
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
      $mail->send();
    }
  }
}
