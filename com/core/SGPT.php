<?php

class SGPT
{
	private $autoload;
	private $menuActions = array();
	private $ajaxCallbacks = array();
	private $postCallbacks = array();
	private $shortcodes = array();
	private $slugs = array();
	public $prefix = '';
	public $app_path = '';
	public $app_url = '';

	public function __construct()
	{
		$this->prefix = strtolower(__CLASS__).'_';
		$var = strtoupper($this->prefix).'AUTOLOAD';
		global $$var;
		$this->autoload = $$var;
	}

	public function __call($name, $args)
	{
		$param1 = null;
		$param2 = null;

		if (strpos($name, 'wp_ajax_')===0) {
			$action = $this->ajaxCallbacks[$name];
		}
		else if (strpos($name, 'wp_shortcode_')===0) {;
			$action = $this->shortcodes[$name];
			$param1 = $args[0];
			$param2 = $args[1];
		}
		else {
			$action = $this->menuActions[$name];
		}

		return $this->dispatchAction($action, $param1, $param2);
	}

	public function run()
	{
		$this->registerSetupController();

		add_action('plugins_loaded', array($this, 'sgptSetVersion'));

		if (count($this->autoload['menu_items'])) {
			add_action('admin_menu', array($this, 'loadMenu'));
		}

		if (count($this->autoload['network_admin_menu_items'])) {
			add_action('network_admin_menu', array($this, 'loadNetworkAdminMenu'));
		}

		$this->registerAjaxCallbacks();
		$this->registerShortcodes();
		$this->registerPostCallbacks();

		add_action('admin_enqueue_scripts', array($this, 'includeAdminScriptsAndStyles'));
		add_action('wp_enqueue_scripts', array($this, 'includeFrontScriptsAndStyles'));
		add_action('media_buttons', array($this, 'sgpt_media_button'));
		add_action('admin_post_sgptExport', array($this, 'sgptExport'));
	}

