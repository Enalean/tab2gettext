<?php
/**
 * Copyright (c) Enalean, 2011. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class trackerPluginDescriptor extends PluginDescriptor {

    function __construct() {
        parent::__construct($GLOBALS['Language']->getText('plugin_tracker', 'descriptor_name'), false, $GLOBALS['Language']->getText('plugin_tracker_suffix', 'descriptor_description'));
        $this->setVersionFromFile(dirname(__FILE__).'/../VERSION');

        // concatenations
        $GLOBALS['Language']->getText('plugin_tracker', 'plugin_allowed_project_title', array($this->plugin->getPluginInfo()->getPluginDescriptor()->getFullName()));
        $GLOBALS['Language']->getText('plugin_tracker', 'plugin_allowed_project_title', $this->plugin->getPluginInfo()->getPluginDescriptor()->getFullName());
        $GLOBALS['Language']->getText('plugin_tracker', 'key_with_two_replacements', $a, $b);
        $GLOBALS['Language']->getText('plugin_tracker', 'key_with_two_replacements', [$a, $b]);

        // ignore such expressions
        $this->$method();
        $this->{$this->method}();
    }
}
