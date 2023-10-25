<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, inital-scale=1.0">
  <link rel="stylesheet" href="stylesheets/bootstrap.min.css">
</head>

<body class="bg-dark">
  <?php
  #Name of Server
  $serverName = "localhost";
  #It seems like ODBC Driver 18 works for our current installations, however, the TrustServerCertificate must be added as true or the connection fails
  $connectionInfo = array("Database" => "FuelReporting", "UID" => "report", "PWD" => "Instore123", "TrustServerCertificate" => True, "Driver" => 'ODBC Driver 18 for SQL Server');
  $conn = sqlsrv_connect($serverName, $connectionInfo);

  #/Will spit out any error messages that may occur or it will not print anything
  if ($conn) {

  } else {
    echo "Connection could not be established.<br />";
    die(print_r(sqlsrv_errors(), true));
  }



  $dateid = (isset($_POST['date-id'])) ? $_POST['date-id'] : date('Y/m/d');
  ?>

  <div class="container">
    <div class="row">
      <div class="col">
        <div class="card mt-5">

          <div class="card-header">

            <h2 class="display-6">Cascade Fuel Reporting:
              <?php echo $dateid ?>
            </h2>
            <form id="myform" method="post">
              <select name="date-id" onchange="change()">
                <?php
                $date = date("Y/m/d");
                $date_tsql = "select distinct DT from dbo.Fuel_Volume";
                $date_stmt = sqlsrv_query($conn, $date_tsql);
                ?>
                <option value=""> ---Choose a Date ---</option>
                <?php
                while ($row = sqlsrv_fetch_array($date_stmt, SQLSRV_FETCH_ASSOC)) {
                  ?>
                  <option value="<?php echo date_format($row['DT'], 'Y/m/d');
                  $date = $row['DT']; ?>"><?php echo date_format($row['DT'], 'Y/m/d') ?></option>;
                  <?php
                }
                #$tsql = "select * from dbo.Fuel_Volume WHERE DT = '2023-06-27 00:00:00.000'";
                #tsql = "select * from dbo.Fuel_Volume WHERE DT = DATEADD(day, -10, CAST(CAST(GETDATE() AS DATE) AS datetime2))";
                

                ?>
              </select>
            </form>
            <script>
              function change() {
                document.getElementById("myform").submit();
              }
            </script>
            <?php

            $tsql = "select * from dbo.Fuel_Volume WHERE DT = DATEADD(day, 0, CAST(CAST('" . $dateid . "' AS DATE) AS datetime2))";
            $stmt = sqlsrv_query($conn, $tsql);

            ?>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <tr class="bg-dark text-white">
                <td> Pump ID </td>
                <td> Grade </td>
                <td> Meter Open </td>
                <td> Meter Close </td>
                <td> Meter Total </td>
                <td> Volume Sold </td>
                <td> Variance </td>
              </tr>
              <tr>
                <?php
                $u_vol = 0;
                $d_vol = 0;
                $s_vol = 0;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                  ?>
                  <td>
                    <?php echo $row['pump_ID'] ?>
                  </td>

                  <td>
                    <?php
                    # Converts the grade ID to the name of the fuel
                    if ($row['grade_ID'] == 1) {
                      echo 'UNLEADED';
                      $u_vol += $row['vol_sold'];
                    } elseif ($row['grade_ID'] == 3) {
                      echo 'DIESEL';
                      $d_vol += $row['vol_sold'];
                    } elseif ($row['grade_ID'] == 2) {
                      echo 'SUPER';
                      $s_vol += $row['vol_sold'];
                    }
                    ?>
                  </td>
                  <td>
                    <?php echo $row['meter_open'] ?>
                  </td>
                  <td>
                    <?php echo $row['meter_close'] ?>
                  </td>
                  <td>
                    <?php echo $row['meter_total'] ?>
                  </td>
                  <td>
                    <?php echo $row['vol_sold'] ?>
                  </td>
                  <td>
                    <?php echo $row['variance'] ?>
                  </td>
                </tr>


                <?php
                }
                ?>
              <tr class="bg-dark">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td></td>
                <td>UNLEADED</td>
                <td>
                  <?php echo $u_vol ?>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>DIESEL</td>
                <td>
                  <?php echo $d_vol ?>
                </td>
              </tr>
              <tr>
                <td></td>
                <td>SUPER</td>
                <td>
                  <?php echo $s_vol ?>
                </td>
              </tr>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>




  <?php
  sqlsrv_close($conn)
    ?>
</body>

</html>