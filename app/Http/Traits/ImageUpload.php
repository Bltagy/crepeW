<?php
namespace App\Http\Traits;
trait ImageUpload {

	public function adminUpload($image, $dir) {
		$image = $image['fileList'][0]['thumbUrl'];
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/' . $dir . '/') . $name);
			$uploadedimage = $name;
			return $uploadedimage;
		}else{
			return false;
		}
	}
}
