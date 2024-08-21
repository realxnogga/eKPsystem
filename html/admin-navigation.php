<?php
include 'connection.php';

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<style>



  
  .sidebar-img {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
  }

  .sidebar-item img {
    margin-bottom: 10px; /* Adjust as needed */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
  }

  .sidebar-img span.hide-menu {
    text-align: center;
  }
</style>
<!--  Body Wrapper -->
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.html" class="text-nowrap logo-img">
            <!--<img src="assets/images/logos/dark-logo.svg" width="180" alt="" />-->
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
          <li class="sidebar-img">
    <span></span>
    <span class="hide-menu text-center">
        <img id="profilePic" src="profile_pictures/<?php echo $user['profile_picture'] ?: 'defaultpic.jpg'; ?>" alt="" class="d-block ui-w-80" style="max-width: 120px; max-height: 120px; margin-right: 10px;">
    </span>
</li>

            <br>

            <li class="sidebar-img">
                <span>
                </span>
                <span class="hide-menu"> <?php
                $fname = $_SESSION['first_name'];
                $lname = $_SESSION['last_name'];
                echo "<h4><b>Welcome,</b></h4>";
            ?></span>
              </a>
            </li>

            <li class="sidebar-img">
                <span>
                </span>
                <span class="hide-menu"> <?php
                echo "<h4><b>$fname $lname</b></h4>";
            ?></span>
              </a>
            </li>
            
<br>



            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">ACTIONS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="admin_dashboard.php" aria-expanded="false">
                <span>
                <i class="ti ti-dashboard"></i>
                </span>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="admin_acc_req.php" aria-expanded="false">
                <span>
                <i class="ti ti-user-check"></i>
                </span>
                <span class="hide-menu">Account Requests</span>
              </a>
            </li>
        <!--    <li class="sidebar-item">
              <a class="sidebar-link" href="sec-corner.php" aria-expanded="false">
                <span>
                <i class="ti ti-align-box-left-bottom"></i>
                </span>
                <span class="hide-menu">Secretaries Corner</span>
              </a>
            </li>-->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">OTHERS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="admin_setting.php" aria-expanded="false">
                <span>
                <i class="ti ti-settings"></i>
                </span>
                <span class="hide-menu">Settings</span>
              </a>
            </li>
          <!--  <li class="sidebar-item">
              <a class="sidebar-link" href="about.php" aria-expanded="false">
                <span>
                <i class="ti ti-info-circle"></i>
                </span>
                <span class="hide-menu">About Us</span>
              </a>
            </li>-->
          </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img id="profilePic" src="profile_pictures/<?php echo $user['profile_picture'] ?: 'defaultpic.jpg'; ?>" alt="" style="max-width: 35px; max-height: 35px;" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    



                    <a href="sa_setting.php" class="d-flex align-items-center gap-2 dropdown-item">
                    <i class="ti ti-settings"></i>
                      <p class="mb-0 fs-3">Settings</p>
                    </a>

                 

                

                    <a href="logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->

  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="assets/js/dashboard.js"></script>