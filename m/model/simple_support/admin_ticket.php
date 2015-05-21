<?php
	class ModelSimpleSupportAdminTicket extends Model {
		
		public function addTicket($data) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket` SET simple_support_department_id='" . (int)$data['simple_support_department_id'] . "', user_id='" . (int)$data['user_id'] . "', subject='" . $this->db->escape($data['subject']) . "', description='" . $this->db->escape($data['description']) . "', simple_support_ticket_status_id='" . (int)$this->config->get('simple_support_status_id') . "', status='" . (int)$data['status'] . "', date_added=NOW(), date_modified=NOW()");
			
			$simple_support_ticket_id = $this->db->getLastId();
			
			if($this->config->get('simple_support_ticket_prefix')) {
				$ticket_prefix = $this->config->get('simple_support_ticket_prefix');
			} else {
				$ticket_prefix = "SUPT-";
			}
			
			$ticket_id = $ticket_prefix . $simple_support_ticket_id;
			
			$this->db->query("UPDATE `" . DB_PREFIX . "simple_support_ticket` SET ticket_id='" . $this->db->escape($ticket_id) . "' WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_history` SET simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', user_id='" . (int)$this->user->getId() . "', description='" . $this->db->escape($data['description']) . "', date_added=NOW()");
			
 			$simple_support_ticket_history_id = $this->db->getLastId();
			
			if(isset($data['files'])) {
				foreach($data['files'] as $file) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_images` SET simple_support_ticket_history_id='" . (int)$simple_support_ticket_history_id . "', simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', image='" . $this->db->escape($file['filename']) . "'");
				}
			}	
			
			$this->language->load('simple_support/admin_ticket');
			$this->load->model('simple_support/status');
			$this->load->model('user/user');
			
			$user_info = $this->model_user_user->getUser($data['user_id']);
			
			$subject = $this->config->get('config_name') . " - " .$data['subject'];
			
			$message = sprintf($this->language->get('mail_heading'), $user_info['username']) . "\n\n";
			
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
			$mail->setTo($user_info['email']);			
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();	
			
		}
		
		public function editTicket($simple_support_ticket_id, $data) {
			$this->db->query("UPDATE `" . DB_PREFIX . "simple_support_ticket` SET simple_support_department_id='" . (int)$data['simple_support_department_id'] . "', simple_support_ticket_status_id='" . (int)$data['simple_support_ticket_status_id'] . "', user_id='" . (int)$data['user_id']. "', status='" . (int)$data['status'] . "', date_modified=NOW() WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_history` SET simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', user_id='" . (int)$this->user->getId() . "', description='" . $this->db->escape($data['description']) . "', date_added=NOW()");
			
			$simple_support_ticket_history_id = $this->db->getLastId();
			
			if(isset($data['files'])) {
				foreach($data['files'] as $file) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "simple_support_ticket_images` SET simple_support_ticket_history_id='" . (int)$simple_support_ticket_history_id . "', simple_support_ticket_id='" . (int)$simple_support_ticket_id . "', image='" . $this->db->escape($file['filename']) . "'");
				}
			}	
			
			if(isset($data['notify_customer'])) {
				$this->language->load('simple_support/ticket');
				$this->load->model('simple_support/status');
				$this->load->model('user/user');
				
				$user_info = $this->model_user_user->getUser($data['user_id']);
				
				$subject = $this->config->get('config_name') . " - " .$data['subject'] . " - " . $data['ticket_id'];
				
				$message = sprintf($this->language->get('mail_heading'), $data['username']) . "\n\n";
				
				$ticket_status_info = $this->model_simple_support_status->getTicketStatus($data['simple_support_ticket_status_id']);
				
				$message .= sprintf($this->language->get('mail_ticket_status'), $ticket_status_info['name']) . "\n\n";
				
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
				$mail->setTo($user_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($this->config->get('config_name'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();				
			}			
		}
		
		public function deleteTicket($simple_support_ticket_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_ticket` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_ticket_history` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "simple_support_ticket_images` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
		}

		public function getTotalTickets($data = array()) {
			$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "simple_support_ticket` WHERE user_id <> 0 AND customer_id = ''";
			
			if(empty($data['view_all'])) {
				$sql .= " AND user_id='" . $this->user->getId() . "'";
			}
			
			$query = $this->db->query($sql);
			
			return $query->row['total'];
		}
		
		public function getTickets($data = array()) {
			$sql = "SELECT sst.*, CONCAT(u.firstname, ' ', u.lastname) AS user_name, u.email AS user_email FROM `" . DB_PREFIX . "simple_support_ticket` sst LEFT JOIN `" . DB_PREFIX . "user` u ON(sst.user_id=u.user_id) WHERE sst.user_id <> 0 AND sst.customer_id = ''";
			
			$sort_data = array(
				'username',
				'sst.ticket_id',
				'sst.simple_support_ticket_status_id',
				'sst.status',
				'sst.subject',
				'sst.date_added',
				'sst.date_modified'
			);
			
			if(empty($data['view_all'])) {
				$sql .= " AND sst.user_id='" . $this->user->getId() . "'";
			}
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY sst.date_modified";	
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
		
		public function getTicketInfo($simple_support_ticket_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket` WHERE  simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			return $sql->row;
		}
		
		public function getUserInfo($simple_support_ticket_id) {
			$sql = $this->db->query("SELECT u.* FROM `" . DB_PREFIX . "user` u LEFT JOIN `" . DB_PREFIX . "simple_support_ticket` sst ON(u.user_id=sst.user_id) WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			return $sql->row;
		}

		public function getDepartmentWiseUser($simple_support_department_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_department` ssd LEFT JOIN `" . DB_PREFIX . "simple_support_department_employee` ssde ON(ssd.simple_support_department_id = ssde.simple_support_department_id) WHERE ssd.simple_support_department_id='" . (int)$simple_support_department_id . "' AND ssde.admin_department <> ''");
			
			$user_id = $sql->row['department_head_id'];
			
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");

			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_group_id='" . (int)$query->row['user_group_id'] . "' AND status=1");
			
			return $sql->rows;				
		}		
		
		public function getTicketHistories($simple_support_ticket_id, $start = 0, $limit = 10) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket_history` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;	
		}
		
		public function getImages($simple_support_ticket_history_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket_images` WHERE simple_support_ticket_history_id='" . (int)$simple_support_ticket_history_id . "'");
			
			return $sql->rows;	
		}
		
		public function getTicketImage($simple_support_ticket_images_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_ticket_images` WHERE simple_support_ticket_images_id='" . (int)$simple_support_ticket_images_id . "'");
			
			return $sql->row;	
		}		
		
		public function getTotalTicketHistories($simple_support_ticket_id) {
			$sql = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "simple_support_ticket_history` WHERE simple_support_ticket_id='" . (int)$simple_support_ticket_id . "'");
			
			return $sql->row['total'];
		}	
		
		public function getDepartments() {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "simple_support_department` ssd LEFT JOIN `" . DB_PREFIX . "simple_support_department_employee` ssde ON(ssd.simple_support_department_id = ssde.simple_support_department_id) WHERE ssd.language_id='" . (int)$this->config->get('config_language_id') . "' AND ssde.admin_department <> '' AND ssd.status=1");
			
			return $sql->rows;
		}
			
	}
?>