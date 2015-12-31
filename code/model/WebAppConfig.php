<?php

/**
 * AppConfig.
 *
 * @property string  AppTitle       Title of the webapp to be displayed underneath the app icon.
 * @property bool    Fullscreen     Option to show the app in fullscreen mode.
 * @property enum    StatusBar      Statusbar options.
 * @property bool    MinimalUI      Option to use the Minimal UI (new in iOS7).
 * @property bool    UserScalable   Viewport option to disable/enable scaling of the content.
 * @property bool    Javascript     Option to use experimental javascript, the javascript is used to keep a user in the fullscreen app experience but has to be used with caution because it overrides all <a> tags includeing the links that link to external sites and the links that dont go anywhere. This is beiing worked on.
 *
 * @method   HasMany Icons          This module can manage multiple sorts of icons for multiple device types.
 * @method   HasMany StartupScreens This module can manage multiple sorts of splash screens for multiple device types.
 *
 * @author   Bram de Leeuw
 * @package  MobileWebAppAdmin
 */

class WebAppConfig extends DataObject implements PermissionProvider
{

    private static $db = array(
        "AppTitle" => "Varchar(255)",
        "Fullscreen" => "Enum('yes,no','no')",
        "StatusBar" => "Enum('default,black,black-translucent','default')",
        "MinimalUIOption" => "Enum('yes,no','no')",
        "MinimalUI" => "Varchar(255)",
        "UserScalable" => "Enum('yes,no','no')",
        //"Width" => "Enum('yes,no','no')",
        "Javascript" => "Enum('yes,no','no')"
    );

    private static $has_many = array(
        'WebAppIcons' => 'WebAppIcon',
        'WebAppStartupScreens' => 'WebAppStartupScreen'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Config',
            new TextField(
                'AppTitle',
                'App Title:'
            )
        );

        $fields->addFieldToTab(
            'Root.Config',
            new OptionsetField(
                'Fullscreen',
                'Fullscreen app:',
                singleton('WebAppConfig')->dbObject('Fullscreen')->enumValues()
            )
        );

        $fields->addFieldToTab(
            'Root.Config',
            new DropdownField(
                'StatusBar',
                'StatusBar style:',
                singleton('WebAppConfig')->dbObject('StatusBar')->enumValues()
            )
        );

        $fields->addFieldToTab('Root.Config', new LabelField('Label', 'Viewport meta options'));
        $fields->addFieldToTab(
            'Root.Config',
            new OptionsetField(
                'UserScalable',
                'Let users scale content:',
                singleton('WebAppConfig')->dbObject('UserScalable')->enumValues()
            )
        );
        $fields->addFieldToTab(
            'Root.Config',
            new OptionsetField(
                'MinimalUIOption',
                'Use Minimal UI:',
                singleton('WebAppConfig')->dbObject('MinimalUIOption')->enumValues()
            )
        );

        $fields->addFieldToTab('Root.Config', new LabelField('Label', 'Adds experimental javascript that keeps an user inside a fullscreen app instead of opening a new browser window'));
        $fields->addFieldToTab(
            'Root.Config',
            new OptionsetField(
                'Javascript',
                'Add javascript:',
                singleton('WebAppConfig')->dbObject('Javascript')->enumValues()
            )
        );

        // add a icons tab with a gridfield
        $gridField = new GridField("WebAppIcons", "WebAppIcons", $this->WebAppIcons(), GridFieldConfig_RecordEditor::create());
        $fields->addFieldToTab("Root.Icons", $gridField);

        // add a StartupScreens tab with a gridfield
        $gridField = new GridField("WebAppStartupScreens", "WebAppStartupScreens", $this->WebAppStartupScreens(), GridFieldConfig_RecordEditor::create());
        $fields->addFieldToTab("Root.StartupScreens", $gridField);

        // remove the default empty tab and the empty WebAppIcons WebAppStartupScreens tabs
        $fields->removeByName(array('Main', 'WebAppIcons', 'WebAppStartupScreens'));

        // hide the MinimalUI field, this gets written inBefore Write. See below
        $fields->addFieldToTab('Root.Config', new HiddenField('MinimalUI'));

