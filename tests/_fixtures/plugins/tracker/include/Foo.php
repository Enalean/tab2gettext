<?php
/**
 * Copyright (c) Enalean, 2011. All Rights Reserved.
 */

class trackerPluginDescriptor extends PluginDescriptor {

    function __construct() {
        parent::__construct($GLOBALS['Language']->getText('plugin_tracker', 'descriptor_name'), false, $GLOBALS['Language']->getText('plugin_tracker_suffix', 'descriptor_description'));
        $this->setVersionFromFile(dirname(__FILE__).'/../VERSION');

        //duplicated translations
        $GLOBALS['Language']->getText('plugin_tracker', 'name');
        $GLOBALS['Language']->getText('plugin_tracker', 'other_name');

        // concatenations
        $GLOBALS['Language']->getText('plugin_tracker', 'plugin_allowed_project_title', array($this->plugin->getPluginInfo()->getPluginDescriptor()->getFullName()));
        $GLOBALS['Language']->getText('plugin_tracker', 'plugin_allowed_project_title', $this->plugin->getPluginInfo()->getPluginDescriptor()->getFullName());
        $GLOBALS['Language']->getText('plugin_tracker', 'key_with_two_replacements', [$a, $b]);

        // ignore such expressions
        $this->$method();
        $this->{$this->method}();
    }
}
