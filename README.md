## Mail sending test.  
Trying to send mail with php, mail check process up to 60 sec, mail sending process up to 10 sec.  

Mails in db > 5 000 000  
Have to send for 20%  
Days in month ~ 30  
Have to check 75%  

So in one process...  
Send: 5 000 000 * 20 % / 30 days * 5 sec avg  = 46 hours  
Check email: 5 000 000 * 20 % / 30 days * 75% * 30 sec avg = 208 hours (no checks before)  

= 256 hours... one mailing



### Run with DB
 - use test_support/datagen.php
 - php ./run.php

### Run with test mok
- php ./run test <count of mails to send> 

### TO check
- minimal php-build = **SUCCESS** up to 3 times faster (without mem limit)
- decrease php memory limit = **FAIL** (minimal start memory for zend engine 2M, common more than 20M)
  