        return $fields;
    }

    public function onBeforeWrite()
    {
        $MinimalUI = $this->MinimalUIOption;

        if ($MinimalUI == 'yes') {
            $this->setField('MinimalUI', ' , minimal-ui');
        } elseif ($MinimalUI == 'no') {
            $this->setField('MinimalUI', '');
        }

        parent::onBeforeWrite();
    }

    /**
     * The code from here on out is copied from the SiteConfig and is written by
     *
     * Get the actions that are sent to the CMS. In
     * your extensions: updateEditFormActions($actions)
     *
     * @return Fieldset
     */
    public function getCMSActions()
    {
        if (Permission::check('ADMIN') || Permission::check('EDIT_SITECONFIG')) {
            $actions = new FieldList(
                FormAction::create('save_siteconfig', _t('CMSMain.SAVE', 'Save'))
                    ->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept')
            );
        } else {
            $actions = new FieldList();
        }

        $this->extend('updateCMSActions', $actions);

        return $actions;
    }

    /**
     * @return String
     */
    public function CMSEditLink()
    {
        return singleton('MobileWebAppAdmin')->Link();
    }

    /**
     * Get the current sites SiteConfig, and creates a new one
     * through {@link make_site_config()} if none is found.
     *
     * @return SiteConfig
     */
    public static function current_site_config()
    {
        if ($siteConfig = DataObject::get_one('WebAppConfig')) {
            return $siteConfig;
        }

        return self::make_site_config();
    }

    /**
     * Setup a default SiteConfig record if none exists
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $siteConfig = DataObject::get_one('WebAppConfig');
        if (!$siteConfig) {
            self::make_site_config();
            DB::alteration_message("Added default site config", "created");
        }
    }

    /**
     * Create SiteConfig with defaults from language file.
     *
     * @return SiteConfig
     */
    public static function make_site_config()
    {
        $config = WebAppConfig::create();
        $config->write();
        return $config;
    }

    /**
     * Can a user view pages on this site? This method is only
     * called if a page is set to Inherit, but there is nothing
     * to inherit from.
     *
     * @param mixed $member
     * @return boolean
     */
    public function canView($member = null)
    {
        if (!$member) {
            $member = Member::currentUserID();
        }
        if ($member && is_numeric($member)) {
            $member = DataObject::get_by_id('Member', $member);
        }

        if ($member && Permission::checkMember($member, "ADMIN")) {
            return true;
        }

        if (!$this->CanViewType || $this->CanViewType == 'Anyone') {
            return true;
        }

        // check for any logged-in users
        if ($this->CanViewType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())) {
            return true;
        }

        return false;
    }

    /**
     * Can a user edit pages on this site? This method is only
     * called if a page is set to Inherit, but there is nothing
     * to inherit from.
     *
     * @param mixed $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        if (!$member) {
            $member = Member::currentUserID();
        }
        if ($member && is_numeric($member)) {
            $member = DataObject::get_by_id('Member', $member);
        }

        if ($member && Permission::checkMember($member, "ADMIN")) {
            return true;
        }

        // check for any logged-in users
        if (!$this->CanEditType || $this->CanEditType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($this->CanEditType == 'OnlyTheseUsers' && $member && $member->inGroups($this->EditorGroups())) {
            return true;
        }

        return false;
    }

    public function providePermissions()
    {
        return array(
            'EDIT_SITECONFIG' => array(
                'name' => _t('SiteConfig.EDIT_PERMISSION', 'Manage site configuration'),
                'category' => _t('Permissions.PERMISSIONS_CATEGORY', 'Roles and access permissions'),
                'help' => _t('SiteConfig.EDIT_PERMISSION_HELP', 'Ability to edit global access settings/top-level page permissions.'),
                'sort' => 400
            )
        );
    }

    /**
     * Can a user create pages in the root of this site?
     *
     * @param mixed $member
     * @return boolean
     */
    public function canCreateTopLevel($member = null)
    {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        if (Permission::check('ADMIN')) {
            return true;
        }

        if ($member && Permission::checkMember($member, "ADMIN")) {
            return true;
        }

        // check for any logged-in users
        if ($this->CanCreateTopLevelType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($member && is_numeric($member)) {
            $member = DataObject::get_by_id('Member', $member);
        }
        if ($this->CanCreateTopLevelType == 'OnlyTheseUsers' && $member && $member->inGroups($this->CreateTopLevelGroups())) {
            return true;
        }


        return false;
    }
}
