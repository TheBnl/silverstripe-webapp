<?php

class WebAppIcon extends DataObject
{

    // TODO restric user to only be able to upload one of each icon
    // TODO restrict amount of objects

    private static $db = array(
        "Size" => "Enum('152x152,144x144,120x120,114x114,76x76,72x72,57x57','152x152')"
    );
    private static $has_one = array(
        'Image' => 'Image',
        'WebAppConfig' => 'WebAppConfig'
    );

    private static $summary_fields = array(
        'thumbnailImage' => 'Icon',
        'Size' => 'Size'
    );

    public function thumbnailImage()
    {
        return $this->Image()->CroppedImage(30, 30);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            new DropdownField(
                'Size',
                'Icon size:',
                singleton('WebAppIcon')->dbObject('Size')->enumValues()
            )
        );

        $fields->addFieldToTab(
            'Root.Main',
            $uploadField = new UploadField(
                $name = 'Image',
                $title = 'Upload a App Icon'
            )
        );
        $uploadField->setAllowedMaxFileNumber(1);
        $uploadField->setFolderName('appicons');
        $uploadField->setAllowedExtensions(array('png'));
        $sizeMB = 1;
        $size = $sizeMB * 1024 * 1024;
        $uploadField->getValidator()->setAllowedMaxFileSize($size);

        // hide the settings field
        $fields->removeByName('WebAppConfigID');

        return $fields;
    }
}
