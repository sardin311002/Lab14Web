<?php

namespace App\Controllers;

use App\Models\ArtikelModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Artikel extends BaseController
{
  public function index()
  {
    $title = 'Daftar Artikel';
    $model = new ArtikelModel();
    $artikel = $model->findAll();
    return view('artikel/index', compact('artikel', 'title'));
  }

  // Untuk Artikel
  public function view($slug)
  {
    $model = new ArtikelModel();
    $artikel = $model->where(['slug' => $slug])->first();
    // Menampilkan error apabila data tidak ada. 
    if (!$artikel) {
      throw PageNotFoundException::forPageNotFound();
    }
    $title = $artikel['judul'];
    return view('artikel/detail', compact('artikel', 'title'));
  }

  // Menu Admin
  public function admin_index()
  {
    $title = 'Daftar Artikel';
    $q = $this->request->getVar('q') ?? '';
    $model = new ArtikelModel();
    $data = [
      'title' => $title,
      'q' => $q,
      'artikel' => $model->like('judul', $q)->paginate(2), #Data dibatasi 2 record perhalaman
      'pager' => $model->pager,
    ];
    return view('artikel/admin_index', $data);
  }

  // ADD
  public function add()
  {
    // Validasi data
    $validation = \Config\Services::validation();
    $validation->setRules(['judul' => 'required']);
    $isDataValid = $validation->withRequest($this->request)->run();

    if ($isDataValid) {
      $file = $this->request->getFile('gambar');
      $file->move(ROOTPATH . 'public/gambar');

      $artikel = new ArtikelModel();
      $artikel->insert([
        'judul' => $this->request->getPost('judul'),
        'isi' => $this->request->getPost('isi'),
        'slug' => url_title($this->request->getPost('judul')),
        'gambar' => $file->getName(),
      ]);
      return redirect('admin/artikel');
    }
    $title = "Tambah Artikel";
    return view('artikel/form_add', compact('title'));
  }

  // Edit
  public function edit($id)
  {
    $artikel = new ArtikelModel();

    $validation = \Config\Services::validation();
    $validation->setRules(['judul' => 'required']);
    $isDataValid = $validation->withRequest($this->request)->run();

    if ($isDataValid) {
      $artikel->update(
        $id,
        [
          'judul' => $this->request->getPost('judul'),
          'isi' => $this->request->getPost('isi'),
        ]
      );
      return redirect('admin/artikel');
    }
    $data = $artikel->where('id', $id)->first();
    $title = "Edit artikel";
    return view('artikel/form_edit', compact('title', 'data'));
  }

  // Delete
  public function delete($id)
  {
    $artikel = new ArtikelModel();
    $artikel->delete($id);
    return redirect('admin/artikel');
  }
}