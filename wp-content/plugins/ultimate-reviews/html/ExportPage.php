<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div><h2>Export</h2>

<?php if ($URP_Full_Version != "Yes") { ?>
	<div class='ewd-urp-upgrade notice'>Upgrade to the premium version to use these features</div>
<?php } ?>

<form method="post" action="admin.php?page=EWD-URP-Options&DisplayPage=Export&Action=EWD_URP_Export_To_Excel">
<input type='hidden' name='Format_Type' value='CSV' />
<table class="form-table">
</table>


<p class="submit"><input type="submit" name="Export_Submit" id="submit" class="button button-primary" value="Export to CSV" <?php if ($URP_Full_Version != "Yes") {echo "disabled";} ?> /></p></form>

<form method="post" action="admin.php?page=EWD-URP-Options&DisplayPage=Export&Action=EWD_URP_Export_To_Excel">
<input type='hidden' name='Format_Type' value='XLS' />
<table class="form-table">
</table>


<p class="submit"><input type="submit" name="Export_Submit" id="submit" class="button button-primary" value="Export to Spreadsheet" <?php if ($URP_Full_Version != "Yes") {echo "disabled";} ?> /></p></form>

</div>