<?php 
session_start();
if (!isset($_REQUEST["moduleID"])){
    echo "error";
    die();
}
require dirname(__DIR__) . DIRECTORY_SEPARATOR . "connection.php";
require dirname(__DIR__) . DIRECTORY_SEPARATOR . "language.php";

$moduleID = $_REQUEST["moduleID"];
$row = $conn->query("SELECT * FROM dsgvo_training_modules WHERE id = $moduleID")->fetch_assoc();
$name = $row["name"];
?>
 <form method="POST">
 <div class="modal fade">
      <div class="modal-dialog modal-content modal-md">
        <div class="modal-header"><i class="fa fa-cubes"></i> <?php echo $lang['TRAINING_BUTTON_DESCRIPTIONS']['EDIT_SET'] ?> </div>
        <div class="modal-body">
            <label>Name*</label>
            <input type="text" class="form-control" name="name" placeholder="Name des Sets" required value="<?php echo $name ?>"/>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $lang['CANCEL']; ?></button>
          <button type="submit" class="btn btn-warning" name="editModule" value="<?php echo $moduleID; ?>"><?php echo $lang['EDIT'] ?></button>
        </div>
      </div>
    </div>
</form>