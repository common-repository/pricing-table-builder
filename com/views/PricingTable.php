<?php

global $sgpt;
$sgpt->includeLib('Table');
$sgpt->includeModel('PricingTable');

class SGPT_PricingTableTableView extends SGPT_Table
{
	public function __construct()
	{
		parent::__construct('sgpt');

		$this->setRowsPerPage(10);
		$this->setTablename(SGPT_PricingTableModel::TABLE);
		$this->setColumns(array(
			'id',
			'name',
			'theme'
		));
		$this->setDisplayColumns(array(
			'id' => 'ID',
			'name' => 'Title',
			'theme' => 'Theme',
			'shortcode' => 'Auto shortcode',
			'options' => 'Options'
		));
		$this->setSortableColumns(array(
			'id' => array('id', false),
			'name' => array('name', true)
		));
        $this->setInitialSort(array(
            'id' => 'DESC'
        ));
	}

	public function customizeRow(&$row)
	{
        global $sgpt;
        $id = $row[0];
        $editUrl = $sgpt->adminUrl('PricingTable/save','id='.$id);
        $row[3] = "<input type='text' onfocus='this.select();' style='font-size:12px;' readonly value='[sgpt_pricing_table id=".$id."]' class='large-text code'>";
		$row[4] = '<a href="'.$editUrl.'">'.__('Edit', 'sgpt').'</a>
					&nbsp;&nbsp;/&nbsp;&nbsp;
					<a href="#" onclick="SGPT.ajaxDelete('.$id.')">'.__('Delete', 'sgpt').'</a>
					&nbsp;&nbsp/&nbsp;&nbsp;
					<a href="#" onclick="SGPT.ajaxSgptClone('.$id.')">'.__('Clone', 'sgpt').'</a>';
	}

	public function customizeQuery(&$query)
	{
		//$query .= ' LEFT JOIN wp_sns_backups ON wp_sns_backups.id='.$this->tablename.'.id';
	}
}
