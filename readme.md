## A WordPress plug-in that turns WooCommerce into a machine that sells registrations for meetings, classes, or sessions. It includes minimal styles and should work with any theme.

Source on Bitbucket | Download

This plugin is opinionated and broad in scope. When activated, it assumes the site will be used only for selling registrations to meeting series. Broadly, changes made by the plugin fit into the following 

*   Adds “Meeting Dates” and “Meeting Venues” in the admin and front-end.
*   Renames and removes certain features in WooComerce. Changes buttons, labels, and text on the front-end of the site to provide a consistent user experience.
*   Adds fields in the editor to choose a post that describes the meeting series. Insert this description at the top of the page on the front-end. (This makes it easy to host multiple meetings that cover the same shared subject matter.)

Specifically, the plugin makes the following changes:  

 - Add Meeting Venue taxonomy. Use the name field for the venue name, and the description field for the venue address.
 - Add fields for Meeting Schedule and Meeting Venue to the product edit product screen.
 - Output Meeting Schedule and Meeting Venue on the product page beneath the short description.
 - Output Meeting Schedule and Meeting Venue on checkout page and confirmation pages.
 - Add Meeting Schedule and Meeting Venue as an order line item in dashboard and notification emails. 
 - Add minimal CSS styles for Meeting Schedule and Meeting Venues. 
 - Rename WooCommerce “Orders” to “Registrations”
 - Rename WooCommerce “Products” to “Meeting Series”
 - Replace the “Buy Now” (add to cart) button in the product loop with one that links to the product page instead of adding the item to the cart. Labels "Details and Registration" or "Sold Out :("
 - Replace “Add to Cart” text on single product pages with “Register Now”
 - Rename “Place Order” button to “Continue→”
 - Change product availability labels to “__ Space(s) Left” or “Sold Out :(”
 - Change order received text to “We have sent the registration details and receipt to your email, or you can print this page.”
 - Add print CSS for the Order Confimation Page to facilitate a tidy printed invoice.
 
The plugin depends on WooCommerce and [metabox.io](https://metabox.io).  The plugin is open source and [free of copyright or -left encumberment](https://unlicense.org). Suggestions and contributions encouraged.

If you want to style the output, you can use the following selectors:

    .meeting-schedule-wrapper { /*  */ }
    .meeting-schedule { /* */ }
    .meeting-venue-wrapper { /* */ }
    .meeting-description-wrapper { /* */ }