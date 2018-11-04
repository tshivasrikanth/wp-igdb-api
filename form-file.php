<div class="rbbwrap">
<h1>IGDB API</h1>
<form method="POST">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="igdbsearchkey">SEARCH KEY</label>
            </th>
            <td>
                <input type="text" class="regular-text" name="igdbsearchkey" id="searchkey" value="<?php echo $searchKey; ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php wp_nonce_field( 'IgdbNonce' ); ?>
                <input type="submit" value="Submit" class="button button-primary button-large">
            </th>
            <td></td>
        </tr>   
</table>
<div>
<?php if(strlen($apikey) == 0){?>
<div class="notice notice-success is-dismissible">
    <p>IGDB API KEY is not configured <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_settings_rbb">click here</a></p>
</div>
<?php } ?>
<?php if(count($results)){?>
<div class="notice notice-success is-dismissible">
    <p><?php echo $results; ?></p>
</div>
<?php } ?>
	<h4 class="fl">Available Search Keys</h4><h4 class="fr"><a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb">Goto Products List page</a></h4>
    <table class="cb widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th width="50" class="manage-column" scope="col">Delete</th>
				<th class="manage-column" scope="col">Search Key</th>
				<th width="150" class="manage-column" scope="col">Add to Database</th>
				<th width="200" class="manage-column" scope="col">Timestamp</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($searchKeyResults as $searchVal){ ?>
			<tr class="alternate">
				<td><input type="checkbox" class="regular-text" name="delete_list[]" value="<?php echo $searchVal->searchKey; ?>" ></td>
				<td><b><?php echo $searchVal->searchKey; ?></b></td>
				<?php if($searchVal->is_processed){ ?>
					<td><input type="button" value="Processed" class="button button-primary button-large button-disabled"></td>
				<?php } else {?>
					<td>
					<input title="You can skip this step, it will be processed in next cron schedule automatically" id="search_key_bt_<?php echo $searchVal->id; ?>" type="button" value="Process" class="processSearchValue button button-primary button-large">
					<input id="search_key_id_<?php echo $searchVal->id; ?>" type="hidden" value="<?php echo $searchVal->id; ?>">
					</td>
				<?php } ?>
				<td><?php echo date("F d, Y h:i:s A", $searchVal->timestamp); ?></td>
			</tr>
		<?php } ?>
		</tbody>    
	</table>
	</div>
</form>
</div>