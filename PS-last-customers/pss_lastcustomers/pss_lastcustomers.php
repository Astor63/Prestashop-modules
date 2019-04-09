<?php
/**
 * Display in a front-office block a list of the last customers.
 *
 * See LICENCE.txt for terms of use
 * History :
 * 	@version 0.3 (2012-11-12) : 
 *		Compatible with Prestashop 1.5.x
 *		Doesn't support multi-shop feature
 *		No more Prestascope pub
 * 	@version 0.2 (2011-11-30) : UTF-8 encode correct for last name
 * 	@version 0.1 : first version
 */
class pss_lastcustomers extends Module
{

	// absolute URL to this module
	public $absoluteUrl;
	// absolute path (in OS sense) to this module
	public $absolutePath;
	// the admin URL to get configuration screen for current module
	private $confUrl;

	private $classWarning;
	private $classError;

	private $_config = array(
		'PRESTASCOPE_LASTCUST_NB'	 	=> '10',
		// $last_name $first_name $country_name $postal_code $state_name $city_name
		'PRESTASCOPE_LASTCUST_PATTERN' 	=> '$first_name $last_name, $city_name',
		'PRESTASCOPE_LASTCUST_FLAGS'	=> '1',
		);

	function __construct()
	{
		// Module definition
		$this->name = 'pss_lastcustomers';
		// some changes between 1.3.x (and previous) and 1.4.x Prestashop versions
		if ($this->isPs12x() || $this->isPs13x())
		{
			$this->tab = 'Prestascope';
		}
		else if ($this->isPs14x())
		{
			$this->tab = 'front_office_features';
			$this->author = 'PrestaScope';
		}
		else if ($this->isPs15x())
		{
			$this->tab = 'front_office_features';
			$this->author = 'PrestaScope';
			// set the version compliancy
			$this->ps_versions_compliancy = array('min' => '1.2', 'max' => '1.6');	// 1.2 included / 1.6 excluded
		}
		$this->version = '0.3';

		// full url & path to current module
		// Use Tools::getHttpHost(false, true).__PS_BASE_URI__
		$this->absoluteUrl = $this->is_https()?'https':'https'.'://'.$_SERVER['HTTP_HOST']. _MODULE_DIR_ . $this->name . '/';
		$this->absolutePath = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->name.DIRECTORY_SEPARATOR;

		// standard constructor
		parent::__construct();

		// custom display for module list
		$this->displayName = $this->l('PSS/Block last customers');
		$buf = '<style type="text/css">';
		$buf .= 'a.descriptionLink {color:blue;text-decoration:underline;} a.descriptionLink:hover{color:red;text-decoration:none;}';
		$buf .= 'div.pssDescriptionDiv {background:#6a6a6a; color:white; padding:3px; margin-top:2px;}';
		$buf .= '</style>';
		$buf .= '<div class="pssDescriptionDiv">';
		$buf .= $this->l('Display a customizable list of last customers in a block');
		$buf .= '</div>';
        $this->description = $buf;

		// the admin URL to get configuration for current module
		$tab = Tools::getValue('tab');
		$token = Tools::getValue('token');
		$mainParts = explode('?', $_SERVER['REQUEST_URI']);
		$this->confUrl = $mainParts[0].'?tab=AdminModules&amp;configure='.$this->name.'&amp;token='.$token;

		// initialize class to use to display warning / errors
		$this->classWarning = 'warn';
		if (!$this->isPs14x() && !$this->isPs15x())
			$this->classWarning .= ' warning';
		$this->classError = 'error';
		if (!$this->isPs14x() && !$this->isPs15x())
			$this->classError .= ' alert';
	}
	private function is_https()
	{
		return strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? true : false;
	}
	function install()
	{
		// check required version - 1.2.x or 1.3.x or 1.4.x or 1.5.x
				if (	!parent::install() ||
				!$this->registerHook('leftColumn') ||
				!$this->_installConfig())
			return false;
		return true;
	}
	public function uninstall() {
		if(		!parent::uninstall() ||
				!$this->_uninstallConfig())
			return false;
		return true;
	}
	private function _installConfig() {
		foreach ($this->_config as $key => $value) {
			Configuration::updateValue($key, $value, true);
		}
		return true;
	}
	private function _uninstallConfig() {
		foreach ($this->_config as $key => $value) {
			Configuration::deleteByName($key);
		}
		return true;
	}
	/* *****************************************************************************************
	 *
	 *						FUNCTION TO DISPLAY BO FORMS AND MANAGE ACTIONS
	 *
	 * ***************************************************************************************** */
	/**
	 * Deal with BO configuration form user actions
	 */
	public function getContent()
	{
		global $cookie;

		// empty errors buffer
		$this->_errors = array();

		// common title to all displays
		$this->_html = '<h2>Prestascope : '.$this->l('Block last customers').'</h2>';

		// >>> UPDATE CONF
		if (Tools::getValue('submitUpdateSettings'))
		{
			if (!isset($_POST['PRESTASCOPE_LASTCUST_NB']) || !Validate::isUnsignedInt($_POST['PRESTASCOPE_LASTCUST_NB']))
				$this->_errors[] = $this->l('The number of customers should be a valid integer');
			if (!isset($_POST['PRESTASCOPE_LASTCUST_PATTERN']) || strlen($_POST['PRESTASCOPE_LASTCUST_PATTERN'])<=0)
				$this->_errors[] = $this->l('Row pattern should not be empty');
			if (count($this->_errors)==0)
			{
				Configuration::updateValue('PRESTASCOPE_LASTCUST_NB', (int)Tools::getValue('PRESTASCOPE_LASTCUST_NB'));
				Configuration::updateValue('PRESTASCOPE_LASTCUST_PATTERN', pSQL(Tools::getValue('PRESTASCOPE_LASTCUST_PATTERN'), true), true);
				Configuration::updateValue('PRESTASCOPE_LASTCUST_FLAGS', isset($_POST['PRESTASCOPE_LASTCUST_FLAGS']) && pSQL($_POST['PRESTASCOPE_LASTCUST_FLAGS'])==1?1:0);

				$this->_html .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
			}
		}

		// display errors if any
		if (!empty($this->_errors))
			foreach ($this->_errors as $error)
				$this->_html .= '<div class="alert error">'.$error.'</div>';

		return $this->_displayMainForm();
	}

