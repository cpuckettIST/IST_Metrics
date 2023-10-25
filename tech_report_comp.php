<!DOCTYPE html>
<html>

<head>
    <title>InStore Metrics</title>
    <meta charset="UTF-8">

    <meta http-equiv="refresh" content="5" />
    <meta name="viewport" content="width=device-width, inital-scale=1.0">
    <link rel="stylesheet" href="stylesheets/istmetrics.css">
</head>

<body>
    <?php
    #Name of Server
    $serverName = "10.38.98.3";
    #It seems like ODBC Driver 18 works for our current installations, however, the TrustServerCertificate must be added as true or the connection fails
    $connectionInfo = array("Database" => "CoIST_LIVE", "UID" => "cpuckett", "PWD" => "JupiterSkies13!", "TrustServerCertificate" => True, "Driver" => 'ODBC Driver 18 for SQL Server');
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    #/Will spit out any error messages that may occur or it will not print anything
    if ($conn) {

    } else {
        echo "Connection could not be established.<br />";
        die(print_r(sqlsrv_errors(), true));
    }
    $tsql = "SELECT PrefFullName,AgentID FROM dbo.SHAgents WHERE EXISTS (SELECT TechnicianID FROM dbo.SCCalls WHERE AgentID = TechnicianID AND AgentID != '28' AND CAST(Date as DATE) > '2023-07-01')";
    $stmt = sqlsrv_query($conn, $tsql);
    ?>
    <div id="container">
        <div class="menu">
            <a href="reports">Home</a>
            <a id="active" href="tech_report">Technician Metrics</a>
            <a href="cust_report">Customer Statistics</a>
        </div>
        <div class="submenu">
            <a href="tech_report">Weekly Open/Close</a>
            <a id="active" href="tech_report_comp">Weekly Closure Comparison</a>
            <a href="tech_report_open">Open Calls</a>
        </div>
        <div class="page_title">
            <h1>Technician Metrics — Weekly Closure Comparison</h1>
            <p>*** Coming Soon ***</p>
        </div>
        <div class="data_body">


        </div>

    </div>
</body>