<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Traits\FileHandler;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AuthorCollection;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;


/**
 * @OA\Tag(
 *     name="Authors",
 *     description="API Endpoints for managing authors"
 * )
 */
class AuthorController extends Controller
{
    use HttpResponses, FileHandler;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/authors",
     *     tags={"Authors"},
     *     summary="Get paginated list of authors",
     *     description="Fetches a paginated list of authors.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AuthorResource")),
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
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        //
        $pageSize = $request->page_size ?? 20;
        try {
            Log::info("Fetch paginated Authors Collection");
            $query = Author::query()->paginate($pageSize);

            return new AuthorCollection($query);

        } catch (\Throwable $th) {
            Log::error(["message" => 'Failed to retrieve author collection']);
            return $this->error('Server error', 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/api/authors",
     *     tags={"Authors"},
     *     summary="Create a new author",
     *     description="Creates a new author.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Author data",
     *         @OA\JsonContent(ref="#/components/schemas/StoreAuthorRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Author")
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
     *             @OA\Property(property="message", type="string", example="Unable to save author")
     *         )
     *     )
     * )
     */
    public function store(StoreAuthorRequest $request)
    {
        // Validate request 
        Log::info("Validate Create Author Request");
        $request->validated();
        $data = $request->all();
        [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'profile_image' => $image,
            'slug' => $slug
        ] = $data;

        $imageUrl = null;

        try {
            Log::info("Store Author profile picture");

            $imageUrl = $this->uploadFile($image, $slug, 'avatar');
            $data['profile_image'] = $imageUrl;

            Log::info("Create Author Model");
            $author = Author::create($data);

            return $this->success($author);
        } catch (\Throwable $exception) {
            $deleteStatus = $this->deleteFile($imageUrl);
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "AuthorController@store",
                "line" => 72
            ]);
            return $this->error('Unable to save author', 500);
        }

        return $this->error('Something went wrong', 503);

    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/authors/{id}",
     *     tags={"Authors"},
     *     summary="Get a specific author",
     *     description="Fetches details of a specific author.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Author")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author not found")
     *         )
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
    public function show(String $id)
    {
        try {
            $author = Author::with('books')->where('id', $id)->first();

            if ($author) {
                return $this->success($author);
            }
                
            return $this->error('Author not found', 404);
            
        } catch (\Throwable $th) {
            return $this->error('Server error', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
        /**
     * @OA\Put(
     *     path="/api/authors/{id}",
     *     tags={"Authors"},
     *     summary="Update an existing author",
     *     description="Updates details of an existing author.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Author data",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAuthorRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Author")
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
     *             @OA\Property(property="message", type="string", example="Something Went Wrong")
     *         )
     *     )
     * )
     */
    public function update(UpdateAuthorRequest $request, string $id)
    {
        
        $data = $request->all();

        try {
            
            Log::info(["message"=> "Checking if author exists"]);
            $author = Author::findOrFail($id);
            $author = $author->fill($data);

            $name = "$author->first_name $author->last_name";
            $slug = Str::slug($name);
            Log::info(["message"=> "Verify Slug $slug is $author->slug"]);
            $author->slug = $author->slug === $slug ? $author->slug : $slug;

            Log::info(["message"=> "save info"]);
            $author->save();
            return $this->success($author);

        } catch (\Throwable $exception) {
            Log::error([
                "message" =>  $exception->getMessage(),
                "controller_action" => "AuthorController@update",
                "line" => 125
            ]);
            return $this->error("Something Went Wrong", 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/authors/{id}",
     *     tags={"Authors"},
     *     summary="Delete an author",
     *     description="Deletes an existing author.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Author deleted successfully.")
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
    public function destroy(string $id)
    {

        try {
            $author = Author::findOrFail($id);
            // Delete Image
            $this->deleteFile($author->profile_image);
            
            // Delete the user
            Log::info(["message" => "Delete author {$author->getFullName()}"]);
            $author->delete();
            return $this->success(['message' => 'Author deleted successfully.']);

            

        } catch (\Throwable $th) {
            Log::error([
                "message" =>  $exception->getMessage(),
                "controller_action" => "AuthorController@destroy",
                "line" => 149
            ]);
            return $this->error($exception->getMessage(), 500);
        }
        
    }
}
