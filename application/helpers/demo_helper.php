<?php

function is_on_demo_host()
{
    return false;
    return isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'demo.phppointofsale.com' || $_SERVER['HTTP_HOST'] == 'demo.phppointofsalestaging.com');
}
?>