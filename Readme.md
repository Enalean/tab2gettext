.tab to gettext
===============

This is intended to replace all usages of .tab to gettext (.po) counterparts.

Usage
-----

```
./run.php \
    ~/tuleap plugin_tracker \
    tuleap-tracker \
    ~/tuleap/cache.lang.en_US.php \
    ~/tuleap/cache.lang.fr_FR.php \
    ~/tuleap/plugins/tracker/site-content \
    tracker.tab
```

where:
* `~/tuleap` is the path to the sources (⚠️ all php files in it will be parsed!)
* `plugin_tracker` is the primary key
* `tuleap-tracker` is the target domain
* `~/tuleap/cache.lang.en_US.php` is the cached file of the en_US strings (can be copied from /var/tmp/tuleap_cache)
* `~/tuleap/cache.lang.fr_FR.php` is the cached file of the fr_FR strings (can be copied from /var/tmp/tuleap_cache)
* `~/tuleap/plugins/tracker/site-content` is the target site-content directory (where .po file will be updated)
* `tracker.tab` is the .tab files name that we need to treat

Then you should run `make generate-po` to sort po entries. Look for 
warnings as you may end up with duplicated entries (poedit may fix 
some issues for you).
