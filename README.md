# Mobile web app admin for silverstripe
Add a tab to the Silverstripe cms from witch mobile web app capabilities can be managed.

## Installation
1. Extract the folder in the root of the silverstripe installation.
2. Run a /dev/build?flush=all

note: This module is in development, it is not (yet) recommended to use this module.
note: Splashscreens like the ipad-retina-portrait and ipad-retina-landscape can be bulky in size, sometimes 5mb in size,
you need to check the server settings if you are not allowed to upload files bigger than 2mb. This can be done in the php.ini file.
Change the value of `upload_max_filesize = 2M` to `upload_max_filesize = 5M` or more.
Sometimes you'll also have to change the value `post_max_size`.