<!-- wp:paragraph -->
<p>A WordPress plug-in that turns WooCommerce into a machine that sells registrations for meetings, classes, or sessions. It includes minimal styles and should work with any theme.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns has-2-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:button {"align":"right"} -->
<div class="wp-block-button alignright"><a class="wp-block-button__link">Source on Bitbucket</a></div>
<!-- /wp:button --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:button {"align":"left"} -->
<div class="wp-block-button alignleft"><a class="wp-block-button__link">Download</a></div>
<!-- /wp:button --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph -->
<p>This plugin is opinionated and broad in scope. When activated, it assumes the site will be used only for selling registrations to meeting series. Broadly, changes made by the plugin fit into the following </p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>Adds “Meeting Dates” and “Meeting Venues” in the admin and front-end.</li><li>Renames and removes certain features in WooComerce. Changes buttons, labels, and text on the front-end of the site to provide a consistent user experience.</li><li>Adds fields in the editor to choose a post that describes the meeting series. Insert this description at the top of the page on the front-end. (This makes it easy to host multiple meetings that cover the same shared subject matter.)</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Specifically, the plugin makes the following changes:<br/></p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>Add Meeting Venue taxonomy. Use the name field for the venue name, and the descritption field for the venue address.<br/></li><li>Add fields for Meeting Schedule and Meeting Venue to the product edit product screen.<br/></li><li>Output Meeting Schedule and Meeting Venue on the product page beneath the short description.</li><li>Output Meeting Schedule and Meeting Venue  on checkout page and confirmation pages.</li><li>Add Meeting Schedule and Meeting Venue as an order line item in dashboard and notification emails. </li><li>Add minimal CSS styles for Meeting Schedule and Meeting Venues. </li><li>Rename WooCommerce “Orders” to “Registrations”</li><li>Rename WooCommerce “Products” to “Meeting Series”</li><li>Replace the “Buy Now” (add to cart) button in the product loop with one that links to the product page instead of adding the item to the cart. Labels "Details and Registration" or "Sold Out :("</li><li>Replace “Add to Cart” text on single product pages with “Register Now”</li><li>Rename “Place Order” button to “Continue→”</li><li>Change product availability labels to “__ Space(s) Left” or “Sold Out :(”</li><li>Change order received text to “We have sent the registration details and receipt to your email, or you can print this page.”<br/></li><li>Add print CSS for the Order Confimation Page to facilitate a tidy printed invoice.</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>The plugin depends on WooCommerce and <a href="https://metabox.io">metabox.io</a>.  The plugin is open source and <a href="https://unlicense.org">free of copyright or -left encumberment</a>. Suggestions and contributions encouraged.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If you want to style the output, you can use the following selectors:</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>.meeting-schedule-wrapper { /*  */ }
.meeting-schedule { /* */ }
.meeting-venue-wrapper { /* */ }
.meeting-description-wrapper { /* */ }</code></pre>
<!-- /wp:code -->