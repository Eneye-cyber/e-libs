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
use App\Http\Resources\ShowBookResource;

/**
 * @OA\Tag(
 *     name="Books",
 *     description="API Endpoints for managing books"
 * )
 */
class BookController extends Controller
{
    use HttpResponses, FileHandler;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="List all books",
     *     operationId="index",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Pagination Page Number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BookResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Query failed")
     *         )
     *     )
     * )
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

    /**
     * @OA\Post(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="Create a new book",
     *     operationId="store",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreBookRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Book")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unable to store book information")
     *         )
     *     )
     * )
     */
    public function store(StoreBookRequest $request)
    {
        // Validate request 
        Log::info("Validate Create Book Request");
        $data = $request->all();
        [
            'title' => $title,
            'cover_image' => $cover_image,
        ] = $data;

        $imageUrl = null;
        $fileUrl = null;
        $book_file = isset($data['book_file']) ? $data['book_file'] : null;
        $author_id = isset($data['author_id']) ? $data['author_id'] : null;
        
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

    }

    /**
     * Display the specified resource.
     */
     /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     tags={"Books"},
     *     summary="Fetch a single book",
     *     operationId="show",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to fetch",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ShowBookResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Book not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        try {

            Log::info("Fetch specified book");
            $book = Book::with('author')->where('id', $id)->first();

            if ($book) {
                $book = new ShowBookResource($book);
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
    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     tags={"Books"},
     *     summary="Update a book",
     *     operationId="update",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateBookRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Book")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unable to update book information")
     *         )
     *     )
     * )
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

            $book->save();

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
    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     tags={"Books"},
     *     summary="Delete a book",
     *     operationId="destroy",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Query failed")
     *         )
     *     )
     * )
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

            return $this->success(['message' => 'Book deleted successfully.']);
            

        } catch (\Throwable $th) {
            Log::error([
                "message" =>  $th->getMessage(),
                "controller_action" => "BookController@destroy",
                "line" => $th->getLine()
            ]);
            return $this->error($th->getMessage(), 500);
        }
    }
}
