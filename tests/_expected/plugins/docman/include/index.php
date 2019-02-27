<?php

// I can include stuff defined in another plugin
echo dgettext('tuleap-tracker', 'Tracker');
echo $GLOBALS['Language']->getText('plugin_docman', 'descriptor_name');
