<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * No redistribute in other sites, or copy.
 *
 * @author    RSI
 * @copyright 2007-2015 RSI
 * @license   http://localhost
 */


if (!defined('_PS_VERSION_'))
	exit;

class SoldItems extends Module
{
	private $output = '';
	private $_html = '';
	private $_postErrors = array();
	
	public function __construct()
	{
		$this->name = 'solditems';
		if (_PS_VERSION_ < '1.4.0.0')
		$this->tab = 'pricing_promotion';
		if (_PS_VERSION_ > '1.4.0.0')
		{
			$this->tab           = 'pricing_promotion';
			$this->author        = 'RSI';
			$this->need_instance = 0;
		}
		
		$this->version = '2.1.0';

		parent::__construct();

		$this->displayName = $this->l('Sold Items');
		$this->description = $this->l('Shows the quantity sold of a product - www.catalogo-onlinersi.com.ar');
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
			if (_PS_VERSION_ > '1.6.0.0')
			$this->bootstrap = true;

	}

	public function install()
	{
		if (!Configuration::updateValue('SOLDITEMS_NBR', 1) || !Configuration::updateValue('SOLDITEMS_ST', 1) || !parent::install() || !$this->registerHook('extraright') || !$this->registerHook('header'))
			return false;

		return true;
	}

	public function postProcess()
	{
		$errors = false;

		if ($errors)
			echo $this->displayError($errors);
	}


	private function _postProcess()
	{
		if (isset($_POST['submitSoldItems']))
		{
			Configuration::updateValue('SOLDITEMS_NBR', $_POST['nbr']);
			Configuration::updateValue('SOLDITEMS_ST', $_POST['st']);
			
		}
			$output .= $this->displayConfirmation($this->l('Settings updated').'<br/>');
			return $output;
	}
	public function getConfigFieldsValues()
{
		$fields_values = array(
			'nbr' => Tools::getValue('nbr', Configuration::get('SOLDITEMS_NBR')),
				'st' => Tools::getValue('st', Configuration::get('SOLDITEMS_ST')),
			
		);
		return $fields_values;

	}
	public function displayForm()
	{
		/*$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		$languages       = Language::getLanguages();
		$iso             = Language::getIsoById($defaultLanguage);*/
		/*$divLangName     = 'link_label';*/
		$this->_html .= '
		
    
      <form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="form">
  <form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Show items solded more than:').'</label>
				<div class="margin-form">
					<input type="text" size="5" name="nbr" value="'.Tools::getValue('nbr', Configuration::get('SOLDITEMS_NBR')).'" />
					
					
				
		</div>
		<label>'.$this->l('Image style:').'</label>
		 <div class="margin-form"  >

        <select name="st" id="st">
        <option value="1"'.((Configuration::get('SOLDITEMS_ST') == '1') ? 'selected="selected"' : '').'>'.$this->l('1').'</option>
        <option value="2"'.((Configuration::get('SOLDITEMS_ST') == '2') ? 'selected="selected"' : '').'>'.$this->l('2').'</option>
		 <option value="3"'.((Configuration::get('SOLDITEMS_ST') == '3') ? 'selected="selected"' : '').'>'.$this->l('3').'</option>
		  <option value="4"'.((Configuration::get('SOLDITEMS_ST') == '4') ? 'selected="selected"' : '').'>'.$this->l('4').'</option>
		   <option value="5"'.((Configuration::get('SOLDITEMS_ST') == '5') ? 'selected="selected"' : '').'>'.$this->l('5').'</option>
        </select>

        </div> 
					
				
		<center><input type="submit" name="submitSoldItems" value="'.$this->l('Save').'" class="button" /></center>
  			<center>	<a href="../modules/solditems/moduleinstall.pdf">README</a></center><br/>	
			<center>	<a href="../modules/solditems/termsandconditions.pdf">TERMS</center></a><br/>	
			Video:<br/>
								  		<center><iframe width="640" height="360" src="https://www.youtube.com/embed/m4VKBJTs-yk?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe></center><br/>
										Other Products:<br/>
										<object type="text/html" data="http://catalogo-onlinersi.net/modules/productsanywhere/images.php?idproduct=&desc=yes&buy=yes&type=home_default&price=yes&style=false&color=10&color2=40&bg=ffffff&width=800&height=310&lc=000000&speed=5&qty=15&skip=29,14,42,44,45&sort=1" width="800" height="310" style="border:0px #066 solid;"></object>
										  <center>  <p>Follow  us:</p></center>
     <center><p><a href="https://www.facebook.com/ShackerRSI" target="_blank"><img src="'.$this->_path.'views/img/facebook.png" style="  width: 64px;margin: 5px;" /></a>
        <a href="https://twitter.com/prestashop_rsi" target="_blank"><img src="'.$this->_path.'views/img/twitter.png" style="  width: 64px;margin: 5px;" /></a>
         <a href="https://www.pinterest.com/prestashoprsi/" target="_blank"><img src="'.$this->_path.'views/img/pinterest.png" style="  width: 64px;margin: 5px;" /></a>
           <a href="https://plus.google.com/+shacker6/posts" target="_blank"><img src="'.$this->_path.'views/img/googleplus.png" style="  width: 64px;margin: 5px;" /></a>
            <a href="https://www.linkedin.com/profile/view?id=92841578" target="_blank"><img src="'.$this->_path.'views/img/linkedin.png" style="  width: 64px;margin: 5px;" /></a>
               <a href="https://www.youtube.com/channel/UCBFSNtJpjYj4zLX9nO_oZkg" target="_blank"><img src="'.$this->_path.'views/img/youtube.png" style="  width: 64px;margin: 5px;" /></a>
            </p></center>
  		
  		</form>
		
    </fieldset>
	<!--<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Contribute').'</legend>
				<p class="clear">'.$this->l('You can contribute with a donation if our free modules and themes are usefull for you. Clic on the link and support us!').'</p>
				<p class="clear">'.$this->l('For more modules & themes visit: www.catalogo-onlinersi.com.ar').'</p>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="HMBZNQAHN9UMJ">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" width="1" height="1">
	</fieldset>
</form>-->
	';






		return $this->_html;
	}
private function _displayInfo()
	{
		return $this->display(__FILE__, 'views/templates/hook/infos.tpl');
	}

