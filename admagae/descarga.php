<?php
header("Content-disposition: attachment; filename=archivo.txt");
header("Content-type: text/plain");
readfile("archivo.txt");
?>