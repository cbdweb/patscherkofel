<form id="psl_add_members" class="cart woocommerce" method="post">
	<div id="member_wrapper">
		<p>
		You have multiple Memberships<br />Please select one to use with this booking.
		</p>
		<?php
			foreach ($account_ids as $index => $id)
			{
				$select = ($index == 0) ? 'checked' : '';
				echo '<p>';
				echo '<label for="membership_id_'.$index.'">'.$id.' </label>';
				echo '<input id="membership_id_'.$index.'" type="radio" name="multi_account_number" value="'.$id.'" '.$select.'>';
				echo '</p>';
			}

		?>

	</div>
	<br>
	<input type="hidden" name="_new_members_nonce" value="<?php echo wp_create_nonce('_new_members_nonce'); ?>">
	<br>
	<hr />
	<br>
	<button type="submit" class="wc-bookings-booking-form-button button float-right alt">Next</button> 
</form>
