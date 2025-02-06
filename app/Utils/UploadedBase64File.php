<?php
namespace App\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedBase64File extends UploadedFile
{
	public function __construct(string $base64Content, $path)
	{
		$extension = explode('/', mime_content_type($base64Content))[1];
		$data = base64_decode($this->getBase64String($base64Content));
		$filename = strtotime('now') . '.' . $extension;
		file_put_contents($path . '/'. $filename, $data);
		$error = null;
		$mimeType = null;
		parent::__construct($path . '/' . $filename, $filename, $mimeType, $error);
	}

	private function getBase64String(string $base64Content): string
	{
		$data = explode(';base64,', $base64Content);
		return $data[1];
	}

}