<!DOCTYPE html>
<html>

<head>
    <title>InStore Metrics</title>
    <meta charset="UTF-8">
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
    $connectionInfo = array(
        "Database" => "CoIST_LIVE",
        "UID" => "cpuckett",
        "PWD" => "JupiterSkies13!",
        "TrustServerCertificate" => True,
        "Driver" => 'ODBC Driver 18 for SQL Server'
    );
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    #/Will spit out any error messages that may occur or it will not print anything
    if ($conn) {

    } else {
        echo "Connection could not be established.<br />";
        die(print_r(sqlsrv_errors(), true));
    }
    $tsql = "SELECT CustomerID,CustomerName, (SELECT COUNT(*) FROM dbo.SCCalls WHERE dbo.ARCustomers.CustomerID = CustomerID AND Status IN ('P','H') AND TechnicianID NOT IN ('26','28','39')) AS Calls  FROM dbo.ARCustomers 
WHERE EXISTS (SELECT CustomerID FROM dbo.SCCalls WHERE dbo.ARCustomers.CustomerID = CustomerID AND Status  IN ('P','H') AND TechnicianID NOT IN ('26','28','39'))
ORDER BY Calls DESC";
    $stmt = sqlsrv_query($conn, $tsql);
    ?>
    <div id="container">
        <div class="menu">
            <a href="reports">Home</a>
            <a href="tech_report">Technician Metrics</a>
            <a id="active" href="cust_report">Customer Statistics</a>
        </div>
        <div class="submenu">
            <a id="active" href="cust_report">Customer Open Calls</a>
            <a href="cust_properties">Customer Properties</a>
            <a href="tech_report_open"></a>
        </div>
        <div class="page_title">
            <h1>Customer Open Calls</h1>
        </div>
        <div class="cust_data_body">
            <table>
                <colgroup>
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                    <col width="5%">
                </colgroup>
                <tr class="table_header">
                    <td colspan=18>Customer Name</td>
                    <td colspan=2 style="text-align: center">Open Calls</td>
                </tr>
                <?php
                ##This while loop adds the names of the customers as well as their call count
                #Addtionally, a nested while loop is precreating the tickets per customer so when expanded they are there
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <!-- This clickID variable is allowing divs to be tagged uniquely so that the toggleCustomer fucntion is per customer instead of all -->
                        <?php $clickID = $row['CustomerID'] ?>
                        <td colspan=18 style=" user-select: none;" onclick="toggleCustomer('<?php echo $clickID; ?>')">
                            <a class="hover_name">
                                <?php echo $row['CustomerName'] ?>
                            </a>
                            <div style="display:none;" id="<?php echo $row['CustomerID'] ?>">
                                <?php
                                $custCallQ = "SELECT  Date, Description, Notes, (SELECT PrefFullName FROM dbo.SHAgents WHERE dbo.SCCalls.TechnicianID = dbo.SHAgents.AgentID)  AS TechnicanName FROM dbo.SCCalls WHERE TechnicianID != '28' AND Status = 'P' AND CustomerID =" . $row['CustomerID'] . "";
                                $custCallT = sqlsrv_query($conn, $custCallQ);
                                ?>
                                <!-- This is the beginning of the embedded table showing the customer tickets -->
                                <table style="margin-top:10px;">

                                    <tr class="subtable_header">
                                        <td>Date</td>
                                        <td>Technician</td>
                                        <td>Description</td>
                                        <td>Notes</td>
                                    </tr>
                                    <?php
                                    ##Filling the data from SQL
                                    while ($cust = sqlsrv_fetch_array($custCallT, SQLSRV_FETCH_ASSOC)) {
                                        ?>
                                        <tr class="subtable_body">
                                            <td>
                                                <?php echo $cust['Date']->format('m/d/Y') ?>
                                            </td>
                                            <td>
                                                <?php echo $cust['TechnicanName'] ?>
                                            </td>
                                            <td>
                                                <?php echo $cust['Description'] ?>
                                            </td>
                                            <td>
                                                <?php echo $cust['Notes'] ?>
                                            </td>

                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </td>
                        <td colspan=2 style="text-align: center">
                            <?php echo $row['Calls'] ?>
                        </td>

                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>