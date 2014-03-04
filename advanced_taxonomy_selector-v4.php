<?php

class acf_field_advanced_taxonomy_selector extends acf_field
{
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options


	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct()
	{
		// vars
		$this->name = 'advanced_taxonomy_selector';
		$this->label = __('Advanced Taxonomy Selector');
		$this->category = __("Relational",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			'taxonomies' => '',
			'data_type' => 'terms',
			'field_type' => 'multiselect',
			'allow_null' => true,
			'return_value' => 'term_id'
		);

		// do not delete!
    parent::__construct();


    // settings
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);

	}


	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	function create_options($field)
	{
		$field = array_merge($this->defaults, $field);

		// key is needed in the field names to correctly save the data
		$key = $field['name'];

		$taxonomies = get_taxonomies( array( '_builtin' => false, 'public' => true ), 'objects' );
		$choices = array( 'all' => __( 'All Taxonomies', 'acf' ) );
		foreach ( $taxonomies as $slug => $taxonomy ) {
			$choices[$slug] = $taxonomy->label;
		}

		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Type", 'acf'); ?></label>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'    =>  'radio',
			'name'    =>  'fields[' . $key . '][data_type]',
			'value'   =>  $field['data_type'],
			'choices' =>  array(
				'terms' => __( 'Choose Terms', 'acf' ),
				'taxonomy'  => __( 'Choose Taxonomies', 'acf' ),
			)
		));

		?>
	</td>
</tr>


<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Taxonomies", 'acf'); ?></label>
		<p class="description"><?php _e("Set taxonomies users will be able to choose from", 'acf'); ?></p>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'    =>  'select',
			'name'    =>  'fields[' . $key . '][taxonomies]',
			'value'   =>  $field['taxonomies'],
			'multiple'  =>  true,
			'choices' =>  $choices
		));

		?>
	</td>
</tr>


<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Field Type", 'acf'); ?></label>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'    =>  'select',
			'name'    =>  'fields[' . $key . '][field_type]',
			'value'   =>  $field['field_type'],
			'choices' =>  array(
				'multiselect'  => __( 'Multiselect', 'acf' ),
				'select'       => __( 'Select', 'acf' )
			)
		));

		?>
	</td>
</tr>

<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Allow Null?", 'acf'); ?></label>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'    =>  'radio',
			'name'    =>  'fields[' . $key . '][allow_null]',
			'layout'  => 'horizontal',
			'value'   =>  $field['allow_null'],
			'choices' =>  array(
				1 => __( 'Yes', 'acf' ),
				0  => __( 'No', 'acf' ),
			)
		));

		?>
	</td>
</tr>


<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Return Value", 'acf'); ?></label>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'    =>  'radio',
			'name'    =>  'fields[' . $key . '][return_value]',
			'value'   =>  $field['return_value'],
			'choices' =>  array(
				'term_id' => __( 'Term ID / Taxonomy Slug', 'acf' ),
				'object'  => __( 'Term Object / Taxonomy Object', 'acf' ),
			)
		));

		?>
	</td>
</tr>



		<?php

	}


	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function create_field( $field )
	{

		$taxonomies = $field['taxonomies'];
		if( empty( $field['taxonomies'] ) || ( !empty( $field['taxonomies'] ) && in_array( 'all', $field['taxonomies'] ) ) ) {
			$taxonomies = get_taxonomies( array( '_builtin' => false, 'public' => true ) );
		}

		$taxonomies = array_values( $taxonomies );
		
		if ( $field['data_type'] == 'terms' ) {
			$term_list = array();

			foreach ( $taxonomies as $taxonomy_slug ) {
				$taxonomy = get_taxonomy( $taxonomy_slug );
				$terms = get_terms( $taxonomy_slug );
				$term_list[$taxonomy_slug] = array(
					'name' => $taxonomy->label,
					'slug' => $taxonomy_slug,
					'terms' => array()
				);
				foreach ( $terms as $term ) {
					$term_list[$taxonomy_slug]['terms'][] = array(
						'name' => $term->name,
						'slug' => $term->slug,
						'term_id' => $term->term_id
					);
				}
			}

			if ( $field['field_type'] == 'multiselect' || $field['field_type'] == 'select' ) :
			?>
			<div>
				<?php if ( $field['field_type'] == 'multiselect' ) : ?>
				<select name="<?php echo $field['name'] ?>[]" multiple='multiple'>
				<?php else : ?>
				<select name="<?php echo $field['name'] ?>[]">
				<?php endif ?>

					<?php if( !empty( $field['allow_null'] ) ) : ?>
						<option value=''><?php _e( 'All Taxonomies', 'acf' ) ?></option>
					<?php endif ?>
					<?php foreach ( $term_list as $taxonomy ) : ?>
					<optgroup label='<?php echo $taxonomy['name'] ?>'>
						<?php
							foreach( $taxonomy['terms'] as $term ) :
							$value = $taxonomy['slug'] . '_' . $term['term_id'];
							$selected = ( in_array( $value, $field['value'] ) ) ? 'selected="selected"' : '';
						?>
							<option <?php echo $selected ?> value='<?php echo $value ?>'><?php echo $term['name'] ?></option>
						<?php endforeach ?>
					</optgroup>
					<?php endforeach ?>
				</select>
			</div>
			<?php

			endif;

		}
		else {

			if ( $field['field_type'] == 'multiselect' || $field['field_type'] == 'select' ) :
			?>
			<div>
				<?php if ( $field['field_type'] == 'multiselect' ) : ?>
				<select name="<?php echo $field['name'] ?>[]" multiple='multiple'>
				<?php else : ?>
				<select name="<?php echo $field['name'] ?>[]">
				<?php endif ?>
					<?php 
						foreach ( $taxonomies as $taxonomy_slug ) : 
						$taxonomy = get_taxonomy( $taxonomy_slug );
						$selected = ( 
							( is_array( $field['value'] ) && in_array( $taxonomy_slug, $field['value'] ) )
							||
							( is_string( $field['value'] ) && $taxonomy_slug == $field['value'] )
							||
							( empty( $field['value'] ) && $taxonomy_slug == 'bsd_item_type' )
						) ? 'selected="selected"' : ''
					?>
						<option <?php echo $selected ?> value='<?php echo $taxonomy_slug ?>'><?php echo $taxonomy->label ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<?php

			endif;


		}


	}


	function update_value($value, $post_id, $field) {
		if( $value == array( 0 => '' ) ) {
			return '';
		}

		return $value;
	}


}


// create field
new acf_field_advanced_taxonomy_selector();

?>
