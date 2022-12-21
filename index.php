<?php
if (isset($_POST['submit'])) {
    $city = $_POST['city'];

    $t=time();
    $today = (date("Y-m-d",$t));
    $sub_date = $_POST['day'];
    $newDate = date('Y-m-d', strtotime($today. "- $sub_date days"));
    $prev_date = strtotime($newDate);
    // echo $prev_date;
    $url = "http://api.openweathermap.org/data/2.5/weather?q=$city&dt=$prev_date&appid=49c0bad2c7458f1c76bec9654081a661";
}
else{
    $city = "Delhi";
    $t=time();
    $today = strtotime((date("Y-m-d",$t)));
    $url = "http://api.openweathermap.org/data/2.5/weather?q=$city&dt=$today&appid=49c0bad2c7458f1c76bec9654081a661";
}
    $open_weather = curl_init();
    curl_setopt($open_weather, CURLOPT_URL, $url);
    curl_setopt($open_weather, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($open_weather);
    curl_close($open_weather);
    $result = json_decode($result, true);
    // echo '<pre>';
    // print_r($result);
    // echo '</pre>';
    // print_r($_POST);
?>

    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Weather Application</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>
    <style> 
    .fa-sun {
                margin-left: 1rem;
                transition: all linear;
                animation: rotate 10s linear infinite;
                color: #f9d71c;
            }
             @keyframes rotate {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
}
  .fa-cloud {
                margin-left: 1rem;
                transition: all linear;
                color: grey;
            }
        
    </style>


    <body class="bg-info">

        <div class="container p-4 d-flex flex-column align-items-center justify-content-center">
            <h1> uCertify Weather App </h1>
            <p> Calculates Weather data in real time </p>

            <form method="post" class="mt-2">
                <input type="text" name="city" class="form-control" placeholder="Enter City Name">
                 <label for="day">Choose Any Day:</label>
                    <select name="day" class="form-control">
                        <option value="0">Today</option>
                        <option value="1">1 Day</option>
                        <option value="2">2 Day</option>
                        <option value="3">3 Day</option>
                        <option value="4">4 Days</option>
                        <option value="5">5 Days</option>
                        <option value="6">6 Days</option>
                        <option value="7">7 Days</option>
                        <option value="8">8 Days</option>
                        <option value="9">9 Days</option>
                    </select>
                <input type="submit" class="btn btn-primary my-2 mx-1" name="submit" value="submit">
            </form>




            <div class="card my-2" style="width: 18rem;">
                <img class="card-img-top" src="hurricane-storm.gif" alt="Card image cap">
                <?php
    function kelvinToCelsius(float $kelvin)
    {
        echo round(($kelvin - 273.15), 2);
    }
                ?>
                <div class="card-body">
                    <p class="card-text">
                       <p><b> City : </b> <?php echo $city.",".$result['sys']['country']; ?> </p>
                       <p><b> Temprature : </b> <?php kelvinToCelsius($result['main']['temp']);

                       ?> &#8451;
                       <?php if ($result['weather']['0']['main'] == 'Clear') {
        echo '<span class="ms-3"><i class="fa-solid fa-sun"></i></span>';
    }
    if ($result['weather']['0']['main'] == 'Clouds') {
        echo '<span class="ms-3"><i class="fa-solid fa-cloud"></i></span>';
    } else {
        echo '<span class="ms-3"><i class="fa-solid fa-cloud-sun-rain"></i></span>';
    }
                       ?></p> 
                       <p><b> Minimum Temprature : </b> <?php kelvinToCelsius($result['main']['temp_min']);

                       ?> &#8451;
                       </p>
                       <p><b> Maximum Temprature : </b> <?php kelvinToCelsius($result['main']['temp_max']);

                       ?> &#8451;
                       </p>
                       
                       <p><b> Wind Speed : </b> <?php echo $result['wind']['speed']; ?> </p>

                      
                    </p>
                </div>
            </div>



        </div>
       <?php 
?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>

    </html>