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

class AuthorController extends Controller
{
    use HttpResponses, FileHandler;
    /**
     * Display a listing of the resource.
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
    public function show(String $id)
    {
        try {
            $author = Author::find($id);

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
    public function update(UpdateAuthorRequest $request, string $id)
    {
        
        $data = $request->all();

        try {
            
            Log::info(["message"=> "Checking if author exists"]);
            $author = Author::findOrFail($id);
            $author = $author->fill($data);

            $name = "{$author->first_name} {$author->last_name}";
            $slug = Str::slug($name);
            $author->slug = $author->slug === $slug ? $author->slug : $slug;

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
    public function destroy(string $id)
    {

        try {
            $author = Author::findOrFail($id);
            // Delete the user
            Log::info(["message" => "Delete author {$author->getFullName()}"]);
            // Delete Image
            if($this->deleteFile($author->profile_image)) {
                $author->delete();
                return response()->json(['message' => 'Author deleted successfully.'], 200);
            }

            throw new Exception("Unable to delete author information", 1);
            

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
