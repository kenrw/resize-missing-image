resize-missing-image
====================

Resize an image on-the-fly from an Apache 404 and a source image.

The basic idea here is PHP is used to resize images.  When it does,
with the implementations I am familiar with, it does so by
acting like a Web server.  So, I think the best way to serve 
images is with a web server: Apache.

However, not the spectrum of sizes are created in advance.  This
method creates them as needed.  The PHP resize script becomes
aware of a needed image when the Apache Server redirects a 404
(missing file).  

For example, here is an image that exits:

`http://www.example.com/images/landscape.jpg`

Here is one that will need to be created:

`http://www.example.com/images/600x300/landscape.jpg`

The image doesn't exist, so Apache calls the 404 handler: 
`resize_missing_image.php`

It resizes the image (currently with a system command with "convert")
and stores the image in the correct path.  Finally, the php script
redirects with `header("Location: ...")` to load the resized image.

The next  time the web page calls for the resized image, it will exist in 
the path, and the PHP script is not involved any longer.