	public function sgptExport()
	{
		global $wpdb;
		global $sgpt;
		$allData = array();
		$exportArray = array();

		$mainSgpt = new SGPT_PricingTableModel();
		$sgptFeature = new SGPT_PtFeatureModel();
		$sgptPlan = new SGPT_PtPlanModel();
		$mainTableName = $mainSgpt::TABLE;
		$mainFeature = $sgptFeature::TABLE;
		$mainPlan = $sgptPlan::TABLE;
		
		$sgptDataSqlPt = "SELECT * FROM ".$wpdb->prefix.'sgpt_'.$mainTableName;
		$getAllPricingTables = $wpdb->get_results($sgptDataSqlPt, ARRAY_A);

		foreach ($getAllPricingTables as $singlePricingTable) {
			$sgptDataSqlPlan = "SELECT * FROM ".$wpdb->prefix.'sgpt_'.$mainPlan.' WHERE pt_id='.intval($singlePricingTable['id']);
			$getAllPlans = $wpdb->get_results($sgptDataSqlPlan, ARRAY_A);
			$singlePricingTable['plans'] = array();
			foreach ($getAllPlans as $singlePlan) {
				$sgptDataSqlFeature = "SELECT * FROM ".$wpdb->prefix.'sgpt_'.$mainFeature.' WHERE plan_id='.intval($singlePlan['id']);
				$getAllFeatures = $wpdb->get_results($sgptDataSqlFeature, ARRAY_A);
				$singlePlan['features'] = array();
				foreach ($getAllFeatures as $singleFeature) {
					$singlePlan['features'][] = $singleFeature;
				}

				$singlePricingTable['plans'][] = $singlePlan;
			}

			$exportArray[] = $singlePricingTable;
		}

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"sgptexportdata.txt\";" );
		header("Content-Transfer-Encoding: binary");
		echo base64_encode(serialize($exportArray));
	}

	public function sgptSetVersion()
	{
		$this->includeModel('PtPlan');

		$sgptVersion = get_option('SGPT_VERSION');
		if (!$sgptVersion || $sgptVersion < SGPT_VERSION) {
			SGPT_PtPlanModel::create();
			update_option('SGPT_VERSION', SGPT_VERSION);
		}
	}

	public function sgpt_media_button() {
		$buttonTitle = "Insert pricing table";
		$output = '';
		$buttonIcon = '<i class="dashicons dashicons-welcome-widgets-menus" style="padding: 3px 2px 0px 0px"></i>';
		$output = '<a href="javascript:void(0);" onclick="jQuery(\'#sgpt-thickbox\').dialog({ width: 450, modal: true });" class="button" title="'.$buttonTitle.'" style="padding-left: .4em;">'. $buttonIcon.$buttonTitle.'</a>';
		echo $output;
		add_action('admin_footer',array($this,'mediaButtonThickboxs'));
	}

	public function mediaButtonThickboxs()
	{
		global $sgpt;
		$sgpt->includeStyle('page/styles/media-button-popup');
		$sgpt->includeStyle('page/styles/jquery-ui-dialog');
		$sgpt->includeScript('core/scripts/jquery-ui-dialog');

		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('#sgpt-insert').on('click', function () {
					var id = jQuery('#sgpt-buttons-id').val();
					if ('' === id) {
						jQuery('.not-selected-notice-message').css('display', 'inline');
						return;
					}
					else {
						jQuery('.not-selected-notice-message').css('display', 'none');
					}
					selectionText = '';
					if (typeof(tinyMCE.editors.content) != "undefined") {
						selectionText = (tinyMCE.activeEditor.selection.getContent()) ? tinyMCE.activeEditor.selection.getContent() : '';
					}
					window.send_to_editor('[sgpt_pricing_table id=' + id + ']');
					jQuery('#sgpt-thickbox').dialog( 'close' );
				});
			});
		</script>
		<div id="sgpt-thickbox" title="Insert pricing table" style="height:0px;width:350px;display:none">
			<div class="wrap">
				<p class="insert-title">Insert the shortcode for showing a Pricing Table.</p>
				<div>
					<select id="sgpt-buttons-id">
						<option value="">Please select...</option>
						<?php
							global $wpdb;
							$proposedTypes = array();
							$orderBy = 'id DESC';
							$allTables = SGPT_PricingTableModel::finder()->findAll();
							foreach ($allTables as $table) : ?>
								<option value="<?php echo esc_attr($table->getId());?>"><?php echo esc_html($table->getName());?></option>;
							<?php endforeach; ?>
					</select>
				</div>
				<p class="not-selected-notice-message" style="display:none">Notice : select your pricing table</p>
				<p class="submit">
					<input type="button" id="sgpt-insert" class="button-primary dashicons-share" value="Insert"/>
					<a  class="button-secondary" onclick="jQuery('#sgpt-thickbox').dialog( 'close' )" title="Cancel">Cancel</a>
				</p>
			</div>
		</div>
	<?php
	}

	public function includeController($controller)
	{
		require_once($this->app_path.'com/controllers/'.$controller.'.php');
	}

	public function includeView($view)
	{
		require_once($this->app_path.'com/views/'.$view.'.php');
	}

	public function includeModel($model)
	{
		require_once($this->app_path.'com/models/'.$model.'.php');
	}

	public function includeLib($lib)
	{
		require_once($this->app_path.'com/lib/'.$lib.'.php');
	}

	public function includeCore($core)
	{
		require_once($this->app_path.'com/core/'.$core.'.php');
	}

	public function asset($component)
	{
		return $this->app_url.'assets/'.$component;
	}

	public function tablename($tbl)
	{
		global $wpdb;
		return $wpdb->prefix.$this->prefix.$tbl;
	}

	public function layoutPath($layout)
	{
		return $this->app_path.'com/layouts/'.$layout.'.php';
	}

	public function adminUrl($action, $extra='')
	{
		$url = admin_url().'admin.php?page='.$this->actionToSlug($action);
		if ($extra) $url .= '&'.$extra;
		return $url;
	}

	public function adminPostUrl($action, $extra='')
	{
		$url = admin_url().'admin-post.php?action='.$action;
		if ($extra) $url .= '&'.$extra;
		return $url;
	}

	public function url($component)
	{
		return $this->app_url.$component;
	}

	public function redirect($component)
	{
		header('Location: '.$this->url($component));
		exit;
	}

	public function registerSetupController()
	{
		$this->includeController('Setup');
		$controllerName = $this->prefix.'SetupController';

		register_activation_hook($this->app_path.'app.php', array($controllerName, 'activate'));
		register_deactivation_hook($this->app_path.'app.php', array($controllerName, 'deactivate'));
		register_uninstall_hook($this->app_path.'app.php', array($controllerName, 'uninstall'));

		add_action('wpmu_new_blog', array($controllerName, 'createBlog'));
	}

	private function registerAjaxCallbacks()
	{
		if (count($this->autoload['front_ajax'])) {
			foreach ($this->autoload['front_ajax'] as $callback) {
				$id = 'wp_ajax_nopriv_'.$this->prefix.$callback['controller'].'_'.$callback['action'];
				$this->ajaxCallbacks[$id] = array($callback['controller'], $callback['action']);
				add_action($id, array($this, $id));

				$id = 'wp_ajax_'.$this->prefix.$callback['controller'].'_'.$callback['action'];
				$this->ajaxCallbacks[$id] = array($callback['controller'], $callback['action']);
				add_action($id, array($this, $id));
			}
		}

		if (count($this->autoload['admin_ajax'])) {
			foreach ($this->autoload['admin_ajax'] as $callback) {
				$id = 'wp_ajax_'.$this->prefix.$callback['controller'].'_'.$callback['action'];
				$this->ajaxCallbacks[$id] = array($callback['controller'], $callback['action']);
				add_action($id, array($this, $id));
			}
		}
	}

	private function registerPostCallbacks()
	{
		if (count($this->autoload['admin_post'])) {
			foreach ($this->autoload['admin_post'] as $callback) {
				$id = 'admin_post_'.$this->prefix.$callback['controller'].'/'.$callback['action'];
				$this->postCallbacks[$id] = array($callback['controller'], $callback['action']);
				add_action($id, array($this, $id));
			}
		}
	}

	private function registerShortcodes()
	{
		foreach ($this->autoload['shortcodes'] as $shortcode) {
			$id = 'wp_shortcode_'.$shortcode['shortcode'];
			$this->shortcodes[$id] = array($shortcode['controller'], $shortcode['action']);
			add_shortcode($shortcode['shortcode'], array($this, $id));
		}
	}

	public function setLocale($locale)
	{
		return 'en';
	}

	private function prepareForLocalization()
	{
		//add_filter('locale', array($this, 'setLocale'));
		//load_plugin_textdomain($this->prefix, false, dirname(plugin_basename(__FILE__)).'/../strings/');
	}

	public function includeScript($script)
	{
		if ($script=='wp_enqueue_media') {
			wp_enqueue_media();
			return;
		}

		if ($script=='wp_ajax_library') {
			add_action('wp_head', array($this, 'addAjaxLibrary'));
			return;
		}

		if (is_admin()) {
			wp_enqueue_script($this->prefix.$script, $this->asset($script.'.js'), array('jquery','jquery-ui-core', 'jquery-ui-sortable', 'wp-color-picker', 'media-upload', 'media-models'), false, true);
			wp_enqueue_media();
		}
		wp_enqueue_script($this->prefix.$script, $this->asset($script.'.js'), array('jquery'), false,true);
	}

	public function includeStyle($style)
	{
		wp_enqueue_style($this->prefix.$style, $this->asset($style.'.css'), array('wp-color-picker'));
		wp_enqueue_style('hugeit-free-banner', plugins_url('../../assets/page/styles/free-banner.css', __FILE__));
	}

	public function includeAdminScriptsAndStyles($hook)
	{
		if (count($this->autoload['admin_scripts'])) {
			foreach ($this->autoload['admin_scripts'] as $script) {
				$this->includeScript($script);
			}
		}

		if (count($this->autoload['admin_styles'])) {
			foreach ($this->autoload['admin_styles'] as $style) {
				$this->includeStyle($style);
			}
		}
	}

	public function includeFrontScriptsAndStyles($hook)
	{
		if (count($this->autoload['front_scripts'])) {
			foreach ($this->autoload['front_scripts'] as $script) {
				$this->includeScript($script);
			}
		}

		if (count($this->autoload['front_styles'])) {
			foreach ($this->autoload['front_styles'] as $style) {
				$this->includeStyle($style);
			}
		}
	}

	public function addAjaxLibrary() {
		$html = "<script type=\"text/javascript\">\n";
		$html .= 'var ajaxurl = "'.admin_url('admin-ajax.php' ).'";'."\n";
		$html .= "</script>\n";

		echo $html;
	}

	public function actionToSlug($action)
	{
		if (isset($this->slugs[$action])) {
			return $this->slugs[$action];
		}

		return '';
	}

	private function dispatchAction($action, $param1, $param2)
	{
		$this->includeController(ucfirst($action[0]));

		$controllerName = strtoupper($this->prefix).ucfirst($action[0]).'Controller';

		$controller = new $controllerName();
		$controller->setController($action[0]);
		$controller->setAction($action[1]);
		return $controller->dispatch($param1, $param2);
	}

	public function loadMenu()
	{
		$this->loadMenuItems('menu_items');
	}

	public function loadNetworkAdminMenu()
	{
		$this->loadMenuItems('network_admin_menu_items');
	}

	public function loadMenuItems($key)
	{
		$autoload = $this->autoload;
		foreach ($autoload[$key] as $menu_item) {
			$menu_item_slug = $this->prefix.$menu_item['id'];
			$menu_item_func = array($this, $menu_item['id']);
			$this->menuActions[$menu_item['id']] = array($menu_item['controller'], $menu_item['action']);
			$this->slugs[$menu_item['controller'].'/'.$menu_item['action']] = $menu_item_slug;
			add_menu_page(
				$menu_item['page_title'],
				$menu_item['menu_title'],
				$menu_item['capability'],
				$menu_item_slug,
				$menu_item_func,
				$menu_item['icon']
			);
			if (count($menu_item['submenu_items'])) {
				foreach ($menu_item['submenu_items'] as $submenu_item) {
					$submenu_item_slug = $this->prefix.$submenu_item['id'];
					$submenu_item_func = array($this, $submenu_item['id']);
					$this->menuActions[$submenu_item['id']] = array($submenu_item['controller'], $submenu_item['action']);
					$this->slugs[$submenu_item['controller'].'/'.$submenu_item['action']] = $submenu_item_slug;
					add_submenu_page(
						$menu_item_slug,
						$submenu_item['page_title'],
						$submenu_item['menu_title'],
						$submenu_item['capability'],
						$submenu_item_slug,
						$submenu_item_func
					);
				}
			}
		}
	}
}
