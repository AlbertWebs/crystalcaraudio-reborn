<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CustomerController extends Controller
{
    public function loginForm() { return view('customer.login'); }
    public function registerForm() { return view('customer.register'); }
    public function forgotPasswordForm() { return view('customer.forgot-password'); }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => ['required','email','max:190']]);
        $status = PasswordBroker::sendResetLink($request->only('email'));
        return $status === PasswordBroker::RESET_LINK_SENT
            ? back()->with('status', 'If an account uses that email, a password setup link is on its way.')
            : back()->withErrors(['email' => 'We could not send a password link right now. Please try again.']);
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        return view('customer.password-reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'], 'email' => ['required','email'],
            'password' => ['required','confirmed',Password::min(10)],
        ]);
        $status = PasswordBroker::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        return $status === PasswordBroker::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Your password is set. You can now sign in.')
            : back()->withErrors(['email' => 'This password link is invalid or expired. Request a new one.']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email' => 'Those details did not match an account.'])->onlyInput('email');
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard.home'));
    }

    public function register(Request $request)
    {
        $data = $request->validate(['name' => ['required','string','max:160'], 'email' => ['required','email','max:190','unique:users,email'], 'phone' => ['nullable','string','max:24'], 'password' => ['required','confirmed',Password::min(10)]]);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
        DB::table('users')->where('id', $user->id)->update(['phone' => $data['phone'] ?? null]);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard.home');
    }

    public function dashboard()
    {
        $user = request()->user();
        $customerOrders = StoreOrder::where('email', $user->email);
        $orderStats = (clone $customerOrders)->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')->first();
        $orders = (clone $customerOrders)->latest()->limit(10)->get();
        $legacyOrders = collect();
        if ($user->legacy_customer_id) {
            $legacyIds = DB::table('legacy_archive_records')->where('source_table','orders')->get(['payload'])->map(function ($row) {
                try { return json_decode(\Illuminate\Support\Facades\Crypt::decryptString($row->payload), true); } catch (\Throwable) { return null; }
            })->filter(fn ($order) => (string) ($order['user_id'] ?? '') === (string) $user->legacy_customer_id);
            $legacyOrders = $legacyIds;
        }
        $wishlist = DB::table('customer_wishlists')->join('catalog_products','catalog_products.id','=','customer_wishlists.product_id')
            ->where('customer_wishlists.user_id',$user->id)->select('catalog_products.*')->get();
        return view('customer.dashboard', compact('user','orders','orderStats','legacyOrders','wishlist'));
    }

    public function update(Request $request)
    {
        $data = $request->validate(['name' => ['required','string','max:160'], 'phone' => ['nullable','string','max:24'], 'address' => ['nullable','string','max:1000']]);
        $user = $request->user();
        $user->name = $data['name'];
        $user->save();
        DB::table('users')->where('id',$user->id)->update(['phone' => $data['phone'] ?? null, 'address' => $data['address'] ?? null]);
        return back()->with('success','Your details are saved.');
    }

    public function addWishlist(CatalogProduct $product)
    {
        DB::table('customer_wishlists')->updateOrInsert(['user_id' => request()->user()->id, 'product_id' => $product->id], ['created_at' => now(), 'updated_at' => now()]);
        return back()->with('success','Saved to your wishlist.');
    }

    public function legacyAddWishlist(int $id)
    {
        return $this->addWishlist(CatalogProduct::findOrFail($id));
    }

    public function removeWishlist(int $id)
    {
        DB::table('customer_wishlists')->where('user_id',request()->user()->id)->where('product_id',$id)->delete();
        return back()->with('success','Removed from your wishlist.');
    }

    public function logout(Request $request)
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
