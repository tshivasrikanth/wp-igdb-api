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
                <?php wp_nonce_field( 'EbayNonce' ); ?>
                <input type="submit" value="Submit" class="button button-primary button-large">
            </th>
            <td></td>
        </tr>   
</table>
<div>
<?php if(count($results)){?>
<div class="notice notice-success is-dismissible">
    <p><?php echo $results; ?></p>
</div>
<?php } ?>
	<h4 class="fl">Available Search Keys</h4><h4 class="fr"><a href="javascript:;">Goto Products List page</a></h4>
    <table class="cb widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th class="manage-column" scope="col">Delete</th>
				<th class="manage-column" scope="col">Search Key</th>
				<th class="manage-column" scope="col">Timestamp</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($searchKeyResults as $searchVal){ ?>
			<tr class="alternate">
				<td><input type="checkbox" class="regular-text" name="delete_list[]" value="<?php echo $searchVal->searchKey; ?>" ></td>
				<td><?php echo $searchVal->searchKey; ?></td>
				<td><?php echo date("F d, Y h:i:s A", $searchVal->timestamp); ?></td>
			</tr>
		<?php } ?>
		</tbody>    
	</table>
	</div>
</form>
</div>