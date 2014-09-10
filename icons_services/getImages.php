<?php
// No direct access to this file
// defined('_JEXEC') or die('Restricted access');

$filenameArray = array();

$handle = opendir(dirname(realpath(__FILE__)).'/icons/');
        while($file = readdir($handle)){
            if($file !== '.' && $file !== '..'){
                array_push($filenameArray, "/thirteen/icons_services/icons/$file");
            }
        }

echo json_encode($filenameArray);
?>
