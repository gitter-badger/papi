<div class="wrap">
	<div class="papi-options-logo"></div>
	<h1><?php echo papi()->name; ?></h1>

	<br/>

	<h3><?php _e( 'Page Types', 'papi' ); ?></h3>
	<table class="wp-list-table widefat papi-options-table">
		<thead>
		<tr>
			<th>
				<strong><?php _e( 'Name', 'papi' ); ?></strong>
			</th>
			<th>
				<strong><?php _e( 'Page Type ID', 'papi' ); ?></strong>
			</th>
			<th>
				<strong><?php _e( 'Template', 'papi' ); ?></strong>
			</th>
			<th>
				<strong><?php _e( 'Page Count', 'papi' ); ?></strong>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$page_types = papi_get_all_page_types( true );
		foreach ( $page_types as $key => $page_type ) {
			if ( ! method_exists( $page_type, 'get_boxes' ) ) {
				continue;
			}
			?>
			<tr>
				<td><a href="<?php echo sanitize_text_field( $_SERVER['REQUEST_URI'] ); ?>&view=management-page-type&page_type=<?php echo esc_attr( $page_type->get_id() ); ?>"><?php echo esc_html( $page_type->name ); ?></a></td>
				<td><?php echo esc_html( $page_type->get_id() ); ?></td>
				<td>
					<?php
					if ( ! current_user_can( 'edit_themes' ) || defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
							echo esc_html( $page_type->template );
					} else {
						$theme_dir  = get_template_directory();
						$theme_name = basename( $theme_dir );
						$url        = site_url() . '/wp-admin/theme-editor.php?file=' . $page_type->template . '&theme=' . $theme_name;

						if ( empty( $page_type->template ) ) {
							_e( 'Page Type has no template file', 'papi' );
						} else {
							if ( file_exists( $theme_dir . '/' . $page_type->template ) ) :
								?>
								<a href="<?php echo esc_attr( $url ); ?>"><?php echo esc_html( $page_type->template ); ?></a>
							<?php
							else :
								_e( 'Template file does not exist', 'papi' );
							endif;
						}
					}
					?>
				</td>
				<td><?php echo esc_html( papi_get_number_of_pages( $page_type->get_id() ) ); ?></td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
</div>
