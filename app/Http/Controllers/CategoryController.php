<?php


namespace App\Http\Controllers;


use App\Actions\FileAction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = auth()->guard('api')->user();
        $this->authorize(['admin']);
    }

    public function createCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make( $request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'file' => 'required|mimes:jpeg,jpg,png'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);
        }

        Category::create([
            'name'=>$request->input('name'),
            'description'=>$request->input('description'),
            'image'=>FileAction::upload($request)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category created',
            'data' => []
        ]);
    }

    public function editCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make( $request->all(), [
            'category_id' => 'required|int|exists:categories,id',
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'file' => 'nullable|mimes:jpeg,jpg,png'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);
        }

        $category = Category::find($request->category_id);
        $category->name = $request->name ?? $category->name;
        $category->description = $request->description ?? $category->description;
        $category->image = ($request->file )? FileAction::upload($request): $category->image;

        return response()->json([
            'status' => true,
            'message' => 'Category updated',
            'data' => []
        ]);
    }
}
