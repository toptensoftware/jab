<?php
jabRequire("forms");
?>
<h1>Import</h1>

<?php // ---------------- Import Form----------------- ?>
<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REQUEST_URI_CLEAN"]?>" enctype="multipart/form-data"> 
 
<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
<?php jabHtmlFileUpload("importFile") ?>
<?php jabHtmlCheckBox("Drop all old content", "dropoldcontent", True) ?>
<div class="clearer"></div>
<?php jabHtmlSubmitButton("Upload", "upload") ?>
</form> 
