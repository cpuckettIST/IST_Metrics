<!DOCTYPE html>
<html>

<head>
    <title>InStore Metrics</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, inital-scale=1.0">
    <link rel="stylesheet" href="stylesheets/istmetrics.css">
</head>

<body>
    <?php
    $date_id = '2023-10-22';
    #Name of Server
    $serverName = "10.38.98.3";
    #It seems like ODBC Driver 18 works for our current installations, however, the TrustServerCertificate must be added as true or the connection fails
    $connectionInfo = array("Database" => "Cole_Reports", "UID" => "cpuckett", "PWD" => "JupiterSkies13!", "TrustServerCertificate" => True, "Driver" => 'ODBC Driver 18 for SQL Server');
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    #/Will spit out any error messages that may occur or it will not print anything
    if ($conn) {

    } else {
        echo "Connection could not be established.<br />";
        die(print_r(sqlsrv_errors(), true));
    }
    #Making query to new table Open_Call which will track the open calls at the end of each week Sunday-Saturday
    $tsql = "SELECT * FROM dbo.Open_Call WHERE Date > GETDATE()-180";
    # WHERE Date >= CAST(DATEADD(MONTH, -3, GetDate()) AS Date)
    $stmt = sqlsrv_query($conn, $tsql);
    #Declaring empty arrays to parse data into from SQL tables
    $date_array = [];
    $call_array = [];
    $tot_vol_array = [];
    $vol_array = [];
    $close_call_array = [];
    #Looping through the rows in Open_Call table and parsing Date to the date_array and Open_Calls to call_array
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        array_push($date_array, $row['Date']->format('m/d/Y'));
        array_push($call_array, $row['Open_Calls']);

    }
    #Creating new query to parse data from the Closed_Called table to the close_call_array
    $tsql_close = "SELECT * FROM dbo.Closed_Calls WHERE Date > GETDATE()-180 ORDER BY Date";
    $stmt_close = sqlsrv_query($conn, $tsql_close);
    while ($row = sqlsrv_fetch_array($stmt_close, SQLSRV_FETCH_ASSOC)) {
        array_push($close_call_array, $row['Closed_Calls']);

    }
    #Real Volume Calls 
    #$tsql_vol = "DECLARE @CurDate DATE SELECT @CurDate = CAST(DATEADD(DD,-(DATEPART(DW,GETDATE())-6),GETDATE()) as Date) SELECT Date,(SELECT COUNT(CallID) FROM CoIST_LIVE.dbo.SCCalls WHERE Date BETWEEN DATEADD(day, -6, dbo.Open_Call.Date) AND dbo.Open_Call.Date) AS 'Calls' FROM dbo.Open_Call WHERE Date > GETDATE()-180 UNION ALL SELECT @CurDate as DT, (SELECT COUNT(CallID) FROM CoIST_LIVE.dbo.SCCalls WHERE Date BETWEEN DATEADD(day, -6, @CurDate) AND @CurDate) AS 'Calls'";
    $tsql_vol = "DECLARE @CurDate DATE SELECT @CurDate = CAST(DATEADD(DD,-(DATEPART(DW,GETDATE())-6),GETDATE()) as Date) SELECT Date,(SELECT COUNT(CallID) FROM CoIST_LIVE.dbo.SCCalls WHERE CAST(CoIST_LIVE.dbo.SCCalls.Date as DATE) BETWEEN DATEADD(day, -6, dbo.Open_Call.Date)  AND dbo.Open_Call.Date AND TechnicianID != '28' AND TechnicianID != '26')  AS 'Calls' FROM dbo.Open_Call WHERE Date > GETDATE()-180UNION ALL SELECT @CurDate as DT, (SELECT COUNT(CallID) FROM CoIST_LIVE.dbo.SCCalls WHERE TechnicianID != '28' AND TechnicianID != '26' AND Date BETWEEN DATEADD(day, -6, @CurDate) AND @CurDate) AS 'Calls' ORDER BY Date ASC";
    $stmt_vol = sqlsrv_query($conn, $tsql_vol);
    while ($row = sqlsrv_fetch_array($stmt_vol, SQLSRV_FETCH_ASSOC)) {
        array_push($vol_array, $row['Calls']);

    }
    #These are hitting the current numbers
    
    #Creating connect info to get to the CoIST_LIVE database in the e-automate
    $connectionInfoCurrent = array("Database" => "CoIST_LIVE", "UID" => "cpuckett", "PWD" => "JupiterSkies13!", "TrustServerCertificate" => True, "Driver" => 'ODBC Driver 18 for SQL Server');
    $connCurrent = sqlsrv_connect($serverName, $connectionInfoCurrent);
    #Create query of SCCalls table for calls with pending 'P' flag and returning a count of the rows
    $tot_open_calls = "SELECT CallID FROM dbo.SCCalls WHERE Status IN ('P') AND TechnicianID != '28' AND TechnicianID != '26'";
    $tot_open_stmt = sqlsrv_query($connCurrent, $tot_open_calls, array(), array("Scrollable" => 'static'));
    $tot_open_num = sqlsrv_num_rows($tot_open_stmt);
    #Create query of SCCalls table for calls with invoiced or okay to bill 'I' or 'OKB' flag and returning a count of the rows
    $closed_calls = "SELECT CallID FROM dbo.SCCalls WHERE CAST(Date as DATE) BETWEEN '" . $date_id . "' AND '" . date('Y/m/d', strtotime($date_id . ' + 6 days')) . "'   AND TechnicianID != '28' AND TechnicianID != '26' AND (Status = 'I' OR Status = 'OKB')";
    $close_stmt = sqlsrv_query($connCurrent, $closed_calls, array(), array("Scrollable" => 'static'));
    $close_num = sqlsrv_num_rows($close_stmt);

    #Push current weeks number into the arrays to show in the graph
    array_push($close_call_array, $close_num);
    array_push($call_array, $tot_open_num);
    array_push($date_array, 'Current Week');
    echo "<script>console.log('" . json_encode($close_call_array) . "');</script>";
    echo "<script>console.log('" . json_encode($call_array) . "');</script>";
    echo "<script>console.log('" . json_encode($date_array) . "');</script>";
    #Fills the tot_vol_array with the total of open calls with the closed calls from that week
    for ($x = 0; $x < count($call_array); $x++) {
        $vol = ($call_array[$x] + $close_call_array[$x]);
        array_push($tot_vol_array, $vol);
    }
    ?>
    <div id="container">
        <div class="menu">
            <a href="reports">Home</a>
            <a id="active" href="tech_report">Technician Metrics</a>
            <a href="cust_report">Customer Statistics</a>
        </div>
        <div class="submenu">
            <a href="tech_report">Weekly Open/Close</a>
            <a href="tech_report_comp">Weekly Closure Comparison</a>
            <a id="active" href="tech_report_open">Open Calls</a>
            <a href="lingering">Lingering Calls</a>
        </div>
        <div class="page_title">
            <h1>Technician Metrics — Open Calls</h1>
            <p>*Began omitting Todd's calls 8/12/23 /</p>
        </div>
        <style>
            #chart-wrapper {
                display: inline-block;
                position: relative;
                width: 100%;
                height: 500px;
            }
        </style>
        <div id="chart-wrapper">
            <canvas id="open_chart"></canvas>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>

            //inserting the enclosed php segments allows the parsing of php arrays to javascript arrays that can be used for the graphing part of this page
            var call_array = <?php echo json_encode($call_array); ?>;
            var date_array = <?php echo json_encode($date_array); ?>;
            var close_call_array = <?php echo json_encode($close_call_array); ?>;
            var tot_call_array = <?php echo json_encode($tot_vol_array); ?>;
            var vol_call_array = <?php echo json_encode($vol_array); ?>;

            //Not exactly how this is working -- its using a Chart.js framework to graph the data
            const ctx = document.getElementById('open_chart');
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: date_array,
                    datasets: [{
                        label: '# of Open Calls',
                        data: call_array,
                        borderWidth: 3
                    },
                    {
                        label: '# of Closed Call',
                        data: close_call_array,
                        borderWidth: 1
                    },
                    {
                        label: 'Help Desk Call Volume',
                        data: vol_call_array,
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }, responsive: true,
                    maintainAspectRatio: false
                }

            });

        </script>


    </div>
</body>