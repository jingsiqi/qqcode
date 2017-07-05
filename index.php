<?php
$dir_name = array();
$base_name = __DIR__."/prototype";
if ($dh = opendir($base_name)) {
    while (( $file = readdir($dh)) !== false) {
        if ($file != "." && $file != ".." && is_dir($base_name."/".$file)){
            $dir_name[] = $file;
}
    }
    closedir($dh);
}
include "index.html";
?>