<?php

namespace Modules\Category\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\Category\Entities\Category;
use Modules\Category\Transformers\CategoryTableResourceCollection;
use Modules\Category\Transformers\CategoryTableResource;

class CategoryController extends Controller
{
    #create : create-category
    #view list : view-category
    #show: view-category
    #delete : delete-category
    #update category: update-category

    /**
     * Display a listing of the resource.
     * @return Response
     */
    
    protected $user = null;
    
    public function __construct(){
        $this->user = auth()->user();        
    }

    public function index()
    {
        if($this->user->can('view-category')){
            $categories = Category::paginate();
            return response()->json(
                [
                    'success'   =>true,
                    'message'   =>'successfully retrieve some category data',
                    'data'      => new CategoryTableResourceCollection($categories)
                ], 200
            );
        }

        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('category::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if($this->user->can('create-category')){
            $data = $request->all();
            $this->validate($request, [
                'name' => 'required|string|max:100',
                'description' => 'required|max:255'
            ]);

            #create category
            $categoryData = $request->only('name','description');
            $category = new Category;
            $category->name = $categoryData['name'];
            $category->description = $categoryData['description'];
            
            #store category data
            if($category->save()){
                return response()->json([
                    'success'   => true,
                    'message'   => 'Created',
                    'data'      => new CategoryTableResource($category)
                ], 201);
            }
            return response()->json([
                'success'   => false,
                'message'   => 'Unprocesable entity',
                'errors'      => [
                    'Unable to create new record'
                ]
            ], 422);
        }
        
        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if($this->user->can('view-category')){
            $category = Category::find($id);
            if(!$category){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }
            return response()->json([
                "success" => true,
                "message" => "successfully retrieve category data",
                "data" => new CategoryTableResource($category),
            ]);
        } //end of if permission
        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('category::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if($this->user->can('update-category')){
            $this->validate($request, [
                'name' => 'required|string|max:100',
                'description' => 'required|max:255'
            ]);

            #update category
            $data = $request->only('name','description');
            
            $category = Category::find($id);
            if(!$category){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }
            
            $category['name'] = $data['name'];
            $category['description'] = $data['description'];
            
            if ($category->save()) {
                return response()->json([
                    "success" => true,
                    "message" => "Category ".$category->id." has been updated",
                    "data"    => new CategoryTableResource($category)
                ]); 
            }
            return response()->json([
                'success'   => false,
                'message'   => "Unprocessable Entry",
                'errors'    => ["Can't update Category"],
            ], 222);
        } //end of if permission
        return response()->json(
            [
                'success'   =>false,
                'message'   =>'Unauthorized',
                'errors'    => 'You\'re prohibited to access this resource'
            ], 401
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        // if($this->user->can('delete-category')){
            $category = Category::find($id);
            if(!$category){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }

            $category->delete();
            return response()->json([
                "status"  => true,
                "message" => "Delete success",
                "data"    => $category
            ]);
        // } //end of permission
        // return response()->json(
        //     [
        //         'success'   =>false,
        //         'message'   =>'Unauthorized',
        //         'errors'    => 'You\'re prohibited to access this resource'
        //     ], 401
        // );
    }
}
