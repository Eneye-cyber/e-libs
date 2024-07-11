<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Traits\FileHandler;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Enums\BookStatusEnum;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BookCollection;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    use HttpResponses, FileHandler;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $pageSize = $request->page_size ?? 20;

        try {
            Log::info("Fetch paginated Books Collection");
            $query = Book::query()->paginate($pageSize);

            return new BookCollection($query);

        } catch (\Throwable $th) {
            Log::error([
                "message" => 'Failed to retrieve books collection',
                "controller_action" => 'BookController@index',
                "cause" => $th->getMessage(),
            ]);
            return $this->error('Server error', 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        // Validate request 
        Log::info("Validate Create Book Request");
        $data = $request->all();
        [
            'title' => $title,
            'cover_image' => $cover_image,
            'author_id' => $author_id,
        ] = $data;

        $imageUrl = null;
        $fileUrl = null;
        $book_file = isset($data['book_file']) ? $data['book_file'] : null;
        
        try {
            Log::info("Store book cover picture");
            // Generate slug and store files
            $slug = Str::slug($title);
            $imageUrl = $this->uploadFile($cover_image, $slug, 'covers');
            $fileUrl = is_null($book_file) ? null 
                : $this->uploadFile($book_file, $slug, 'books');

            // Update data parameters
            $data['cover_image'] = $imageUrl;
            $data['book_file'] = $fileUrl;
            $data['status'] = is_null($fileUrl) ? 'Unavailable' : $data['status'] ;

            if(isset($author_id)) {
                $query = Author::findOrFail($author_id)
                    ->books()
                    ->create($data);
                
                return $this->success($query);
            }

            $query = Book::create($data);

            return $this->success($query);
        } catch (\Throwable $exception) {
            $this->deleteFile($imageUrl);
            $this->deleteFile($fileUrl);
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "BookController@store",
                "line" => 87
            ]);
            return $this->error('Unable to store book information', 500);
        }

        return $this->error('Something went wrong', 503);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            Log::info("Fetch specified book");
            $book = Book::with('author')->where('id', $id)->firstOrFail();

            if ($book) {
                return $this->success($book);
            }
            Log::error([
                "message" => 'Book not found',
                "line" => 60
            ]);
            return $this->error('Book not found in our database', 404);
            
        } catch (\Throwable $th) {
            Log::error([
                "message" => 'Failed to find book',
                "controller_action" => "BookController@show",
                "cause" => $th->getMessage(),
            ]);
            return $this->error('Server error', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, string $id)
    {
        // Validate request 
        Log::info("Validate Update Book Request");
        $data = $request->all();

        $book_file = $data['book_file'] ?? null;
       
        try {
       
            $book = Book::findOrFail($id);
            if(is_null($book_file) && is_null($book->book_file)) {
                $data['status'] = BookStatusEnum::UNAVAILABLE;
            }
            $book = $book->fill($data);

            return $this->success($book);
       
        } catch (\Throwable $exception) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "BookController@update",
                "line" => $exception->getLine()
            ]);
            return $this->error("Unable to update book information", 500);
        }

        return $this->error("Something went wrong", 503);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $book = Book::findOrFail($id);
            // Delete the Book
            Log::info(["message" => "Delete Book {$book->title}"]);
            // Delete Image and file
            Log::info(["message" => "Delete Book Cover"]);
            $this->deleteFile($book->cover_image);
            Log::info(["message" => "Delete Book file"]);
            $this->deleteFile($book->book_file);
            $book->delete();

            return response()->json(['message' => 'Book deleted successfully.'], 200);
            

        } catch (\Throwable $th) {
            Log::error([
                "message" =>  $exception->getMessage(),
                "controller_action" => "BookController@destroy",
                "line" => $exception->getLine()
            ]);
            return $this->error($exception->getMessage(), 500);
        }
    }
}
