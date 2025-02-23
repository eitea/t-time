<?php
/**
* company => id
* client  => id
* project  => id
* user => id
* users => [id1, id2, ...]
* bookings => [charged, break, drive]
* logs => [activity, hideAll]
* date => [fromDate, toDate] || [month]
* procedures => [transitions[id], status, hideAll]
* acceptance => status
* requestType => type
* tasks => status
* taskview => 'default'
* priority => int
* employees => array<string>()
**/

if(isset($filterings['savePage']) && !empty($_SESSION['filterings']['savePage']) && $_SESSION['filterings']['savePage'] != $filterings['savePage']){
    $_SESSION['filterings'] = array();
}
if(isset($_POST['set_filter_apply'])){ //NONE of these if's may have an else! (THINK)
    if(isset($_POST['searchCompany'])){
        $filterings['company'] = intval($_POST['searchCompany']);
    }
    if(isset($_POST['searchClient'])){
        $filterings['client'] = intval($_POST['searchClient']);
    }
    if(isset($_POST['searchSupplier'])){
        $filterings['supplier'] = intval($_POST['searchSupplier']);
    }
    if(isset($_POST['searchProject'])){
        $filterings['project'] = intval($_POST['searchProject']);
    }
    if(isset($_POST['searchUser'])){
        $filterings['user'] = intval($_POST['searchUser']);
    }
    if(isset($_POST['searchUsers'])){
        $filterings['users'] = array_map("intval", $_POST['searchUsers']);
    }
    if(isset($_POST['searchCharged'])){
        $filterings['bookings'][0] = intval($_POST['searchCharged']);
    }
    if(isset($filterings['bookings'][1])){
        if(isset($_POST['searchBreaks'])){
            $filterings['bookings'][1] = 'checked';
        } else {
            $filterings['bookings'][1] = '';
        }
    }
    if(isset($filterings['bookings'][2])){
        if(isset($_POST['searchDrives'])){
            $filterings['bookings'][2] = 'checked';
        } else {
            $filterings['bookings'][2] = '';
        }
    }
    if(isset($_POST['searchActivity'])){
        $filterings['logs'][0] = intval($_POST['searchActivity']);
    }
    if(isset($filterings['logs'][1])){
        if(isset($_POST['searchAllTimestamps'])){
            $filterings['logs'][1] = 'checked';
        } else {
            $filterings['logs'][1] = '';
        }
    }

    if(!empty($_POST['searchDateFrom'])){
        $filterings['date'][0] = test_input($_POST['searchDateFrom']);
    }
    if(!empty($_POST['searchDateTo'])){
        $filterings['date'][1] = test_input($_POST['searchDateTo']);
        if(strtotime($filterings['date'][0]) > strtotime($filterings['date'][1])){
            $filterings['date'][1] = $filterings['date'][0];
        }
    }

    if(isset($_POST['searchTransitions'])){
        $filterings['procedures'][0] = array_map("test_input", $_POST['searchTransitions']);
    }
    if(isset($_POST['searchProcessStatus'])){
        $filterings['procedures'][1] = intval($_POST['searchProcessStatus']);
    }
    if(isset($filterings['procedures'][2])){
        if(isset($_POST['searchAllProcesses'])){
            $filterings['procedures'][2] = 'checked';
        } else {
            $filterings['procedures'][2] = '';
        }
    }
    if(isset($_POST['searchRequestType'])){
        $filterings['requestType'] = test_input($_POST['searchRequestType']);
    }
    if(isset($_POST['searchAcceptance'])){
        $filterings['acceptance'] = intval($_POST['searchAcceptance']);
    }
    if(isset($_POST['searchTask'])){
        $filterings['tasks'] = test_input($_POST['searchTask']);
    }
	if(isset($filterings['taskview'])){
		if(isset($_POST['searchTaskView'])){
	        $filterings['taskview'] = 'default';
	    } else {
            $filterings['taskview'] = 'all';
        }
    }

    if(isset($_POST['searchPriority'])){
        $filterings['priority'] = intval($_POST['searchPriority']);
    }
    if(isset($_POST['searchEmployees'])){
        $filterings['employees'] = $_POST['searchEmployees'];
    }
    if(isset($filterings['savePage'])){
        $_SESSION['filterings'] = $filterings;
    } else {
        $_SESSION['filterings'] = array();
    }
}

