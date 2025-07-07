<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$weather = null;
$error = null;
$history = [];
$apiKey = "8P2NQ8BNU93EQT8JPHTE8K2ED";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["city"])) {
  $city = urlencode($_POST["city"]);
  $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/$city/next7days?unitGroup=metric&key=$apiKey&contentType=json";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);

  if ($response === false) {
    $error = "❌ Error fetching weather data: " . curl_error($ch);
  } else {
    $data = json_decode($response, true);
    if (isset($data['address'])) {
      $weather = $data;
      $_SESSION['history'][] = $data['resolvedAddress'];
    } else {
      $error = "Invalid city name or API error.";
    }
  }
  curl_close($ch);
}
$history = $_SESSION['history'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Weather Now ⛅</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
    }
    body {
      background-color: #0d0d1f;
      font-family: 'Segoe UI', sans-serif;
      color: white;
      text-align: center;
      padding: 40px;
      min-height: 100vh;
      box-sizing: border-box;
    }
    .container {
      max-width: 1000px;
      margin: auto;
      background: #1a1a40;
      padding: 30px;
      border-radius: 25px;
      box-shadow: 0 0 25px #ff007f;
    }
    h1 {
      color: #ff007f;
      font-size: 3rem;
      text-shadow: 0 0 15px #ff007f;
    }
    input[type="text"] {
      width: 70%;
      padding: 14px;
      border-radius: 10px;
      border: none;
      font-size: 1rem;
      background-color: #2d2d60;
      color: #fff;
      box-shadow: 0 0 10px rgba(255, 0, 127, 0.4);
      transition: 0.4s ease;
    }
    input[type="text"]:hover,
    input[type="text"]:focus {
      outline: none;
      box-shadow: 0 0 20px #ff66b2;
    }
    button {
      padding: 12px 25px;
      font-size: 1rem;
      border: none;
      border-radius: 30px;
      font-weight: bold;
      margin: 10px 5px;
      cursor: pointer;
      transition: 0.3s ease;
      box-shadow: 0 0 12px transparent;
    }
    button[type="submit"] {
      background-color: #ff007f;
      color: white;
    }
    button[type="submit"]:hover,
    #toggleHistory:hover {
      background: transparent;
      border: 2px solid #ff007f;
      color: #ff007f;
      box-shadow: 0 0 20px #ff007f;
    }
    #locationBtn {
      background-color: #3b82f6;
      color: white;
    }
    #locationBtn:hover {
      background: transparent;
      border: 2px solid #3b82f6;
      color: #3b82f6;
      box-shadow: 0 0 20px #3b82f6;
    }
    .result {
      margin-top: 20px;
      background-color: #262650;
      padding: 15px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(255, 0, 127, 0.3);
    }
    .forecast-container {
      margin-top: 30px;
      overflow-x: auto;
      white-space: nowrap;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    .forecast-container::-webkit-scrollbar {
      display: none;
    }
    .forecast-day {
      display: inline-block;
      background-color: #262650;
      padding: 15px;
      margin: 0 10px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(255, 0, 127, 0.3);
      width: 160px;
    }
    .error {
      color: #ff4d4f;
      font-weight: bold;
    }
    .history {
      margin-top: 20px;
      font-size: 0.9rem;
      color: #aaa;
      display: none;
    }
    #toggleHistory {
      background-color: #6b7280;
      color: white;
    }
    #toggleHistory:hover {
      background: transparent;
      border: 2px solid #6b7280;
      color: #6b7280;
      box-shadow: 0 0 20px #6b7280;
    }
  </style>
  <script>
    function getLocationWeather() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(success, error);
      } else {
        alert("Geolocation is not supported by this browser.");
      }

      function success(position) {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lon}&localityLanguage=en`)
          .then(response => response.json())
          .then(data => {
            if (data.city) {
              document.querySelector("input[name='city']").value = data.city;
              document.querySelector("form").submit();
            } else {
              alert("❌ Could not determine city.");
            }
          });
      }

      function error() {
        alert("❌ Unable to retrieve your location.");
      }
    }

    function toggleHistory() {
      const historyDiv = document.querySelector(".history");
      historyDiv.style.display = historyDiv.style.display === "none" ? "block" : "none";
    }
  </script>
</head>
<body>
  <div class="container">
    <h1>🌤 Weather Now ⛅</h1>
    <form method="POST">
      <input type="text" name="city" placeholder="Enter City Name" required>
      <br><br>
      <button type="submit">Get Weather</button>
      <button type="button" id="locationBtn" onclick="getLocationWeather()">📍 Use My Location</button>
      <button type="button" id="toggleHistory" onclick="toggleHistory()">📂 Show/Hide Search History</button>
    </form>

    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($weather): ?>
      <div class="result">
        <h2><?= htmlspecialchars($weather['resolvedAddress']) ?></h2>
        <p><strong>Condition:</strong> <?= $weather['days'][0]['conditions'] ?></p>
        <p>🌡 Temp: <?= $weather['days'][0]['temp'] ?>°C</p>
        <p>💧 Humidity: <?= $weather['days'][0]['humidity'] ?>%</p>
        <p>💨 Wind: <?= $weather['days'][0]['windspeed'] ?> km/h</p>
      </div>

      <h3 style="margin-top: 30px; color:#f43f5e;">📆 7-Day Forecast</h3>
      <div class="forecast-container">
        <?php for ($i = 0; $i < 7; $i++): ?>
          <div class="forecast-day">
            <p><strong><?= date('l, M j', strtotime($weather['days'][$i]['datetime'])) ?></strong></p>
            <p><?= $weather['days'][$i]['conditions'] ?></p>
            <p>🌡 <?= $weather['days'][$i]['temp'] ?>°C</p>
            <p>💧 <?= $weather['days'][$i]['humidity'] ?>%</p>
          </div>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($history)): ?>
      <div class="history">
        <h4>📌 Search History:</h4>
        <ul>
          <?php foreach (array_reverse($history) as $item): ?>
            <li><?= htmlspecialchars($item) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
