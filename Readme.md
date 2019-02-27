.tab to gettext
===============

This is intended to replace all usages of .tab to gettext (.po) counterparts.

Usage
-----

```
./run.php ~/tuleap plugin_tracker tuleap-tracker ~/tuleap/cache.lang.en_US.php
```

where:
* `~/tuleap` is the path to the sources (⚠️ all php files in it will be parsed!)
* `plugin_tracker` is the primary key
* `tuleap-tracker` is the target domain
* `~/tuleap/cache.lang.en_US.php` is the cached file of the en_US strings (can be located in /var/tmp/tuleap_cache)

