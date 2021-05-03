<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Guru;
use App\Models\API\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
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
            'email' => ['required', 'email'],
            'password' => 'required',
            'id_role' => 'required',
            'birthdate' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $id_role = $request->id_role;
        $birthdate = $request->birthdate;

        // Create User baru
        $responseCreateUser = DB::insert('
        insert into user (email, password, name, birthdate, id_role) values (?, ?, ?, ?, ?)
        ', [$email, $password, $name, $birthdate, $id_role]);
        if ($responseCreateUser != 1) {
            // Insert gagal
            return [
                'message' => 'Insert User Data error',
                'code' => 500,
            ];
        }
        // Berhasil insert
        // ambil data user terakhir
        $currentUser = $this->getLastUserData();

        // Check role
        // if ($id_role == 'R0001') {
        //     // Tambah User Admin
        // }
        if ($id_role == 'R0002') {
            // Tambah Guru Baru
            $responseCreateGuru = (new GuruController)->add($name, $currentUser['id_user']);
            if($responseCreateGuru == false){
                // Delete inserted user
                $this->deleteUserById($currentUser['id_user']);
                return [
                    'message' => 'Insert Guru Data error, User insert cancelled',
                    'code' => 500,
                ];
            }
        }
        if ($id_role == 'R0003') {
            // Tambah Murid Baru
            $responseCreateMurid = (new MuridController)->add($name, $currentUser['id_user']);
            if($responseCreateMurid == false){
                // Delete inserted user
                $this->deleteUserById($currentUser['id_user']);
                return [
                    'message' => 'Insert Murid Data error, User insert cancelled',
                    'code' => 500,
                ];
            }
        }
        return [
            'message' => 'Insert Data Success',
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
        $target = User::where('id_user', $id)->first();
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', 'email'],
            'password' => 'required',
            'birthdate' => ['required', 'date'],
            'link_foto' => 'required',
            'izin_edit' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'code' => 500,
            ];
        }
        $currentUser = $this->show($id);
        // Update User
        $responseUpdateUser = $this->updateUser(
            $id,
            $request->name,
            $request->email,
            $request->password,
            $request->birthdate,
            $request->link_foto,
            $request->izin_edit,
        );
        if($responseUpdateUser == false){
            return [
                'message' => 'Update User Data error',
                'code' => 500,
            ];
        }

        //Check Role
        if($currentUser['id_role'] == "R0002"){
            // Update Guru
            $responseUpdateGuru = DB::update('
            update guru set name=? where id_user=?
            ', [$request->name, $id]);
            if($responseUpdateGuru != 1){
                // Cancel User Update
                $this->updateUser(
                    $currentUser['id_user'],
                    $currentUser['name'],
                    $currentUser['email'],
                    $currentUser['password'],
                    $currentUser['birthdate'],
                    $currentUser['link_foto'],
                    $currentUser['izin_edit'],
                );
                // Return error message
                return [
                    'message' => 'Update Guru Data error, Cancel Update User data',
                    'code' => 500,
                ];
            }
        }
        if($currentUser['id_role'] == "R0003"){
            // Update Murid
            $responseUpdateMurid = (new MuridController)->updateByUserId($id, $request->name);
            if($responseUpdateMurid == false){
                // Cancel User Update
                $this->updateUser(
                    $currentUser['id_user'],
                    $currentUser['name'],
                    $currentUser['email'],
                    $currentUser['password'],
                    $currentUser['birthdate'],
                    $currentUser['link_foto'],
                    $currentUser['izin_edit'],
                );
                // Return error message
                return [
                    'message' => 'Update Murid Data error, Cancel Update User data',
                    'code' => 500,
                ];
            }
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
        // $target = User::where('id_user', $id)->first();
        $target = $this->show($id);
        $id_user = $target['id_user'];
        $id_role = $target['id_role'];

        $responseDeleteUser = User::where('id_user', $id_user)->delete();
        if($responseDeleteUser != 1){
            return [
                'message' => 'Failed to Delete User Data',
                'code' => 500,
            ];
        }
        if($id_role == "R0002"){
            // Delete Data Guru
            $responseDeleteGuru = Guru::where('id_user', $id_user)->delete();
            if($responseDeleteGuru != 1){
                // Insert again user data
                $this->insertUserFull(
                    $target['id_user'],
                    $target['name'],
                    $target['email'],
                    $target['password'],
                    $target['birthdate'],
                    $target['id_role'],
                    $target['link_foto'],
                    $target['izin_edit']
                );
                // Error message
                return [
                    'message' => 'Failed to Delete Guru Data',
                    'code' => 500,
                ];
            }
        }
        if($id_role == "R0003"){
            // Delete Data Murid
            $responseDeleteMurid = (new MuridController)->deleteByUserId($id_user);
            if($responseDeleteMurid == false){
                // Insert again user data
                $this->insertUserFull(
                    $target['id_user'],
                    $target['name'],
                    $target['email'],
                    $target['password'],
                    $target['birthdate'],
                    $target['id_role'],
                    $target['link_foto'],
                    $target['izin_edit']
                );
                // Error message
                return [
                    'message' => 'Failed to Delete Murid Data',
                    'code' => 500,
                ];
            }
        }

        return [
            'message' => 'Data Deleted Successfully',
            'code' => 200,
        ];
    }

    private function deleteUserById($id_user){
        $responseDeleteUser = User::where('id_user', $id_user)->delete();
        if($responseDeleteUser == 0){
            return false;
        }
        return true;
    }

    private function updateUser($id_user, $name, $email, $password, $birthdate, $link_foto, $izin_edit){
        $responseUpdateUser = DB::update('
        update user
        set
        name=?,
        email=?,
        password=?,
        birthdate=?,
        link_foto=?,
        izin_edit=?
        where id_user=?
        ', [
            $name,
            $email,
            $password,
            $birthdate,
            $link_foto,
            $izin_edit,
            $id_user
        ]);
        if($responseUpdateUser == 0){
            return false;
        }
        return true;
    }

    private function insertUserFull($id_user, $name, $email, $password, $birthdate, $id_role, $link_foto, $izin_edit){
        $responseCreateUser = DB::insert('
        insert into user (id_user, email, password, name, birthdate, id_role, link_foto, izin_edit) values (?, ?, ?, ?, ?, ?, ?)
        ', [$id_user, $email, $password, $name, $birthdate, $id_role, $link_foto, $izin_edit]);
        if($responseCreateUser == 0){
            return false;
        }
        return true;
    }

    private function getLastUserData()
    {
        return User::orderBy('id_user', 'desc')->first();
    }
}
