[![Build Status](https://travis-ci.org/Enalean/tab2gettext.svg?branch=master)](https://travis-ci.org/Enalean/tab2gettext)

.tab to gettext
===============

This is intended to replace all usages of .tab to gettext (.po) counterparts.

Usage
-----

```
php index.php tab2gettext \
    --src-dir $HOME/tuleap \
    --primary-key plugin_tracker \
    --domain tuleap-tracker \
    --en-cache /tmp/cache.lang.en_US.php \
    --fr-cache /tmp/cache.lang.fr_FR.php \
    --target-dir $HOME/tuleap/plugins/tracker/site-content \
    --src-tab tracker.tab
```

where:
* `$HOME/tuleap` is the path to the sources (⚠️ all php files in it will be parsed!)
* `plugin_tracker` is the primary key
* `tuleap-tracker` is the target domain
* `/tmp/cache.lang.en_US.php` is the cached file of the en_US strings (can be copied from /var/tmp/tuleap_cache)
* `/tmp/cache.lang.fr_FR.php` is the cached file of the fr_FR strings (can be copied from /var/tmp/tuleap_cache)
* `$HOME/tuleap/plugins/tracker/site-content` is the target site-content directory (where .po file will be updated)
* `tracker.tab` is the .tab files name that we need to treat

Then you should run `make generate-po` to sort po entries. Look for 
warnings as you may end up with duplicated entries (poedit may fix 
some issues for you).
