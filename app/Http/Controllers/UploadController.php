<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Traits\FileHandler;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UploadFileRequest;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    use HttpResponses, FileHandler;
    public function UpdateFile(UploadFileRequest $request)
    {
        $data = $request->all();

        if($data['group'] === 'author') {
            Log::info(["message" => "Change author avatar picture"]);
            return $this->UpdateProfilePicture($data);
        }

        return $this->UpdateBookMedia($data);
    }

    public function UpdateProfilePicture($data)
    {
        $image = $data['image'];
        $id = $data['id'];
        try {
            Log::info(["message" => "Find author"]);
            $author = Author::findOrFail($id);
            $slug = $author->slug;
            
            $this->deleteFile($author->profile_image);
            
            Log::info(["message" => "Upload new image"]);
            $imageUrl = $this->uploadFile($image, $slug, 'avatar');
            $author->profile_image = $imageUrl;

            $author->save();

            return $this->success(["message" => "Image Upload Successful", "author" => $author]);

        } catch (\Throwable $exception) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "UploadController@UploadFile",
                "line" => $exception->getLine()
            ]);
            return $this->error('Server Error', 503);
        }
    }

    public function UpdateBookMedia($data)
    {
        $image = $data['image'] ?? null;
        $file = $data['book'] ?? null;
        $id = $data['id'];

        try {
            Log::info(["message" => "Find Book"]);
            $book = Book::findOrFail($id);
            $slug = Str::slug($book->title);

            if(isset($image)) {
                Log::info(["message" => "Delete cover image"]);
                $this->deleteFile($book->cover_image);
                $book->cover_image = $this->uploadFile($image, $slug, 'covers');
            } 

            if(isset($file)) {
                Log::info(["message" => "Delete book file"]);
                $this->deleteFile($book->book_file);
                $book->book_file = $this->uploadFile($file, $slug, 'books');
            }

            $book->save();

            return $this->success(["message" => "Book Media Updated Successfully", "book" => $book]);

        } catch (\Throwable $exception) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "UploadController@UpdateBookMedia",
                "line" => $exception->getLine()
            ]);
            return $this->error('Server Error', 503);
        }
    }

}
