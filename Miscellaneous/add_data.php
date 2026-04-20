<?php require '../link.php'; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<meta charset="utf-8">
	<title>Import Excel To MySQL</title>
</head>

<body>
	<form class="" action="" method="post" enctype="multipart/form-data">
		<input type="file" name="excel" required value="">
		<button type="submit" name="import">Import</button>
	</form>
	<?php
	function format_date($year, $date)
	{
		$arr = explode('-', $date);
		$t = $arr[0];
		$arr[0] = $arr[1];
		$arr[1] = $t;
		$arr[2] = $year . $arr[2];
		$date = implode('-', $arr);
		return $date;
	}
	if (isset($_POST["import"])) {
		$fileName = $_FILES["excel"]["name"];
		$fileExtension = explode('.', $fileName);
		$fileExtension = strtolower(end($fileExtension));
		$newFileName = date("Y.m.d") . " - " . date("h.i.sa") . "." . $fileExtension;

		$targetDirectory = "uploads/" . $newFileName;
		move_uploaded_file($_FILES['excel']['tmp_name'], $targetDirectory);

		require 'excelReader/excel_reader2.php';
		require 'excelReader/SpreadsheetReader.php';

		$reader = new SpreadsheetReader($targetDirectory);
		$status = false;
		foreach ($reader as $key => $row) {

			//Admin or Student or Faculty
			/*
				$id = $row[0];
				$name = $row[1];
				$password = $row[2];
				$hash = password_hash($password,PASSWORD_DEFAULT);
				if(mysqli_query($link, "INSERT INTO student VALUES('', '$id', '$name', '$password', '$hash')")){
					$status = true;
				}
				else{
					$status = false;
				}
			*/

			//Student Master Data
			/*
			$id = $row[0];
			$admno = $row[1];
			$firstname = $row[2];
			$surname = $row[3];
			$fathername = $row[4];
			$mothername = $row[5];
			$dob = $row[6];
			$gender = $row[7];
			$mobile = $row[8];
			$aadhar = $row[9];
			$class = $row[10];
			$section = $row[11];
			$religion = $row[12];
			$caste = $row[13];
			$category = $row[14];
			$houseno = $row[15];
			$area = $row[16];
			$village = $row[17];
			$doj = $row[18];
			$previous_school = $row[19];
			$van_route = $row[20];
			$referred_by = $row[21];
			if (mysqli_query($link, "INSERT INTO student_master_data VALUES('', '$id','$admno', '$firstname', '$surname', '$fathername', '$mothername',
            '$dob', '$gender', '$mobile', '$aadhar','$class','$section', '$religion', '$caste', '$category', '$houseno', '$area','$village',
             '$doj','$previous_school','$van_route','$referred_by')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/

			//Van_Routes
			/*
				$route = $row[0];
				mysqli_query($link,"INSERT INTO `van_route` VALUES('','$route')");
				*/
			//Class Wise Exams
			/*
				$class = $row[0];
				$exam = $row[1];
				$max = $row[2];
				if(mysqli_query($link,"INSERT INTO `class_wise_examination` VALUES('','$class','$exam','$max')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/

			//Class Wise Subjects
			/*
				$class = $row[0];
				$exam = $row[1];
				$subject = $row[2];
				$max = $row[3];
				if(mysqli_query($link,"INSERT INTO `class_wise_subjects` VALUES('','$class','$exam','$subject','$max')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/

			//Class Wise Marks
			/*
				$class = $row[0];
				$section = $row[1];
				$id = $row[2];
				$name = $row[3];
				$exam = $row[4];
				$sub1 = $row[5];
				$sub2 = $row[6];
				$sub3 = $row[7];
				$sub4 = $row[8];
				$sub5 = $row[9];
				$sub6 = $row[10];
				$sub7 = $row[11];
				$sub8 = $row[12];
				$sub9 = $row[13];
				$sub10 = $row[14];
				$sub11 = $row[15];
				if(mysqli_query($link,"INSERT INTO `stu_marks` VALUES('','$class','$section','$id','$name','$exam','$sub1','$sub2','$sub3','$sub4','$sub5','$sub6','$sub7','$sub8','$sub9','$sub10','$sub11','')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/

			//Actual Fee
			/*
				$type = $row[0];
				$class = $row[1];
				$route = $row[2];
				$fee = $row[3];
				if(mysqli_query($link,"INSERT INTO `actual_fee` VALUES('','$type','$class','$route','$fee')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/
			//Student Fee Master Data
			/*
				$id = $row[0];
				$name = $row[1];
				$class = $row[2];
				$section = $row[3];
				$street = $row[4];
				$type = $row[5];
				$actual = $row[6];
				$last = $row[7];
				$current = $row[8];
				$total = $row[9];
				$route = $row[10];
				if(mysqli_query($link,"INSERT INTO `stu_fee_master_data` VALUES('','$id','$name','$class','$section','$street','$type','$actual','$last','$current','$total','$route')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/
			//Student Fee Balances
			/*
				$id = $row[0];
				$type = $row[1];
				$balance = $row[2];
				if(mysqli_query($link,"INSERT INTO `fee_balances` VALUES('','$id','$type','$balance')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/
			//Student Paid Fees
			/*
			$id = $row[0];
			$name = $row[1];
			$type = $row[2];
			$class = $row[3];
			$section = $row[4];
			$fee = $row[5];
			$date = $row[6];
			$bill = $row[7];
			$route = $row[8];
			if (mysqli_query($link, "INSERT INTO `stu_paid_fee` VALUES('','$id','$name','$type','$class','$section','$fee','$date','$bill','$route')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/

			//Debiter's Master Data
			/*
				$ac = $row[0];
				$name = $row[1];
				$address = $row[2];
				$mobile = $row[3];
				$amount = $row[4];
				$doc = $row[5];
				if(mysqli_query($link,"INSERT INTO `debiter_master_data` VALUES('','$ac','$name','$address','$mobile','$amount','$doc')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/

			//Debiter's Transaction Details
			/*
				$ac = $row[0];
				$amount = $row[1];
				$dop = $row[2];
				$bill = $row[3];
				$purpose = $row[4];
				if(mysqli_query($link,"INSERT INTO `tran_details` VALUES('','$ac','$amount','$dop','$bill','$purpose')")){
					$status = true;
				}
				else{
					$status = false;
				}
				*/

			//Admission Book Entry
			/*
			$admno = $row[0];
			$firstname = $row[1];
			$surname = $row[2];
			$parentname = $row[3];
			$dob = $row[4];
			$residence = $row[5];
			$occupation = $row[6];
			$previous_school = $row[7];
			$class_come = $row[8];
			$gender = $row[9];
			$tc_no = $row[10];
			$tc_pro = $row[11];
			$doa = $row[12];
			$mothertongue = $row[13];
			$nationality = $row[14];
			$religion = $row[15];
			$caste = $row[16];
			$medium = $row[17];
			$class_admission = $row[18];
			$class_leave = $row[19];
			$dol = $row[20];
			$dot = $row[21];
			if (mysqli_query($link, "INSERT INTO `admissionb` VALUES('','$admno','$firstname','$surname','$parentname','$dob',
				'$residence','$occupation','$previous_school','$class_come','$gender','$tc_no','$tc_pro','$doa','$mothertongue',
				'$nationality','$religion','$caste','$medium','$class_admission','$class_leave','$dol','$dot')")) {

				$status = true;
			} else {
				$status = false;
			}
			*/
			//Working Days
			/*
			$month = $row[0];
			$days = $row[1];
			if (mysqli_query($link, "INSERT INTO `working_days` VALUES('', '$month', '$days')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/
			//Attendance Daily
			/*
			$id = $row[0];
			$date = format_date($row[1]);
			$am = $row[2];
			$pm = $row[2];
			if (mysqli_query($link, "INSERT INTO `attendance_daily` VALUES('', '$id', '$date','$am','$pm')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/

			//Attendance Master Data
			/*
			$id = $row[0];
			$jun = $row[1];
			$jul = $row[2];
			$aug = $row[3];
			$sep = $row[4];
			$oct = $row[5];
			$nov = $row[6];
			$dec = $row[7];
			$jan = $row[8];
			$feb = $row[9];
			$mar = $row[10];
			$apr = $row[11];
			if (mysqli_query($link, "INSERT INTO `stu_att_master` VALUES('','$id','$jun', '$jul','$aug','$sep','$oct','$nov','$dec','$jan','$feb','$mar','$apr')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/
			//Employee Master Data
			/*
			$id = $row[0];
			$name = $row[1];
			$surname = $row[2];
			$fathername = $row[3];
			$qualification = $row[4];
			$relation = $row[5];
			if($row[6] != ""){
				$dob = format_date("19",$row[6]);
			}
			else{
				$dob = $row[6];
			}
			$mobile = $row[7];
			$houseno = $row[8];
			$area = $row[9];
			$village = $row[10];
			if($row[11] != ""){
				$doj = format_date("20",$row[11]);
			}
			else{
				$doj = $row[11];
			}
			$designation = $row[12];
			if (mysqli_query($link, "INSERT INTO `employee_master_data`(Emp_Id,Emp_First_Name,Emp_Sur_Name,Father_Name,Qualification,
			Relation,DOB,Mobile,House_No,Area,Village,DOJ,Designation,Status) VALUES('$id','$name','$surname','$fathername','$qualification',
			'$relation','$dob','$mobile','$houseno','$area','$village','$doj','$designation','Working')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/

			//Time Table
			/*
			$class = $row[0];
			$section = $row[1];
			$period1 = $row[2];
			$period2 = $row[3];
			$period3 = $row[4];
			$period4 = $row[5];
			$period5 = $row[6];
			$period6 = $row[7];
			$period7 = $row[8];
			$period8 = $row[9];
			echo $class . ' ' . $section . ' ' . $period1 . ' ' . $period2 . ' ' . $period3 . ' ' . $period4 . ' ' . $period5 . ' ' . $period6 . ' ' . $period7 . ' ' . $period8 . '<br>';
			if (mysqli_query($link, "INSERT INTO `time_table` VALUES('','$class','$section','$period1','$period2',$period3','$period4','$period5','$period6',$period7','$period8')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/

			//VVIP
			/*
			$id = $row[0];
			if (mysqli_query($link, "INSERT INTO `vvip` VALUES('','$id')")) {
				$status = true;
			} else {
				$status = false;
			}
			*/
		}

		if ($status) {
			echo
			"
			<script>
			alert('Succesfully Imported');
			document.location.href = '';
			</script>
			";
		} else {
			echo
			"
			<script>
			alert('Import Failed');
			//document.location.href = '';
			</script>
			";
		}
	}
	?>
	<!--
SET @autoid :=0;
UPDATE student SET S_No = @autoid := (@autoid+1);
ALTER TABLE student AUTO_INCREMENT = 1;
	-->
</body>

</html>