<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div><h2>Import</h2>

<?php if ($URP_Full_Version != "Yes") { ?>
	<div class='ewd-urp-upgrade notice'>Upgrade to the premium version to use some of these features</div>
<?php } ?>
<h4>WooCommerce Import</h4>
<p>Import reviews from WooCommerce using the button below.</p>
<a href='admin.php?page=EWD-URP-Options&DisplayPage=WooCommerceImport&Action=EWD_URP_WooCommerceImport'>
<button class="button button-primary">Import</button>
</a>

<h4>Import Reviews from a Spreadsheet</h4>
<form method="post" action="admin.php?page=EWD-URP-Options&DisplayPage=WooCommerceImport&Action=EWD_URP_ImportReviewsFromSpreadsheet" enctype="multipart/form-data">
<?php wp_nonce_field('URP_Admin_Action', 'URP_Admin_Action'); ?>
<div class="form-field form-required">
		<label for="Reviews_Spreadsheet"><?php _e("Spreadsheet Containing Reviews", 'ultimate-reviews') ?></label><br />
		<input name="Reviews_Spreadsheet" id="Reviews_Spreadsheet" type="file" value=""/>
</div>


<p class="submit"><input type="submit" name="Import_Submit" id="submit" class="button button-primary" value="Import Spreadsheet Reviews"  <?php if ($URP_Full_Version != "Yes") {echo "disabled";} ?>/></p></form>

</div>