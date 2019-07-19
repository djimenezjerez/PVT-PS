<?php
namespace App\Http\Controllers;
use JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Validator;
use Ldap;

/** @resource Authenticate
 *
 * Resource to authenticate via username/password credentials
 */
class AuthController extends Controller
{
  /**
   * Get a JWT via given credentials.
   *
   * Login, return a JsonWebToken to request as "Bearer" Authorization header
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function login(Request $request)
  {
    $token = null;
    $credentials = $request->only('username', 'password');
    $rules = [
      'username' => 'required',
      'password' => 'required',
    ];
    $validator = Validator::make($credentials, $rules);
    if($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => $validator->messages()
      ]);
    }

    try {
      if ($credentials['username'] == 'admin') {
        $token = JWTAuth::attempt($credentials);
      } else {
        $user = User::whereUsername($credentials['username'])->whereStatus('active')->first();
        if ($user) {
          if (!env("LDAP_AUTHENTICATION")) {
            $token = JWTAuth::attempt($credentials);
          } else {
            $ldap = new Ldap();
            if ($ldap->connection && $ldap->verify_open_port()) {
              if ($ldap->bind($credentials['username'], $credentials['password'])) {
                if (!Hash::check($request['password'], $user->password)) {
                  $user->password = Hash::make($request['password']);
                  $user->save();
                }
                $token = JWTAuth::attempt($credentials);
                $ldap->unbind();
              }
            }
          }
        }
      }
    } catch(JWTException $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'No autorizado',
        'errors' => [
          'type' => ['Usuario desactivado'],
        ],
      ], 500);
    } finally {
      if ($token) {
        return $this->respondWithToken($token);
      } else {
        return response()->json([
          'status' => 'error',
          'message' => 'No autorizado',
          'errors' => [
            'type' => ['Usuario desactivado'],
          ],
        ], 401);
      }
    }
  }

  /**
   * Log the user out (Invalidate the token).
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout(Request $request)
  {
    $token = $request->header('Authorization');
    try {
      JWTAuth::invalidate($token);
      return response()->json([
          'status' => 'success',
          'message'=> "User successfully logged out."
      ]);
    } catch (JWTException $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to logout, please try again.'
      ], 500);
    }
  }

  /**
   * Get the token array structure.
   *
   * @param  string $token
   *
   * @return \Illuminate\Http\JsonResponse
   */
  protected function respondWithToken($token)
  {
    $user = Auth::user();
    $username = $user->username;
    $ip = request()->ip();
    \Log::info("Usuario ".$username." autenticado desde la dirección ".$ip);

    return response()->json([
      'status' => 'success',
      'token' => $token,
      'user' => $user,
      'token_type' => 'Bearer',
      'message' => 'Indentidad verificada',
    ], 200);
  }

  public function guard()
  {
    return Auth::Guard('api');
  }
}