<?php

namespace App\Http\Controllers\Lomba;

use App\Http\Controllers\Controller;
use App\Models\Lomba\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            # jika request berasal dari ajax
            $data = Kategori::all();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . asset('assets/rulebook/' . $row->rulebook) . '" data-toggle="tooltip" class="btn btn-primary btn-sm"><i class="fas fa-file-download"></i> Rulebook</a>';
                    $actionBtn = $actionBtn . '<button data-url="' . route('kategori.edit', $row->id) . '" class="btn btn-warning btn-sm ml-2 mr-2 edit"><i class="fas fa-edit"></i> Edit</button>';
                    $actionBtn = $actionBtn . ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $row->id . '" data-url="' . route('kategori.destroy', $row->id) . '" data-original-title="Delete" class="btn btn-danger btn-sm deleteItem"><i class="fas fa-trash"></i></a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.kategori.index');
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
            'nama_kategori' => 'required',
            'deskripsi' =>  'required|string|max:100',
            'rulebook' => 'mimes:pdf',
            'image' => 'image|mimes:jpeg,png,jpg,svg|max:1048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $thumbnail = time() . '.' . $request->image->extension();
        $aturan = time() . '.' . $request->rulebook->extension();
        Kategori::Create([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi' => $request->deskripsi,
            'image' => $thumbnail,
            'rulebook' => $aturan
        ]);
        $request->image->move(public_path('frontend/lomba'), $thumbnail);
        $request->rulebook->move(public_path('assets/rulebook'), $aturan);

        return response()->json(['success' => 'Kategori Berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if ($request->ajax()) {
            # jika ajak maka
            $item = Kategori::find($id);
            return view('admin.kategori.edit', compact('item'));
        } else {
            # jika tidak maka
            return redirect()->route('kategori.index');
        }
    }

    public function update(Request $request, $id)
    {
      $request->validate([
            'nama_kategori' => 'required',
            'deskripsi' =>  'required|string|max:100',
            'rulebook' => 'mimes:pdf',
        ]);

        $data = Kategori::find($id);
        if ($request->rulebook != "") {
            # jika rulebook tidak null
            $rulebook = public_path('assets/rulebook/' . $data->rulebook);
            unlink($rulebook);

            $aturan = time() . '.' . $request->rulebook->extension();
            $data->update([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
                'rulebook' => $aturan
            ]);
            $request->rulebook->move(public_path('assets/rulebook'), $aturan);

            return redirect()->route('kategori.index')->with('success', 'Berhasil Edit Kategori');
        } else {
            # jika kosong maka
            $data->update([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
            ]);
            return redirect()->route('kategori.index')->with('success', 'Berhasil Edit Kategori');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Kategori::find($id);
        $rulebook = public_path('assets/rulebook/' . $data->rulebook);
        $images = public_path('frontend/lomba/' . $data->image);
        unlink($rulebook);
        unlink($images);
        $data->delete();
        return response()->json(['success' => 'Kategori deleted successfully']);
    }
}
