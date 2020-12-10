<?php require $_SERVER['DOCUMENT_ROOT'].'/livewhale/frontend.php';?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>

    <title>
      LiveWhale Data Visualization Module
    </title>
  	<meta name="pagename" content="COVID–19 Cases by County"/>
    <xphp var="exclude_global">true</xphp>
    <xphp var="theme">2020</xphp>
</head>
  <body class="homepage">
    <main>
      <div class="container">
            <h2>
              Data Module: COVID-19 statistics
            </h2>
            
            <code>&lt;xphp var=&quot;covid_stats_7dayavg&quot; format=&quot;json&quot; counties=&quot;Alameda County, California; San Mateo County, California&quot;/&gt;</code>
            
          <xphp var="covid_stats_7dayavg" format="json" counties="Alameda County, California; San Mateo County, California"/>

            <p clear="all"  style="margin-top: 100px;"></p>
            
            <code>&lt;xphp var=&quot;covid_stats_7dayavg_per100Kpop&quot; format=&quot;json&quot; counties=&quot;Alameda County, California;&quot; population=&quot;1671000&quot;/&gt;</code>
            
          <xphp var="covid_stats_7dayavg_per100Kpop" format="json" counties="Alameda County, California;" population="1671000"/>

      </div>
    </main>
    
    <style>
      .covid-chart {display: block; } /* hide <table> used by Highcharts to generate chart */
    </style>
    <script src="highcharts.js" type="text/javascript"></script> 
    <script src="jquery.highchartTable.js" type="text/javascript"></script> 
    <script>
      $(document).ready(function() {
       $('table.covid-chart').highchartTable();
      });
    </script>
  </body>
</html>