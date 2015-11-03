<?php
namespace Craft;

class FruitIcons_IconFieldType extends BaseFieldType
{
    /**
     * Fieldtype name
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Icon');
    }
    
	/**
	 * Returns the default icon vendor options.
	 *
	 * @return array
	 */
	public function getIconVendors()
	{
		return array(
			'fontawesome' => 'Font Awesome',
			'glyphicons' => 'Glyphicons',
			'icomoon' => 'IcoMoon',
			'ionicons' => 'Ionicons'
		);
	}
	
	/**
	 * Returns the default values
	 *
	 * @return array
	 */
	public function getValueDefaults()
	{
		return array(
			'icon' => false,
			'class' => false,
			'vendor' => false
		);
	}

    /**
     * Define database column
     *
     * @return AttributeType::Mixed
     */
    public function defineContentAttribute()
    {
        return array(AttributeType::Mixed);
    }
    
	/**
	 * Defines the settings.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		$settings['vendor'] = AttributeType::String;
		$settings['defaultIcon'] = AttributeType::Bool;
		$settings['defaultIconClass'] = AttributeType::String;
		//$settings['customVendorName'] = AttributeType::String;	
		//$settings['customVendorTag'] = AttributeType::String;	
		//$settings['customVendorIcons'] = AttributeType::String;	
		return $settings;
	}

	/**
	 * Returns the field's settings HTML.
	 *
	 * @return string|null
	 */
	public function getSettingsHtml()
	{			
		return craft()->templates->render('fruiticons/_fieldtype/settings', array(
			'vendors' 			   		=> $this->getIconVendors(),
			'settings' 			  	 	=> $this->getSettings()
		));
	}


    /**
     * Display our fieldtype
     *
     * @param string $name  Our fieldtype handle
     * @return string Return our fields input template
     */
    public function getInputHtml($name, $value)
    {

    	// Settings
    	$settings = $this->getSettings();
    	
    	// This is where we will be getting vendor icons etc etc
    	require_once(craft()->path->getPluginsPath() . 'fruiticons/data/fontawesome.php' );
		$icons = fontAwesomeIcons();

       	// Selectize Includes
    	craft()->templates->includeJsResource('fruiticons/tools/selectize/js/standalone/selectize.js');
    	craft()->templates->includeCssResource('fruiticons/tools/selectize/css/selectize.css');    
    	craft()->templates->includeCssResource('fruiticons/tools/selectize/css/selectize.default.css');    

       	// Font Libraries
    	craft()->templates->includeCssFile('//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css');

       	// Plugin Includes
    	craft()->templates->includeJsResource('fruiticons/js/icons.js');
    	craft()->templates->includeCssResource('fruiticons/css/icons.css');
    	
    	$id = craft()->templates->formatInputId($name);
        craft()->templates->includeJs('var fruitIcons = new FruitIcons("'.craft()->templates->namespaceInputId($id).'");');

		// Render The Field
    	return craft()->templates->render('fruiticons/_fieldtype/input', array(
            'name'  => $name,
            'value'  => isset($value['isDefault']) ? null : $value,
            'icons' => $icons,
            'settings' => $settings
        )); 
    }

    
	/**
	 * Returns the input value as it should be saved to the database.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValueFromPost($value)
	{
		if($value != '')
		{
			$value = array(
				'class' => $value,
				'icon' => '<i class="fa '.$value.'"></i>'
			);
 		}
		return is_array($value) ? json_encode($value) : $value;
	}


	/**
	 * Preps the field value for use.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepValue($value)
	{
		// Settings
		$settings = $this->getSettings();
		
		if(is_array($value))
		{
			$value['icon'] = TemplateHelper::getRaw($value['icon']);
		}
		elseif($settings['defaultIcon'])
		{
			// Default Icon
			$value['isDefault'] = true;
			$value['class'] = $settings['defaultIconClass'];
			$value['icon'] = TemplateHelper::getRaw('<i class="fa '.$settings['defaultIconClass'].'"></i>');
		}

		/*
		if(is_array($value))
		{
			// Icons
			$icons = craft()->config->get('icons', 'fruiticons');

			// Get Defualts
			$defaults = $this->getValueDefaults();
		
			// Merge With Defaults
			$value = array_merge($defaults, $value);
			
			
			// Process?
			if($value['class'] == '')
			{
				$value = false;	
			}
			else
			{				
				// Build Icon Object
				$value['icon'] = '<i class="'.$value['class'].'"></i>';
				$value['vendor'] = 'fontawesome';


				// TODO : Add Vendor Logic
				switch($value['vendor'])
				{
					case('fontawesome'): 


						break;
				}
				

			}
		}
		*/
		
		return is_array($value) ? $value : null;
	}    
	

	
	
	/**
	 * Validates the value beyond the checks that were assumed based on the content attribute.
	 *
	 * Returns 'true' or any custom validation errors.
	 *
	 * @param array $value
	 * @return true|string|array
	 */
	public function validate($value)
	{
		$errors = array();


		return $errors ? $errors : true;
			
	}		
	
}
