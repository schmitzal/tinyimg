# TYPO3 Extension tinyimg
Image compression for all pngs and jpgs uploaded to the backend (using the tinypng API)

## What does it do?
It's a small extension, that hooks (using signals) into the TYPO3 file upload and compresses every jpg or png uploaded to the backend using the tinify API: https://tinypng.com/developers

This API can reduce up to 80% of the file size of your images, which will increase your page speed. Without loosing quality.

## Installation
1. Get extension from TER
2. Create an API key* at https://tinypng.com/developers
3. Enter the API key* in the extension configuration (using the extension manager)
4. Have a nice day :)

<span style="font-size: 80%">_*Note that the tinify API is limited to 500 compressions per month.
If you need more, there is a payed version which can be activated in your dashboard (where you got your API key)_</span>

## Contribution

Bugs and feature requests are welcome. Feel free to create an [issue](https://github.com/schmitzal/tinyimg/issues) and i'll have a look at it as soon as possible.

Code improvements are also highly appreciated, as I'm a young developer and sure there is stuff to optimize.

[![alt text](https://cdn.tinypng.com/images/apng/panda-waving.png)](https://tinypng.com/)