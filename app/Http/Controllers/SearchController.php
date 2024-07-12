<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BookResource;
use App\Http\Resources\AuthorResource;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    use HttpResponses;
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:255',
        ]);

        if ($validator->fails()){
            Log::info(["message" => "Search Validation Failed"]);
            return $this->error($validator->errors()->toJson(), 400);
        }

        $query = $request->input('query');
        
        try {
            Log::info(["message" => "Search Book title"]);
            $books = Book::where('title', 'LIKE', "%$query%")->get();

            Log::info(["message" => "Search Author names"]);
            $authors = Author::where('first_name', 'LIKE', "%{$query}%")
                            ->orWhere('last_name', 'LIKE', "%{$query}%")
                            ->get();
            
            return $this->success([
                'books' => BookResource::collection($books),
                'authors' => AuthorResource::collection($authors),
            ]);

        } catch (\Throwable $exception) {
            Log::error([
                'message' => $exception->getMessage(),
                'controller_action' => 'SearchController@search',
                'line' => $exception->getLine(),
            ]);

            return $this->error("Query failed", 500);
            ;
        }
    }
}
