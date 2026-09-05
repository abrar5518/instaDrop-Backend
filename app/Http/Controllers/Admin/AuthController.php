<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function create(){return view('admin.auth.login');}
 public function store(Request $request){$credentials=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);if(!Auth::attempt($credentials,$request->boolean('remember'))){return back()->withErrors(['email'=>'The email address or password is incorrect.'])->onlyInput('email');}$request->session()->regenerate();return redirect()->intended(route('admin.dashboard'));}
 public function destroy(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('admin.login');}
}
