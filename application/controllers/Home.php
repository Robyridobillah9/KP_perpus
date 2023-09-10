<?php
//skrip tidak dapat diakses secara langsung melalui URL dan hanya dapat diakses melalui aplikasi web.
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function index()
    {
//pengaturan data

        $data = [
            'title'    => 'Home',
            'buku'     => $this->db->get('buku')->result_array()
        ];

//memuat tampilan halaman
        $this->template->load('user/template', 'user/home', $data);
        
    }
}
