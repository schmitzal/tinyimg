# TYPO3 Extension tinyimg
Image compression for all pngs and jpgs uploaded to the backend (using the tinypng API)

## What does it do?
It's a small extension, that hooks (using signals) into the TYPO3 file upload and compresses every jpg or png uploaded to the backend using the tinify API: https://tinypng.com/developers

This API can reduce up to 80% of the file size of your images, which will increase your page speed. Without loosing quality.

## Installation via composer
1. Get extension tinyimg: `composer require schmitzal/tinyimg`
2. Active extension (using extension manager or commandline)

#### _Installation via extension manager_
Installing the extension via extension manager is possible but as tinyimg requires the [tinify/tinify](https://packagist.org/packages/tinify/tinify) library you will have to take care of installing this package manually.

## Configuration
1. Create an API key* at https://tinypng.com/developers
2. Enter the API key* in the extension configuration
3. Include the static TypoScript. It disables compression while on application context "Development" - so you wont loose compressions during development and testing
4. Have a nice day :)

<span style="font-size: 80%">_*Note that the tinify API is limited to 500 compressions per month.
If you need more, there is a payed version which can be activated in your dashboard (where you got your API key)_</span>

## Setup for already existing projects
This extension contains an extbase command which runs through all files storages and compresses 100 images in it on each run. Depending on the size of this images this takes a while.

Make sure to have an updated index. TYPO3 comes with an index updater as a scheduler task called "File Abstraction Layer: Update storage index (scheduler)".

Also be aware that the tinify API is limited to 500 free compressions (see note above). So on huge websites it will be reached quickly.

## TypoScript reference
| Setting        | Type   | Default | Description                                                                                                                        |
|----------------|--------|---------|------------------------------------------------------------------------------------------------------------------------------------|
| debug          | bool   | 0       | Enable or disable debugging mode. Stops extension from compressing images. Use in development mode to avoid waisting compressions. |
| excludeFolders | string | empty   | Comma-separated list of folders which should be excluded from compression. Relative from storage (e.g. fileadmin), starting with "/" (e.g. "/user_upload,/folder_under_fileadmin") |

## Contribution
Bugs and feature requests are welcome. Feel free to create an [issue](https://github.com/schmitzal/tinyimg/issues) and i'll have a look at it as soon as possible.

Code improvements are also highly appreciated, as I'm a young developer and sure there is stuff to optimize.

## Sponsoring
This extension is powered by [Interlutions GmbH](https://www.interlutions.de/).
Most of the time working on this extension is at work, so my thanks go to Interlutions. :)
