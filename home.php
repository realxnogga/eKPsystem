<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home</title>
  <link rel="shortcut icon" type="image/png" href=".assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

  <script>
    // Function to update and display the visit count
    function updateVisitCount() {
      try {
        if (typeof(Storage) !== "undefined") {
          if (localStorage.getItem('visitCount')) {
            let visitCount = parseInt(localStorage.getItem('visitCount')) + 1;
            localStorage.setItem('visitCount', visitCount);
          } else {
            localStorage.setItem('visitCount', 1);
          }
          document.getElementById('visitCount').innerHTML = "Total Website Visits: " + localStorage.getItem('visitCount');
        } else {
          console.error("Local storage is not supported.");
        }
      } catch (error) {
        console.error("An error occurred: " + error.message);
      }
    }

    window.onload = updateVisitCount;
  </script>
  <style>
    body {
      background-image: url('img/homebg.jpg');
      background-size: cover;
    }

    .card {
      box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
    }

    .translucent-background {
      background-color: rgba(0, 0, 0, 0.4);
      padding: 10px;
      border-radius: 10px;
      color: white;
      margin: 20px;
    }
  </style>
</head>

<body>
  <div class="body-wrapper">
    <header class="app-header">
      <h1 class="text-center text-white animate__animated animate__fadeInDown" style="text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
        Welcome to E-Katarungan Pambarangay System
      </h1>
    </header>

    <!-- Tab content -->
    <div id="homeTab" style="display: block;">
      <br>
      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="col-lg-100">
            <div class="row">
              <div class="col-lg-12">
                <div class="card-body visit p-0 text-center">
                  <div id="visitCount" class="animate__animated animate__fadeInUp" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);"></div><br>
                  <div id="clock" class="animate__animated animate__fadeInUp" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); font-size: 30px;"></div>

                  <script>
                    function updateClock() {
                      var now = new Date();
                      var hours = now.getHours();
                      var minutes = now.getMinutes();
                      var seconds = now.getSeconds();
                      var meridiem = (hours >= 12) ? 'PM' : 'AM';

                      hours = (hours % 12) || 12;
                      hours = (hours < 10) ? '0' + hours : hours;
                      minutes = (minutes < 10) ? '0' + minutes : minutes;
                      seconds = (seconds < 10) ? '0' + seconds : seconds;

                      var timeString = hours + ':' + minutes + ':' + seconds + ' ' + meridiem;
                      var dayOfWeek = getDayOfWeek(now.getDay());
                      var fullDate = getFullDate(now);

                      document.getElementById('clock').innerHTML = timeString + '<br>' + dayOfWeek + ', ' + fullDate;
                    }

                    function getDayOfWeek(dayIndex) {
                      var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                      return days[dayIndex];
                    }

                    function getFullDate(date) {
                      var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                      var day = date.getDate();
                      var month = monthNames[date.getMonth()];
                      var year = date.getFullYear();

                      return day + ' ' + month + ' ' + year;
                    }

                    setInterval(updateClock, 1000);
                    updateClock();
                  </script>
                </div>
              </div>
            </div>
          </div>

          <br>

          <div class="card-body card-develop text-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title fw-semibold mb-4 mx-auto text-white fs-20 animate__animated animate__zoomIn" style="font-size: 25px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                The Developers 2024
              </h5>
            </div>
            <div class="row">
              <div class="col-sm-2 col-xl-2">
                <div class="card overflow-hidden rounded-2 animate__animated animate__fadeInLeft">
                  <div class="position-relative">
                    <img src="img/phil.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal">
                  </div>
                  <div class="card-body pt-3 p-4">
                    <h6 class="fw-semibold fs-4">Phil Bojo Repotente</h6>
                  </div>
                </div>
              </div>

              <div class="col-sm-2 col-xl-2">
                <div class="card overflow-hidden rounded-2 animate__animated animate__fadeInRight">
                  <div class="position-relative">
                    <img src="img/angel.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal4">
                  </div>
                  <div class="card-body pt-3 p-4">
                    <h6 class="fw-semibold fs-4">Angel May L. De Guzman</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-8 d-flex align-items-stretch">
              <div class="card w-100 rounded-2 animate__animated animate__zoomIn" style="background-image: url('img/home1.png'); background-size: cover; background-position: center;">
                <div class="translucent-background" style="text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); font-size: 14px;">
                  Benchmarking Activity, Barangay San Vicente, Biñan City, Laguna, March 11, 2024
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
      function showTab(tabName) {
        document.getElementById('homeTab').style.display = 'none';
        document.getElementById('downloadsTab').style.display = 'none';
        document.getElementById('loginTab').style.display = 'none';
        document.getElementById(tabName + 'Tab').style.display = 'block';
      }
    </script>
</body>

</html>