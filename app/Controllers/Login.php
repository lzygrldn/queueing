<?php

namespace App\Controllers;

use App\Models\UserModel;

class Login extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return redirect()->to('/')->with('error', 'Please enter username and password');
        }

        $user = $this->userModel->where('username', $username)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->to('/')->with('error', 'Invalid username or password');
        }

        // Set session based on role
        if ($user['role'] === 'admin') {
            session()->set('admin_logged_in', true);
            session()->set('user_id', $user['id']);
            session()->set('username', $user['username']);
            return redirect()->to(base_url('admin'));
        } else {
            // Window staff - redirect to their assigned window
            session()->set('window_logged_in', true);
            session()->set('user_id', $user['id']);
            session()->set('username', $user['username']);
            session()->set('window_id', $user['window_id']);
            return redirect()->to(base_url('window/' . $user['window_id']));
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
