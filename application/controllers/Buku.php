<?php
//skrip tidak dapat diakses secara langsung melalui URL dan hanya dapat diakses melalui aplikasi web.
defined('BASEPATH') or exit('No direct script access allowed');

class Buku extends CI_Controller
{
    public function index()
    {
// make sure pengguna sudah login sebagai admin atau belum 

        if (!$this->session->userdata('isLogin') || $this->session->userdata('hak_akses') !== 'admin') {
            redirect(base_url());
        }
        
// pengambilan data buku terbaru dari database

        $query   = $this->db->query("SELECT MAX(kd_buku) as kd_buku from buku");
        $hasil   = $query->row();
        $nourut  = substr($hasil->kd_buku, 2, 3);
        $kd_buku = (int)$nourut + 1;
        $kd_buku = "BK".sprintf("%03s", $kd_buku);

// tampilan manajemen buku dimuat
        $data = [
            'title'    => 'Buku',
            'buku'     => $this->db->order_by('id', 'desc')->get('buku')->result_array(),
            'kd_buku'  => $kd_buku,
        ];
        // var_dump($kd_buku); die;
        $this->template->load('admin/template', 'admin/buku/index', $data);
    }

// tambah data buku    
    public function add()
    {

// make sure pengguna sudah login sebagai admin atau belum
        if (!$this->session->userdata('isLogin') || $this->session->userdata('hak_akses') != 'admin') {
            redirect(base_url());
        }

        //check pengiriman form dan pengambilan data 
        if(isset($_POST)){
            $data = [
                'kd_buku'       =>$this->input->post('kd_buku'),
                'judul_buku'    =>$this->input->post('judul_buku'),
                'penerbit'      =>$this->input->post('penerbit'),
                'pengarang'     =>$this->input->post('pengarang'),
                'tahun_terbit'  =>$this->input->post('tahun_terbit'),
                'nomor_rak'     =>$this->input->post('nomor_rak'),
                'jumlah'        =>$this->input->post('jumlah'),
            ];
            
//konfigurasi upload 
            $config['upload_path']      = 'assets/img/buku/';
            $config['allowed_types']    = 'gif|jpg|png|jpeg';

            $this->load->library('upload', $config);
//upload gambar
            if (!$this->upload->do_upload('sampul')){
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"Maaf, gagal upload foto buku: '.$this->upload->display_errors().'!</div>');
                redirect('/buku');
            }
            else{
                $data['sampul'] = $this->upload->data('file_name'); 
                if($this->db->insert('buku', $data)){
                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Tambah Data!</div>');
                    redirect('/buku');
                }else{
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"Maaf, tambah data!</div>');
                    redirect('/buku');
                }
            }

            
        }
    }

    public function update($id)
    {
// make sure pengguna sudah login sebagai admin atau belum
        if (!$this->session->userdata('isLogin') || $this->session->userdata('hak_akses') != 'admin') {
            redirect(base_url());
        }

        if(isset($_POST)){
            $data = [
                'judul_buku'    =>$this->input->post('judul_buku'),
                'penerbit'      =>$this->input->post('penerbit'),
                'pengarang'     =>$this->input->post('pengarang'),
                'tahun_terbit'  =>$this->input->post('tahun_terbit'),
                'nomor_rak'     =>$this->input->post('nomor_rak'),
                'jumlah'        =>$this->input->post('jumlah'),
            ];

// konfigurasi upload

            $config['upload_path']      = 'assets/img/buku/';
            $config['allowed_types']    = 'gif|jpg|png|jpeg';

            $this->load->library('upload', $config);

            if($_FILES['sampul']['name']!=''){
                if (!$this->upload->do_upload('sampul')){
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"Maaf, gagal upload foto buku: '.$this->upload->display_errors().'!</div>');
                    redirect('/buku');
                }else{
                    $data['sampul'] = $this->upload->data('file_name');
                }
            }

// update data buku berdasarkan id yang sesuai
            if($this->db->update('buku', $data, ['id'=>$id])){
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Ubah Data!</div>');
                redirect('/buku');
            }else{
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"Maaf, ubah data!</div>');
                redirect('/buku');
            }
        }
    }

// ambil data buku berdassarkan ID
    public function getdata($id){
        $data = $this->db->get_where('buku', ['id'=>$id])->row_array();
        echo json_encode($data);
    }

// Hapus Buku dari DB berdasarkan ID
    public function delete($id)
    {
        if (!$this->session->userdata('isLogin') || $this->session->userdata('hak_akses') != 'admin') {
            redirect(base_url());
        }
        $res = $this->db->get_where('buku', ['id'=>$id])->row_array();
        unlink('assets/img/buku/'.$res['sampul']);
        if($this->db->delete('buku', ['id'=>$id])){
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Hapus Data!</div>');
            redirect('/buku');
        }else{
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"Maaf, gagal hapus data!</div>');
            redirect('/buku');
        }
    }
}
