<?php

//skrip tidak dapat diakses secara langsung melalui URL dan hanya dapat diakses melalui aplikasi web.
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function index()
    {

//make sure/validasi sudah login atau belum (username dan pass wajib diisi)
        isLogin();
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        if ($this->form_validation->run() == false) {
            $data = [
                'title' => 'Login'
            ];
            $this->template->load('auth/template', 'auth/login', $data);
        } else {

            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $user = $this->db->get_where('admin', ['username' => $username])->row_array();

            // hak akses berdasarkan nilai status (1 untuk admin, 0 untuk keperpus dan sisanya untuk siswa)
            if($user['status']=='1'){
                $hak_akses = 'admin';
            }elseif($user['status']=='0'){
                $hak_akses = 'keperpus';
            }else{
                $hak_akses = 'siswa';
            }
// pengecekan username dan pass di tabel admin dan penentuan hak akses
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'isLogin'   => true,
                        'username'  => $user['username'],
                        'nama'      => $user['nama_lengkap'],
                        'hak_akses' => $hak_akses,
                    ];
                    $this->session->set_userdata($data);
                    redirect('/admin');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password Salah!</div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Username belum terdaftar!</div>');
                redirect('auth');
            }
        }
    }

//logout pengguna
    public function logout()
    {
        $this->session->unset_userdata('nama');
        $this->session->unset_userdata('username');
        $this->session->unset_userdata('isLogin');
        session_destroy();
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');
        redirect('/');
    }

//block halaman yang tidak diizinkan 
    public function blocked()
    {
        $this->load->view('auth/blocked');
    }
}
