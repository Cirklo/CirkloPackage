<?php
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="downloaded.pdf"');

// Code to create the pdf goes here...
print_r($_POST);


// The PDF source is in original.pdf
readfile('original.pdf');


?>