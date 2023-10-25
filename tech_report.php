<!DOCTYPE html>
<html>

<head>
    <title>InStore Metrics</title>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30" />
    <meta name="viewport" content="width=device-width, inital-scale=1.0">
    <link rel="stylesheet" href="stylesheets/istmetrics.css">
</head>

<body>
    <?php
    #Name/IP of Server
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
    #This is a revised verison of the original block -- the idea here is to stage the data almost completely with the SQL query so that the display can
    #be manipulated. Before the names of technicians were loaded seperately of the data which meant it could only be sorted based on the Technician/Agent ID
    $tsql = "DECLARE @CurDate DATE SELECT @CurDate = CAST(DATEADD(DD,-(DATEPART(DW,GETDATE())-7),GETDATE()) as Date) 
            SELECT
                PrefFullName,
                COUNT(t1.CallID) as [ToC],
                (SELECT COUNT(CallID) FROM dbo.SCCalls o1 WHERE o1.TechnicianID=t1.TechnicianID AND Date BETWEEN DATEADD(day,-6, @CurDate) AND @CurDate) as [New Open],
                (SELECT COUNT(CallID) FROM dbo.SCCalls o1 WHERE STATUS IN ('I','OKB') AND o1.TechnicianID=t1.TechnicianID AND Date BETWEEN DATEADD(day,-6, @CurDate) AND @CurDate) as [New Close],

                ROUND((
                ISNULL(NULLIF((SELECT COUNT(CallID) FROM dbo.SCCalls o1 WHERE STATUS IN ('I','OKB') AND o1.TechnicianID=t1.TechnicianID AND Date BETWEEN DATEADD(day,-6, @CurDate) AND @CurDate),0)
                /
                NULLIF(CAST((SELECT COUNT(CallID) FROM dbo.SCCalls o1 WHERE o1.TechnicianID=t1.TechnicianID AND Date BETWEEN DATEADD(day,-6, @CurDate) AND @CurDate) AS FLOAT),0),0)
                )*100,0)
                as [Closure] 

            FROM  dbo.SCCalls t1
            LEFT JOIN dbo.SHAgents t2
            ON t2.AgentId = t1.TechnicianID
            WHERE 
                EXISTS (SELECT TechnicianID FROM dbo.SCCalls WHERE AgentID = TechnicianID AND AgentID NOT IN (28,26,39) AND CAST(Date as DATE) > '2023-07-01') AND Status IN ('P','H')
            GROUP BY PrefFullName, TechnicianID
            ORDER by [New Open] DESC";
    #Converts our string SQL query above to an object that can be used to fetch an array of the output as done below with the $row while loop
    $stmt = sqlsrv_query($conn, $tsql);
    ?>
    <div id="container">
        <div class="menu">
            <a href="reports">Home</a>
            <a id="active" href="tech_report">Technician Metrics</a>
            <a href="cust_report">Customer Statistics</a>
        </div>
        <div class="submenu">
            <a id="active" href="tech_report">Weekly Open/Close</a>
            <a href="tech_report_comp">Weekly Closure Comparison</a>
            <a href="tech_report_open">Open Calls</a>
            <a href="lingering">Lingering Calls</a>
        </div>
        <div class="page_title">
            <h1>Technician Metrics — Weekly Open/Close</h1>
        </div>
        <!--This is the start of the display table -->
        <div class="data_body">
            <table>
                <tr class="table_header">
                    <td>Total Open Calls</td>
                    <td>Technician</td>
                    <td>Opened Calls</td>
                    <td>Closed Calls</td>
                    <td>Weekly Closure Rate</td>
                </tr>
                <?php
                #These variables are looped when displaying the Technician rows allowing for the total row to be calculated and displayed
                $total_ToC = 0;
                $total_New_Open = 0;
                $total_New_Close = 0;
                $total_Closure;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $total_ToC += $row['ToC'];
                    $total_New_Open += $row['New Open'];
                    $total_New_Close += $row['New Close'];
                    ?>
                    <tr>
                        <td>
                            <?php echo $row['ToC'] ?>
                        </td>
                        <td onclick="DetailEvent()">
                            <?php echo $row['PrefFullName'] ?>
                        </td>
                        <td>
                            <?php echo $row['New Open'] ?>
                        </td>
                        <td>
                            <?php echo $row['New Close'] ?>
                        </td>
                        <td>
                            <?php if ($row['Closure'] == 0) {
                                echo "—";
                            } else {
                                echo $row['Closure'], ' %';
                            } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php
                ?>
                <tr class="table_footer">
                    <td>
                        <?php echo $total_ToC ?>
                    </td>
                    <td>Total</td>
                    <td>
                        <?php echo $total_New_Open ?>
                    </td>
                    <td>
                        <?php echo $total_New_Close ?>
                    </td>
                    <td>
                        <?php try {
                            echo round(($total_New_Close / $total_New_Open) * 100), ' %';
                        } catch (DivisionByZeroError $e) {
                            echo 0, " %";
                        } ?>
                    </td>
            </table>
        </div>
    </div>
</body>