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
    $connectionInfo = array("Database" => "CoIST_LIVE", "UID" => "cpuckett", "PWD" => "JupiterSkies13!", "TrustServerCertificate" => True, "Driver" => 'ODBC Driver 18 for SQL Server');
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    #/Will spit out any error messages that may occur or it will not print anything
    if ($conn) {

    } else {
        echo "Connection could not be established.<br />";
        die(print_r(sqlsrv_errors(), true));
    }
    #Declaring empty arrays to parse data into from SQL tables
    $date_array = [];
    $PMS_array = [];
    $FU_array = [];
    $OC_array = [];
    $HD_array = [];
    $OCN_array = [];
    $ES_array = [];
    $UP_array = [];
    $PM_array = [];
    $II_array = [];
    #Fills both the date and vol array
    $tsql_vol = "SELECT CAST(Date as Date) as DayDate, COUNT(CallID) 'Call Count' FROM dbo.SCCalls WHERE DATEPART(dw, Date)  NOT IN (1, 7)  AND DATEADD(WEEK, -4, CAST(GETDATE() as DATE)) <= CAST(Date as Date) GROUP BY CAST(Date as Date) ORDER BY CAST(Date as Date) ASC";
    $stmt_vol = sqlsrv_query($conn, $tsql_vol);
    while ($row = sqlsrv_fetch_array($stmt_vol, SQLSRV_FETCH_ASSOC)) {
        array_push($date_array, $row['DayDate']->format('m/d/Y'));

    }

    $t_vol = "SELECT CAST(Date as DATE) as Date,ct.CallType,sc.CallTypeID,COUNT(sc.CallTypeID) as 'Call Count' FROM dbo.SCCalls sc LEFT JOIN dbo.SCCallTypes ct ON sc.CallTypeID = ct.CallTypeID WHERE Date > DATEADD(week, -4, GETDATE()) GROUP BY ct.CallType,sc.CallTypeID, CAST(Date as DATE) ORDER BY CAST(Date as DATE) ASC";
    $s_vol = sqlsrv_query($conn, $t_vol);
    while ($row = sqlsrv_fetch_array($s_vol, SQLSRV_FETCH_ASSOC)) {
        if ($row['CallTypeID'] = 103) {
            array_push($PMS_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 80) {
            array_push($FU_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 99) {
            array_push($OC_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 100) {
            array_push($HD_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 98) {
            array_push($OCN_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 79) {
            array_push($ES_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 113) {
            array_push($UP_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 102) {
            array_push($PM_array, $row['Call Count']);
        }
        if ($row['CallTypeID'] = 115) {
            array_push($II_array, $row['Call Count']);
        }
    }


    echo "<script>console.log('" . json_encode($HD_array) . "');</script>";
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
            <h1>Technician Metrics — Daily Call Volume</h1>
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
            var date_array = <?php echo json_encode($date_array); ?>;
            var vol_call_array = <?php echo json_encode($HD_array); ?>;

            //Not exactly how this is working -- its using a Chart.js framework to graph the data
            const ctx = document.getElementById('open_chart');
            var myLineChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: date_array,
                    datasets: [{
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