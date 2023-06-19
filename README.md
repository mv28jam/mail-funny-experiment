### Run with DB
 - use test_support/datagen.php
 - php ./run.php

### Run with test mok
- php ./run test <count of mails to send> 

### TO check
- minimal php-build = **SUCCESS** up to 3 times faster (without mem limit)
- decrease php memory limit = **FAIL** (minimal start memory for zend engine 2M, real more than 20M)
  