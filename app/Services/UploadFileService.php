<?php

namespace App\Services;

use Illuminate\Support\Facades\File as File;
use Intervention\Image\Facades\Image as Image;
use Illuminate\Support\Str;

class  UploadFileService
{

    public static function uploadImg($files, $url = 'images', $key = 'image', $width = null, $height = null)
    {
        $dist = storage_path('app/public/' . $url . "/");
        if ($url != 'images' && !File::isDirectory(storage_path('app/public/images/' . $url . "/"))) {
            File::makeDirectory(storage_path('app/public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $url . DIRECTORY_SEPARATOR), 0777, true);
            $dist = storage_path('app/public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $url . DIRECTORY_SEPARATOR);
        } elseif (File::isDirectory(storage_path('app/public/images/' . $url . "/"))) {
            $dist = storage_path('app/public/images/' . $url . "/");
        }
        $image = "";
        if (!is_array($files)) {
            $dim = getimagesize($files);
            $width = $width ?? $dim[0];
            $height = $height ?? $dim[1];
        }

        if (gettype($files) == 'array') {
            $image = [];
            foreach ($files as $img) {
                $dim = getimagesize($img);
                $width = $width ?? $dim[0];
                $height = $height ?? $dim[1];

                if ($img && $dim['mime'] != "image/gif") {
                    Image::make($img)->resize($width, $height, function ($cons) {
                        $cons->aspectRatio();
                    })->save($dist . $img->hashName());
                    $image[][$key] = $img->hashName();
                } elseif ($img && $dim['mime'] == "image/gif") {
                    $image = self::uploadGIFImg($img, $dist);
                }
            }
        } elseif ($dim && $dim['mime'] == "image/gif") {
            $image = self::uploadGIFImg($files, $dist);
        } else {
            Image::make($files)->resize($width, $height, function ($cons) {
                $cons->aspectRatio();
            })->save($dist . $files->hashName());
            $image = $files->hashName();
        }
        return $image;
    }

    public  static function uploadGIFImg($gif_image, $dist)
    {
        $file_name = Str::uuid() . "___" . $gif_image->getClientOriginalName();
        if ($gif_image->move($dist, $file_name)) {
            return $file_name;
        }
    }

    public static function uploadFile($files, $url = 'files', $key = 'file', $model = null)
    {
        $dist = storage_path('app/public/' . $url);
        if ($url != 'images' && !File::isDirectory(storage_path('app/public/files/' . $url . "/"))) {
            File::makeDirectory(storage_path('app/public' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $url . DIRECTORY_SEPARATOR), 0777, true);
            $dist = storage_path('app/public' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $url . DIRECTORY_SEPARATOR);
        } elseif (File::isDirectory(storage_path('app/public/files/' . $url . "/"))) {
            $dist = storage_path('app/public/files/' . $url . "/");
        }
        $file = '';

        if (gettype($files) == 'array') {
            $file = [];
            foreach ($files as $new_file) {
                $file_name = time() . "___file_" . $new_file->getClientOriginalName();
                if ($new_file->move($dist, $file_name)) {
                    $file[][$key] = $file_name;
                }
            }
        } else {
            $file = $files;
            $file_name = time() . "___file_" . $file->getClientOriginalName();
            if ($file->move($dist, $file_name)) {
                $file =  $file_name;
            }
        }

        return $file;
    }
}
