<?
/*
	Example:
		A URL request for a resized image:

		http://www.example.com/images/products/marmot/300x300/speedascent2.jpg

		A 404 error occurs because the resized image is not there :(

		Our class examines the path ( /images/products/marmot/300x300/speedascent2.jpg)
		we break that down to 3 components:

			/images/products/marmot
			300x300
			speedascent2.jpg

		Now, what does exist is the full-sized image.  And that image should be in this path:

			/images/products/marmot/speedascent2.jpg

		So, all we do is create the folder

			/images/products/marmot/300x300

		Then we resize the image with our image resize libray (we don't care how it gets done -- using
		GD, imageMagik, whatever).

		The the resized image is stored in the newly created folder with the same name.

		Now, subsequent Apache requests for 

			http://www.example.com/images/products/marmot/300x300/speedascent2.jpg

		Will be satisfied normally with a nice Status 200 code.  

		Of course, any image resize (32x32, 20x16, ...) will be done the same way.  And,
		there may be a bunch of other image names too.  Once the resized folder, say 300x300 is 
		created, it will not need to be created again.

*/
include_once "./RelativePath.php";
define(IMAGE_DIR, $_SERVER['DOCUMENT_ROOT']);
umask(2);

class resize_missing_image {
	function __construct($image_dir)
	{
		$this->base_image_dir = $image_dir;
	}
	function image_failed()
	{
		header("Status: 404");	 //image not found
	}
	function clean_path($path)
	{
		return RelativePath::getRelativePath($path);
	}
	function is_resize_reasonable($w, $h)
	{
		return ( ($w >= 16) && ($w < 1200) && ($h >= 16) && ($h < 1200));
	}
	/*
		use your choice of image resizing 
	*/
	function resize_and_save($source, $target, $w, $h)
	{
		$size = "{$w}x{$h}";
		@mkdir( dirname($target), 0770 );
		$cmd = "convert -size $size -resize $size $source $target";
		system($cmd);
	}
	function main()
	{
		$request_rel_path_name  =
		$request_path		= $this->clean_path(rawurldecode($_SERVER['REQUEST_URI']));
/*
		list(
		  $_,
		  $request_rel_path_name
		)			= explode($this->base_image_dir, $request_path, 2);
/**/
		$request_base_name	= basename($request_rel_path_name);
		$request_dir_name	= dirname($request_rel_path_name);
		
		$source_image_file	= $this->clean_path($this->base_image_dir . "/" . dirname($request_dir_name) . "/$request_base_name");
		$target_image_file	= $this->clean_path($this->base_image_dir . "/$request_rel_path_name");
		
		$width_by_height	= basename( $request_dir_name );
		list($width, $height)	= explode('x', $width_by_height);
		$width			+= 0;
		$height			+= 0;
if (0) {
//phpinfo(); exit;
echo "<PRE>";
print_r(array($request_path,
$this->base_image_dir,
$request_rel_path_name,
$source_image_file,
$target_image_file,
));
exit;
}
		if ( file_exists( $source_image_file ) && $this->is_resize_reasonable($width, $height) ) {
			$this->resize_and_save($source_image_file, $target_image_file, $width, $height );

			header("Location: ".$_SERVER['REQUEST_URI']);
		} else {
			// FIXME?? 404 is appropriate for source file not found, but is it for "is_size_reasonable()" ???
			header("Status: 404");
		}
		exit;									// XXX exit XXX
	}
}
$resize_missing_image = new resize_missing_image(IMAGE_DIR);
$resize_missing_image->main();
