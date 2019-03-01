<?php
/**
 * Copyright (c) Enalean, 2011. All Rights Reserved.
 */

class trackerPluginDescriptor extends PluginDescriptor {

    function __construct() {
        parent::__construct(dgettext('tuleap-tracker', 'Tracker'), false, dgettext('tuleap-tracker', 'Trackers new generation'));
        $this->setVersionFromFile(dirname(__FILE__).'/../VERSION');

        //duplicated translations
        dgettext('tuleap-tracker', 'Tracker');
        dgettext('tuleap-tracker', 'Tracker');

        // concatenations
        sprintf(dgettext('tuleap-tracker', '%1$s projects restriction'), $this->plugin->getPluginInfo()->getPluginDescriptor()->getFullName());
        sprintf(dgettext('tuleap-tracker', '%1$s projects restriction'), $this->plugin->getPluginInfo()->getPluginDescriptor()->getFullName());
        sprintf(dgettext('tuleap-tracker', '%2$s blah %1$s'), $a, $b);
        sprintf(dgettext('tuleap-tracker', '%2$s blah %1$s'), $a, $b);

        // ignore such expressions
        $this->$method();
        $this->{$this->method}();
    }
}
