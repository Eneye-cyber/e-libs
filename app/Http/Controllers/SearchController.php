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

/**
 * @OA\Tag(
 *     name="Search",
 *     description="API Endpointfor searching books and authors"
 * )
 */
class SearchController extends Controller
{
    use HttpResponses;
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search books and authors",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Query string for search",
     *         @OA\Schema(type="string")
     *     ),
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful search",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="books", type="array", @OA\Items(ref="#/components/schemas/BookResource")),
     *                 @OA\Property(property="authors", type="array", @OA\Items(ref="#/components/schemas/AuthorResource"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message")
     *         )
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