	private function _displayAdds()
	{
		return $this->display(__FILE__, 'views/templates/hook/adds.tpl');
	}
		public function renderForm()
	{
		$this->_postProcess();
$options                          = array(

			array(
				'id_option' => '1',
				'name'      => $this->l('1'),
			),
			array(
				'id_option' => '2',
				'name'      => $this->l('2'),
			),
			array(
				'id_option' => '3',
				'name'      => $this->l('3'),
			),
			array(
				'id_option' => '4',
				'name'      => $this->l('4'),
			),
			array(
				'id_option' => '5',
				'name'      => $this->l('5'),
			),
			

		);
		$fields_form                      = array(
			'form' => array(
				'legend'      => array(
					'title' => $this->l('Configuration'),
					'icon'  => 'icon-cogs'
				),
				'description' => $this->l('Settings'),
				'input'       => array(
				/*	array(
						'type'    => 'text',
						'label'   => $this->l('Order status ID'),
						'name'    => 'state1',
						'desc'    => $this->l('Order status (will be filled automatically)'),
						
					),*/
					array(
						'type'    => 'text',
						'label'   => $this->l('Show items solded more than:'),
						'name'    => 'nbr',
					//	'desc'    => $this->l('Read the README to get this ID number'),
						
					),
				
				array(
						'type'    => 'select',
						'label'   => $this->l('Type of image'),
						'name'    => 'st',
						'options' => array(
							'query' => $options,
							'id'    => 'id_option',
							'name'  => 'name'
						)
					),),
				'submit'      => array(
					'title' => $this->l('Update settings'),
				)


			),




		);
		$helper                           = new HelperForm();
		$helper->show_toolbar             = true;
		$helper->table                    = $this->table;
		$lang                             = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language    = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form                = array();
		$helper->identifier               = $this->identifier;
		$helper->submit_action            = 'submitSoldItems';
		$helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

		$helper->token    = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages'    => $this->context->controller->getLanguages(),
			'id_language'  => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form));
	}
	public function getContent()
	{
		$output = '';
		$errors = '';
		if (_PS_VERSION_ < '1.6.0.0')
		{
		$this->postProcess();
		
		

		if (Tools::isSubmit('submitSoldItems'))
		{
			$nbr  = Tools::getValue('nbr');
			$st  = Tools::getValue('st');
			//$text = Tools::getValue('text');
			Configuration::updateValue('SOLDITEMS_NBR', $nbr);
			Configuration::updateValue('SOLDITEMS_ST', $st);
			//Configuration::updateValue('SOLDITEMS_TEXT', $text);

			$this->_html .= @$errors == '' ? $this->displayConfirmation('Settings updated successfully') : @$errors;

		}

		return $this->displayForm();
		}
		else
					return $this->_html.$this->_displayInfo().$this->renderForm().$this->_displayAdds();

		

	}

	public function getProductscath($id_product)
	{
		$result = Db::getInstance()->getRow('
		
		SELECT SUM(od.`product_quantity`) AS totalCount
		
		FROM '._DB_PREFIX_.'order_detail od 
		WHERE od.product_id = '.(int)$id_product.'');

		return $result['totalCount'];

	}

	public function hookHeader($params)
	{
		if (_PS_VERSION_ < '1.4.0.0')
			return $this->display(__FILE__, 'views/templates/front/solditems-header.tpl');
		if (_PS_VERSION_ > '1.4.0.0' && _PS_VERSION_ < '1.5.0.0')
			Tools::addCSS(__PS_BASE_URI__.'modules/solditems/views/css/style.css', 'all');
		if (_PS_VERSION_ > '1.5.0.0')
			$this->context->controller->addCSS(($this->_path).'views/css/style.css', 'all');
			
	}

	public function hookExtraRight($params)
	{
		$id_product = Tools::getValue('id_product');
		$result     = '
		
		SELECT COUNT(p.`id_product`) AS totalCount
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN '._DB_PREFIX_.'order_detail od ON od.product_id = p.id_product
		LEFT JOIN '._DB_PREFIX_.'orders o ON od.id_order = o.id_order
		WHERE p.active = 1 AND o.valid = 1 AND p.id_product = '.(int)$id_product.'';


		$productsi = SoldItems::getProductscath($id_product);
		$pric      = Configuration::get('SOLDITEMS_NBR');
$soldimg      = Configuration::get('SOLDITEMS_ST');



		$this->context->smarty->assign(array(
			'pric'       => $pric,
			'soldimg'       => $soldimg,
			'id_product' => $id_product,
			'productsi'  => $productsi

		));

		return $this->display(__FILE__, 'views/templates/front/solditems.tpl');
	}

}

?>