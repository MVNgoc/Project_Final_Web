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

	require_once('./admin/db.php');

	if(isset($_POST["leave-view"])){
		$username = $_POST["leave-view"];
		$sql = "SELECT * FROM leaveform WHERE username = '$username' ";
		$conn = open_database();
		$stm = $conn -> prepare($sql);
		$result = $conn-> query($sql);
		$row = $result->fetch_assoc();

		$leavetype = $row["leavetype"];
		$star_date = $row["star_date"];
		$end_date = $row["end_date"];
		$date_number = $row["date_num"];
		$leavereason = $row["leavereson"];
		$leavestatus = $row["leave_status"];

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
	<title>Home Page</title>
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
						if($_SESSION['positionid'] == 3) {
							echo '<li class="nav-item">
									<a class="nav-link" href="phongban.php">Quản lý phòng ban</a>
								</li>
								<li class="nav-item day-off-header">
									<a class="nav-link" href="#">Nghỉ phép</a>
									<ul class="navbar-nav day-off-tag">
										<li class="nav-item">
										<a class="nav-link" href="#">Duyệt đơn nghỉ phép</a>
										</li>
									</ul>
								</li>';	
						}
                        else if ($_SESSION['positionid'] == 1 || $_SESSION['positionid'] == 2) {
                            if($_SESSION['positionid'] == 1) {
                                echo '<li class="nav-item day-off-header">
                                        <a class="nav-link" href="#">Nghỉ phép</a>
                                        <ul class="navbar-nav day-off-tag">
											<li class="nav-item">
                                            	<a class="nav-link" href="dayoffform.php">Tạo đơn xin nghỉ phép</a>
                                            </li>
                                            <li class="nav-item">
                                            	<a class="nav-link" href="#">Duyệt đơn nghỉ phép</a>
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
					<h3 class="text-center text-secondary mt-2 mb-3 mb-3">Đơn xin nghỉ phép</h3>
					<form method="post" action="" novalidate>
						<div class="form-group">
							<label for="leavetype">Tiêu đề</label>
							<input value="<?php echo $leavetype; ?>" name="leavetype" required class="form-control" type="leavetype" placeholder="Nhập tiêu đề" id="leavetype" readonly>
						</div>

						<div class="form-group">
							<label for="star_date">Thời gian bắt đầu</label>
							<input value="<?= $star_date ?>" name="star_date" required class="form-control" type="date"  id="star_date" readonly>
						</div>

						<div class="form-group">
							<label for="end_date">Thời gian kết thúc</label>
							<input value="<?= $end_date ?>" name="end_date" required class="form-control" type="date" id="end_date" readonly>
						</div>

						<div class="form-group">
							<label for="date_number">Số ngày nghỉ</label>
							<input value="<?= $date_number ?>" name="date_number" required class="form-control" type="text" id="date_number" readonly>
						</div>

						<div class="form-group">
							<label for="leavereson">Lí do nghỉ phép</label>
							<input value="<?= $leavereason ?>" name="leavereson" required class="form-control" type="text" placeholder="Nhập lí do nghỉ" id="leavereson" readonly>
						</div>

						<div class="form-group">
							<label for="leave-status">Trạng thái</label>
							<input value="<?= $leavestatus ?>" name="leave-status" required class="form-control" type="text" id="leave-status" readonly>
						</div>

						
					</form>

				</div>
			</div>
    	</div>

		

		<footer class="footer">
			
		</footer>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<script src="/main.js"></script> <!-- Sử dụng link tuyệt đối tính từ root, vì vậy có dấu / đầu tiên -->
</body>

</html>