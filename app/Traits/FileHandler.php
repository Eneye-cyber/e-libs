<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileHandler {
    /**
     * Uploads Images to a spcified directory and returns the image url
     * @param  object  $file
     * @param  string  $disk
     * @param  string  $directory
     * @return string  The URL of the uploaded file.
     */
    protected function uploadFile(object $file, $name = null, $directory = 'uploads') : string
    {
        $disk = 'public';
        $fileName = $name ?? Str::random(10);
        $fileName =  $fileName. '.' . $file->getClientOriginalExtension();

        // Store the file
        Log::info(["message" => "Store file {$fileName} in {$directory}"]);
        $filePath = $file->storeAs($directory, $fileName, $disk);

        // Return the file URL
        return Storage::disk($disk)->url($filePath);
    }

    /**
     * Delete a file from the specified disk.
     *
     * @param  string  $fileUrl
     * @param  string  $disk
     * @return bool  True if the file was deleted, false otherwise.
     */
    protected function deleteFile(string $fileUrl, string $disk = 'public') : bool
    {
        if(is_null($fileUrl)){
            return false;
        }
        
       // Parse the URL to get the file path
       $filePath = parse_url($fileUrl, PHP_URL_PATH);

       // Remove the leading slash from the file path if necessary
       $filePath = ltrim($filePath, '/');

       Log::info(["message" => "Delete {$fileUrl} from {$filePath}"]);

       return Storage::disk($disk)->delete($filePath);
    }

}
?>