=== Taxonomy Images ===
Contributors:         mfields, husobj, jamiemchale
Donate link:          https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QSYTTQZBRKQVE
Tags:                 taxonomy, tag, category, term, image, upload, media
Requires at least:    3.5
Tested up to:         5.2.1
Stable tag:           1.0
License:              GPLv2 or later
License URI:          http://www.gnu.org/licenses/gpl-2.0.html

Associate images from your media library to categories, tags and custom taxonomies.

== Description ==

= Displaying Your Images in Your Theme =

There are a few filters that you can use in your theme to display the image associations created by this plugin. Please read below for detailed information.

= Display a single image representing the term archive =

The following filter will display the image associated with the term asked for in the query string of the URL. This filter only works in views that naturally use templates like category.php, tag.php, taxonomy.php and all of their derivatives. Please read about [template hierarchy](http://codex.wordpress.org/Template_Hierarchy) for more information about these templates. The simplest use of this filter looks like:

`print apply_filters( 'taxonomy-images-queried-term-image', '' );`

This code will generate and print an image tag. It's output can be modifed by passing an optional third parameter to apply_filters(). This parameter is an array and the following keys may be set:

* __after__ _(string)_ - Text to append to the image's HTML.

* __attr__ _(array)_ - Key / value pairs representing the attributes of the `img` tag. Available options include: `alt`, `class`, `src` and `title`. This array will be passed as the fourth parameter to WordPress core function `wp_get_attachment_image()` without modification.

* __before__ _(string)_ - Text to prepend to the image's HTML.

* __image_size__ _(string)_ - May be any image size registered with WordPress. If no image size is specified, 'thumbnail' will be used as a default value. In the event that an unregistered size is specified, this filter will return an empty string.

Here's an example of what a fully customized version of this filter might look like:

`print apply_filters( 'taxonomy-images-queried-term-image', '', array(
	'attr'       => array(
		'alt'   => 'Custom alternative text',
		'class' => 'my-class-list bunnies turtles',
		'src'   => 'this-is-where-the-image-lives.png',
		'title' => 'Custom Title',
		),
	'before'     => '<div id="my-custom-div">',
	'after'      => '</div>',
	'image_size' => 'medium'
) );
`

= Similar functionality =

If you just need to get the database ID for the image, you may want to use:

`$image_id = apply_filters( 'taxonomy-images-queried-term-image-id', 0 );`

If you need to get the full object of the image, you may want to use:

`$image = apply_filters( 'taxonomy-images-queried-term-image-object', '' );`

If you need to get the URL to the image, you may want to use the following:

`$image_url = apply_filters( 'taxonomy-images-queried-term-image-url', '' );`

You can specify the size of the image in an option third parameter:

`
$image_url = apply_filters( 'taxonomy-images-queried-term-image-url', '', array(
	'image_size' => 'medium'
) );
`

If you need data about the image, you may want to use:

`$image_data = apply_filters( 'taxonomy-images-queried-term-image-data', '' );`

You can specify the size of the image in an option third parameter:

`
$image_data = apply_filters( 'taxonomy-images-queried-term-image-data', '', array(
	'image_size' => 'medium'
	) );
`

= List term images associated with a post object =

When a post is being displayed you may want to display the images associated with all of the terms associated with the post. The `taxonomy-images-list-the-terms` filter does this. Here's what it looks like in its simplest form:

`print apply_filters( 'taxonomy-images-list-the-terms', '' );`

This filter accepts an optional third parameter that you can use to customize its output. It is an array which recognizes the following keys:

* __after__ _(string)_ - Text to append to the output. Default value is a closing unordered list tag.

* __after_image__ _(string)_ - Text to append to each image. Default value is a closing list-item tag.

* __before__ _(string)_ - Text to prepend to the output. Default value is an open unordered list tag with an class attribute of "taxonomy-images-the-terms".

* __before_image__ _(string)_ - Text to prepend to each image. Default value is an open list-item tag.

* __image_size__ _(string)_ - Any registered image size. Values will vary from installation to installation. Image sizes defined in core include: "thumbnail", "medium" and "large". "full" may also be used to get the unmodified image that was uploaded. Defaults to "thumbnail".

* __post_id__ _(int)_ - The post to retrieve terms from. Defaults to the ID property of the global `$post` object.

* __taxonomy__ _(string)_ - Name of a registered taxonomy to return terms from. Defaults to `category`.

Here's an example of what a fully customized version of this filter might look like:

`
print apply_filters( 'taxonomy-images-list-the-terms', '', array(
	'before'       => '<div class="my-custom-class-name">',
	'after'        => '</div>',
	'before_image' => '<span>',
	'after_image'  => '</span>',
	'image_size'   => 'detail',
	'post_id'      => 1234,
	'taxonomy'     => 'post_tag',
) );
`

= Working with all terms of a given taxonomy =

You will want to use the `taxonomy-images-get-terms` filter. This filter is basically a wrapper for WordPress core function [get_terms()](http://codex.wordpress.org/Function_Reference/get_terms). It will return an array of enhanced term objects: each term object will have a custom property named `image_id` which is an integer representing the database ID of the image associated with the term. This filter can be used to create custom lists of terms. Here's what it's default useage looks like:

`$terms = apply_filters( 'taxonomy-images-get-terms', '' );`

Here is what php's `print_r()` function may return:

`
Array
(
	[0] => stdClass Object
		(
			[term_id]          => 8
			[name]             => Pirate
			[slug]             => pirate
			[term_group]       => 0
			[term_taxonomy_id] => 8
			[taxonomy]         => category
			[description]      => Pirates live in the ocean and ride around on boats.
			[parent]           => 0
			[count]            => 1
			[image_id]         => 44
		)
)
`

As you can see, all of the goodness of `get_terms()` is there with an added bonus: the `image_id` parameter!

This filter recognizes an optional third parameter which is an array of arguments that can be used to modify its output:

* __cache_images__ _(bool)_ If this value is `true` all associated images will be queried and cached for later use in various template tags. If it is set to `false`, this query will be suppressed. Do not set this value to false unless you have a really good reason for doing so :) Default value is `true`.

* __having_images__ _(bool)_ If this value is `true` then only terms that have associated images will be returned. Setting it to `false` will return all terms. Default value is `true`.

* __taxonomy__ _(string)_ Name of a registered taxonomy to return terms from. Multiple taxonomies may be specified by separating each name by a comma. Defaults to `category`.

* __term_args__ _(array)_ Arguments to pass to [`get_terms()`](http://codex.wordpress.org/Function_Reference/get_terms) as the second parameter. Default value is an empty array.

Here's an example of a simple custom loop that you can use to display all term images:

`
$terms = apply_filters( 'taxonomy-images-get-terms', '' );
if ( ! empty( $terms ) ) {
	print '<ul>';
	foreach ( (array) $terms as $term ) {
		print '<li><a href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '">' . wp_get_attachment_image( $term->image_id, 'detail' ) . '</a></li>';
	}
	print '</ul>';
}
`

= Support =

If you have questions about integrating this plugin into your site, please [add a new thread to the WordPress Support Forum](https://wordpress.org/tags/taxonomy-images?forum_id=10#postform). I try to answer these, but I may not always be able to. In the event that I cannot there may be someone else who can help.

= Bugs, Suggestions =

Development of this plugin is hosted in a public repository on [Github](https://github.com/benhuson/Taxonomy-Images). If you find a bug in this plugin or have a suggestion to make it better, please [create a new issue](https://github.com/benhuson/Taxonomy-Images/issues/new)

= Hook it up yo! =

If you have fallen in love with this plugin and would not be able to sleep without helping out in some way, please see the following list of ways that you can _hook it up!_:

* __Rate it!__ - Use the star tool on the right-hand sidebar of the [plugin homepage](https://wordpress.org/plugins/taxonomy-images/).

* __Let me know if it works__ - Use the _Compatibility_ widget on the [plugin homepage](https://wordpress.org/plugins/taxonomy-images/) to let everyone know that the current version works with your version of WordPress.

* __Do you Twitter?__ Help promote by using this shortlink: [http://bit.ly/taxonomy-images](http://bit.ly/taxonomy-images)

* __Are you a writer?__ Help promote by writing an article on your website about this plugin.

= Need More Taxonomy Plugins? =

The original author of this plugin, Michael Fields, released a handful of plugins dealing with taxonomies. Please see his [WordPress.org profile](https://profiles.wordpress.org/mfields/) for more information.

== Installation ==

1. Download
1. Unzip the package and upload to your `/wp-content/plugins/` directory.
1. Log into WordPress and navigate to the "Plugins" panel.
1. Activate the plugin.
1. Click the "Taxonomy Images" link under the Settings section in the admin menu. There you can select the taxonomies that you would like to add image support for.

== Screenshots ==

1. Edit terms admin page.
2. Edit term admin page.
3. Media Modal

== Upgrade Notice ==

= 1.0 =
Fixed full image size sometimes not being returned. Prepare plugin structure for term meta compatibility.

= 0.9.7 =
Remove use of deprecated `image_resize` function. Bump minimum WordPress version to 3.5.

= 0.9.6 =
Fixed issue where if no terms have images but 'having_images' is false, nothing would be returned (props Matt).

= 0.9.5 =
Fix loading of admin stylesheet when editing terms in WordPress 4.5

= 0.9.4 =
Fix for taxonomy names that may contain characters other than lowercase and underscores (e.g. uppercase).

= 0.9.3 =
Fix post permissions error when using media modal.

= 0.9.2 =
Fix old and new media modal opening simultaneously in some circumstances.

= 0.9.1 =
Fixes media modal not opening on newly created terms.

= 0.9 =
Fixes media modal for newer versions of WordPress. Tested up to WordPress 4.3.1 (requires 3.4+).

= 0.8 =
Major and minor bug fixes tested with WordPress 3.6.

= 0.7 =
Complete rewrite. Better everything. Many bug fixes.

== Changelog ==

= Unreleased =

= 1.0 =
* __BUGFIX:__ Fixed full image size sometimes not being returned.
* __UPDATE:__ Control, blank and default images moves to `images` folder.
* __UPDATE:__ Prepare plugin structure for term meta compatibility.

= 0.9.7 =
* __UPDATE:__ Remove use of deprecated `image_resize` function.
* __UPDATE:__ Bump minimum WordPress version to 3.5.

= 0.9.6 =
* __BUGFIX:__ Fix issue where if no terms have images but 'having_images' is false, nothing would be returned (props Matt).

= 0.9.5 =
* __BUGFIX:__ Fix loading of admin stylesheet when editing terms in WordPress 4.5

= 0.9.4 =
* __BUGFIX:__ Fix for taxonomy names that may contain characters other than lowercase and underscores (e.g. uppercase).

= 0.9.3 =
* __BUGFIX:__ Fix post permissions error when using media modal.

= 0.9.2 =
* __BUGFIX:__ Fix old and new media modal opening simultaneously in some circumstances.
* __UPDATE:__ Documentation: Pedantic corrections. Props [Gary Jones](https://profiles.wordpress.org/garyj)
* __UPDATE:__ Added CHANGELOG.md file.

= 0.9.1 =
* __BUGFIX:__ Fixes media modal not opening on newly created terms.
* __UPDATE:__ Adhere to WordPress PHP coding standards.

= 0.9 =
* __COMPAT:__ Add support for WordPress 3.5+ media modal.
* __COMPAT:__ Confirmed minimum WordPress 3.4 required.
* __UPDATE:__ Add Spanish translation.
* __UPDATE:__ Make images retina sized in the admin.
* __UPDATE:__ Re-color admin image and icons to fit with more recent versions of WordPress.
* __UPDATE:__ Stop using deprecated `image_resize()` function.
* __UPDATE:__ Stop using deprecated `screen_icon()` function.
* __UPDATE:__ Move JavaScript and CSS files to subfolders.
* __UPDATE:__ Added screenshots for WordPress.org
* __UPDATE:__ Add husobj as a contributor.

= 0.8.0 =
* __COMPAT:__ Use `jQuery.on()` instead of `jQuery.live()`. Props [jamiemchale](http://profiles.wordpress.org/jamiemchale).
* __UPDATE:__ Pass an empty array as default second parameter of `taxonomy_images_plugin_get_the_terms()` and `taxonomy_images_plugin_list_the_terms()`.
* __UPDATE:__ Give the button on the custom admin screen a class of `button-primary`.
* __UPDATE:__ Store the return value of `get_posts()` in a variable called `$images`. Not sure why, but this should not harm anything.
* __UPDATE:__ Change license to `GPLv2 or later` for maximum flexibility and compatibility.
* __UPDATE:__ Random whitespace fixes.
* __UPDATE:__ Update Documentation.
* __UPDATE:__ CSS coding standards.
* __UPDATE:__ Bump version number.
* __UPDATE:__ Update readme files.
* __UPDATE:__ Add jamiemchale as a contributor.

= 0.7.3 =
* __BUGFIX:__ Fixed the delete image button on edit-terms.php.
* __UPDATE:__ Better escaping.
* __UPDATE:__ Introduced `.pot` file and languages directory.

= 0.7.2 =
* __UPDATE:__ Return data for fullsize images in archive views. [See this thread](https://wordpress.org/support/topic/image-size-full).

= 0.7.1 =
* __BUGFIX:__ Remove unused link code which is throwing an error when no taxonomies support images.

= 0.7 =
* __COMPAT:__ No longer breaks display of the [Better Plugin Compatibility Control](https://wordpress.org/plugins/better-plugin-compatibility-control/) plugin.
* __UPDATE:__ Created a custom filter interface for plugin and theme integration.
* __UPDATE:__ Lots of inline documentation added.
* __UPDATE:__ Added custom notices if plugin is used in an unsupported way.
* __UPDATE:__ No notices generated by PHP or WordPress.
* __UPDATE:__ Deprecated function calls removed.
* __UPDATE:__ Security updates.
* __UPDATE:__ All strings are now internationalized.
* __UPDATE:__ Add image to term functionality mimics "Add Featured Image".
* __UPDATE:__ Taxonomy modal button now available in search + upload states.
* __UPDATE:__ Image interface has been added to single term edit screen.
* __UPDATE:__ Users can now choose which taxonomys have image support.
* __UPDATE:__ All functions are now private.
* __UPDATE:__ Shortcode deprecated.
* __UPDATE:__ All global variables and constants have been removed or deprecated.

= 0.6 =
* __UPDATE:__ Completely recoded.
* Never released.

= 0.5 =
* __UPDATE:__ Direct link to upload new files from `edit-tag.php` has been introduced.
* __UPDATE:__ Ability to create an image/term association immediately after upload has been introduced.
* __UPDATE:__ Users can now delete image/term associations.
* __UPDATE:__ Created standalone javascript files - removed inline scripts.
* __UPDATE:__ Obsessive compulsive syntax modifications.
* __UPDATE:__ Localization for strings - still need to "fine-tooth-comb" this.
* __UPDATE:__ Removed all debug functions.

= 0.4.4 =
* __BUGFIX:__ `get_image_html()` Now populates the image's `alt` attribute with appropriate data. Props to [jaygoldman](https://wordpress.org/support/profile/jaygoldman).

= 0.4.3 =
* __COMPAT:__ Removed use of deprecated function `is_taxonomy()` - props to [anointed](https://profiles.wordpress.org/users/anointed).
* __COMPAT:__ Included a definition for `taxonomy_exists()` function for backwards compatibility with 2.9 branch. This function is new in WordPress version 3.0.
* __UPDATE:__ Support for WordPress 3.0 has been added. Support for all beta versions of 3.0 has been dropped.

= 0.4.2 =
* __UPDATE:__ Changed button name from "Category" to "Taxonomy".
* __UPDATE:__ Support for 2.9 branch has been added again.

= 0.4.1 =
* __UPDATE:__ Added support for dynamic taxonomy hooks for `_tag_row()`.
* __BROKEN:__ Support for 2.9 branch has been temporarily removed.

= 0.4 =
* __BUGFIX:__ `get_thumb()` now returns the fullsize URL if there is no appropriate intermediate image.
* __UPDATE:__ Added "taxonomy_images_shortcode".

= 0.3 =
* __BUGFIX:__ Deleted the `register_deactivation_hook()` function - sorry to all 8 who downloaded this plugin so far :)
* __COMPAT:__ Changed the firing order of every hook utilizing the `category_rows` method to 15. This allows this plugin to be compatible with [Reveal IDs for WP Admin](https://wordpress.org/plugins/reveal-ids-for-wp-admin-25/). Thanks to [Peter Kahoun](https://profiles.wordpress.org/kahi/)
* __COMPAT:__ Added Version check for PHP5.
* __UPDATE:__ `$settings` and `$locale` are now public properties.
* __UPDATE:__ Object name changed to `$taxonomy_images_plugin`.
* __UPDATE:__ Added argument `$term_tax_id` to both `print_image_html()` and `get_image_html()`.

= 0.2 =
* Original Release - Works with WordPress 2.9.1.
