<?php

namespace App\Services\Files;

use Illuminate\Support\Facades\Storage;

class FileService{



public function deleteFile($disk,$path) {

    if (!Storage::disk($disk)->exists($path)) {

        return false;

      }

return Storage::disk($disk)->delete($path);


}

}