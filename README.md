## Mail sending test.  
Trying to send mail with php, mail check process up to 60 sec, mail sending process up to 10 sec.  

Mails in db > 5 000 000  
Have to send for 20%  
Days in month ~ 30  
Have to check 75%  
2 mails in month

So in one process...  
Send: 5 000 000 * 20 % / 30 days * 2 times * 5 sec avg  = 92 hours  
Check email: 5 000 000 * 20 % / 30 days * 75% * 30 sec avg = 208 hours (no checks before)

= 300 hours... one mailing by ONE process

### Check script for multi process by exec (I wanted to check for a long time)
4 core, 8 GB RAM, LA > 1, Ubuntu 22 desktop, php8.1    
Limits: 3Gb RAM, LA 3.6  
Working with DB test

80 847 "checked"   
78 829 "sent"  

Output: _Processed 80847 in **02:43:49** Bad 2018 Failed 775_  

#### RESULT OF RESEARCH = RAM is bottleneck

### Run with DB
 - use test_support/datagen.php
 - php ./run.php

### Run with test mok
- php ./run test 100

### TO check
- minimal php-build = **SUCCESS** up to 3 times faster (without mem limit)
- decrease php memory limit = **FAIL** (minimal start memory for zend engine 2M, common more than 20M)
  