	/**
	 * Build the BO configuration main form
	 */
	private function _displayMainForm()
	{
		global $cookie;

		// load all conf values
		$confs = Configuration::getMultiple(array_keys($this->_config));

		$this->_html .= '
			<form method="post" action="'.$this->confUrl.'">
				<fieldset>
					<legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Block settings').'</legend>
						<label>'.$this->l('Number of customers').': </label>
						<div class="margin-form">
							<input type="text" size="4" maxlength="4" name="PRESTASCOPE_LASTCUST_NB" value="'.$confs['PRESTASCOPE_LASTCUST_NB'].'" /> <sup>*</sup>
							<p>'.$this->l('The number of customers to display in the block').'</p>
						</div>
						<label>'.$this->l('Customer row pattern').': </label>
						<div class="margin-form">
							<input type="text" size="110" maxlength="255" name="PRESTASCOPE_LASTCUST_PATTERN" value="'.htmlspecialchars (stripslashes($confs['PRESTASCOPE_LASTCUST_PATTERN'])).'" /> <sup>*</sup>
							<p>'.$this->l('Each customer row is build with this pattern. You could include constant characters, HTML tags and the following fields : ').'</p>
							<p>'.$this->l('$last_name $first_name $country_name $postal_code $state_name $city_name').'</p>
							<p>'.$this->l('$last_name will display only the first capitalized letter following by a dot').'</p>
						</div>
						<label>'.$this->l('Display country flags').' :</label>
						<div class="margin-form">
							<input type="checkbox" name="PRESTASCOPE_LASTCUST_FLAGS" id="PRESTASCOPE_LASTCUST_FLAGS" value="1" '.(Configuration::get('PRESTASCOPE_LASTCUST_FLAGS') == 1 ? 'checked' : '').' />
							&nbsp;<label for="PRESTASCOPE_LASTCUST_FLAGS" class="t">'.$this->l('ok to display').'</label>
						</div>

						<div class="margin-form">
							<input type="submit" value="'.$this->l('   Update   ').'" name="submitUpdateSettings" class="button" />
						</div>
						<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
					</fieldset>
				</form>';

		return $this->_html;
	}
	// ******************************************************************************************
	//
	//
	// Block display
	//
	//
	// ******************************************************************************************
	function hookLeftColumn($params)
	{
		global $smarty, $cookie, $link;

		// extract criterias from configuration
		$confs = Configuration::getMultiple(array_keys($this->_config));

		// load the customers to display
		/*
			SELECT dummy.id_customer,dummy.id_address_delivery,a.postcode,a.city,co.iso_code,c.firstname,c.lastname FROM
			(SELECT id_customer,id_address_delivery FROM ps_orders o GROUP BY id_customer,id_address_delivery ORDER BY invoice_date DESC LIMIT 5) as dummy
			INNER JOIN ps_address a ON dummy.id_address_delivery=a.id_address
			INNER JOIN ps_country co ON a.id_country=co.id_country
			INNER JOIN ps_customer c ON dummy.id_customer=c.id_customer
		 */
		$query = '
			SELECT dummy.`id_customer`,dummy.`id_address_delivery`,a.`postcode`,a.`city`,st.`name` as state_name,co.`iso_code` as country_iso_code,col.`name` as country,c.`firstname`,c.`lastname` FROM
			(SELECT `id_customer`,`id_address_delivery` FROM `'._DB_PREFIX_.'orders` o GROUP BY `id_customer`,`id_address_delivery` ORDER BY `invoice_date` DESC LIMIT '.$confs['PRESTASCOPE_LASTCUST_NB'].') as dummy
				INNER JOIN `'._DB_PREFIX_.'address` a ON dummy.`id_address_delivery`=a.`id_address`
				INNER JOIN `'._DB_PREFIX_.'country` co ON a.`id_country`=co.`id_country`
				INNER JOIN `'._DB_PREFIX_.'country_lang` col ON co.`id_country`=col.`id_country`
				LEFT JOIN `'._DB_PREFIX_.'state` st ON a.`id_state`=st.`id_state`
				INNER JOIN `'._DB_PREFIX_.'customer` c ON dummy.`id_customer`=c.`id_customer`
				WHERE col.`id_lang`='.$cookie->id_lang;
		$customers = Db::getInstance()->ExecuteS($query);
		
		// trim all elements
		//$customers = array_map("trim", $customers);
		array_walk_recursive($customers, 'trim');
		// prepare datas
		foreach ($customers as &$customer)
		{
			// Keep only first capital letter of name and append a dot
			if (strlen($customer['lastname'])>0)
//				$customer['lastname'] = strtoupper(substr($customer['lastname'], 0, 1)).'.';
				$customer['lastname'] = utf8_encode(strtoupper(substr($customer['lastname'], 0, 1))).'.';
			// force the country iso code to lower case for image inclusion
			$customer['country_iso_code'] = strtolower($customer['country_iso_code']);
		}

		// apply pattern to each data
		foreach ($customers as &$customer)
		{
			$customer['display'] = $this->applyPattern($customer, stripslashes($confs['PRESTASCOPE_LASTCUST_PATTERN']));
		}

		// publish for template
		$displayFlag = intVal($confs['PRESTASCOPE_LASTCUST_FLAGS']);

		$smarty->assign(array(
			'customers'=>$customers,
			'displayFlags'=>$displayFlag,
			'absoluteUrl'=>$this->absoluteUrl,
			'ps15x'=>($this->isPs15x()?'true':'false')
			));

		return $this->display(__FILE__, 'pss_lastcustomers.tpl');
	}
	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	// ******************************************************************************************
	//
	//
	// Tools for installation
	//
	//
	// ******************************************************************************************
	/**
	 * Check if installed Prestashop is a 1.2.x version
	 */
	public static function isPs12x()
	{
		return self::checkPsVersion('1.2');
	}
	/**
	 * Check if installed Prestashop is a 1.3.x version
	 */
	public static function isPs13x()
	{
		return self::checkPsVersion('1.3');
	}
	/**
	 * Check if installed Prestashop is a 1.4.x version
	 */
	public static function isPs14x()
	{
		return self::checkPsVersion('1.4');
	}
	/**
	 * Check if installed Prestashop is a 1.5.x version
	 */
	public static function isPs15x()
	{
		return self::checkPsVersion('1.5');
	}
	/**
	 * Check if installed Prestashop match an input radix
	 */
	public static function checkPsVersion($radixVersion)
	{
		// get PS version
		$psVersion = _PS_VERSION_;
		if ($psVersion==null)
			return false;

		// look for version like 1.3.1, 1.3.7.0 or 1.4.3
		$subVersions = explode('.', $psVersion);
		$searchVersions = explode('.', $radixVersion);

		for ($i=0;$i<count($searchVersions);$i++)
		{
			// compare each sub part of version
			if ($subVersions[$i] !== $searchVersions[$i])
				return false;
		}
		return true;
	}
	// ******************************************************************************************
	//
	//
	// Some other tools
	//
	//
	// ******************************************************************************************
	private function applyPattern($customer, $pattern)
	{
		// init text with pattern
		$text = $pattern;

		// then search / replace known fields
		// $last_name $first_name $country_name $postal_code $state_name $city_name
		$text = str_replace('$first_name', $customer['firstname'], $text);
		$text = str_replace('$last_name', $customer['lastname'], $text);
		$text = str_replace('$country_name', $customer['country'], $text);
		$text = str_replace('$postal_code', $customer['postcode'], $text);
		$text = str_replace('$city_name', $customer['city'], $text);
		$text = str_replace('$state_name', $customer['state_name'], $text);

		return $text;
	}
}
?>
