<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BookCollection;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    use HttpResponses;
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            Log::info("Fetch specified book");
            $book = Book::find($id);

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
    public function update(UpdateBookRequest $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }
}
