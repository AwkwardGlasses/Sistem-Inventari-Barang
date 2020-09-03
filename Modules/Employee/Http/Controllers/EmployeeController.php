<?php

namespace Modules\Employee\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Employee\Entities\Employee;
use Modules\Employee\Transformers\EmployeeTableResourceCollection;
use Modules\Employee\Transformers\EmployeeTableResource;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Modules\Address\Entities\Address;

class EmployeeController extends Controller
{
    #create : create-employee
    #view list : view-employee
    #show: view-profile || view-employee
    #delete : delete-employee
    #update profile(self): update-profile
    #update employee: update-employee

    protected $user = null;

    function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if ($this->user->can('view-employee')) {
            $employees = Employee::paginate();
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Successfully retrieve some employee data',
                    'data'    => new EmployeeTableResourceCollection($employees)
                ]
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
            'errors'  => ['You\'re prohibited to access this resource']
        ], 401);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if ($this->user->can('create-employee')) {
            #collect all incoming data
            $data = $request->all();
            #validate incoming request
            $this->validate($request, [
                'name'          => 'required|string',
                'email'         => 'required|email|unique:users,email',
                'phone_number'  => 'numeric',
                'position_id'   => 'required|numeric',
            ]);

            DB::beginTransaction();
            #create account
            $userData = $request->only(['name', 'email']);
            $user = new User;
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->password = Hash::make($userData['email']);

            if ($user->save()) {
                $data['user_id'] = $user->id;
                #store employee data
                $employee = Employee::create($data);
                if ($employee) {
                    #save to DB
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Created',
                        'data'    => new EmployeeTableResource($employee)
                    ], 201);
                }
            }

            #rollback db transaction
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unprocessable Entity',
                'errors'  => [
                    'Unable to create new record'
                ]
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
            'errors'  => ['You\'re prohibited to access this resource']
        ], 401);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if (!$this->user->can('view-employee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors'  => ['You\'re prohibited to access this resource']
            ], 401);
        }

        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Unprocessable Entity',
                'errors'  => ['No matched record found']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Found 1 mached record',
            'data'    => new EmployeeTableResource($employee)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->can('update-employee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors'  => ['You\'re prohibited to access this resource']
            ], 401);
        }

        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Unprocessable Entity',
                'errors'  => ['No record found']
            ], 422);
        }

        #validate incoming request
        $this->validate($request, array_merge([
            'name'          => 'required|string',
            // 'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'numeric',
            'position_id'   => 'required|numeric'
        ], Address::REQUEST_RULES));

        #collect all incoming data
        $data = $request->only(['name', 'phone_number', 'position_id']);

        DB::beginTransaction();
        #update employee data
        if (!$employee->update($data)) {
            #rollback db transaction
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unprocessable Entity',
                'errors'  => [
                    'An error occured while trying to update employee'
                ]
            ], 422);
        }

        #when any address id is given
        if ($address_id = $request->input('address_id')) {
            #check if the address exist
            if (!$address = Address::find($address_id)) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Unprocessable Entity',
                    'errors'  => ['Address doesn\'t exist']
                ], 422);
            }

            #update address associated with the employee
            $employee->address()->associate($address);
            // $employee->address_id = $address_id;
            // $employee->save();
        }
        #when no address id is given
        else {
            #collet all required address data
            $addressData = $request->only(array_keys(Address::REQUEST_RULES));

            #when the employee have no address yet
            if (!isset($employee->address->id)) {
                #create new address for the employee
                // $employee->address()->create($addressData);
                $address = Address::create($addressData);

                #associate address with the employee
                $employee->address()->associate($address);
            }
            #already has address
            else {
                #update related address data
                $employee->address()->update($addressData);

                // $current_address_id = $employee->address_id;
                // $current_address = Address::find($current_address_id);
                // $current_address->update($current_address);
            }
        }

        #save the updates
        if ($employee->save()) {
            DB::commit();
            $employee->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Updated',
                'data'    => new EmployeeTableResource($employee)
            ], 201);
        }

        #rollback db transaction
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Unprocessable Entity',
            'errors'  => [
                'An error occured while trying to update a record'
            ]
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete-employee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors'  => ['You\'re prohibited to access this resource']
            ], 401);
        }

        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Unprocessable Entity',
                'errors'  => ['No record found']
            ], 422);
        }

        DB::beginTransaction();
        if ($employee->delete()) {
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => '1 record deleted successfully',
                'data'    => new EmployeeTableResource($employee)
            ], 200);
        }

        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Unprocessable Entity',
            'errors'  => ['An error occured while trying to update post']
        ], 422);
    }
}
