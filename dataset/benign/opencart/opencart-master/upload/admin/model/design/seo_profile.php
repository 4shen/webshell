<?php
class ModelDesignSeoProfile extends Model {
	public function addSeoProfile($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_profile` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `key` = '" . $this->db->escape((string)$data['key']) . "', `regex` = '" . $this->db->escape((string)$data['regex']) . "', `push` = '" . $this->db->escape((string)$data['push']) . "', `remove` = '" . $this->db->escape((string)$data['remove']) . "', `sort_order` = '" . (int)$data['sort_order'] . "'");
	}

	public function editSeoProfile($seo_profile_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_profile` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `key` = '" . $this->db->escape((string)$data['key']) . "', `regex` = '" . $this->db->escape((string)$data['regex']) . "', `push` = '" . $this->db->escape((string)$data['push']) . "', `remove` = '" . $this->db->escape((string)$data['remove']) . "', `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `seo_profile_id` = '" . (int)$seo_profile_id . "'");
	}

	public function deleteSeoProfile($seo_profile_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_profile` WHERE `seo_profile_id` = '" . (int)$seo_profile_id . "'");
	}

	public function getSeoProfile($seo_profile_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_profile` WHERE `seo_profile_id` = '" . (int)$seo_profile_id . "'");

		return $query->row;
	}

	public function getSeoProfiles($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_profile`";

		$sort_data = array(
			'name',
			'key',
			'regex',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
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

	public function getTotalSeoProfiles() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "seo_profile`");

		return $query->row['total'];
	}
}