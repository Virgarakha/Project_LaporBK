<?php

namespace App\Http\Controllers;

use App\Models\Balasan;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PengaduanController extends Controller
{
    public function index(){
        $user = auth()->user();
        $aduan = Pengaduan::where('user_id', $user->id)->with('user')->get();
        return response()->json($aduan, 200);
    }

    public function showbyuser($id){
        $pengaduan = Pengaduan::where('id', $id)->first();
        $balasan = Balasan::where('pengaduan_id', $id)->with('user')->first();

        $oke = '';
        if(!$balasan){
            $oke = 'Belum ada balasan';
        } else {
            $oke = $balasan;
        }

        return response()->json([
            'pengaduan' => $pengaduan,
            'balasan' => $balasan
        ], 200);
    }

    public function store(Request $request){
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|in:akademik,pribadi,sosial,bullying,lainnya',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengaduan = Pengaduan::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'status' => 'baru',
        ]);

        return response()->json(['message'=>'Pengaduan berhasil dibuat', 'pengaduan'=>$pengaduan], 200);
    }

    public function update(Request $request, $id){
        $user = auth()->user();
        $pengaduan = Pengaduan::find($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|in:akademik,pribadi,sosial,bullying,lainnya',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $pengaduan->update([
            'user_id' => $user->id,
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'status' => 'baru',
        ]);

        return response()->json(['message'=>'Pengaduan berhasil di edit', 'pengaduan'=>$pengaduan], 200);
    }

    public function destroy($id){
        $pengaduan = Pengaduan::find($id);
        $pengaduan->delete();
        return response()->json([
            'message' => 'Berhasil mengahapus pengaduan!'
        ], 200);
    }


    // BK ROLE

    public function getData(){
        $aduan = Pengaduan::with('user')->get();
        return response()->json($aduan, 200);
    }

    public function reply(Request $request, $id)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengaduan = Pengaduan::findOrFail($id);

        $balasan = Balasan::create([
            'pengaduan_id' => $pengaduan->id,
            'guru_id' => $user->id,
            'message' => $request->message,
        ]);

        $pengaduan->status = 'diproses';
        $pengaduan->save();

        return response()->json(['message'=>'Balasan berhasil dikirim', 'balasan'=>$balasan], 200);
    }

    public function show($id){
        $pengaduan = Pengaduan::where('id', $id)->with('user')->first();

        $balasan = Balasan::where('pengaduan_id', $id)->with('guru')->first();

        $oke = '';
        if(!$balasan){
            $oke = 'Belum ada balasan';
        } else {
            $oke = $balasan;
        }

        return response()->json([
            'pengaduan' => $pengaduan,
            'balasan' => $balasan
        ], 200);
    }

    public function editBalasan(Request $request, $id){
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $balasan = Balasan::findOrFail($id);
        $pengaduan = Pengaduan::where('id', $balasan->pengaduan_id)->first();

        $balasan->update([
            'pengaduan_id' => $pengaduan->id,
            'guru_id' => $user->id,
            'message' => $request->message,
        ]);

        $pengaduan->status = 'diproses';
        $pengaduan->save();

        return response()->json(['message'=>'Balasan berhasil diedit', 'balasan'=>$balasan], 200);
    }

    public function hapusBalasan($id){
        $balasan = Balasan::find($id);
        $balasan->delete();
        return response()->json([
            'message' => 'Berhasil menghapus balasan!'
        ], 200);
    }

    public function setStatus(Request $request, $id){
        $pengaduan = Pengaduan::find($id);

        $request->validate([
            'status' => 'required'
        ]);

        $pengaduan->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'berhasil set status data pengaduan'
        ], 200);
    }

    public function filterPengaduan(){
        $baru = Pengaduan::where('status', 'baru')->with('user')->get();
        $proses = Pengaduan::where('status', 'diproses')->with('user')->get();
        $selesai = Pengaduan::where('status', 'selesai')->with('user')->get();

        return response()->json([
            'baru' => $baru,
            'proses' => $proses,
            'selesai' => $selesai,
            'baruCount' => $baru->count(),
            'prosesCount' => $proses->count(),
            'selesaiCount' => $selesai->count(),
        ], 200);
    }

}
