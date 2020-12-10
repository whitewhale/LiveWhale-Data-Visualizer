# LiveWhale-Data-Visualizer
This custom module reads data from APIs or online CSV files and presents it as an HTML table in LiveWhale. Works with the Highcharts (highcharts.com) and HighchartTable (highcharttable.org) plugins to create dynamic interactive charts.

As configured, module renders county-level COVID data from the Knowi API (https://www.knowi.com/coronavirus-dashboards/covid-19-api/).

Demo: https://www.livewhale.com/_data-visualizer/

## Installation
1. Add the data_visualizer module to /client/modules and log out of LiveWhale and back in again to register the new custom module.
2. Upload the data-visualizer-demo folder to your public web root.
3. Configure index.php to add the county of your choice (and population if needed).

Note: Module includes Highcharts plugin, which requires a license (highcharts.com).
