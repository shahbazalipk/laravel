<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();
        
        if ($admin && $admin->checkPassword($request->password)) {
            session([
                'admin_logged_in' => true,
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'admin_name' => $admin->name,
            ]);
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
    
    public function logout()
    {
        session()->forget(['admin_logged_in', 'admin_id', 'admin_email', 'admin_name']);
        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
}
