
## RSWeb

`/doorbell` is different from RSDoorBellServer, independent and legacy system.

### Fix here

#### /www/.htpasswd
Generate this file with htpasswd (Basic auth).

#### /www/.htaccess
```
###MY_DOMAIN###
###ROOT_DIR###
###MY_USER###
```

#### Python Path
```
/www/script/api/wimax.php
/www/script/api/sensor.php
/www/script/api/wimax.php
```

Edit `###PYTHON_PATH###` to your valid python path.

TODO: no need to edit multiple files.

### Library

This repository has and is using a copy of [Idiorm](https://github.com/j4mie/idiorm/).
