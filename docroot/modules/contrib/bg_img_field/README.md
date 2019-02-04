# Background Image Field

## Dependencies
* Token
* Core: Responsive Images

## Installation
Install as you would any other drupal module. See more information [here](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

## Configuration
1. Create responsive image style /admin/config/media/responsive-image-style
    * The only responsive image style that will be picked up by the field formatter are the ones that have selected a single image style.
2. Add the field on an entity type such as node, paragraph_item or, custom entity.

### Troubleshooting
If you do not see the background image, please make sure to check that the css selector is actually apart of the HTML. The field will not create the selector you choose it already has to exist for it to work.

If you don't see any available responsive image styles in the managed display setting on the entity type you will most likley need to create one following the outline configurations above.

