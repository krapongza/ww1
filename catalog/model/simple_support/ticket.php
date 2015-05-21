<?php
	class ModelSimpleSupportTicket extends Model {
		
		public function addTicket($data) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket` SET customer_id='" . (int)$this->customer->getId() . "', simple_support_department_id='" . (int)$data['department_id'] . "', subject='" . $this->db->escape($data['subject']) . "', description='" . $this->db->escape($data['description']) . "', simple_support_ticket_status_id='" . (int)$this->config->get('simple_support_status_id') . "', status=1, date_added=NOW(), date_modified=NOW()");
			
			$simple_support_ticket_id = $this->db->getLastId();
			
			if($this->config->get('simple_support_ticket_prefix')) {
				$ticket_prefix = $this->config->get('simple_support_ticket_prefix');
			} else {
				$ticket_prefix = "SUPT-";
			}
			
			$ticket_id = $ticket_prefix . $simple_support_ticket_id;
			
			$this->db->query("UPDATE `" . DB_PREFIX . "simple_support_ticket` SET ticket_id='" . $this->db->escape($ticket_id) . "' WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_history` SET simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', customer_id='" . (int)$this->customer->getId() . "', description='" . $this->db->escape($data['description']) . "', date_added=NOW()");
			
 			$simple_support_ticket_history_id = $this->db->getLastId();
			
			if(isset($data['files'])) {
				foreach($data['files'] as $file) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_images` SET simple_support_ticket_history_id='" . (int)$simple_support_ticket_history_id . "', simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', image='" . $this->db->escape($file['filename']) . "'");
				}
			}
			
			// now assign ticket to department head			
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_department` WHERE simple_support_department_id='" . (int)$data['department_id'] . "'");
			
			$this->db->query("UPDATE `" . DB_PREFIX . "simple_support_ticket` SET user_id='" . $sql->row['department_head_id'] . "' WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			/*
			$customer_name = $this->customer->getFirstName() . " " . $this->customer->getLastName();
			
			$this->language->load('simple_support/ticket');
			
			$subject = $this->config->get('config_name') . " - " .$data['subject'];
			
			$message = sprintf($this->language->get('mail_heading'), $customer_name) . "\n\n";
			
			$message .= $this->language->get('text_mail_appriciate') . "\n\n";
			
			$message .= $this->language->get('text_ticket_label') . "\n";
			
			$message .= $this->language->get('text_ticket_id') . " " . $ticket_id . "\n\n";
			
			$message .= strip_tags(html_entity_decode($data['description'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			
			$message .= $this->language->get('mail_body_footer') . "\n\n";
				
			$message .= $this->config->get('config_name') . "\n";
			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');				
			$mail->setTo($this->customer->getEmail());			
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();*/
		}
		
		public function addHistory($simple_support_ticket_id, $data) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_history` SET simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', customer_id='" . (int)$this->customer->getId() . "', description='" . $this->db->escape($data['description']) . "', date_added=NOW()");
			
			$simple_support_ticket_history_id = $this->db->getLastId();
			
			if(isset($data['files'])) {
				foreach($data['files'] as $file) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_images` SET simple_support_ticket_history_id='" . (int)$simple_support_ticket_history_id . "', simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', image='" . $this->db->escape($file['filename']) . "'");
				}
			}	
		}
		
		public function getTotalTicket($data = array()) {
			$sql = "SELECT COUNT(DISTINCT(sst.simple_support_ticket_id)) AS total FROM `" . DB_PREFIX . "simple_support_ticket` sst LEFT JOIN `" . DB_PREFIX . "simple_support_department` ssd ON(sst.simple_support_department_id=ssd.simple_support_department_id) LEFT JOIN `" . DB_PREFIX . "simple_support_status` sss ON(sst.simple_support_ticket_status_id=sss.simple_support_status_id) WHERE sst.customer_id = '" . (int)$this->customer->getId() . "' AND sst.status=1 AND ssd.language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			if(!empty($data['filter_search'])) {
				$sql .= " AND ( LCASE(sst.ticket_id) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_search'])). "%' OR LCASE(sst.subject) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_search'])) . "%' )";	
			}	
		
			$query = $this->db->query($sql);
			
			return $query->row['total'];		
		}	
		
		public function getTickets($data = array()) {
			$sql = "SELECT sst.*, ssd.name AS department_name, sss.name AS status_name FROM `" . DB_PREFIX . "simple_support_ticket` sst LEFT JOIN `" . DB_PREFIX . "simple_support_department` ssd ON(sst.simple_support_department_id=ssd.simple_support_department_id) LEFT JOIN `" . DB_PREFIX . "simple_support_status` sss ON(sst.simple_support_ticket_status_id=sss.simple_support_status_id) WHERE sst.customer_id = '" . (int)$this->customer->getId() . "' AND sst.status=1 AND ssd.language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			$sort_data = array();
			
			if(!empty($data['filter_search'])) {
				$sql .= " AND ( LCASE(sst.ticket_id) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_search'])). "%' OR LCASE(sst.subject) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_search'])) . "%' )";	
			}
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY sst.date_added";	
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
		
		public function getDepartments() {
			$sql = $this->db->query("SELECT ssd.* FROM `" . DB_PREFIX . "simple_support_department` ssd LEFT JOIN `" . DB_PREFIX . "simple_support_department_employee` ssde ON(ssd.simple_support_department_id=ssde.simple_support_department_id) WHERE ssd.language_id='" . (int)$this->config->get('config_language_id') . "' AND ssd.status=1 AND ssde.customer_department <> ''");
			
			return $sql->rows;
		}
		
		public function getTicketInfo($simple_support_ticket_id) {
			$sql = "SELECT sst.*, ssd.name AS department_name, sss.name AS status_name FROM `" . DB_PREFIX . "simple_support_ticket` sst LEFT JOIN `" . DB_PREFIX . "simple_support_department` ssd ON(sst.simple_support_department_id=ssd.simple_support_department_id) LEFT JOIN `" . DB_PREFIX . "simple_support_status` sss ON(sst.simple_support_ticket_status_id=sss.simple_support_status_id) WHERE sst.simple_support_ticket_id='" . (int)$simple_support_ticket_id . "' AND sst.customer_id = '" . (int)$this->customer->getId() . "' AND sst.status=1 AND ssd.language_id='" . (int)$this->config->get('config_language_id') . "'";
			
			$query = $this->db->query($sql);
			
			return $query->row;
		}	
		
		public function getTicketHistories($simple_support_ticket_id, $start = 0, $limit = 10) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket_history` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;	
		}	
		
		public function getUser($user_id) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
	
			return $query->row;
		}
		
		public function getImages($simple_support_ticket_history_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket_images` WHERE simple_support_ticket_history_id='" . (int)$simple_support_ticket_history_id . "'");
			
			return $sql->rows;	
		}	
		
		public function getTotalTicketHistories($simple_support_ticket_id) {
			$sql = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "simple_support_ticket_history` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			return $sql->row['total'];
		}	
		
		public function getTicketImage($simple_support_ticket_images_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket_images` WHERE simple_support_ticket_images_id='" . (int)$simple_support_ticket_images_id . "'");
			
			return $sql->row;	
		}	
			
	}
?>