<html>
   <head>
      <title>Temperature and Humidity over X Days</title>
      <link href="Default.css" rel="stylesheet" type="text/css">
      <meta http-equiv="refresh" content="300" />
      <script src="fusioncharts/js/fusioncharts.js"></script>
      <script src="fusioncharts/js/fusioncharts.charts.js"></script>
      <script src="fusioncharts/js/themes/fusioncharts.theme.fint.js"></script>
   </head>
   <body>

<?php
$start = microtime(true);
include("fusioncharts/fusioncharts.php");
$hostdb = "####";  // MySQl host
$userdb = "####";  // MySQL username
$passdb = "####";  // MySQL password
$namedb = "####";  // MySQL database name
$dbhandle = new mysqli($hostdb, $userdb, $passdb, $namedb);
if ($dbhandle->connect_error) {
  exit("There was an error with your connection: ".$dbhandle->connect_error);
}

    //Device key
    if(isset($_GET['location'])) {
        $location = $_GET['location'];
    } else {
        $location = "[Default Location]";
    }

    //Determine duration
    if(isset($_GET['duration'])){
        $duration = $_GET['duration'];
    } else {
        $duration = "1"; //Defaults to 1 day of data
    }

  $strQuery = "SELECT DATE_FORMAT(datetime, '%c/%e %l:%i %p') AS category, temperature as value1, humidity as value2 FROM [Table Name] WHERE device_name = '" . $location . "' AND datetime > now() - INTERVAL " . $duration . " DAY ORDER BY [ID]";
 	$result = $dbhandle->query($strQuery) or exit("Error code ({$dbhandle->errno}): {$dbhandle->error}");
  if ($result) {

	$arrData = array(
        "chart" => array(
          	)
         	);
    $xml   = new SimpleXMLElement('<xml/>');
    $chart = $xml->addChild('chart');
    $chart->addAttribute('caption',"Temperature and Humidity on device " . $location);
    $chart->addAttribute('subcaption',"Last " . $duration . " Day(s)");
    $chart->addAttribute('showValues',"0");
    $chart->addAttribute('drawAnchors',"0");
    $chart->addAttribute('theme',"fint");
    $chart->addAttribute('setAdaptiveYMin',"1");
    $categories = $chart->addChild('categories');

      $axis1    = $chart->addChild('axis');
      $dataset1 = $axis1->addChild('dataset');
      $axis1->addAttribute('title',"Temperature");
      $axis1->addAttribute('tickWidth',"10");
      $axis1->addAttribute('numberSuffix',"°F");
      $axis1->addAttribute('divlineDashed',"1");
      $axis1->addAttribute('setAdaptiveYMin',"1");
      $axis1->addAttribute('color',"#d46d00");
      $dataset1->addAttribute('seriesName',"Temperature");
      $dataset1->addAttribute('lineThickness',"3");

      $axis2    = $chart->addChild('axis');
      $dataset2 = $axis2->addChild('dataset');
      $axis2->addAttribute('title',"Humidity");
      $axis2->addAttribute('axisOnLeft',"0");
      $axis2->addAttribute('numDivlines',"8");
      $axis2->addAttribute('tickWidth',"10");
      $axis2->addAttribute('numberSuffix',"%");
      $axis2->addAttribute('divlineDashed',"1");
      $axis2->addAttribute('color',"#006cc4");
      $axis2->addAttribute('maxValue',"100");
      $axis2->addAttribute('minValue',"0");
      $dataset2->addAttribute('seriesName',"Humidity");

      // pushing elements values

          while($row = mysqli_fetch_array($result)) {
            $label    = $row['category'];
              $category = $categories->addChild('category');
                $category->addAttribute('label',"$label");

            $value1  = ($row['value1']*1.8+32);
              $set1     = $dataset1->addChild('set');
                $set1->addAttribute('value',"$value1");

            $value2  = $row['value2'];
                $set2= $dataset2->addChild('set');
                  $set2->addAttribute('value',"$value2");

        	}



      $xml_data = $xml->asXML();
      
      //minifying the xml code
      $xml_data = preg_replace("/\r\n|\r|\n|\<[\?\/]{0,1}xml[^>]*>/",'',$xml_data);

			// chart object
      $multiaxislineChart = new FusionCharts("multiaxisline","chart1" , 750, 500, "chart-container", "xml", $xml_data);

      $multiaxislineChart->render();

      // closing db connection
      $dbhandle->close();

   }

?>
       <div class="back_navigation"><a href="detail.php"><</a></div>
        <table style="margin: auto">
             <tbody>
                 <tr>
                     <td><div id="chart-container"><!-- Fusion Charts will render here--></div></td>
                     <td valign="top">
                         <a href="?location=<?php echo $location ?>&duration=1">Day</a><br>
                         <a href="?location=<?php echo $location ?>&duration=7">Week</a>
                     </td>
                 </tr>
             </tbody>
        </table>
        <?php
            $end = microtime(true);
            $creationtime = ($end - $start);
            printf("<p align='center' style='display: none; '><font size='1'>Page created in %.6f seconds.</font></p>", $creationtime);
        ?>
    </body>
</html>