//read saved filters
if(isset($filterings['savePage']) && !empty($_SESSION['filterings']['savePage']) && $_SESSION['filterings']['savePage'] == $filterings['savePage']){
  $filterings = $_SESSION['filterings'];
}

$scale = 0;
if(isset($filterings['date']) || isset($filterings['logs'])){$scale++;}
if(isset($filterings['user']) || isset($filterings['users'])){$scale++;}
if(isset($filterings['company'])){$scale++;}
if(isset($filterings['procedures'])){$scale++;}
if(isset($filterings['tasks'])){$scale++;}
$styles = array(20, 90);
if($scale > 1){ //2 columns
  $styles = array(40, 45);
}
if($scale > 2){ //3 columns
  $styles = array(60, 32);
}
?>
<style>
.filter_column{
  width:<?php echo $styles[1]; ?>%;
  display:inline;
  float:left;
  padding-left:20px;
}
</style>
<div id="filterings_dropdown" class="dropdown" style="display:inline">
  <button id="set_filter_search" type="button" class="btn btn-default" data-toggle="dropdown" title="<?php echo $lang['SEARCH_OPTIONS']; ?>"><i class="fa fa-search"></i></button>
  <div class="dropdown-menu" style="width:<?php echo $styles[0]; ?>vw">
    <form method="POST">
      <div class="container-fluid"><br>
        <div class="filter_column">
          <?php
          if(isset($filterings['company'])){
              $result_fc = mysqli_query($conn, "SELECT * FROM companyData WHERE id IN (".implode(', ', $available_companies).")");
              if($result_fc && $result_fc->num_rows > 1){
                  echo '<label>'.$lang['COMPANY'].'</label>';
                  if(isset($filterings['client'])){
                      echo '<select class="js-example-basic-single" name="searchCompany" onchange="set_filter.showClients(this.value, \''.$filterings['client'].'\');" >';
                  } elseif(isset($filterings['supplier'])){
                      echo '<select class="js-example-basic-single" name="searchCompany" onchange="set_filter.showSupplier(this.value, \''.$filterings['supplier'].'\');" >';
                  } else {
                      echo '<select class="js-example-basic-single" name="searchCompany">';
                  }
                  echo '<option value="0">...</option>';
                  while($result_fc && ($row_fc = $result_fc->fetch_assoc())){
                      $checked = '';
                      if($filterings['company'] == $row_fc['id']) {
                          $checked = 'selected';
                      }
                      echo "<option $checked value='".$row_fc['id']."' >".$row_fc['name']."</option>";
                  }
                  echo '</select><br><br>';
              } else {
                  $filterings['company'] == $available_companies[0];
              }
          }
          if(isset($filterings['supplier'])){
            echo '<label>'.$lang['SUPPLIER'].'</label>';
            echo '<select id="searchSupplierHint" class="js-example-basic-single" name="searchSupplier">';
            $result_fc = mysqli_query($conn, "SELECT * FROM clientData WHERE isSupplier = 'TRUE' AND companyID IN (".implode(', ', $available_companies).")");
            echo '<option value="0">...</option>';
            while($result_fc && ($row_fc = $result_fc->fetch_assoc())){
              $checked = '';
              if($filterings['supplier'] == $row_fc['id']) {
                $checked = 'selected';
              }
              echo "<option $checked value='".$row_fc['id']."' >".$row_fc['name']."</option>";
            }
            echo '</select><br><br>';
          }
          if(isset($filterings['client'])){
            echo '<label>'.$lang['CLIENT'].'</label>';
            if(isset($filterings['project'])){
              echo '<select id="searchClientHint" class="js-example-basic-single" name="searchClient" onchange="set_filter.showProjects(this.value, \''.$filterings['project'].'\');" >';
            } else {
              echo '<select id="searchClientHint" class="js-example-basic-single" name="searchClient">';
            }
            $result_fc = mysqli_query($conn, "SELECT * FROM clientData WHERE isSupplier = 'FALSE' AND companyID IN (".implode(', ', $available_companies).")");
            echo '<option value="0">...</option>';
            while($result_fc && ($row_fc = $result_fc->fetch_assoc())){
              $checked = '';
              if($filterings['client'] == $row_fc['id']) {
                $checked = 'selected';
              }
              echo "<option $checked value='".$row_fc['id']."' >".$row_fc['name']."</option>";
            }
            echo '</select><br><br>';
          }
          if(isset($filterings['project'])): ?>
            <label for="searchProjectHint"><?php echo $lang['PROJECT']; ?></label>
            <select id="searchProjectHint" class="js-example-basic-single" name="searchProject" >
            </select>
          <?php endif; ?>
        </div>

    <div class="filter_column">
          <?php if(isset($filterings['user'])){
            echo '<label>'.$lang['USERS'].'</label>';
            echo '<select class="js-example-basic-single" name="searchUser" >';
            echo '<option value="0">...</option>';
            $result_fc = mysqli_query($conn, "SELECT id, firstname, lastname FROM UserData WHERE id IN (".implode(', ', $available_users).")");
            while($result_fc && ($row_fc = $result_fc->fetch_assoc())){
              $checked = '';
              if($filterings['user'] == $row_fc['id']) { $checked = 'selected'; }
              echo "<option $checked value='".$row_fc['id']."' >".$row_fc['firstname'].' '.$row_fc['lastname']."</option>";
            }
            echo '</select><br><br>';
          } elseif(isset($filterings['users'])){
            echo '<label>'.$lang['USERS'].'</label>';
            echo '<select class="js-example-basic-single" name="searchUsers[]" multiple="multiple">';
            $result_fc = mysqli_query($conn, "SELECT id, firstname, lastname FROM UserData WHERE id IN (".implode(', ', $available_users).")");
            while($result_fc && ($row_fc = $result_fc->fetch_assoc())){
              $selected = '';
              if(in_array($row_fc['id'], $filterings['users'])) { $selected = 'selected'; }
              echo "<option $selected value='".$row_fc['id']."'>".$row_fc['firstname'].' '.$row_fc['lastname'].'</option>';
            }
            echo '</select><br><br>';
          }
          ?>
          <?php if(isset($filterings['bookings'])): ?>
            <label><?php echo $lang['BOOKINGS']; ?></label>
            <select name="searchCharged" class="js-example-basic-single">
              <option value='0' <?php if($filterings['bookings'][0] == '0'){echo 'selected';}?> >...</option>
              <option value='1' <?php if($filterings['bookings'][0] == '1'){echo 'selected';}?> ><?php echo $lang['NOT_CHARGED']; ?></option>
              <option value='2' <?php if($filterings['bookings'][0] == '2'){echo 'selected';}?> ><?php echo $lang['CHARGED']; ?></option>
            </select>
            <?php if($filterings['bookings'][0] != 1){ echo '<small>*'.$lang['INFO_CHARGED'].'</small>'; } ?>
            <div class="checkbox">
              <label><input type="checkbox" name="searchBreaks" <?php echo $filterings['bookings'][1]; ?> /><?php echo $lang['BREAKS']; ?></label>
              <label><input type="checkbox" name="searchDrives" <?php echo $filterings['bookings'][2]; ?> /><?php echo $lang['DRIVES']; ?></label>
            </div>
          <?php endif; ?>
          <?php if(isset($filterings['requestType'])): ?>
            <label><?php echo $lang['REQUEST_TYPE']; ?></label>
            <select name="searchRequestType" class="js-example-basic-single">
              <option value="0"><?php echo $lang['DISPLAY_ALL']; ?></option>
              <?php
              foreach($lang['REQUEST_TOSTRING'] as $key => $value){
                $selected = '';
                if($key == $filterings['requestType']) $selected = 'selected';
                echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
            }
              ?>
            </select>
            <br><br>
          <?php endif; ?>
          <?php if(isset($filterings['acceptance'])): ?>
            <label>Status</label>
            <select name="searchAcceptance" class="js-example-basic-single">
              <option value="-1"><?php echo $lang['DISPLAY_ALL']; ?></option>
              <option value="0" <?php if($filterings['acceptance'] == '0'){echo 'selected';} ?>><?php echo $lang['REQUESTSTATUS_TOSTRING'][0]; ?></option>
              <option value="1" <?php if($filterings['acceptance'] == '1'){echo 'selected';} ?>><?php echo $lang['REQUESTSTATUS_TOSTRING'][1]; ?></option>
              <option value="2" <?php if($filterings['acceptance'] == '2'){echo 'selected';} ?>><?php echo $lang['REQUESTSTATUS_TOSTRING'][2]; ?></option>
            </select>
          <?php endif; ?>
          <?php if(isset($filterings['tasks'])): ?>
              <label><?php echo $lang['DYNAMIC_PROJECTS']; ?></label>
              <select name="searchTask" class="js-example-basic-single">
                  <option value="0"><?php echo $lang['DISPLAY_ALL']; ?></option>
                  <option value="DEACTIVATED" <?php if($filterings['tasks'] == "DEACTIVATED") echo 'selected'; ?>>Deaktiviert</option>
                  <option value="ACTIVE" <?php if($filterings['tasks'] == 'ACTIVE') echo 'selected'; ?>>Aktiv</option>
                  <option value="ACTIVE_PLANNED" <?php if($filterings['tasks'] == 'ACTIVE_PLANNED') echo 'selected'; ?>>Aktiv (Geplant)</option>
                  <option value="DRAFT" <?php if($filterings['tasks'] == 'DRAFT') echo 'selected'; ?>>Entwurf</option>
                  <option value="REVIEW_1" <?php if($filterings['tasks'] == 'REVIEW_1') echo 'selected'; ?>>Review (Aktiv)</option>
                  <option value="REVIEW_2" <?php if($filterings['tasks'] == 'REVIEW_2') echo 'selected'; ?>>Review (Inaktiv)</option>
                  <option value="COMPLETED" <?php if($filterings['tasks'] == 'COMPLETED') echo 'selected'; ?>>Abgeschlossen</option>
              </select>
			  <?php
			  if(isset($filterings['taskview'])){
				  $selected = $filterings['taskview'] == 'default' ? 'checked' : '';
				  echo '<input type="checkbox" name="searchTaskView" ',$selected,' value="default"> Übernommene Tasks verstecken';
			  }
			  ?>
			  <br><br>
		  <?php endif; ?>
          <?php if(isset($filterings['priority'])): ?>
              <label><?php echo $lang['DYNAMIC_PROJECTS_PROJECT_PRIORITY']; ?></label>
              <select name="searchPriority" class="js-example-basic-single">
                  <option value="0"><?php echo $lang['DISPLAY_ALL']; ?></option>
                  <option value="1" <?php if($filterings['priority'] == 1) echo 'selected'; ?>><?php echo $lang['PRIORITY_TOSTRING'][1] ?></option>
                  <option value="2" <?php if($filterings['priority'] == 2) echo 'selected'; ?>><?php echo $lang['PRIORITY_TOSTRING'][2] ?></option>
                  <option value="3" <?php if($filterings['priority'] == 3) echo 'selected'; ?>><?php echo $lang['PRIORITY_TOSTRING'][3] ?></option>
                  <option value="4" <?php if($filterings['priority'] == 4) echo 'selected'; ?>><?php echo $lang['PRIORITY_TOSTRING'][4] ?></option>
                  <option value="5" <?php if($filterings['priority'] == 5) echo 'selected'; ?>><?php echo $lang['PRIORITY_TOSTRING'][5] ?></option>
              </select>
              <br><br>
          <?php endif; ?>
          <?php if(isset($filterings['employees'])): ?>
              <label><?php echo $lang["EMPLOYEE"]; ?>/ Team</label>
              <select class="select2-team-icons js-example-basic-single " name="searchEmployees[]" multiple="multiple">
                  <?php
                  $modal_options = '';
                  $result = $conn->query("SELECT id, firstname, lastname FROM UserData WHERE id IN (".implode(', ', $available_users).")");
                  while ($row = $result->fetch_assoc()){ $modal_options .= '<option value="'.$row['id'].'" data-icon="user">'.$row['firstname'] .' '. $row['lastname'].'</option>'; }
                  $result = str_replace('<option value="', '<option value="user;', $modal_options); //append 'user;' before every value
                  for($i = 0; $i < count($filterings['employees']); $i++){
                      $result = str_replace('<option value="'.$filterings['employees'][$i].'" ', '<option selected value="'.$filterings['employees'][$i].'" ', $result);
                  }
                  echo $result;
                  $selected = '';
				  if(!empty($available_teams) && !Permissions::has("TASKS.ADMIN")){
					  $result = $conn->query("SELECT id, name FROM teamData WHERE id IN (".implode(', ', $available_teams).") ");
				  } else {
					  $result = $conn->query("SELECT id, name FROM teamData");
				  }
                  while ($row = $result->fetch_assoc()) {
                      $selected .= '<option value="team;'.$row['id'].'" data-icon="group" >'.$row['name'].'</option>';
                  }
                  for($i = 0; $i < count($filterings['employees']); $i++){
                      $selected = str_replace('<option value="'.$filterings['employees'][$i].'" ', '<option selected value="'.$filterings['employees'][$i].'" ', $selected);
                  }
                  echo $selected;
                  ?>
              </select>
              <input type="hidden" name="searchEmployees[]">
          <?php endif;?>
    </div>

        <?php if(isset($filterings['date']) || isset($filterings['logs'])): ?>
          <div class="filter_column">
            <?php if(isset($filterings['date'][1])): ?>
              <label><?php echo $lang['FROM']; ?></label>
              <div class="input-group">
                <input type="text" maxlength="10" id="searchDateFrom" class="form-control datepicker" name="searchDateFrom" value="<?php echo $filterings['date'][0]; ?>" />
                <span class="input-group-btn">
                  <button id="putDate" type="button" class="btn btn-default" title="Bis Monatsende"><i class="fa fa-arrow-down"></i></button>
                </span>
              </div>
              <br><label><?php echo $lang['TO']; ?></label>
              <div class="input-group">
                <input type="text" maxlength="10" id="searchDateTo" class="form-control datepicker" name="searchDateTo" value="<?php echo $filterings['date'][1]; ?>" />
                <span class="input-group-btn">
                  <button id="putDateUp" type="button" class="btn btn-default" title="Ab Monatsanfang"><i class="fa fa-arrow-up"></i></button>
                </span>
              </div><br>
            <?php else: ?>
              <label><?php echo $lang['DATE']; ?></label>
              <input type="text" maxlength="7" class="form-control monthpicker" name="searchDateFrom" value="<?php echo $filterings['date'][0]; ?>" /><br>
            <?php endif; ?>

            <?php if(isset($filterings['logs'])): ?>
            <label><?php echo $lang['ACTIVITY']; ?></label>
              <select name="searchActivity" class="js-example-basic-single">
                <option value="0"><?php echo $lang['DISPLAY_ALL']; ?></option>
                <option value="1" <?php if($filterings['logs'][0] == '1'){echo 'selected';}?>><?php echo $lang['VACATION']; ?></option>
                <option value="2" <?php if($filterings['logs'][0] == '2'){echo 'selected';}?>><?php echo $lang['SPECIAL_LEAVE']; ?></option>
                <option value="4" <?php if($filterings['logs'][0] == '4'){echo 'selected';}?>><?php echo $lang['VOCATIONAL_SCHOOL']; ?></option>
                <option value="6" <?php if($filterings['logs'][0] == '6'){echo 'selected';}?>><?php echo $lang['COMPENSATORY_TIME']; ?></option>
              </select>
              <div class="checkbox"><label><input type="checkbox" <?php echo $filterings['logs'][1]; ?> name="searchAllTimestamps"/><?php echo $lang['HIDE_ZEROE_VALUE']; ?></label></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if(isset($filterings['procedures'])): ?>
          <div class="filter_column">
            <label><?php echo $lang['PROCESSES']; ?></label>
            <select class="js-example-basic-single" name="searchTransitions[]" multiple="multiple">
              <?php
              foreach($transitions as $i){
                $selected = '';
                if(in_array($i, $filterings['procedures'][0])){
                  $selected = 'selected';
                }
                echo "<option $selected value='$i'>".$lang['PROPOSAL_TOSTRING'][$i].'</option>';
              }
              ?>
            </select>
            <br><br>
            <label><?php echo $lang['PROCESS_STATUS']; ?></label>
            <select class="js-example-basic-single"  name="searchProcessStatus">
              <option value="-1"><?php echo $lang['DISPLAY_ALL']; ?></option>
              <?php
              for($i=0; $i < 3; $i++){
                $selected = '';
                if($i == $filterings['procedures'][1]){
                  $selected = 'selected';
                }
                echo '<option value="'.$i.'" '.$selected.' >'.$lang['OFFERSTATUS_TOSTRING'][$i].'</option>';
              }
              ?>
            </select>
            <div class="checkbox"><label><input type="checkbox" <?php echo $filterings['procedures'][2]; ?> name="searchAllProcesses"/><?php echo $lang['HIDE_PROCESSED_DATA']; ?></label></div>
          </div>
        <?php endif; ?>
        <div class="container-fluid text-right">
          <div class="col-xs-12"><br><button type="submit" class="btn btn-warning" name="set_filter_apply"><?php echo $lang['APPLY']; ?></button><br><br></div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
