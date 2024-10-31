<?php

global $sgpt;
$sgpt->includeModel('Model');
$sgpt->includeModel('PtPlan');

class SGPT_PtFeatureModel extends SGPT_Model
{
	const TABLE = 'pt_feature';
	protected $id;
	protected $plan_id;
	protected $name;


	public static function finder($class = __CLASS__)
	{
		return parent::finder($class);
	}

    public static function getFeaturesNameAsString($features)
    {
        $str = '';
        foreach($features as $feature)
        {
            if($feature->getName() == '') continue;
            $str.=$feature->getName().PHP_EOL;
        }
        return $str;
    }

    public static function featuresToArray($features)
    {
        $dataArray = array();
        $str = '';
        foreach($features as $feature)
        {
            if($feature->getName() == '') continue;
            $dataArray[] = $feature->getName();
        }
        return $dataArray;
    }

	public static function create()
	{
        global $sgpt;
        global $wpdb;
		$tablename = $sgpt->tablename(self::TABLE);
        $sgptPlanTableName = $sgpt->tablename(SGPT_PtPlanModel::TABLE);

        $query = "CREATE TABLE IF NOT EXISTS $tablename (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `plan_id` int(11) unsigned NOT NULL,
                    `name` varchar(255) NOT NULL,
                     PRIMARY KEY (`id`),
                    KEY `fk_id_idx` (`plan_id`),
                    CONSTRAINT `$sgptPlanTableName"."_"."fk_ptf_ptp` FOREIGN KEY (`plan_id`) REFERENCES `$sgptPlanTableName` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
