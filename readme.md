## A plug-in that turns WordPress and WooCommerce into a machine that sells registrations for meetings, classes, or sessions.

Source on Bitbucket | Download

The plugin makes use of using standard WooCommerce and WordPress hooks, actions, and filters. It is opinionated, lightweight, and it should work with any theme.

June 2021 

 - Adds fields in the editor to choose a "Meeting Series Mentor"; linking to another page on the site that describes the person. If set, a "Led by..." section is will appear on the meeting page next to the Venue and Schedule information. 
 - Source: Refactor the term product->meeting in the plugin to be parallel with other naming.

<h2>Features</h2>

Meeting Series for WooCommerce is opinionated. It assumes the site will be used only for selling registrations to meeting series. Though it is broad in scope, changes made by the plugin fall into four groups:

- Adds “Meeting Dates” and “Meeting Venues” in the admin and front-end.
- Adds fields in the editor to choose a "Meeting Series Subject"; another static page on your site that describes the meeting series. (This makes it easy to host multiple meetings that cover the same shared subject matter.) This description is inserted at the top of the page on the front-end. 
- Renames and removes certain features in WooComerce. Changes buttons, labels, and text on the front-end of the site to provide a consistent user experience.
- Provides print styles for printing the registration confirmation page as an invoice. 

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
 - Add print CSS for the Order Confirmation Page to facilitate a tidy printed invoice.

The plugin depends on WooCommerce and [metabox.io](https://metabox.io).  The plugin is open source and [free of copyright or -left encumberment](https://unlicense.org). Suggestions and contributions encouraged.

If you want to style the output, you can use the following selectors:

    .meeting-schedule-wrapper { /*  */ }
    .meeting-schedule { /* */ }
    .meeting-venue-wrapper { /* */ }
    .meeting-description-wrapper { /* */ }
