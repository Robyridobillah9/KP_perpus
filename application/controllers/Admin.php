<?php
//skrip tidak dapat diakses secara langsung melalui URL dan hanya dapat diakses melalui aplikasi web.
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        //allias
        parent::__construct();
        $this->load->model('M_transaksi', 'm_transaksi');
    }
    public function index()
    {
// make sure pengguna sudah login sebagai admin atau belum
        if (!$this->session->userdata('isLogin') || $this->session->userdata('hak_akses') !== 'admin') {
            redirect(base_url());
        }

//data untuk tampilan admin di array $data
        $data = [
            'title'     => 'Admin',
            'transaksi' => $this->m_transaksi->getData(),
            'jml_buku'  => $this->db->get('buku')->num_rows(),
            'denda'     => $this->db->select_sum('denda')->get('transaksi')->row_array()['denda'],
            'pinjam'    => $this->db->get_where('transaksi', ['tgl_kembali'=>null])->num_rows(),
            'kembali'   => $this->db->get_where('transaksi', ['tgl_kembali !='=>null])->num_rows(),
        ];
//tampilkan 
        $this->template->load('admin/template', 'admin/home', $data);
    }

//menampilkan daftar admin
    public function list()
    {   
        $data['title'] = 'Admin List';
        $data['admin'] = $this->db->order_by('id', 'desc')->get('admin')->result_array();
        $this->template->load('admin/template', 'admin/list', $data);
    }

//penambahan admin
    public function add()
    {
        if(isset($_POST['submit'])){
            $data= [
                'username'      => $this->input->post('username'),
                'nama_lengkap'  => $this->input->post('nama_lengkap'),
                'password'      => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            ];
            // data baru dimasukan ke tabel admin
            if($this->db->insert('admin', $data)){
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Tambah Data!</div>');
                redirect('admin/list');
            }else{
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal Tambah Data!</div>');
                redirect('admin/list');
            }
        }else{  
            redirect('admin/list');
        }
    }

//update admin yang ada
    public function update($id)
    {
        if(isset($_POST['submit'])){    
            $data= [
                'username'      => $this->input->post('username'),
                'nama_lengkap'  => $this->input->post('nama_lengkap'),
            ];
            if( $this->input->post('password') !== '' ){
                $data['password'] =  password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            }
            if($this->db->update('admin', $data, ['id'=>$id])){
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Ubah Data!</div>');
                redirect('admin/list');
            }else{
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal Ubah Data!</div>');
                redirect('admin/list');
            }
        }else{
            redirect('admin/list');
        }
    }
//delete admin berdasarkan ID makanya kosong
    public function delete($id='')
    {
        if($id !==''){
            if($this->db->delete('admin', ['id'=>$id])){
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Hapus Data!</div>');
                redirect('admin/list');
            }else{
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal Hapus Data!</div>');
                redirect('admin/list');
            }
        }else{
            redirect('admin/list');
        }
    }

//ambil data admin berdasarkan ID di DB
    public function getdata($id)
    {

//ambil data di tabel admin (DB) berdasarkan id dan diconvert ke dalam format JSON
        $data = $this->db->get_where('admin', ['id'=>$id])->row_array();
        echo json_encode($data);
    }

// ambil data siswa 
    function get_siswa(){
        if (isset($_GET['term'])) {
            $this->db->like('nama_siswa', $_GET['term']);
            $result = $this->db->get('siswa')->result();
            if (count($result) > 0) {
            foreach ($result as $row)
                $arr_result[] = array(
                    'label' => $row->nama_siswa,
                    'no_reg' => $row->no_reg,
                );
                echo json_encode($arr_result);
            }
        }
    }

//ambil data buku
    function get_buku(){
        if (isset($_GET['term'])) {
            $this->db->like('judul_buku', $_GET['term']);
            $result = $this->db->get('buku')->result();
            if (count($result) > 0) {
            foreach ($result as $row)
                $arr_result[] = array(
                    'label' => $row->judul_buku,
                    'kd_buku' => $row->kd_buku,
                );
                echo json_encode($arr_result);
            }
        }
    }
}
