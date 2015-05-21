<?php

require_once DIR_SYSTEM . 'journal2/classes/journal2_newsletter.php';

class ModelJournal2Newsletter extends Model{

    private $post_data;
    private $get_data;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->post_data = json_decode(file_get_contents('php://input'), true);
        $this->get_data = $this->request->get;

        if ($this->db->query('show tables like "' . DB_PREFIX . 'journal2_newsletter"')->num_rows === 0) {
            $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_newsletter` (
                `email` varchar(128) NOT NULL,
                `token` varchar(64) NOT NULL,
                PRIMARY KEY `pk` (`email`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
        }
    }

    public function getTotalSubscribers() {
        $sql = 'SELECT COUNT(*) AS total FROM ((SELECT email FROM ' . DB_PREFIX . 'customer WHERE newsletter = 1) UNION (SELECT email FROM ' . DB_PREFIX . 'journal2_newsletter)) TEMP';

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getSubscribers($data = array()) {
        $sql = 'SELECT email, status FROM ((SELECT email, 1 as status FROM ' . DB_PREFIX . 'customer WHERE newsletter = 1) UNION (SELECT email, 0 as status FROM ' . DB_PREFIX . 'journal2_newsletter)) TEMP';

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

    public function subscribers() {
        $data = array(
            'limit' => 5,
            'start' => 0
        );
        if (isset($this->post_data['count'])) {
            $data['limit'] = (int)$this->post_data['count'];
        }
        if (isset($this->post_data['page'])) {
            $data['start'] = $data['limit'] * ((int) $this->post_data['page'] - 1);
        }
        return array(
            'total'         => $this->getTotalSubscribers(),
            'subscribers'   => $this->getSubscribers($data)
        );
    }

    public function unsubscribe() {
        if (!$this->user->hasPermission('modify', 'module/journal2')) {
            throw new Exception('You do not have permissions to modify Journal2 module');
        }

        if (!isset($this->post_data['email'])) {
            throw new Exception('Parameter email was not found');
        }

        $newsletter = new Journal2Newsletter($this->registry, $this->post_data['email']);

        if ($newsletter->isSubscribed()) {
            $newsletter->unsubscribe();
        }
    }

    public function export_csv() {
        if (!$this->user->hasPermission('modify', 'module/journal2')) {
            throw new Exception('You do not have permissions to modify Journal2 module');
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . date('Y-m-d_H-i-s', time()).'_newsletter_list.csv');
        header('Content-Transfer-Encoding: binary');

        foreach ($this->getSubscribers() as $subscriber) {
            echo $subscriber['email'] . PHP_EOL;
        }

        exit();
    }

}