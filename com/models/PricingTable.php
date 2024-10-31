<?php

global $sgpt;
$sgpt->includeModel('Model');

class SGPT_PricingTableModel extends SGPT_Model
{
	const TABLE = 'pricing_table';
	protected $id;
	protected $name;
	protected $type;
	protected $theme;
	protected $options;

	public static function finder($class = __CLASS__)
	{
		return parent::finder($class);
	}

	public static function create()
	{
		global $sgpt;
		global $wpdb;
		$tablename = $sgpt->tablename(self::TABLE);

		$query = "CREATE TABLE IF NOT EXISTS $tablename (
					  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `name` varchar(255) NOT NULL,
					  `type` tinyint(4) unsigned NOT NULL,
					  `theme` varchar(255) NOT NULL,
					  `options` text NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		$wpdb->query($query);
	}

	public static function drop()
	{
		global $sgpt;
		global $wpdb;
		$tablename = $sgpt->tablename(self::TABLE);
		$query = "DROP TABLE $tablename";
		$wpdb->query($query);
	}
}
