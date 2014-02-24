<?php

class acf_field_advanced_taxonomy_selector extends acf_Field
{

	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options


	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*	- This function is called when the field class is initalized on each page.
	*	- Here you can add filters / actions and setup any other functionality for your field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	*
	*-------------------------------------------------------------------------------------*/

	function __construct($parent)
	{

		// do not delete!
  	parent::__construct($parent);

		// vars
		$this->name = 'advanced_taxonomy_selector';
		$this->label = __('Advanced Taxonomy Selector');
		$this->category = __("Relational",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			'taxonomies' => '',
			'field_type' => 'multiselect',
			'return_value' => 'term_id'
		);



    // settings
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);

  }


 	/*
  *  helpers_get_path
  *
  *  @description: calculates the path (works for plugin / theme folders)
  *  @since: 3.6
  *  @created: 30/01/13
  */

  function helpers_get_path($file)
  {
    return trailingslashit(dirname($file));
  }


  /*
  *  helpers_get_dir
  *
  *  @description: calculates the directory (works for plugin / theme folders)
  *  @since: 3.6
  *  @created: 30/01/13
  */

  function helpers_get_dir($file)
  {
    $dir = trailingslashit(dirname($file));
    $count = 0;


    // sanitize for Win32 installs
    $dir = str_replace('\\', '/', $dir);


    // if file is in plugins folder
    $wp_plugin_dir = str_replace('\\', '/', WP_PLUGIN_DIR);
    $dir = str_replace($wp_plugin_dir, WP_PLUGIN_URL, $dir, $count);


    if($count < 1)
    {
      // if file is in wp-content folder
      $wp_content_dir = str_replace('\\', '/', WP_CONTENT_DIR);
      $dir = str_replace($wp_content_dir, WP_CONTENT_URL, $dir, $count);
    }


    if($count < 1)
    {
      // if file is in ??? folder
      $wp_dir = str_replace('\\', '/', ABSPATH);
      $dir = str_replace($wp_dir, site_url('/'), $dir);
    }

    return $dir;
  }


	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*	- this function is called from core/field_meta_box.php to create extra options
	*	for your field
	*
	*	@params
	*	- $key (int) - the $_POST obejct key required to save the options to the field
	*	- $field (array) - the field object
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	*
	*-------------------------------------------------------------------------------------*/

	function create_options($key, $field)
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
		<label><?php _e("Return Value", 'acf'); ?></label>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'    =>  'radio',
			'name'    =>  'fields[' . $key . '][return_value]',
			'value'   =>  $field['return_value'],
			'choices' =>  array(
				'term_id' => __( 'Term ID', 'acf' ),
				'object'  => __( 'Object', 'acf' ),
			)
		));

		?>
	</td>
</tr>



		<?php

	}




	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*	- this function is called on edit screens to produce the html for this field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	*
	*-------------------------------------------------------------------------------------*/

	function create_field($field)
	{

		$taxonomies = $field['taxonomies'];
		if( empty( $field['taxonomies'] ) || ( !empty( $field['taxonomies'] ) && in_array( 'all', $field['taxonomies'] ) ) ) {
			$taxonomies = get_taxonomies( array( '_builtin' => false, 'public' => true ) );
		}

		$taxonomies = array_values( $taxonomies );
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



}

?>
