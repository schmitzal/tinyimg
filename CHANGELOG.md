# Changelog

### 1.3.0
* **[FEATURE]** Add support for TYPO3 9.5
* **[CHANGE]** Drop support of non composer installation
* **[BUGFIX]** Remove folder parameter from compression service call in the compression command controller

### 1.2.0
* **[FEATURE]** Add support for extension "aus_driver_amazon_s3" - Thanks to Andreas Hoffmeyer for the patch
* **[BUGFIX]** Show the right filesize after upload by updating the file information in the database
* **[FEATURE]** Add a compression bar after file has been uploaded & show a message when compression is done with the percentage saved
* **[FEATURE]** Add an extbase command controller which loops through all file storage and compresses all images recursively in it

### 1.1.0
* **[RELEASE]** Version 1.1.0 including support for TYPO3 7.6
* **[FEATURE]** Add debugging mode
* **[CLEANUP]** Removed tinypng assets from readme and changed extension icon

### 1.0.1
* **[CLEANUP]** Small code improvements

### 1.0.0
* **[RELEASE]** First version including image compression for every uploaded jpg and png.
