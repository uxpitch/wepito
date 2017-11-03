<?php   

/*
Plugin Name: Wordpress Image Mapper
Plugin URI: http://www.20script.ir
Description: Downloaded from 20script.ir. Image Mapper for Wordpress
Author: Br0
Version: 1.5.1
Author URI: http://www.20script.ir
*/

if (!class_exists("ImageMapperAdmin")) 
{
	require_once dirname( __FILE__ ) . '/image_mapper_class.php';	
	$imagemapper = new ImageMapperAdmin (__FILE__);
}

?>