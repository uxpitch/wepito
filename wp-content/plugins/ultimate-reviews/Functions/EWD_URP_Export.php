<?php

function EWD_URP_Export_To_Excel() {
	$InDepth_Reviews = get_option("EWD_URP_InDepth_Reviews");
    $Review_Categories_Array = get_option("EWD_URP_Review_Categories_Array");
    if (!is_array($Review_Categories_Array)) {$Review_Categories_Array = array();}

    $Default_Fields = array("Product Name (if applicable)", "Review Author", "Reviewer Email (if applicable)", "Review Title", "Review", "Review Image (if applicable)", "Review Video (if applicable)");

    foreach ($Review_Categories_Array as $Review_Categories_Item) {
        $Review_Cat_Types[$Review_Categories_Item['CategoryName']] = $Review_Categories_Item['CategoryType'];
    }

	include_once('../wp-content/plugins/ultimate-reviews/PHPExcel/Classes/PHPExcel.php');
		
	// Instantiate a new PHPExcel object 
	$objPHPExcel = new PHPExcel();  
	// Set the active Excel worksheet to sheet 0 
	$objPHPExcel->setActiveSheetIndex(0);  

	// Print out the regular order field labels
	$objPHPExcel->getActiveSheet()->setCellValue("A1", "Title");
	$objPHPExcel->getActiveSheet()->setCellValue("B1", "Author");
	$objPHPExcel->getActiveSheet()->setCellValue("C1", "Review");
	$objPHPExcel->getActiveSheet()->setCellValue("D1", "Score");
	$objPHPExcel->getActiveSheet()->setCellValue("E1", "Email");
	$objPHPExcel->getActiveSheet()->setCellValue("F1", "Product Name");
	$objPHPExcel->getActiveSheet()->setCellValue("G1", "Categories");

	$column = 'H';
	if ($InDepth_Reviews == "Yes") {
		foreach ($Review_Categories_Array as $Review_Categories_Item) {
			if (!in_array($Review_Categories_Item['CategoryName'], $Default_Fields)) {
    			$objPHPExcel->getActiveSheet()->setCellValue($column."1", $Review_Categories_Item['CategoryName']);
   				$column++;
   			}
		}
	}

	//start while loop to get data  
	$rowCount = 2;
	$params = array(
		'posts_per_page' => -1,
		'post_type' => 'urp_review'
	);
	$Posts = get_posts($params);
	foreach ($Posts as $Post)  
	{  
     	$Author = get_post_meta($Post->ID, "EWD_URP_Post_Author", true );
     	$Score = get_post_meta($Post->ID, "EWD_URP_Overall_Score", true );
     	$Email = get_post_meta($Post->ID, "EWD_URP_Post_Email", true );
     	$Product_Name = get_post_meta($Post->ID, "EWD_URP_Product_Name", true );

     	$Categories = get_the_terms($Post->ID, "urp-review-category");
     	if (is_array($Categories)) {
     		foreach ($Categories  as $Category) {
     			$Category_String .= $Category->name . ",";
     		}
     		$Category_String = substr($Category_String, 0, -1);
     	}
     	else {$Category_String = "";}

     	$objPHPExcel->getActiveSheet()->setCellValue("A" . $rowCount, $Post->post_title);
		$objPHPExcel->getActiveSheet()->setCellValue("B" . $rowCount, $Author);
		$objPHPExcel->getActiveSheet()->setCellValue("C" . $rowCount, $Post->post_content);
		$objPHPExcel->getActiveSheet()->setCellValue("D" . $rowCount, $Score);
		$objPHPExcel->getActiveSheet()->setCellValue("E" . $rowCount, $Email);
		$objPHPExcel->getActiveSheet()->setCellValue("F" . $rowCount, $Product_Name);
		$objPHPExcel->getActiveSheet()->setCellValue("G" . $rowCount, $Category_String);

		$column = 'H';
		if ($InDepth_Reviews == "Yes") {
			foreach ($Review_Categories_Array as $Review_Categories_Item) {
				if (!in_array($Review_Categories_Item['CategoryName'], $Default_Fields)) {
    				$objPHPExcel->getActiveSheet()->setCellValue($column.$rowCount, get_post_meta($Post->ID, "EWD_URP_" . $Review_Categories_Item['CategoryName'], true));
   					$column++;
   				}
			}
		}
			
    	$rowCount++;

    	unset($Category_String);
    	unset($Tag_String);
	} 

	$Format_Type = $_POST['Format_Type'];
	// Redirect output to a client’s web browser (Excel5) 
	if ($Format_Type == "CSV") {
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename="Review_Export.csv"'); 
		header('Cache-Control: max-age=0'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->save('php://output');
	}
	else {
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename="Review_Export.xls"'); 
		header('Cache-Control: max-age=0'); 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 
		$objWriter->save('php://output');
	}

}
?>