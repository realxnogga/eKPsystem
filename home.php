
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
        // Check if local storage is supported
        if (typeof(Storage) !== "undefined") {
          // Check if the 'visitCount' key exists in local storage
          if (localStorage.getItem('visitCount')) {
            // If it exists, increment the count
            let visitCount = parseInt(localStorage.getItem('visitCount')) + 1;
            localStorage.setItem('visitCount', visitCount);
          } else {
            // If it doesn't exist, set the count to 1
            localStorage.setItem('visitCount', 1);
          }

          // Display the visit count on the page
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
                    <div id="visitCount" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);"></div><br>
                    <div id="clock" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); font-size: 30px;"></div>

                    <script>
    function updateClock() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        var meridiem = (hours >= 12) ? 'PM' : 'AM';

        // Convert hours to 12-hour format
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

    // Update the clock every second
    setInterval(updateClock, 1000);

    // Initial call to display the clock immediately
    updateClock();
</script>


                    <div class="row align-items-center justify-content-center">
                        <!-- Your centered content goes here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
        
          <br>

    <div class="card-body card-develop text-center">
        <div class="d-flex align-items-center">
        <h5 class="card-title fw-semibold mb-4 mx-auto text-white fs-20" style="font-size: 25px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">The Developers 2024</h5>

        </div> 
        <div class="row">
          <div class="col-sm-2 col-xl-2">
            <div class="card overflow-hidden rounded-2">
              <div class="position-relative">
              <img src="img/phil.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal">
                <a href="javascript:void(0)"></a>                      </div>
              <div class="card-body pt-3 p-4">
                <h6 class="fw-semibold fs-4">Phil Bojo Repotente</h6>
              </div>
            </div>
          </div>
          <!-- Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <!-- Image inside the modal -->
        <img src="img/phil-1.png" class="img-fluid" alt="...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Link Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
          <div class="col-sm-2 col-xl-2">
            <div class="card overflow-hidden rounded-2">
              <div class="position-relative">
                <a href="javascript:void(0)"><img src="img/angel.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal4"></a>
                <a href="javascript:void(0)"></a>                      </div>
              <div class="card-body pt-3 p-4">
                <h6 class="fw-semibold fs-4">Angel May L. De Guzman</h6>
              </div>
            </div>
          </div>
          <!-- Modal -->
<div class="modal fade" id="imageModal4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <!-- Image inside the modal -->
        <img src="img/angel-1.png" class="img-fluid" alt="...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
          <div class="col-sm-2 col-xl-2">
            <div class="card overflow-hidden rounded-2">
              <div class="position-relative">
                <a href="javascript:void(0)"><img src="img/zydrick.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal2"></a>
                <a href="javascript:void(0)"></a>                      </div>
              <div class="card-body pt-3 p-4">
                <h6 class="fw-semibold fs-4">Prince Zydrick R. Salazar</h6>
              </div>
            </div>
          </div>
           <!-- Modal -->
<div class="modal fade" id="imageModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <!-- Image inside the modal -->
        <img src="img/zydrick-1.png" class="img-fluid" alt="...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
          <div class="col-sm-2 col-xl-2">
            <div class="card overflow-hidden rounded-2">
              <div class="position-relative">
                <a href="javascript:void(0)"><img src="img/grace.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal3"></a>
                <a href="javascript:void(0)"></a>                      </div>
              <div class="card-body pt-3 p-4">
                <h6 class="fw-semibold fs-4">Mary Grace M. Bautista</h6>
              </div>
            </div>
          </div>
          <!-- Modal -->
<div class="modal fade" id="imageModal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <!-- Image inside the modal -->
        <img src="img/grace-1.png" class="img-fluid" alt="...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
          <div class="col-sm-2 col-xl-2">
            <div class="card overflow-hidden rounded-2">
              <div class="position-relative">
                <a href="javascript:void(0)"><img src="img/carl.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal1"></a>
                <a href="javascript:void(0)"></a>                      </div>
              <div class="card-body pt-3 p-4">
                <h6 class="fw-semibold fs-4">Carl Janzell N. Oropesa</h6>
              </div>
            </div>
          </div>
          <!-- Modal -->
