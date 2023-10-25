<!DOCTYPE html>
<html>

<head>
    <title>InStore Metrics</title>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="150" />
    <meta name="viewport" content="width=device-width, inital-scale=1.0">
    <link rel="stylesheet" href="stylesheets/istmetrics.css">
</head>
<!-- This function allows the customer tickets to be toggled per line-->
<script>
    function toggleCustomer(divName) {
        var x = document.getElementById(divName);
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
</script>

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
    $tsql = "SELECT PrefFullName, CustomerName, Description, a.CallID, Cast(a.LastUpdate as Date) as 'Last Updated', DATEDIFF(day, CAST(a.LastUpdate AS Date), GetDate()) as 'Days Since Last Update' FROM dbo.SCCalls a LEFT JOIN dbo.ShAgents b ON a.TechnicianID = b.AgentID LEFT JOIN dbo.ARCustomers c ON a.CustomerID = c.CustomerID WHERE a.LastUpdate < GETDATE()-7 AND Status IN ('P','H') AND  AgentID NOT IN ('28','26','39')  ORDER BY [Days Since Last Update] DESC";
    $stmt = sqlsrv_query($conn, $tsql);
    $count = sqlsrv_query($conn, $tsql, array(), array("Scrollable" => 'static'));
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
            <a href="tech_report_open">Open Calls</a>
            <a id="active" href="lingering">Lingering Calls</a>
        </div>
        <div class="page_title">
            <h1>Technician Metrics — Lingering Calls [
                <?php echo sqlsrv_num_rows($count) ?> ]
            </h1>
        </div>
        <div class="cust_data_body">
            <table>
                <colgroup>
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                </colgroup>
                <tr class="table_header">
                    <td colspan=1>Technician</td>
                    <td colspan=3>Customer</td>
                    <td colspan=1># Days Since Update</td>
                </tr>
                <?php
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr style="padding:10px;">
                        <td colspan=1>
                            <?php echo $row['PrefFullName'] ?>
                        </td>
                        <?php $clickID = $row['CallID'] ?>
                        <td colspan=3 onclick="toggleCustomer('<?php echo $clickID; ?>')">
                            <?php echo $row['CustomerName'] ?>
                            <div style="display:none;" id='<?php echo $row['CallID'] ?>'>

                                <table>

                                    <tr class="subtable_header">
                                        <td>Notes</td>
                                    </tr>
                                    <tr class="subtable_body">
                                        <td>
                                            <?php echo $row['Description'] ?>
                                        </td>

                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td colspan=1>
                            <?php echo $row['Days Since Last Update'] ?>
                        </td>

                    </tr>
                    <?php
                }
                ?>

            </table>
        </div>

    </div>
</body>