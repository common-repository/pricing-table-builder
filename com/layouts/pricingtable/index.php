<div class="wrap">
	<?php require_once plugin_dir_path(__FILE__) . '../free-banner.php'; ?>
	<h2 style="padding:0;">
		<?php _e('Pricing Tables', 'sgpt'); ?>
		<a href="<?php echo $createNewUrl;?>" class="page-title-action add-new-h2"><?php _e('Add new', 'sgpt'); ?></a>
		<?php if (SGPT_PRO_VERSION) : ?>
			<i class="sgpt-import-spinner" style='display:none'><img style='vertical-align:sub;float:right;' width="25px" height="25px" src='<?php echo $sgpt->app_url.'/assets/page/img/spinner-2x.gif';?>'></i>
			<a href="#" id="sgpt-import-js" class="button" style="float:right;margin:0 2px;"><?php _e('Import', 'sgpt'); ?></a>
			<?php if (!@empty($pricingTables)) :?>
				<a href="<?php echo $export;?>" class="button" style="float:right;margin:0 2px;"><?php _e('Export', 'sgpt'); ?></a>
			<?php endif;?>
		<?php endif;?>
		<?php if (!SGPT_PRO_VERSION) : ?>
			<input type="button" value="Upgrade to PRO version" onclick="window.open('<?php echo SGPT_PRO_URL;?>')" style="float:right;background-color: #d54e21;border: 1px solid #d54e21;color:white;cursor:pointer;">
		<?php endif;?>
	</h2>

	<?php echo $table; ?>

</div>
