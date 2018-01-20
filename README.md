# project_utility

## This is for personal use of Kaloyan Yosifov all right goes to me
## It is free, anyone can use it 

### The Socials functions

#### `crb_add_social( $social_name, $arrr )` or `crb_add_socials( $arr )`
The first function you set the social name and add an array of arguments like
```
array(
	'public_name' => ('name of the social that is going to be shown in Theme Options'),
	('icon' or 'image') => (the icon class or image id, or image url ),
)
```

Theese are the required arguments for add socials and add social
The second function you add associative array with social names
```
array(
	( The social name ) => array(
		'public_name' => ('name of the social that is going to be shown in Theme Options'),
		('icon' or 'image') => (the icon class or image id, or image url ),
	),
)
```

You can add other parameters in the array but 'public_name' and ('icon' or 'image') are required </br>

#### `crb_get_social( $args = '', $fields = '' )`

In this function for arguments you can pass 'list' or 'generate_fields' on the $args and for $fields you can pass an array of fields or a single field.

##### The 'list' returns all the socials

##### The generate fields return fields with url for the socials

##### Without passing $args you can get the socials with the url from Theme Optios or whatever you called your tab and render them yourself

The socials function works with carbon fields only <br/>

To add socials you would have to call `crb_add_socials` or `crb_add_social` to add a social in the array. <br/> 

In order for the icons or images to show you have to put `crb_get_socials( 'generate_fields' )` somewhere in the trhem options in a tab or for example `Container::make( 'theme_options', __( 'Theme Options', 'crb' ) )->add_tab( __( 'Socials', 'crb' ), crb_get_socials( 'generate_fields' ) );` this is just and example if you have a better way implementing the socials to them options feel free to do it.

#### `crb_render_socials( ($args not required) )`
Renders the added socials

```
$render_args = array(
			'echo' => true,
			'no_url_show' => true,
			'wrapper_before' => '<div class="socials"><ul>',
			'wrapper_after' => '</ul></div>',
			'item_wrapper_before' => '<li>',
			'item_wrapper_after' => '</li>',
			'render_icons' => true,
			'render_images' => false,
			'image_size' => 'thumbnail',
		);
```

### Socials Hooks

'crb_pre_get_socials'- action hook.In this hook you can call it to add socials instead of adding socials before the container <br/>

'crb_pre_render_socials_args'-filter hook gets arguments before rendering the html <br/>

'crb_rendered_socials_html'- filter hook gets the html before it being rendered <br/>

