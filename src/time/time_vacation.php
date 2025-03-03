<?php include dirname(__DIR__) . '/header.php';?>
<?php require dirname(__DIR__) . "/misc/helpcenter.php"; ?>
<!-- BODY -->

<div class="page-header-fixed">
<div class="page-header">
  <h3><?php echo $lang['DAYS'].' '.$lang['AVAILABLE'].': '. $lang['VACATION']; ?></h3>
</div>

<?php
$curID = 0;
if(!empty($_GET['curID'])){
  $curID = intval($_GET['curID']);
}
if(isset($_POST['filterUserID'])){
  $curID = intval($_POST['filterUserID']);
}
$currentDate = substr(getCurrentTimestamp(),0,10);
if(isset($_POST['newDate'])){
  $currentDate = $_POST['newDate'];
}

$display_all = true;
if(!Permissions::has("TIMES.READ")){
  $display_all = false;
  $curID = $userID; //do not let him change this via url
}
?>
<form method="post" id="form1">
  <div class="row form-group">
    <?php if($display_all): ?>
    <div class="col-xs-3">
      <select id="filterUserID" name="filterUserID" class="js-example-basic-single btn-block">
        <option value='0'>Benutzer ... </option>
        <?php
        foreach($available_users as $i){
          if ($curID == $i) {
            echo "<option value='$i' selected>".$userID_toName[$i]."</option>";
          } else {
            echo "<option value='$i'>".$userID_toName[$i]."</option>";
          }
        }
        ?>
      </select>
    </div>
  <?php endif; ?>
    <div class="col-xs-3">
      <div class="input-group">
        <span class="input-group-addon"><?php echo $lang['TO']; ?></span>
        <input type="text" class="form-control datepicker" name="newDate" value= <?php echo $currentDate; ?> >
        <span class="input-group-btn">
          <button class="btn btn-warning" type="submit">Filter</button>
        </span>
      </div>
    </div>
  </div>
</form>
</div>
<div class="page-content-fixed-150">
<br>
<h4> <?php echo $lang['USED_DAYS'] ?></h4>
<table class="table table-hover">
  <thead>
    <th>Day of Week</th>
    <th>Date</th>
  </thead>
  <tbody>
    <?php
    $usedDays = 0;
    $result = $conn->query("SELECT logs.time FROM logs LEFT JOIN mixedInfoData m ON m.timestampID = logs.indexIM WHERE logs.userID = $curID
		AND (logs.status='1' OR (logs.status='5' AND m.status='1')) AND DATE(logs.time) <= DATE('$currentDate') ORDER BY logs.time DESC"); //5b7512e22f18e
    while($result && ($row = $result->fetch_assoc())){
      echo '<tr>';
      echo '<td>'.$lang['WEEKDAY_TOSTRING'][strtolower(date('D', strtotime($row['time'])))].'</td>';
      echo '<td>'.substr($row['time'],0,10).'</td>';
      echo '</tr>';
      $usedDays++;
    }
    echo "<tr style='font-weight:bold;'><td></td><td>$usedDays days</td></tr>";
    ?>
  </tbody>
</table>

<br><br>

<div class="row">
  <div class="col-sm-6 pull-right">
    <h5><?php echo $lang['ACCUMULATED_DAYS']; ?></h5>
    <ul>
      <?php
      //t returns the number of days in the month of a given date
      $gatheredDays = 0;
      $result_I = $conn->query("SELECT intervalData.*, UserData.exitDate FROM intervalData INNER JOIN UserData ON userID = UserData.id
        WHERE userID = $curID AND (DATE(endDate) <= DATE('$currentDate') OR endDate IS NULL)"); //select all intervals until this date
        while($result_I && ($iRow = $result_I->fetch_assoc())){ //foreach interval
          $i = substr($iRow['startDate'],0,10).' 05:00:00';
          if(!empty($iRow['endDate'])){ //current interval has endDate
            $j = $iRow['endDate'];
          } elseif($iRow['exitDate'] == '0000-00-00 00:00:00'){ //current interval and he HAS an exitDate, calculate until the exitDate.
            $j = $currentDate .' 05:00:00';
          } else {
            $j = $iRow['exitDate'];
          }
          $dayDiff = intval(timeDiff_Hours($i, $j) / 24);
          $gatheredDays += round($iRow['vacPerYear']/365 * $dayDiff); //accumulated vacation
          $i = substr($i, 0, 10);
          $j = substr($j, 0, 10);
          echo "<li>From $i - Until $j ($dayDiff days difference)<ul><li>".$iRow['vacPerYear']." / 365 * $dayDiff = ".round($iRow['vacPerYear']/365 * $dayDiff)." ".$lang['DAYS']."</li></ul></li>";
        }
        $correctionDays = 0;
        $result = $conn->query("SELECT hours, addOrSub FROM $correctionTable WHERE userID = $curID AND cType = 'vac' AND DATE(createdOn) <= DATE('$currentDate')");
        while($result && ($row = $result->fetch_assoc())){
          $correctionDays += intval($row['hours']) * intval($row['addOrSub']);
        }
        ?>
    </ul>
  </div>
  <div class="col-sm-6 pull-left">
    <h4> Saldo </h4>
    <ul>
      <li><?php echo $lang['USED_DAYS'].": $usedDays ". $lang['DAYS']; ?></li>
      <li><?php echo round($gatheredDays - $usedDays + $correctionDays) .' '. $lang['DAYS'] . ' (' . sprintf('%+d ', $correctionDays) . $lang['DAYS'] . ' ' . $lang['CORRECTION'].')'; ?></li>
    </ul>
  </div>
</div>
</div>

<script type="text/javascript">
$('.table').DataTable({
ordering: false,
language: {
  <?php echo $lang['DATATABLES_LANG_OPTIONS']; ?>
},
autoWidth: false
});
</script>
<!-- /BODY -->
<?php include dirname(__DIR__) . '/footer.php'; ?>
