<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        //membuat function construct untuk menggunakan form_validation disemua function  
    }

    public function index()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email'); 
        $this->form_validation->set_rules('password', 'Password', 'trim|required'); 

        if ($this->form_validation->run() == false) {
            $data['title'] = 'eGov Login Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            //ketika validasinya sukses
            $this->_login();
        }
    }

    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        //membuat query buildernya codeigniter ke database kalo dibaca SELECT * FROM TABEL user WHERE email=email
        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        //jika user ada
        if ($user) {
            //jika usernya active
            if ($user['is_active'] == 1) {
                //cek password
                if(password_verify($password, $user['password'])) {
                    
                    $data = [
                        'email' => $user ['email'],
                        'role_id' => $user ['role_id']
                    ];
                    $this->session->set_userdata($data);
                    redirect('user');

                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Mohon maaf... password yang anda masukkan salah, Silahkan login kembali.</div>');
                    redirect('auth');
                }

            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Mohon maaf... email ini belum diaktivasi, Silahkan aktivasi email anda.</div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Mohon maaf... email belum terdaftar, Silahkan login kembali.</div>');
            redirect('auth');
        }
    }

    public function registration()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]',[
            'is_unique' => 'Email ini sudah terdaftar!'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Password tidak sama!',
            'min_length' => 'Password terlalu pendek!'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'eGov User Registration';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');

        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name', 'true')),
                'email' => htmlspecialchars($this->input->post('email', 'true')),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];

            $this->db->insert('user', $data); //memasukkan ke tabel user pada database dari array $data diatas

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Selamat... akun anda telah berhasil dibuat, Silahkan login.</div>');
            redirect('auth');

        }
    }



    public function logout()
    {
        $this->session->unset_userdata['email'];
        $this->session->unset_userdata['role_id'];

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
        Terimakasih... anda telah berhasil logout sistem e-Goverments</div>');
        redirect('auth');
    }

}