$("#putDate").click( function() {
  var d = new Date( $("#searchDateFrom").val());
  d = new Date(d.getFullYear(), d.getMonth()+1, 0);
  d = new Date(d.getTime() - (d.getTimezoneOffset() * 60000));
  $("#searchDateTo").val(d.toISOString().substring(0, 10));
});
$("#putDateUp").click( function() {
  var d = new Date( $("#searchDateTo").val());
  d = new Date(d.getFullYear(), d.getMonth(), 1);
  d = new Date(d.getTime() - (d.getTimezoneOffset() * 60000));
  $("#searchDateFrom").val(d.toISOString().substring(0, 10));
});
$('#filterings_dropdown .dropdown-menu').on({
  "click":function(e){
    e.stopPropagation();
  }
});
//namespace declaration
(function( set_filter, $, undefined ) {
  //public method
  set_filter.showProjects = function(client, project){
    $.ajax({
      url:'ajaxQuery/AJAX_getProjects.php',
      data:{clientID:client, projectID:project},
      type: 'get',
      success : function(resp){
        $("#searchProjectHint").html(resp);
      },
      error : function(resp){}
    });
  };
  set_filter.showClients = function(company, client){
    $.ajax({
      url:'ajaxQuery/AJAX_getClient.php',
      data:{companyID:company, clientID:client},
      type: 'get',
      success : function(resp){
        $("#searchClientHint").html(resp);
      },
      error : function(resp){}
    });
  };
  set_filter.showSupplier = function(company, supplier){
    $.ajax({
      url:'ajaxQuery/AJAX_getSupplier.php',
      data:{companyID:company, supplierID:supplier},
      type: 'get',
      success : function(resp){
        $("#searchSupplierHint").html(resp);
      },
      error : function(resp){}
    });
  };
  set_filter.changeValue = function(cVal, id, val){
    if(cVal == ''){
      document.getElementById(id).selectedIndex = val;
      $('#' + id).val(val).change();
    }
  };
}( window.set_filter = window.set_filter || {}, jQuery ));
</script>

<?php
echo '<script>';
if(!empty($filterings['company'])){
  if(isset($filterings['client'])){
    $nextID = $filterings['client'];
    echo 'set_filter.showClients('.$filterings['company'].', '.$nextID.');';
  }
  if(isset($filterings['supplier'])){
    $nextID = $filterings['supplier'];
    echo 'set_filter.showSupplier('.$filterings['company'].', '.$nextID.');';
  }
}
if(!empty($filterings['client'])){
  if(!empty($filterings['project'])){
    $nextID = $filterings['project'];
  } else {
    $nextID = 0;
  }
  echo 'set_filter.showProjects('.$filterings['client'].', '.$nextID.');';
}
echo '</script>';
?>
