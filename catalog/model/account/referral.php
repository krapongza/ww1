<?php

class ModelAccountReferral extends Model {
  public function addReferral($data) {
    do {
      $code = md5(uniqid('', true));

      $code_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer_referral` WHERE code = '" . $this->db->escape($code) . "'");

      if ($code_query->row) {
        $code = '';
      }
    } while (!$code);

    $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_referral` SET customer_id = '" . (int)$this->customer->getId() . "', code = '" . $this->db->escape($code) . "', email = '" . $this->db->escape($data['email']) . "', single_use = '" . ($data['single_use'] ? 1 : 0) . "', status = '1', date_added = NOW()");

    $customer_referral_id = $this->db->getLastId();

    return $customer_referral_id;
  }

  public function getReferral($customer_referral_id) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE customer_referral_id = '" . (int)$customer_referral_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

    return $query->row;
  }

  public function getReferralByCode($code) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE code = '" . $this->db->escape($code) . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

    return $query->row;
  }

  public function getReferralByEmail($email) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE email = '" . $this->db->escape($email) . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

    return $query->row;
  }

  public function getReferrals($data = array()) {
    $sql = "SELECT * FROM `" . DB_PREFIX . "customer_referral` WHERE customer_id = '" . (int)$this->customer->getId() . "'";

    $sort_data = array(
      'date_added'
    );

    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
      $sql .= " ORDER BY " . $data['sort'];
    } else {
      $sql .= " ORDER BY date_added";
    }

    if (isset($data['order']) && ($data['order'] == 'ASC')) {
      $sql .= " ASC";
    } else {
      $sql .= " DESC";
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

  public function getTotalReferrals() {
    $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_referral` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

    return $query->row['total'];
  }

  public function getReferralCreditTotal() {
    $query = $this->db->query("SELECT SUM(credit) AS total FROM `" . DB_PREFIX . "customer_referral` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

    return $query->row['total'];
  }

  public function getReferralPointsTotal() {
    $query = $this->db->query("SELECT SUM(points) AS total FROM `" . DB_PREFIX . "customer_referral` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

    return $query->row['total'];
  }
}
