<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Entities\SupplierPic;
use Modules\Supplier\Transformers\SupplierPicTableResource;
use Modules\Supplier\Transformers\SupplierPicTableResourceCollection;

class SupplierPicController extends Controller
{
    protected $user = null;

    public function __construct()
    {
        $this->user = auth()->user();
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if($this->user->can('view-vendor-pic')){

            $vendor_pics = SupplierPic::paginate();

            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieve '.count($vendor_pics).' vendor pics',
                'data' => new SupplierPicTableResourceCollection($vendor_pics)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
            'errors' => ['You\re unauthorized to access this resource']
        ], 401);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('vendor::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if($this->user->can('create-vendor-pic')){       //
            // collect all incoming data
            $data = $request->all();
            // create new employee
            $this->validate($request, [
                'supplier_id' => 'required|numeric',
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'phone' => 'required|numeric',
                'address_id' => 'numeric',
                'active' => 'required|boolean'
            ]);

            $vendor_pic = SupplierPic::create($data);

            // Employee::create eloquent method
            if($vendor_pic){
                #save to database
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Create',
                    'data' => new SupplierPicTableResource($vendor_pic)
                ], 201);
            }

        return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => ['You\re unauthorized to access this resource']
            ], 401);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if($this->user->can('view-vendor-pic')) {
            $vendor_pic = SupplierPic::find($id);
            // return $vendor;
            if($vendor_pic){
                return response()->json([
                    'success' => true,
                    'message' => 'Vendor successfully found',
                    'data' => new SupplierPicTableResource($vendor_pic)
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
                'errors' => 'There\'s no vendor with the corresponding id'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('vendor::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if($this->user->can('update-vendor-pic')) {
            $data = $request->all();
            // create new employee
            $this->validate($request, [
                'supplier_id' => 'required|numeric',
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'phone' => 'required|numeric',
                'address_id' => 'numeric',
                'active' => 'required|boolean'
            ]);

            $vendor_pic = SupplierPic::find($id);

            $vendor_pic->supplier_id = $data['supplier_id'];
            $vendor_pic->name = $data['name'];
            $vendor_pic->email = $data['email'];
            $vendor_pic->phone = $data['phone'];
            $vendor_pic->active = $data['active'];
            if($data['address_id'] != null) {
                $vendor_pic->address_id = $data['address_id'];
            }
            $vendor_pic->save();

            if($vendor_pic->save()) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor successfully updated',
                    'data' => new SupplierPicTableResource($vendor_pic)
                ], 201);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Can\'t update vendor',
            'errors' => 'There\'s no vendor with the corresponding id'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        // if($this->user->can('delete-vendor-pic')) {
            $vendor_pic = SupplierPic::find($id);

            if($vendor_pic) {
                $vendor_pic->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor pic successfully deleted',
                    'data' => new SupplierPicTableResource($vendor_pic)
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Can\'t find id',
                'errors' => 'There\'s no vendor pic with the corresponding id'
            ]);
        // }
    }
}
