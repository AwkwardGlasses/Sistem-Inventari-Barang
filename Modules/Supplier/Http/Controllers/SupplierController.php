<?php

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Address\Entities\Address;
use Modules\Supplier\Entities\Supplier;
use Modules\Supplier\Transformers\SupplierTableResource;
use Modules\Supplier\Transformers\SupplierTableResourceCollection;

class SupplierController extends Controller
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
        if($this->user->can('view-vendor')){
            $vendors = Supplier::paginate();
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieve '.count($vendors).' vendor',
                'data' => new SupplierTableResourceCollection($vendors)
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
        return view('Supplier::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if($this->user->can('create-vendor')){
            // collect all incoming data
            $data = $request->only(['name', 'code', 'email', 'contact']);
            // create new employee
            $this->validate($request, array_merge([
                'name' => 'required|string',
                'code' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'contact' => 'required|numeric'
            ], Address::REQUEST_RULES));

            // dibawah itu nyimpan sekalian ke table user
            // DB itu kalau gagal tidak dijalankan
            DB::beginTransaction();
            #store vendor data
            $vendor = Supplier::create($data);
            if($vendor){
                $addressData = $request->only(array_keys(Address::REQUEST_RULES));
                // $vendor->address()->create($addressData);
                // $vendor->save();

                if(!$address = Address::create($addressData)) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Unprocesable entity',
                        'errors' => [
                            'Unable to create new record'
                        ]
                    ], 422);
                }

                $vendor->address()->associate($address);
                $vendor->save();
                #save to database
                DB::commit();
                $vendor->refresh();

                return response()->json([
                    'success' => true,
                    'message' => 'Create',
                    'data' => new SupplierTableResource($vendor)
                ], 201);
            }

            #rollback db transaction
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unprocesable entity',
                'errors' => [
                    'Unable to create new record'
                ]
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
            'errors' => ['You\re unauthorized to access this resource']
        ], 401);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if($this->user->can('view-vendor')) {
            $vendor = Supplier::find($id);
            if($vendor){
                return response()->json([
                    'success' => true,
                    'message' => 'Vendor successfully found',
                    'data' => new SupplierTableResource($vendor)
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
        return view('Supplier::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, array_merge([
            'name' => 'required|string',
            'code' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'contact' => 'required|numeric'
        ], Address::REQUEST_RULES));

        if($this->user->can('update-vendor')) {
            $name = $request->input('name');
            $code = $request->input('code');
            $email = $request->input('email');
            $contact = $request->input('contact');

            $vendor = Supplier::find($id);
            $vendor->name = $name;
            $vendor->code = $code;
            $vendor->email = $email;
            $vendor->contact = $contact;

            $addressData = $request->only(array_keys(Address::REQUEST_RULES));
            if($address_id = $request->input('address_id')) {
                if (!$address = Address::find($address_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Can\'t update vendor',
                        'errors' => 'There\'s no address with the corresponding id'
                    ]);
                }

                $vendor->address()->associate($address);
            }
            else {
                $vendor->address()->update($addressData);
            }

            if($vendor->save()) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor successfully updated',
                    'data' => new SupplierTableResource($vendor)
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
        // if($this->user->can('delete-vendor')) {
            $vendor = Supplier::find($id);

            if($vendor) {
                $vendor->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor successfully deleted',
                    'data' => new SupplierTableResource($vendor)
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Can\'t find id',
                'errors' => 'There\'s no vendor with the corresponding id'
            ]);
        // }

        // return response()->json([
        //     'success' => false,
        //     'message' => 'Unauthorized',
        //     'errors' => ['You\re unauthorized to access this resource']
        // ], 401);
    }
}