<div class="modal fade" id="imageModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <!-- Image inside the modal -->
        <img src="img/carl-1.png" class="img-fluid" alt="...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
          <div class="col-sm-2 col-xl-2">
            <div class="card overflow-hidden rounded-2">
              <div class="position-relative">
                <a href="javascript:void(0)"><img src="img/kisha.png" class="card-img-top rounded-0" alt="..." data-toggle="modal" data-target="#imageModal6"></a>
                <a href="javascript:void(0)"></a>                      </div>
              <div class="card-body pt-3 p-4">
                <h6 class="fw-semibold fs-4">Kisha V. Abrenilla</h6>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal -->
<div class="modal fade" id="imageModal6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <!-- Image inside the modal -->
        <img src="img/kisha-1.png" class="img-fluid" alt="...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<br>
<div class="row">
    <!-- Left side with text -->
    <div class="col-lg-4 d-flex align-items-stretch">
    <p style="font-size: 16px; color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); text-align: justify;">
        Introducing the E-Katarungan Pambarangay System project by DILG Cluster-A in collaboration with Laguna State Polytechnic University, Los Baños, Laguna. This initiative revolutionizes local governance, leveraging technology to enhance the efficiency and accessibility of the barangay justice system, fostering community empowerment and harmonious progress.

        <br><br>

        Leveraging the latest technologies, this cutting-edge system not only enhances project management efficiency but also promotes seamless communication and resource sharing among users. With a user-friendly interface and robust features, it empowers students and educators to effortlessly navigate the collaborative landscape, fostering an environment that nurtures innovation and facilitates knowledge exchange.
    </p>
</div>

<!-- Right side with card -->
<div class="col-lg-8 d-flex align-items-strech">
    <div class="card w-100 rounded-2" style="background-image: url('img/home1.png'); background-size: cover; background-position: center;">
            <div class="translucent-background" style="text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); font-size: 14px;">
                Benchmarking Activity, Barangay San Vicente, Biñan City, Laguna, March 11, 2024
        </div>
    </div>
</div>
<hr>

<div class="card-body card-develop text-center">
        <div class="d-flex align-items-center">
        <h5 class="card-title fw-semibold mb-4 mx-auto text-white fs-20" style="font-size: 25px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">The Developers 2025</h5>
        </div> 

        <hr style="color: white;"> 

<!--_______________________________________________________-->
<!-- Developer Cards Row -->
<div class="row justify-content-center">

  <!-- Developer 1 - Wyeth Laurence M. Larios -->
  <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-4">
    <div class="card h-100 overflow-hidden rounded-2 d-flex flex-column"> <!-- Added flex column -->
      <div class="position-relative">
        <a href="javascript:void(0)"><img src="img/warrenpic.jpg" class="card-img-top rounded-0" alt="Wyeth Larios" data-toggle="modal" data-target="#imageModal5"></a>
      </div>
      <div class="card-body pt-2 p-1 text-center d-flex flex-column flex-grow-1"> <!-- Flex grow added -->
        <h6 class="fw-semibold fs-4 mb-auto">Wyeth Laurence M. Larios</h6> <!-- mb-auto pushes text down -->
        <p class="text-secondary mb-0 mt-auto" style="font-size: 0.8rem;">Version 2 Ekp Developer</p> <!-- mt-auto at bottom -->
      </div>
    </div>
  </div>

  <!-- Developer 2 - Jacob B. Cortes -->
  <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-4">
    <div class="card h-100 overflow-hidden rounded-2 d-flex flex-column">
      <div class="position-relative">
        <a href="javascript:void(0)"><img src="img/jacobpic.jpg" class="card-img-top rounded-0" alt="Jacob Cortes" data-toggle="modal" data-target="#imageModal12"></a>
      </div>
      <div class="card-body pt-2 p-1 text-center d-flex flex-column flex-grow-1">
        <h6 class="fw-semibold fs-4 mb-auto">Jacob B. Cortes</h6>
        <p class="text-secondary mb-0 mt-auto" style="font-size: 0.8rem;">LTIA Developer</p>
      </div>
    </div>
  </div>

  <!-- Developer 3 - John Mark O. Montecillo -->
  <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-4">
    <div class="card h-100 overflow-hidden rounded-2 d-flex flex-column">
      <div class="position-relative">
        <a href="javascript:void(0)"><img src="img/jmpic.jpg" class="card-img-top rounded-0" alt="John Mark Montecillo" data-toggle="modal" data-target="#imageModal7"></a>
      </div>
      <div class="card-body pt-2 p-1 text-center d-flex flex-column flex-grow-1">
        <h6 class="fw-semibold fs-4 mb-auto">John Mark O. Montecillo</h6>
        <p class="text-secondary mb-0 mt-auto" style="font-size: 0.8rem;">LTIA Developer</p>
      </div>
    </div>
  </div>

  <!-- Developer 4 - Lester F. Almadovar -->
  <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-4">
    <div class="card h-100 overflow-hidden rounded-2 d-flex flex-column">
      <div class="position-relative">
        <a href="javascript:void(0)"><img src="img/lesterpic.jpg" class="card-img-top rounded-0" alt="Lester Almadovar" data-toggle="modal" data-target="#imageModal8"></a>
      </div>
      <div class="card-body pt-2 p-1 text-center d-flex flex-column flex-grow-1">
        <h6 class="fw-semibold fs-4 mb-auto">Lester F. Almadovar</h6>
        <p class="text-secondary mb-0 mt-auto" style="font-size: 0.8rem;">LTIA Research Paper</p>
      </div>
    </div>
  </div>

