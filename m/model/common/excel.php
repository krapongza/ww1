<?php
class ModelCommonExcel extends Model {

    function __construct(){
		
    }

	public function exportStock($data='' , $filename='excel' , $template='report_5_stock_report' ) {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');

		require_once DIR_MODEL . 'PHPExcel/IOFactory.php';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(DIR_TEMPLATE . "exceltemplate/".$template.".xls");

		$baseRow = 5;
		$myrow = array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',9=>'J',10=>'K',11=>'L');
		foreach($data as $r => $dataRow) {
			$row = $baseRow + $r;
			$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);


			$i = 0;
			foreach($dataRow as $k => $v){
				$objPHPExcel->getActiveSheet()->setCellValue($myrow[$i].$row, $dataRow[$k]);
				$i++;
			}
		}

		$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
	}

	public function exportSpecialCost($data='' , $filename='excel' , $template='report_5_stock_report' ) {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');

		require_once DIR_MODEL . 'PHPExcel/IOFactory.php';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(DIR_TEMPLATE . "exceltemplate/".$template.".xls");

		$baseRow = 5;
		$objPHPExcel->getActiveSheet()->setCellValue('A5', $data['cost'])
									  ->setCellValue('B5', $data['update_date'])
									  ->setCellValue('C5', $data['product'])
									  ->setCellValue('D5', $data['total']);


		$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
	}
	
	public function exportStockSum($data='' , $filename='excel' , $template='report_5_stock_report' , $sum='' ) {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');

		require_once DIR_MODEL . 'PHPExcel/IOFactory.php';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(DIR_TEMPLATE . "exceltemplate/".$template.".xls");

		$baseRow = 5;
		$sumrow = 0;
		$myrow = array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',9=>'J',10=>'K',11=>'L');
		foreach($data as $r => $dataRow) {
			$row = $baseRow + $r;
			$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);


			$i = 0;
			foreach($dataRow as $k => $v){
				$objPHPExcel->getActiveSheet()->setCellValue($myrow[$i].$row, $dataRow[$k]);
				$i++;
			}
			$sumrow = $row + 1;
		}


		$tmpsumkey = array_keys($sum);
		foreach($tmpsumkey as $r => $dataRow) {
			$objPHPExcel->getActiveSheet()->setCellValue($myrow[0].$sumrow, 'SUM');
			$objPHPExcel->getActiveSheet()->setCellValue($myrow[$r+2].$sumrow, $sum[$dataRow]);
		}


		$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
	}


	public function exportAllProduct($data='' , $filename='excel' , $template='report_5_stock_report' ) {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');

		require_once DIR_MODEL . 'PHPExcel/IOFactory.php';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(DIR_TEMPLATE . "exceltemplate/".$template.".xls");

		$baseRow = 5;
		$objPHPExcel->getActiveSheet()->setCellValue('A5', $data['entry'])
									  ->setCellValue('B5', $data['total'])
									  ->setCellValue('C5', $data['price'])
									  ->setCellValue('D5', 'บาท');


		$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
	}
	

	public function exportWhoBuyProduct($data='' , $filename='excel' , $template='report_8_who_buy_product_report' ) {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');

		require_once DIR_MODEL . 'PHPExcel/IOFactory.php';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(DIR_TEMPLATE . "exceltemplate/".$template.".xls");

		$baseRow = 5;
		foreach($data as $r => $dataRow) {
			$row = $baseRow + $r;
			$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);

			$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $dataRow['model'])
										  ->setCellValue('B'.$row, $dataRow['size'])
										  ->setCellValue('C'.$row, $dataRow['color'])
										  ->setCellValue('D'.$row, $dataRow['amount']) ;
		}

		$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
	}

 	public function exportTopBestBuy($data='' , $filename='excel' , $template='report_5_stock_report' ) {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');

		require_once DIR_MODEL . 'PHPExcel/IOFactory.php';
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load(DIR_TEMPLATE . "exceltemplate/".$template.".xls");

		$baseRow = 5;
		foreach($data as $r => $dataRow) {
			$row = $baseRow + $r;
			$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);

			$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $dataRow['user'])
										  ->setCellValue('B'.$row, $dataRow['orders'])
										  ->setCellValue('C'.$row, $dataRow['cancel'])
										  ->setCellValue('D'.$row, $dataRow['level'])
										  ->setCellValue('E'.$row, $dataRow['point']);
		}

		$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
	}

}
?>