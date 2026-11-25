<?php

namespace App\Http\Controllers\Panel\Autenticacion;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Inicio y cierre de sesión del panel.
 *
 * Usa el trait AuthenticatesUsers de Laravel; los métodos username(), guard(),
 * credentials() y validateLogin() conservan su nombre porque el trait los invoca.
 */
class AccesoController extends Controller
{
    use AuthenticatesUsers;

    /**
     * A dónde se redirige tras iniciar sesión.
     *
     * @var string
     */
    protected $redirectTo = '/panel/inicio';

    public function __construct()
    {
        $this->middleware('guest:admin')->except('cerrarSesion');
    }

    public function mostrarFormulario()
    {
        return view('panel.acceso');
    }

    public function iniciarSesion(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function cerrarSesion(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('acceso');
    }

    /**
     * Campo usado como nombre de usuario.
     */
    public function username()
    {
        return 'usuario';
    }

    /**
     * El formulario envía "contrasena"; Laravel espera la clave "password"
     * en las credenciales para compararla con el hash del usuario.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'contrasena' => 'required|string',
        ]);
    }

    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->input($this->username()),
            'password' => $request->input('contrasena'),
        ];
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }
}
