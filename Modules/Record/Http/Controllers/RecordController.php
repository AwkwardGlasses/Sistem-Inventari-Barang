<?php

namespace Modules\Record\Http\Controllers;

// karena di controller sudah ada validate request
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Record\Entities\Record;
use Modules\Record\Transformers\RecordTableResource;
use Modules\Record\Transformers\RecordTableResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
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
        if(!$this->user->can('view-record')) {
            return $this->unauthorizedResponse([
                'error' => 'Ra entuk ndes'
            ]);
        }

        $records = Record::paginate();

        return $this->okResponse([
            'data' => new RecordTableResourceCollection($records)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('record::create');
    }

    private function generateRefCode() {
        $num = implode("-", Arr::shuffle([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]));
        return implode("-", ["RC", $num, Str::random(4)]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if(!$this->user->can('create-record')) {
            $this->unauthorizedResponse([
                'error' => 'Ra entuk ndes'
            ]);
        }

        $this->validate($request, Record::REQ_RULES);

        $data = $request->only(array_keys(Record::REQ_RULES));
        $data['ref_code'] = $this->generateRefCode();

        $record = Record::create($data);

        if(!$record) {
            DB::rollback();
            return $this->unprocesableEntity([
                'error' => ['Unable to create new record']
            ]);
        }


        return $this->createdResponse([
            'data' => new RecordTableResource($record)
        ]);
    }

    public function storeBatch(Request $request) {
        if(!$this->user->can('create-record')) {
            $this->unauthorizedResponse([
                'error' => 'Ra entuk ndes'
            ]);
        }

        $this->validate($request, array_merge(
            Record::REQ_RULES,
            ['count' => 'required|numeric']
        ));

        $current_time = Carbon::now();
        $data = $request->only(array_keys(Record::REQ_RULES));
        $data['ref_code'] = $this->generateRefCode();
        $data['created_at'] = $current_time;
        $data['arrival_date'] = $current_time;

        $count = intval($request->input('count') ?? 0);

        DB::beginTransaction();

        $records = [];
        for ($i=0; $i<$count; $i++) {
            $records[$i] = Record::create($data);
        }

        if(count($records) !== $count) {
            DB::rollback();
            return $this->unprocesableEntity([
                'error' => ['Unable to create new record']
            ]);
        }

        DB::commit();
        return $this->createdResponse([
            'data' => RecordTableResource::collection($records)
        ]);
    }

    public function updateBatch(Request $request) {
        if(!$this->user->can('update-record')) {
            return $this->unauthorizedResponse();
        }

        // dibawah kalau recordsId null maka array kosongan
        $recordsId = $request->input('id') ?? [];
        // explode = string jadi array
        // implode = array jadi string
        if(is_string($recordsId)) {
            $recordsId = explode(",", $recordsId);
        }

        // dibawah itu query
        // select ref_code from records where id IN [1,2,3]
        $refCodesByid = Record::whereIn('id', $recordsId)->pluck('ref_code')->toArray();

        $refCode = $request->input('ref_code');

        $useId = count($recordsId) > 0;
        // dibawah ini array unique berarti kalau ada yg beda berarti lebih dari 1
        $refCodeIsDifferent = count(array_unique($refCodesByid)) !== 1;

        $noIdOrRefCordGiven = !$refCode && count($recordsId) <= 0;
        $useIdButTheRefCodeAreDifferent = $useId && $refCodeIsDifferent;

        if($useId && !$refCode) {
            $refCode = $refCodesByid[0];
        }

        if($noIdOrRefCordGiven || $useIdButTheRefCodeAreDifferent || !$refCode) {
            return $this->unprocesableEntity([
                'errors' => ['Unable to process the request']
            ]);
        }

        // dibawah ambil jumlah nya, count()
        $recordsByRefCode = Record::where('ref_code', $refCode)->count();
        $notAllSelected = $useId && !$refCode && intval($refCodesByid) !== count($recordsId);

        if (!$recordsByRefCode || $notAllSelected) {
            return $this->unprocesableEntity([
                'errors' => ['Unable to process the request']
            ]);
        }

        $this->validate($request, Record::REQ_RULES);
        $data = $request->only(array_keys(Record::REQ_RULES));

        DB::beginTransaction();
        if(!Record::where('ref_code', $refCode)->update($data)) {
            DB::rollback();
            return $this->unprocesableEntity([
                'errors' => ['Something went wrong while trying to update a record']
            ]);
        }

        DB::commit();
        return $this->okResponse();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if(!$this->user->can('view-record')) {
            return $this->unauthorizedResponse();
        }

        if(!$record = Record::find($id)) {
            return $this->unprocesableEntity([
                'errors' => ["Can't find record with id". $id]
            ]);
        }

        $record = Record::find($id);

        return $this->okResponse([
            'data' => new RecordTableResource($record)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('record::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if(!$this->user->can('update-record')) {
            return $this->unauthorizedResponse();
        }

        if(!$record = Record::find($id)) {
            return $this->unprocesableEntity([
                'errors' => ["Can't find record with id". $id]
            ]);
        }

        $this->validate($request, Record::REQ_RULES);

        $data = $request->only(array_keys(Record::REQ_RULES));
        DB::beginTransaction();
        if(!$record->update($data)) {
            return $this->unprocesableEntity([
                'errors' => ['Something went wrong while trying to update a record']
            ]);
        }

        DB::commit();
        return $this->okResponse([
            'data' => new RecordTableResource($record)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        /* if (!$this->user->can('delete-record')) {
            return $this->unauthorizedResponse();
        }

        $record = Record::find($id);

        if(!$record = Record::find($id)) {
            return $this->unprocesableEntity([
                'errors' => ["Can't find record with id". $id]
            ]);
        }

        if ($record->delete()) {
            DB::commit();
            return $this->okResponse([
                'data' => new RecordTableResource($record)
            ]);
        } */
    }
}
