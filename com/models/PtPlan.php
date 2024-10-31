<?php

global $sgpt;
$sgpt->includeModel('Model');

class SGPT_PtPlanModel extends SGPT_Model
{
	const TABLE = 'pt_plan';
	protected $id;
	protected $pt_id;
	protected $name;
	protected $price;
	protected $price_timeframe;
	protected $button_url;
    protected $button_text;
	protected $button_shortcode;
	protected $highlighted_plan;
	protected $badge_text;

	public static function finder($class = __CLASS__)
	{
		return parent::finder($class);
	}

    public static function toArray($sgptPlan)
    {
        $dataArray = array();
        $dataArray['id'] = $sgptPlan->getId();
        $dataArray['pt_id'] = $sgptPlan->getPt_id();
        $dataArray['plan-name'] = $sgptPlan->getName();
        $dataArray['plan-price'] = $sgptPlan->getPrice();
        $dataArray['pricing-plan'] = $sgptPlan->getPrice_timeframe();
        $dataArray['button-url'] = $sgptPlan->getButton_url();
        $dataArray['button-text'] = $sgptPlan->getButton_text();
        $dataArray['button-shortcode'] = $sgptPlan->getButton_shortcode();
        $dataArray['highlight-plan'] = $sgptPlan->getHighlighted_plan();
        $dataArray['badge-text'] = $sgptPlan->getBadge_text();
        return $dataArray;
    }

	public static function create()
	{
        global $sgpt;
        global $wpdb;
		$tablename = $sgpt->tablename(self::TABLE);

        $query = "CREATE TABLE IF NOT EXISTS $tablename (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `pt_id` int(10) unsigned NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `price` varchar(255) NOT NULL,
                    `price_timeframe` varchar(255) NOT NULL,
                    `button_url` varchar(255) NOT NULL,
                    `button_text` varchar(255) NOT NULL,
                    `button_shortcode` varchar(255) NOT NULL,
                    `highlighted_plan` tinyint(4) NOT NULL,
                    `badge_text` varchar(255) NOT NULL,
                     PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        $query2 = "ALTER TABLE $tablename ADD
                     `button_shortcode` varchar(255) NOT NULL
                     AFTER `button_text`;";
        $wpdb->query($query);
        $wpdb->query($query2);
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