</div>
<!-- Modal for Developer 1 -->
<div class="modal fade" id="imageModal5" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="img/warren.jpg" class="img-fluid" alt="Wyeth Larios">
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Developer 2 -->
<div class="modal fade" id="imageModal12" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="img/jacob.jpg" class="img-fluid" alt="Developer 2">
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Developer 3 -->
<div class="modal fade" id="imageModal7" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="img/jm.jpg" class="img-fluid" alt="Developer 3">
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Developer 4 -->
<div class="modal fade" id="imageModal8" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="img/lester.jpg" class="img-fluid" alt="Developer 4">
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <!-- Left side with text -->
    <div class="col-lg-4 d-flex align-items-stretch">
    <p style="font-size: 16px; color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); text-align: justify;">
    The Lupong Tagapamayapa Incentives Award (LTIA) System is an innovative project developed by DILG Cluster-A in partnership with Laguna State Polytechnic University, Los Baños Campus. This digital transformation initiative modernizes the barangay justice system by integrating technology to streamline dispute resolution processes and enhance transparency in local governance.
        <br><br>

        Leveraging the latest technologies, this cutting-edge system not only enhances project management efficiency but also promotes seamless communication and resource sharing among users. With a user-friendly interface and robust features, it empowers students and educators to effortlessly navigate the collaborative landscape, fostering an environment that nurtures innovation and facilitates knowledge exchange.
    </p>
</div>

<!-- Right side with card -->
<div class="col-lg-8 d-flex align-items-strech">
    <div class="card w-100 rounded-2" style="background-image: url('img/ltia.jpg'); background-size: cover; background-position: center;">
            <div class="translucent-background" style="text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5); font-size: 14px;">
              LTIA System presentation in front of field-officers of Cluster A at Santa Rosa City
        </div>
    </div>
</div>
  <!--_______________________________________________________-->






    <div id="downloadsTab" style="display: none;">
    <div class="body-wrapper">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-100 d-flex align-items-strech">
            <div class="card w-100">
              <div class="card-body">
                <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                  <div class="mb-3 mb-sm-0">
                    <h5 class="card-title fw-semibold">Downloads</h5>
                  </div>
                 
        </div>
</div>



            </div>
          </div>
          </div>
        </div>
  </div>

    </div>
  </div>
    </div>
    <div id="loginTab" style="display: none;">
  <div class="body-wrapper">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-100 d-flex align-items-strech">
            <div class="card w-100">
              <div class="card-body">
                <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                  <div class="mb-3 mb-sm-0">
                    <h5 class="card-title fw-semibold">Title</h5>
                  </div>
                 
        </div>
</div>


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
      // Hide all tabs
      document.getElementById('homeTab').style.display = 'none';
      document.getElementById('downloadsTab').style.display = 'none';
      document.getElementById('loginTab').style.display = 'none';

      // Show the selected tab
      document.getElementById(tabName + 'Tab').style.display = 'block';
    }
  </script>
</body>

</html>
