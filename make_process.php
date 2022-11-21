<?php

pclose(popen('start php index.php 0 1 555', 'r'));
pclose(popen('start php index.php 0 556 1110', 'r'));
pclose(popen('start php index.php 0 1111 1664', 'r'));
pclose(popen('start php index.php 0 1664 2222', 'r'));

pclose(popen('start php index.php 1 1 555', 'r'));
pclose(popen('start php index.php 1 556 1110', 'r'));
pclose(popen('start php index.php 1 1111 1664', 'r'));
pclose(popen('start php index.php 1 1664 2222', 'r'));

pclose(popen('start php index.php 2 1 555', 'r'));
pclose(popen('start php index.php 2 556 1110', 'r'));
pclose(popen('start php index.php 2 1111 1664', 'r'));
pclose(popen('start php index.php 2 1664 2222', 'r'));

pclose(popen('start php index.php 3 1 555', 'r'));
pclose(popen('start php index.php 3 556 1110', 'r'));
pclose(popen('start php index.php 3 1111 1664', 'r'));
pclose(popen('start php index.php 3 1664 2223', 'r'));

pclose(popen('start php index.php 4 1 555', 'r'));
pclose(popen('start php index.php 4 556 1110', 'r'));
pclose(popen('start php index.php 4 1111 1664', 'r'));
pclose(popen('start php index.php 4 1664 2222', 'r'));
