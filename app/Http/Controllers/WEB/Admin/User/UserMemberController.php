<?php

namespace App\Http\Controllers\WEB\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\CreateMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Models\Auth\Role;
use App\Models\User;
use App\Models\User\Member;
use App\Models\Wilayah\Kecamatan;
use App\Models\Wilayah\Kota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RealRashid\SweetAlert\Facades\Alert;

class UserMemberController extends Controller
{
    protected $user;
    protected $role;

    protected $member;

    public function __construct(User $user, Role $role, Member $member)
    {
        $this->user = $user;
        $this->role = $role;
        $this->member = $member;
    }
    public function index()
    {

        $data = [
            'title' => 'Member',
            'breadcrumb' => 'Member',
            'breadcrumb_active' => 'Data Member',
            'button_create' => 'Tambah Member',
            'member' => $this->member::all(),
        ];

        return view('admin.pages.user.member.index', $data);
    }

    public function create()
    {
        try {
            $getProvinsi = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
            $provinsi = $getProvinsi->json();
        } catch (\Exception $e) {
            Alert::warning('Gagal mengambil data provinsi');
            $provinsi = [];
        }
        $data = [
            'title' => 'Member',
            'breadcrumb' => 'Member',
            'breadcrumb_active' => 'Tambah Member',
            'button_create' => 'Tambah Member',
            'card_title' => 'Tambah Member',
            'provinsi' => $provinsi,
        ];

        return view('admin.pages.user.member.create', $data);
    }

    public function store(CreateMemberRequest $request)
    {
        try {
            DB::beginTransaction();

            $imageExtension = $request->file('image')->getClientOriginalExtension();
            $imageName = 'member_' . (count(File::files(public_path('image_member'))) + 1) . '.' . $imageExtension;
            $imagePath = 'image_member/' . $imageName;

            $request->file('image')->move(public_path('image_member'), $imageName);

            $user = $this->user->create([
                'name' => str_replace(' ', '', $request->nama_depan . $request->nama_belakang),
                'email' => $request->email,
                'password' => bcrypt('password'),
                'role_id' => 2,
            ]);

            $this->member->create(array_merge($request->all(), [
                'user_id' => $user->id,
                'image' => $imagePath,
            ]));

            DB::commit();

            Alert::success('success', 'Success Data Berhasil Ditambahkan!');
            session()->flash('success', 'Data Berhasil Ditambahkan!');
            return redirect('/admin/pengguna/member')->with('success', 'Data Berhasil Ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            // Tampilkan pesan error
            Alert::error('Error', 'Error Data Gagal Ditambahkan!' . $e->getMessage());
            session()->flash('error', 'Data Gagal Ditambahkan!');
            return back()->with('error', 'Data Gagal Ditambahkan!');
        }
    }

    public function edit($id)
    {
        $data = [
            'member' => $this->member->findOrFail($id),
        ];
        dd($data);
        return view('admin.pages.user.member.update', $data);
    }

    public function update(UpdateMemberRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $member = $this->member->findOrFail($id);
            $member->update($request->all() + [
                'updated_at' => Carbon::now(),
            ]);
            $member->user->update($request->all() + [
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            Alert::success('success', 'Success Data Berhasil Diubah!');
            return redirect('/admin/pengguna/member')->with('success', 'Data Berhasil Diubah!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Error Data Gagal Diubah!' . $e->getMessage());
            return back()->with('error', 'Data Gagal Diubah!');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $member = $this->member->findOrFail($id);

            $imagePath = public_path('image_member/' . basename($member->image));
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }

            $member->delete();
            $member->user->delete();

            DB::commit();

            Alert::success('success', 'Success Data Berhasil Dihapus!');
            return redirect('/admin/pengguna/member')->with('success', 'Data Berhasil Dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Error Data Gagal Dihapus!' . $e->getMessage());
            return back()->with('error', 'Data Gagal Dihapus!');
        }
    }
}
