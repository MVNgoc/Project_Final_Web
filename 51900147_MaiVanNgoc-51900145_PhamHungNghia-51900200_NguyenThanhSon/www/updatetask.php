<?php 
	// kiểm tra nếu chưa đăng nhập thì sẽ không truy cập được vào trang index mà sẽ bị chuyển hướng vế trang login
	session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

	//nếu chưa thay đổi pass thì sẽ không truy cập được vào trang index mà sẽ bị chuyển hướng vế trang đổi mật khẩu
	if($_SESSION['pwd'] == $_SESSION['username']) {
		header('Location: changepassword.php');
		exit(); // Chuyển đến trang thay đổi mật khẩu
	}

    if ($_SESSION['positionid'] != 1) {
        header('Location: index.php');
        exit();
    }

    require_once('./admin/db.php');

    $user_name = $_SESSION['firstname']." ". $_SESSION['lastname'];
    $tasktitle = '';
    $taskdescription = '';
    $starttime = '';
    $deadline = '';
    $department = '';
    $taskstatus = '';
    $task_deliver = '';
    $error = '';
    $upload = '';

    if(isset($_POST["task-edit"])){
		$id = $_POST["task-edit"];
        $_SESSION['id_task'] = $id;
	}
	else {
		$id = $_SESSION['id_task'];
	}

    $sql = "SELECT task_title, task_description, staff_assign FROM task WHERE id = '$id' ";
    $conn = open_database();
    $stm = $conn -> prepare($sql);
    $result = $conn-> query($sql);
    $row = $result->fetch_assoc();
    $task_title = $row["task_title"];
    $task_description = $row["task_description"];
    $staff_assign = $row["staff_assign"];
    
    $sql = "SELECT DATE_FORMAT(start_time, '%Y-%m-%dT%h:%i') AS start_time FROM task WHERE id = '$id'" ;
    $conn = open_database();
    $stm = $conn -> prepare($sql);
    $result = $conn-> query($sql);
    $row = $result->fetch_assoc();
    $start_time = $row["start_time"];

    $sql = "SELECT DATE_FORMAT(deadline, '%Y-%m-%dT%h:%i') AS deadline FROM task WHERE id = '$id'";
    $conn = open_database();
    $stm = $conn -> prepare($sql);
    $result = $conn-> query($sql);
    $row = $result->fetch_assoc();
    $deadline = $row["deadline"];

    if(isset($_POST['tasktitle']) && isset($_POST['taskdescription']) 
    && isset($_POST['starttime']) && isset($_POST['deadline']) 
    && isset($_POST['department'])) {
        $tasktitle = $_POST['tasktitle'];
        $taskdescription = $_POST['taskdescription'];
        $starttime = $_POST['starttime'];
        $deadline = $_POST['deadline'];
        $department = $_POST['department'];

        $upload = $_FILES['attachfile']['name'];
        $targer = 'files_upload/' . $upload;

		$extension = pathinfo($upload,PATHINFO_EXTENSION);
		$file_name = $_FILES['attachfile']['tmp_name'];
		$file_size = $_FILES['attachfile']['size'];

        if(empty($tasktitle)) {
            $error = 'Vui lòng điền tiêu đề task';
        }
        else if(empty($taskdescription)) {
            $error = 'Vui lòng điền mô tả cho task';
        }
        else if(empty($starttime)) {
            $error = 'Vui lòng điền thời gian bắt đầu task';
        }
        else if(empty($deadline)) {
            $error = 'Vui lòng điền thời gian kết thúc task';
        }
        else if(empty($department)) {
            $error = 'Vui lòng chọn nhân viên thực hiện task';
        }
        else if(!in_array($extension,['png','jpg','jpeg','gif','ppt','zip','rar','pptx','doc','docx','xls','xlsx','pdf']) && !empty($upload)){
			$error = "File bạn gửi không đúng định dạng yêu cầu";
		}
		else if($_FILES['attachfile']['size'] == 0 && !empty($upload)){
			$error = "Kích thước file phải nhỏ hơn 2mb";
		}
        else if(!empty($starttime) && !empty($deadline)) {
            $starttimecheck = explode('-', $starttime); //Tách thành year, month, dayTtime
            $deadlinetimecheck = explode('-', $deadline); //Tách thành year, month, dayTtime
            $starttimecheck2 = explode('T', $starttimecheck[2]); //Tách thành day, time
            $deadlinetimecheck2 = explode('T', $deadlinetimecheck[2]); //Tách thành day, time

            if($starttimecheck[0] > $deadlinetimecheck[0]) {
                $error = 'Thời gian kết thúc task không hợp lệ ';
            }
            else if($starttimecheck[1] > $deadlinetimecheck[1]) {
                $error = 'Thời gian kết thúc task không hợp lệ ';
            }
            else if($starttimecheck2[0] >= $deadlinetimecheck2[0]) {
                $daycheck = ($deadlinetimecheck[1] - $starttimecheck[1])* 30;
                if((($deadlinetimecheck2[0] - $starttimecheck2[0]) + $daycheck) > 0) {
                    $task_deliver = $_SESSION['username'];
                    if(!empty($upload)) {
                        if(move_uploaded_file($file_name, $targer)) {
                            $data = updatetaskFile($tasktitle, $taskdescription, $starttime, $deadline, $department, $upload, $id);
                        }
                        else {
                            $data['code'] = 1;
                        }
                    }
                    else {
                        $data = updatetask($tasktitle, $taskdescription, $starttime, $deadline, $department , $id);
                    }
                    if($data['code'] == 0) {
                        $success = 'Update Task thành công.';
                        $sql = "SELECT task_title, task_description, staff_assign FROM task WHERE id = '$id' ";
                        $conn = open_database();
                        $stm = $conn -> prepare($sql);
                        $result = $conn-> query($sql);
                        $row = $result->fetch_assoc();
                        $task_title = $row["task_title"];
                        $task_description = $row["task_description"];
                        $staff_assign = $row["staff_assign"];
                        
                        $sql = "SELECT DATE_FORMAT(start_time, '%Y-%m-%dT%h:%i') AS start_time FROM task WHERE id = '$id'" ;
                        $conn = open_database();
                        $stm = $conn -> prepare($sql);
                        $result = $conn-> query($sql);
                        $row = $result->fetch_assoc();
                        $start_time = $row["start_time"];

                        $sql = "SELECT DATE_FORMAT(deadline, '%Y-%m-%dT%h:%i') AS deadline FROM task WHERE id = '$id'";
                        $conn = open_database();
                        $stm = $conn -> prepare($sql);
                        $result = $conn-> query($sql);
                        $row = $result->fetch_assoc();
                        $deadline = $row["deadline"];
                    }
                    else {
                        $error = 'Đã có lỗi xảy ra. Vui lòng thử lại sau';
                    }
                }
                else if((($deadlinetimecheck2[0] - $starttimecheck2[0]) + $daycheck) == 0) {
                    $error = 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu ít nhất 1 ngày';
                }   
                else {
                    $error = 'Thời gian kết thúc task không hợp lệ';
                }  
            }
            else {
                $task_deliver = $_SESSION['username'];
                if(!empty($upload)) {
                    if(move_uploaded_file($file_name, $targer)) {
                        $data = updatetaskFile($tasktitle, $taskdescription, $starttime, $deadline, $department, $upload, $id);
                    }
                    else {
                        $data['code'] = 1;
                    }
                }
                else {
                    $data = updatetask($tasktitle, $taskdescription, $starttime, $deadline, $department , $id);
                }
                if($data['code'] == 0) {
                    $success = 'Update Task thành công.';
                    $sql = "SELECT task_title, task_description, staff_assign FROM task WHERE id = '$id' ";
                    $conn = open_database();
                    $stm = $conn -> prepare($sql);
                    $result = $conn-> query($sql);
                    $row = $result->fetch_assoc();
                    $task_title = $row["task_title"];
                    $task_description = $row["task_description"];
                    $staff_assign = $row["staff_assign"];
                    
                    $sql = "SELECT DATE_FORMAT(start_time, '%Y-%m-%dT%h:%i') AS start_time FROM task WHERE id = '$id'" ;
                    $conn = open_database();
                    $stm = $conn -> prepare($sql);
                    $result = $conn-> query($sql);
                    $row = $result->fetch_assoc();
                    $start_time = $row["start_time"];

                    $sql = "SELECT DATE_FORMAT(deadline, '%Y-%m-%dT%h:%i') AS deadline FROM task WHERE id = '$id'";
                    $conn = open_database();
                    $stm = $conn -> prepare($sql);
                    $result = $conn-> query($sql);
                    $row = $result->fetch_assoc();
                    $deadline = $row["deadline"];
                }
                else {
                    $error = 'Đã có lỗi xảy ra. Vui lòng thử lại sau';
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="/style.css"> <!-- Sử dụng link tuyệt đối tính từ root, vì vậy có dấu / đầu tiên -->
	<script src="main.js"></script>
	<title>Department manager</title>
</head>

<body>
	<div class="main">
		<header class="header">
			<nav class="navbar navbar-expand-sm bg-light">
				<div class="tdtu-img">
					<img src="/images/tdt-logo.png" alt="TDTU Image" class="tdtu-picture">
				</div>
				<!-- Links -->
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" href="index.php">Trang chủ</a>
					</li>

					<li class="nav-item">
						<a class="nav-link" href="profile.php">Hồ sơ</a>
					</li>
					<?php
                        if ($_SESSION['positionid'] == 1 || $_SESSION['positionid'] == 2) {
                            if($_SESSION['positionid'] == 1) {
                                echo '<li class="nav-item day-off-header">
                                        <a class="nav-link" href="#">Nghỉ phép</a>
                                        <ul class="navbar-nav day-off-tag">
                                            <li class="nav-item">
												<a class="nav-link" id="showday" type="button">Xem ngày nghỉ phép</a>
											</li>
											<li class="nav-item">
                                            	<a class="nav-link" href="dayoffform.php">Tạo đơn xin nghỉ phép</a>
                                            </li>
                                            <li class="nav-item">
                                            	<a class="nav-link" href="duyetdon.php">Duyệt đơn nghỉ phép</a>
                                            </li>
                                            <li class="nav-item">
                                            	<a class="nav-link" href="dayoffhistory.php">Lịch sử nghỉ phép</a>
                                            </li>
                                        </ul>
                                    </li>';
                            }
                            else {
								echo '<li class="nav-item day-off-header">
                                        <a class="nav-link" href="#">Nghỉ phép</a>
                                        <ul class="navbar-nav day-off-tag">
                                            <li class="nav-item">
												<a class="nav-link" id="showday" type="button">Xem ngày nghỉ phép</a>
											</li>
											<li class="nav-item">
                                            	<a class="nav-link" href="dayoffform.php">Tạo đơn xin nghỉ phép</a>
                                            </li>
											<li class="nav-item">
                                            	<a class="nav-link" href="dayoffhistory.php">Lịch sử nghỉ phép</a>
                                            </li>
                                        </ul>
                                    </li>';
                            }
                        }
					?>
					
					<li class="nav-item">
						<a class="nav-link" href="logout.php">Đăng xuất</a>
					</li>		
				</ul>
			</nav>

            <div class="btn-showlist">
				<button class="btn-list-item"></button>
			</div>
		</header>

		<div class="container">
        <div class="row justify-content-center ">
            <div class="col-xl-5 col-lg-6 col-md-8 border my-5 p-4 rounded mx-3 addstaffform">
                <h3 class="text-center text-secondary mt-2 mb-3 mb-3">Chỉnh sửa Task</h3>
                <form method="post" action="" novalidate enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="tasktitle">Tiêu đề Task</label>
                        <input value="<?= $task_title ?>" name="tasktitle" required class="form-control" type="text" placeholder="Tiêu đề Task" id="tasktitle">         
                    </div>
                    <div class="form-group">
                        <label for="taskdescription">Mô tả</label>
                        <input value="<?php echo  $task_description; ?>" name="taskdescription" required class="form-control" type="text" placeholder="Mô tả" id="taskdescription">
                    </div>
                    <div class="form-group">
                        <label for="starttime">Thời gian bắt đầu</label>
                        <input value="<?=  $start_time ?>" name="starttime" required class="form-control" type="datetime-local" placeholder="Thời gian bắt đầu" id="starttime" readonly>
                    </div>
					<div class="form-group">
                        <label for="deadline">Thời gian kết thúc</label>
                        <input value="<?=  $deadline ?>" name="deadline" required class="form-control" type="datetime-local" placeholder="Thời gian kết thúc" id="deadline">
                    </div>
					<div class="form-group">
                        <label for="department">Chọn nhân viên</label>
                        <?php 
							$sql = 'SELECT * FROM account WHERE department_name = ? AND positionid != 3 AND positionid != 1';
							$conn = open_database();
        					$stm = $conn->prepare($sql);
                            $stm->bind_param('s',$_SESSION['department_name']);
                            if(!$stm->execute()){
                                die('Query error: ' . $stm->error);
                            }
                            $result = $stm->get_result();
                            $dbselected = $staff_assign;
                            if($result-> num_rows > 0) {
                                echo '<select required class="form-control" name="department">';
                                foreach($result as $row) {
                                    if(($row["firstname"].' '.$row["lastname"]) != $dbselected) {
                                        echo '<option value="'.$row["firstname"].' '.$row["lastname"].'">'.$row["firstname"].' '.$row["lastname"].'</option>';
                                    }
                                    else {
                                        echo '<option value="'.$row["firstname"].' '.$row["lastname"].'"selected>'.$row["firstname"].' '.$row["lastname"].'</option>';
                                    }
                                }
							    echo '</select>';
                            }
                            $conn->close();
						
						?>
                    </div>

                    <div class="form-group">
                        <label for="attachfile">File đính kèm</label>
                        <input value="" name="attachfile" required type="file"id="attachfile" style="display: block">
                    </div>

                    <div class="form-group">
                        <?php
                            if (!empty($error)) {
                                echo "<div class='alert alert-danger'>$error</div>";
                            }
                        ?>
                        <button type="submit" class="btn btn-register-js btn-assign-task btn-success px-5 mt-3 mr-2">Update Task</button>
                    </div>
                </form>

            </div>
        </div>
		<?php
			if (!empty($success)) {
				echo "<div class='notification'>
						<div class='notification_success'>$success</div>
					</div>";
			}
		?>
    </div>

		<footer class="footer">
			
		</footer>
        <div id="myModal" class="modal fade" role="dialog">
			<div class="modal-dialog">

				<!-- Modal content-->
				<div class="modal-content ">
					<div class="modal-header text-center">
						<h4 class="modal-title w-100">Xem ngày nghỉ</h4>
					</div>
					<div class="modal-body">
						<h4>Số ngày nghỉ có: <?php echo $_SESSION["day_off"]." ngày"; ?></h4>
						<?php displaydayleftuse($_SESSION["username"]) ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					</div>
				</div>

			</div>
    	</div>	
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<script src="/main.js"></script> <!-- Sử dụng link tuyệt đối tính từ root, vì vậy có dấu / đầu tiên -->
</body>

</html>