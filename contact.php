<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <title>Sports Facility Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/cbf02b9426.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="stylee.css">
    <style>
      .single_advisor_profile {
          position: relative;
          margin-bottom: 39px;
          -webkit-transition-duration: 500ms;
          transition-duration: 500ms;
          z-index: 1;
          border-radius: 15px;
          -webkit-box-shadow: 0 0.25rem 1rem 0 rgba(47, 91, 234, 0.125);
          box-shadow: 0 0.25rem 1rem 0 rgba(47, 91, 234, 0.125);
      }
      .single_advisor_profile .advisor_thumb {
          position: relative;
          z-index: 1;
          border-radius: 15px 15px 0 0;
          margin: 0 auto;
          padding: 0 auto;
          background-color: #3f43fd; /* sini */
          overflow: hidden;
      }
      .single_advisor_profile .advisor_thumb::after {
          -webkit-transition-duration: 500ms;
          transition-duration: 500ms;
          position: absolute;
          width: 150%;
          height: 80px;
          bottom: -45px;
          left: -25%;
          content: "";
          background-color: #ffffff;
          -webkit-transform: rotate(-15deg);
          transform: rotate(-15deg);
      }
      @media only screen and (max-width: 575px) {
          .single_advisor_profile .advisor_thumb::after {
              height: 160px;
              bottom: -90px;
          }
      }
      .single_advisor_profile .advisor_thumb .social-info {
          position: absolute;
          z-index: 1;
          width: 100%;
          bottom: 0;
          right: 30px;
          text-align: right;
      }
      .single_advisor_profile .advisor_thumb .social-info a {
          font-size: 14px;
          color: #020710;
          padding: 0 5px;
      }
      .single_advisor_profile .advisor_thumb .social-info a:hover,
      .single_advisor_profile .advisor_thumb .social-info a:focus {
          color: #3f43fd;
      }
      .single_advisor_profile .advisor_thumb .social-info a:last-child {
          padding-right: 0;
      }
      .single_advisor_profile .single_advisor_details_info {
          position: relative;
          z-index: 1;
          padding: 30px;
          text-align: right;
          -webkit-transition-duration: 500ms;
          transition-duration: 500ms;
          border-radius: 0 0 15px 15px;
          background-color: #ffffff;
      }
      .single_advisor_profile .single_advisor_details_info::after {
          -webkit-transition-duration: 500ms;
          transition-duration: 500ms;
          position: absolute;
          z-index: 1;
          width: 50px;
          height: 3px;
          background-color: #3f43fd;
          content: "";
          top: 12px;
          right: 30px;
      }
      .single_advisor_profile .single_advisor_details_info h6 {
          margin-bottom: 0.25rem;
          -webkit-transition-duration: 500ms;
          transition-duration: 500ms;
      }
      @media only screen and (min-width: 768px) and (max-width: 991px) {
          .single_advisor_profile .single_advisor_details_info h6 {
              font-size: 14px;
          }
      }
      .single_advisor_profile .single_advisor_details_info p {
          -webkit-transition-duration: 500ms;
          transition-duration: 500ms;
          margin-bottom: 0;
          font-size: 14px;
      }
      @media only screen and (min-width: 768px) and (max-width: 991px) {
          .single_advisor_profile .single_advisor_details_info p {
              font-size: 12px;
          }
      }
      .single_advisor_profile:hover .advisor_thumb::after,
      .single_advisor_profile:focus .advisor_thumb::after {
          background-color: #070a57;
      }
      .single_advisor_profile:hover .advisor_thumb .social-info a,
      .single_advisor_profile:focus .advisor_thumb .social-info a {
          color: #ffffff;
      }
      .single_advisor_profile:hover .advisor_thumb .social-info a:hover,
      .single_advisor_profile:hover .advisor_thumb .social-info a:focus,
      .single_advisor_profile:focus .advisor_thumb .social-info a:hover,
      .single_advisor_profile:focus .advisor_thumb .social-info a:focus {
          color: #ffffff;
      }
      .single_advisor_profile:hover .single_advisor_details_info,
      .single_advisor_profile:focus .single_advisor_details_info {
          background-color: #070a57;
      }
      .single_advisor_profile:hover .single_advisor_details_info::after,
      .single_advisor_profile:focus .single_advisor_details_info::after {
          background-color: #ffffff;
      }
      .single_advisor_profile:hover .single_advisor_details_info h6,
      .single_advisor_profile:focus .single_advisor_details_info h6 {
          color: #ffffff;
      }
      .single_advisor_profile:hover .single_advisor_details_info p,
      .single_advisor_profile:focus .single_advisor_details_info p {
          color: #ffffff;
      }
    </style>
    <script>
      function goBack(event) {
        event.preventDefault();
        window.history.back();
      }
    </script>
  </head>
  <body>
    <header>
      <nav class="navbar navbar-inverse">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
              <span class="icon-bar"></span>
            </button>
          </div>
          <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav">
              <li><img src="PZ_tp.svg" width="40" height="40" alt="Logo"></li>
              <li><a href="javascript:void(0);" onclick="goBack(event);" class="nav-btn"> Back</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <div class="container">
        <div class="row" style="margin: 25px;">
          <div class="col-sm-12 text-center">
            <!-- Section Heading-->
            <div class="section_heading">
              <span class="p3">Meet Our Creative Team</span><br>
              <span class="p2">Our website is completely creative, lightweight, clean <br>&amp; super responsive thanks to this great team.</span>
              <div class="line"></div>
            </div>
          </div>
        </div>
        <div class="row" style="padding: 10px;">
          <!-- Single Advisor-->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="single_advisor_profile wow fadeInUp" style="margin-right: 3px;" data-wow-delay="0.2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
              <!-- Team Thumb-->
              <div class="advisor_thumb"><img src="PZ_tp.svg" style="width: 355px;" alt="Helper">
                <!-- Social Info-->
                <div class="social-info">
                  <a href="https://www.instagram.com/tengkumuhammadfaris?utm_source=ig_web_button_share_sheet&igsh=OGQ5ZDc2ODk2ZA=="><i class="fa fa-instagram"></i></a>
                  <a href="https://wa.me/601129140447"><i class="fa fa-whatsapp"></i></a>
                </div>
              </div>
              <!-- Team Details-->
              <div class="single_advisor_details_info">
                <h6>TENGKU FARIS TENGKU AZMAN</h6>
                <p class="designation">Founder &amp; CEO</p>
              </div>
            </div>
          </div>
          <!-- Single Advisor-->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="single_advisor_profile wow fadeInUp" data-wow-delay="0.3s" style="visibility: visible; animation-delay: 0.3s; animation-name: fadeInUp;">
              <!-- Team Thumb-->
              <div class="advisor_thumb"><img src="bukh.png" style="width: 355px" alt="Bukhoury">
                <!-- Social Info-->
                <div class="social-info">
                  <a href="https://www.instagram.com/bukhourymuslim?utm_source=ig_web_button_share_sheet&igsh=OGQ5ZDc2ODk2ZA=="><i class="fa fa-instagram"></i></a>
                  <a href="https://wa.me/601114956232"><i class="fa fa-whatsapp"></i></a>
                </div>
              </div>
              <!-- Team Details-->
              <div class="single_advisor_details_info">
                <h6>BUKHOURY MUSLIM</h6>
                <p class="designation">UI Designer</p>
              </div>
            </div>
          </div>
          <!-- Single Advisor-->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="single_advisor_profile wow fadeInUp" data-wow-delay="0.4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
              <!-- Team Thumb-->
              <div class="advisor_thumb"><img src="PZ_tp.svg" style="width: 355px;" alt="Helper">
                <!-- Social Info-->
                <div class="social-info">
                  <a href="https://www.instagram.com/ikmlazman?utm_source=ig_web_button_share_sheet&igsh=OGQ5ZDc2ODk2ZA=="><i class="fa fa-instagram"></i></a>
                  <a href="https://wa.me/60179720538"><i class="fa fa-whatsapp"></i></a>
                </div>
              </div>
              <!-- Team Details-->
              <div class="single_advisor_details_info">
                <h6>IKMAL AZMAN</h6>
                <p class="designation">Developer</p>
              </div>
            </div>
          </div>
          <!-- Single Advisor-->
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="single_advisor_profile wow fadeInUp" data-wow-delay="0.5s" style="visibility: visible; animation-delay: 0.5s; animation-name: fadeInUp;">
              <!-- Team Thumb-->
              <div class="advisor_thumb"><img src="PZ_tp.svg" style="width: 355px;" alt="Helper">
                <!-- Social Info-->
                <div class="social-info">
                  <a href="tel:+601114956232"><i class="fa fa-phone"></i></a>
                  <a href="mailto:mbukhoury.mb@gmail.com?subject=SPORT%20FACILITY%20HELP"><i class="fa fa-envelope"></i></a>
                </div>
              </div>
              <!-- Team Details-->
              <div class="single_advisor_details_info">
                <h6>PlayZone</h6>
                <p class="designation">Customer Service</p>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <footer class="container-fluid text-center">
        <div class="collapse navbar-collapse" id="myNavbar">
          <ul class="nav navbar-nav navbar-right">
            <li>
              <h5 >Open-source Apache Licensed</h5>
            </li>
          </ul>
        </div>
      </footer>
</html>