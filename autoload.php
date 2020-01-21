<?php

spl_autoload_register(function ($class_name) {
    require_once "cls/$class_name.php";
    echo $class_name;
});