<?php

namespace Modules\Asset\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\Asset\Entities\Asset;
use Modules\Asset\Transformers\AssetTableResourceCollection;
use Modules\Asset\Transformers\AssetTableResource;
use Modules\Category\Entities\Category;

class AssetController extends Controller
{
    #create : create-asset
    #view list : view-asset
    #show: view-asset
    #delete : delete-asset
    #update category: update-asset

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
        if($this->user->can('view-asset')){
            $assets = Asset::paginate();
            return response()->json(
                [
                    'success'   =>true,
                    'message'   =>'successfully retrieve some asset data',
                    'data'      => new AssetTableResourceCollection($assets)
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
        return view('asset::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if($this->user->can('create-asset')){
            $data = $request->all();
            $this->validate($request, [
                'name' => 'required|string|max:100',
                'description' => 'required|max:255',
                'category_id'=>'required'
            ]);

            #create asset
            $assetData = $request->only('name','description','category_id');
            $asset = new Asset;
            $asset->name = $assetData['name'];
            $asset->description = $assetData['description'];
            $asset->category_id = $assetData['category_id'];
            
            #store asset data
            if($asset->save()){
                return response()->json([
                    'success'   => true,
                    'message'   => 'Created',
                    'data'      => new AssetTableResource($asset)
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
        if($this->user->can('view-asset')){
            $asset = Asset::find($id);
            if(!$asset){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }
            return response()->json([
                "success" => true,
                "message" => "successfully retrieve asset data",
                "data" => new AssetTableResource($asset),
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
        return view('asset::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if($this->user->can('update-asset')){
            $this->validate($request, [
                'name' => 'required|string|max:100',
                'description' => 'required|max:255',
                'category_id'=>'required'
            ]);

            #update asset
            $data = $request->only('name','description','category_id');
            
            $asset = Asset::find($id);
            if(!$asset){
                return response()->json([
                    "message" => "unprocessable entity",
                    "errors"    => [
                        "Can't find record with id ".$id
                    ]
                ], 422);
            }

            if (!Category::find($data['category_id'])) {
                return response()->json([
                    'success'   => false,
                    'message'   => "Unprocessable Entry",
                    'errors'    => ["Can't update asset"],
                ], 422);
            }
            
            // $asset['name'] = $data['name'];
            // $asset['description'] = $data['description'];
            // $asset['category_id'] = $data['category_id'];

            if ($asset->update($data)) {
                return response()->json([
                    "success" => true,
                    "message" => "Asset ".$asset->id." has been updated",
                    "data"    => new AssetTableResource($asset)
                ]); 
            }
            return response()->json([
                'success'   => false,
                'message'   => "Unprocessable Entry",
                'errors'    => ["Can't update asset"],
            ], 222);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $asset = Asset::find($id);
        if(!$asset){
            return response()->json([
                "message" => "unprocessable entity",
                "errors"    => [
                    "Can't find record with id ".$id
                ]
            ], 422);
        }

        $asset->delete();
        return response()->json([
            "status"  => true,
            "message" => "Delete success",
            "data"    => $asset
        ]);
    }
}
