<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\AlokasiKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlokasiKelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AlokasiKelas::all();
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
            'id_kelas' => 'required',
            'id_murid' => 'required',
            'id_guru' => 'required',
            'id_mata_pelajaran' => 'required',
            'nilai_tugas' => ['integer', 'max:100'],
            'nilai_uts' => ['integer', 'max:100'],
            'nilai_uas' => ['integer', 'max:100'],
        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }
        $insertionColumn = 'id_kelas, id_murid, id_guru, id_mata_pelajaran';
        $insertionParam = '?, ?, ?, ?';
        $insertionParamValue = [
            $request->id_kelas,
            $request->id_murid,
            $request->id_guru,
            $request->id_mata_pelajaran,
        ];
        if ($request->nilai_tugas) {
            $insertionColumn .= ', nilai_tugas';
            $insertionParam .= ', ?';
            array_push($insertionParamValue, $request->nilai_tugas);
        }
        if ($request->nilai_uts) {
            $insertionColumn .= ', nilai_uts';
            $insertionParam .= ', ?';
            array_push($insertionParamValue, $request->nilai_uts);
        }
        if ($request->nilai_uas) {
            $insertionColumn .= ', nilai_uas';
            $insertionParam .= ', ?';
            array_push($insertionParamValue, $request->nilai_uas);
        }
        if ($request->nilai_tugas && $request->nilai_uts && $request->nilai_uas) {
            $insertionColumn .= ', nilai_akhir';
            $insertionParam .= ', ?';
            $nilai_akhir = ((float)$request->nilai_tugas * 0.3) + ((float)$request->nilai_uts * 0.3) + ((float)$request->nilai_uas * 0.4);
            array_push($insertionParamValue, $nilai_akhir);
        }

        $response = DB::insert("
        insert into alokasi_kelas
        ($insertionColumn)
        values ($insertionParam)", $insertionParamValue);

        if (!$response) {
            return [
                'message' => 'Database insert error',
                'code' => 500,
            ];
        }
        return [
            'message' => 'Data insert success',
            'code' => 200,
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
        $target = $this->getById($id);
        if (!$target) {
            return [
                'message' => 'Data not found',
                'code' => 500,
            ];
        }
        return $target;
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
        $target = $this->getById($id);
        $validator = Validator::make($request->all(), [
            'id_kelas' => 'required',
            'id_murid' => 'required',
            'id_guru' => 'required',
            'id_mata_pelajaran' => 'required',
            'nilai_tugas' => ['integer', 'max:100'],
            'nilai_uts' => ['integer', 'max:100'],
            'nilai_uas' => ['integer', 'max:100'],
        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }

        $setParam = 'id_kelas=?, id_murid=?, id_guru=?, id_mata_pelajaran=?';
        $updateValue = [
            $request->id_kelas,
            $request->id_murid,
            $request->id_guru,
            $request->id_mata_pelajaran,
        ];

        if ($request->nilai_tugas) {
            $setParam .= ', nilai_tugas=?';
            array_push($updateValue, $request->nilai_tugas);
        }
        if ($request->nilai_uts) {
            $setParam .= ', nilai_uts=?';
            array_push($updateValue, $request->nilai_uts);
        }
        if ($request->nilai_uas) {
            $setParam .= ', nilai_uas=?';
            array_push($updateValue, $request->nilai_uas);
        }

        $nilai_tugas_updated = $request->nilai_tugas ? $request->nilai_tugas : $target['nilai_tugas'];
        $nilai_uts_updated = $request->nilai_uts ? $request->nilai_uts : $target['nilai_uts'];
        $nilai_uas_updated = $request->nilai_uas ? $request->nilai_uas : $target['nilai_uas'];
        if ($nilai_tugas_updated != 0 && $nilai_uts_updated != 0 && $nilai_uas_updated != 0) {
            $nilai_akhir = ((float)$nilai_tugas_updated * 0.3) + ((float)$nilai_uts_updated * 0.3) + ((float)$nilai_uas_updated * 0.4);
            $setParam .= ', nilai_akhir=?';
            array_push($updateValue, $nilai_akhir);
        }

        // array_push($updateValue, $id);

        $response = DB::update("
        update alokasi_kelas
        set
        $setParam
        where id_alokasi=?", [...$updateValue, $id]);

        if (!$response) {
            return [
                'message' => 'Database update error',
                'code' => 500,
            ];
        }
        return [
            'message' => 'Update Data Success',
            'code' => 200,
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
        $target = $this->getById($id);
        if (!$target) {
            return [
                'message' => 'Data not found',
                'code' => 500,
            ];
        }
        $response = $this->deleteById($id);
        if (!$response) {
            return [
                'message' => 'Database delete failed',
                'code' => 500,
            ];
        }
        return [
            'message' => 'Data deleted successfully',
            'code' => 200,
        ];
    }

    private function getById($id)
    {
        return AlokasiKelas::where('id_alokasi', $id)->first();
    }
    private function deleteById($id)
    {
        return AlokasiKelas::where('id_alokasi', $id)->delete();
    }
}
