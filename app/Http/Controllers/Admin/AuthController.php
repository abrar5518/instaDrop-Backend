<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller {
 public function create(){return view('admin.auth.login');}
 public function store(Request $request){$credentials=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);if(!Auth::attempt($credentials,$request->boolean('remember'))){return back()->withErrors(['email'=>'The email address or password is incorrect.'])->onlyInput('email');}$request->session()->regenerate();return redirect()->intended(route('admin.dashboard'));}
 public function destroy(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('admin.login');}
 public function edit(Request $request){return view('admin.auth.security',['user'=>$request->user()]);}
 public function update(Request $request){$user=$request->user();$validated=$request->validate(['name'=>['required','string','max:100'],'email'=>['required','email','max:255',Rule::unique('users')->ignore($user->id)],'current_password'=>['required','current_password'],'password'=>['nullable','confirmed',Password::min(12)->letters()->mixedCase()->numbers()->symbols()]]);$user->name=$validated['name'];$user->email=$validated['email'];if(!empty($validated['password'])){$user->password=$validated['password'];}$user->save();$request->session()->regenerate();return back()->with('success','Login and security details updated successfully.');}
}
