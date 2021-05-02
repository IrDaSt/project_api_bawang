<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MataPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return MataPelajaran::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }
        // $request->validate([
        //     'name' => 'required',
        //     'description' => 'required',
        // ]);
        $generatedId = $this->generateIdMataPelajaran();
        $response = DB::insert("
        Insert into mata_pelajaran(id_mata_pelajaran, name, description)
        values(?, ?, ?)
        ", [$generatedId, $request->name, $request->description]);
        if ($response == 1) {
            return [
                'message' => 'Insert data successful',
                'code' => 200,
            ];
        }
        return [
            'message' => 'Database insert error',
            'code' => 500,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return MataPelajaran::where('id_mata_pelajaran', $id)->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }
        $response = DB::update('update mata_pelajaran set name=?, description=? where id_mata_pelajaran=?'
        , [$request->name, $request->description, $id]);
        if ($response == 1) {
            return [
                'message' => 'Update data successful',
                'code' => 200,
            ];
        }
        return [
            'message' => 'Database update error',
            'code' => 500,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = MataPelajaran::where('id_mata_pelajaran', $id)->delete();
        if($response){
            return [
                'message' => 'Data Deleted Successfully',
                'code' => 200,
            ];
        }
        return [
            'message' => 'Failed to Delete Data',
            'code' => 500,
        ];
    }

    private function generateIdMataPelajaran()
    {
        // Get latest id
        $lastData = MataPelajaran::orderBy('id_mata_pelajaran', 'DESC')->first();
        // $lastDataTest = DB::select('select * from mata_pelajaran order by id_mata_pelajaran desc limit 1');
        // $lastDataTest = $this->toArray($lastDataTest);
        // return $lastDataTest[0];
        $lastId = $lastData['id_mata_pelajaran']; // "MP0020"
        $symbolDigit = 2; // How Many Digit in Symbol
        $symbol = substr($lastId, 0, $symbolDigit); // "MP"
        $numberIdStr = substr($lastId, $symbolDigit); // "0020"

        // Hitung numlah nol
        $zeroCount = 0;
        $tempNumberIdStr = $numberIdStr;
        while (true) {
            if (substr($tempNumberIdStr, 0, 1) != "0") {
                break;
            }
            $tempNumberIdStr = substr($tempNumberIdStr, 1);
            $zeroCount = $zeroCount + 1;
        }

        $numberIdAdded = $numberIdStr + 1; // 21
        if (strlen((string)$numberIdAdded) > strlen((string)$numberIdStr)) $zeroCount -= 1;
        $generatedZero = "";
        while ($zeroCount != 0) {
            $generatedZero = $generatedZero . "0";
            $zeroCount--;
        }
        $generatedId = $symbol . $generatedZero . (string)$numberIdAdded; // Add everything
        return $generatedId;
    }

    private function toArray($data){
        $data = array_map(function ($value) {
            return (array)$value;
        }, $data);
        return $data;
    }
}
