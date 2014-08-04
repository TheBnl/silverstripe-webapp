<?php

class WebAppStartupScreen extends DataObject {

	// TODO restric user to only be able to upload one of each icon
	// TODO restrict amount of objects

	private static $db = array(
		"Device" => "Enum('ipad-retina-portrait,ipad-retina-landscape,ipad-portrait,ipad-landscape,iphone-tall,iphone-retina,iphone','ipad-retina-portrait')",
		"Media" => "Varchar(255)"
	);

	private static $has_one = array(
		'Image' => 'Image',
		'WebAppConfig' => 'WebAppConfig'
	);

	private static $summary_fields = array(
		'thumbnailImage' => 'SplashScreen',
		'Device' => 'Device'
	);

	public function thumbnailImage(){
		return $this->Image()->SetHeight(40);
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab(
			'Root.Main',
			new DropdownField(
				'Device',
				'Startup screen size:',
				singleton('WebAppStartupScreen')->dbObject('Device')->enumValues()
			)
		);

		$fields->addFieldToTab(
			'Root.Main',
			$uploadField = new UploadField(
				$name = 'Image',
				$title = 'Upload a Splash Screen'
			)
		);
		$uploadField->setAllowedMaxFileNumber(1);
		$uploadField->setFolderName('splashscreens');
		$uploadField->setAllowedExtensions(array('png'));
		$sizeMB = 5;
		$size = $sizeMB * 1024 * 1024;
		$uploadField->getValidator()->setAllowedMaxFileSize($size);

		// hide the media field, this gets written inBefore Write. See below
		$fields->addFieldToTab('Root.Main', new HiddenField('Media'));
		// hide the settings field
		$fields->removeByName('WebAppConfigID');

		return $fields;
	}

	function onBeforeWrite() {
		$media = $this->Device;

		switch ($media) {
			case 'iphone':
				$this->setField('Media', '(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 1)');
				break;
			case 'iphone-tall':
				$this->setField('Media', '(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)');
				break;
			case 'iphone-retina':
				$this->setField('Media', '(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)');
				break;
			case 'ipad-portrait':
				$this->setField('Media', '(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 1)');
				break;
			case 'ipad-landscape':
				$this->setField('Media', '(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 1)');
				break;
			case 'ipad-retina-portrait':
				$this->setField('Media', '(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)');
				break;
			case 'ipad-retina-landscape':
				$this->setField('Media', '(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)');
				break;
		}

		parent::onBeforeWrite();
	}
}