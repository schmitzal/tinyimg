# TYPO3 Extension tinyimg
Image compression for all pngs and jpgs uploaded to the backend (using the tinypng API)

## What does it do?
It's a small extension, that hooks (using events) into the TYPO3 file upload and compresses every jpg or png uploaded to the backend using the tinify API: https://tinypng.com/developers

This API can reduce up to 80% of the file size of your images, which will increase your page speed. Without loosing quality.

## Installation
`composer require schmitzal/tinyimg`

## Configuration
1. Create an API key* at https://tinypng.com/developers
2. Enter the API key* in the extension configuration
3. Disables compression in extension settings if desired, so you won't lose compressions during development and testing
4. Have a nice day :)

<span style="font-size: 80%">_*Note that the tinify API is limited to 500 compressions per month.
If you need more, there is a paid version which can be activated in your dashboard (where you got your API key)_</span>

## Setup for already existing projects
This extension contains a command which runs through all file storages and compresses 100 images in it on each run. Depending on the size of these images this takes a while.

Make sure to have an updated index. TYPO3 comes with an index updater as a scheduler task called "File Abstraction Layer: Update storage index (scheduler)".

Also be aware that the tinify API is limited to 500 free compressions (see note above). So on huge websites it will be reached quickly.

## TypoScript reference

Configuration has been moved to extension settings

## Contribution
Bugs and feature requests are welcome. Feel free to create an [issue](https://github.com/schmitzal/tinyimg/issues), and I'll have a look at it as soon as possible.

Code improvements are also highly appreciated.

## Sponsoring
This extension is powered by [OPEN Digitalgruppe GmbH](https://www.open.de/).
Most of the time working on this extension is at work, so my thanks go to Open. :)
