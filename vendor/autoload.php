<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function __autoload($class_name) {
    $file_name = strtr($class_name, "\\", DIRECTORY_SEPARATOR);


    //require $_SERVER['PWD']."/{$file_name}.php";
    require "{$file_name}.php";
}
