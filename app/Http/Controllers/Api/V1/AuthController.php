<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Register Wajib Pajak
     * 
     * Membutuhkan verification_token dari proses OTP verification
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_token' => 'required|string',
            'nama_lengkap' => 'required|string',
            'name' => 'required|string|max:100',
            'nik' => 'required|string|size:16', // Format NIK
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'no_whatsapp' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'province_code' => 'required|string',
            'regency_code' => 'required|string',
            'district_code' => 'required|string',
            'village_code' => 'required|string',
            'birth_regency_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Validasi verification token dari OTP
        $otpRecord = VerificationCode::findByVerificationToken($request->verification_token);
        if (!$otpRecord) {
            return $this->sendError('Token verifikasi tidak valid atau sudah kedaluwarsa.', [], 401);
        }

        // Cek duplikat by Hash
        $emailHash = User::generateHash($request->email);
        if (User::where('email_hash', $emailHash)->exists()) {
            return $this->sendError('Email sudah terdaftar.', [], 400);
        }

        $nikHash = User::generateHash($request->nik);
        if (User::where('nik_hash', $nikHash)->exists()) {
            return $this->sendError('NIK sudah terdaftar.', [], 400);
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'name' => $request->name,
            'nik' => $request->nik,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'no_whatsapp' => $request->no_whatsapp,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'province_code' => $request->province_code,
            'regency_code' => $request->regency_code,
            'district_code' => $request->district_code,
            'village_code' => $request->village_code,
            'birth_regency_code' => $request->birth_regency_code,
            'status' => 'verified', // Verified via OTP
            'must_change_password' => false,
        ]);

        // Invalidate verification token (one-time use)
        $otpRecord->update(['verification_token' => null, 'token_expires_at' => null]);

        $token = $user->createToken('BorotaxApp')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap, // Decrypted
                'email' => $user->email, // Decrypted
                'nik' => $user->nik, // Decrypted
                'role' => $user->role,
            ]
        ], 'Registrasi berhasil.');
    }

    /**
     * Login User
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string', // Bisa email atau NIK
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Cari user by Email Hash OR NIK Hash
        $input = $request->email; // Field namanya email, tapi isinya bisa NIK
        $hash = User::generateHash($input);

        $user = User::where('email_hash', $hash)
            ->orWhere('nik_hash', $hash)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment failed attempts logic here if strictly needed
            return $this->sendError('Email/NIK atau password salah.', [], 401);
        }

        if ($user->isLocked()) {
            return $this->sendError('Akun terkunci sementara karena terlalu banyak percobaan login.', [], 429);
        }

        // Reset failed attempts
        $user->resetFailedAttempts();
        $user->update(['last_login_at' => now()]);

        // Revoke old tokens if single session policy? (Optional)
        // $user->tokens()->delete();

        $token = $user->createToken('BorotaxApp')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 'Login berhasil.');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse([], 'Logout berhasil.');
    }

    /**
     * Get Profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        // Decryption automatically handled by Eloquent Accessor (HasEncryptedAttributes trait)
        return $this->sendResponse([
            'id' => $user->id,
            'nik' => $user->nik,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_whatsapp' => $user->no_whatsapp,
            'tempat_lahir' => $user->tempat_lahir,
            'tanggal_lahir' => $user->tanggal_lahir,
            'alamat' => $user->alamat,
            'province_code' => $user->province_code,
            'regency_code' => $user->regency_code,
            'district_code' => $user->district_code,
            'village_code' => $user->village_code,
            'birth_regency_code' => $user->birth_regency_code,
            'foto_url' => $user->foto_selfie_url, // Asumsi foto profil
            'role' => $user->role,
            'created_at' => $user->created_at,
        ], 'Data profile user.');
    }

    /**
     * Update Profile User
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'nama_lengkap' => 'sometimes|required|string',
            'email' => 'sometimes|required|email',
            'no_whatsapp' => 'sometimes|required|string',
            'tempat_lahir' => 'sometimes|required|string',
            'tanggal_lahir' => 'sometimes|required|date',
            'alamat' => 'sometimes|required|string',
            'province_code' => 'sometimes|required|string',
            'regency_code' => 'sometimes|required|string',
            'district_code' => 'sometimes|required|string',
            'village_code' => 'sometimes|required|string',
            'birth_regency_code' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        // Cek duplikat email jika email diubah
        if ($request->has('email') && $request->email !== $user->email) {
            $emailHash = User::generateHash($request->email);
            if (User::where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
                return $this->sendError('Email sudah digunakan oleh user lain.', [], 400);
            }
        }

        $user->update($request->only([
            'name',
            'nama_lengkap',
            'email',
            'no_whatsapp',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'province_code',
            'regency_code',
            'district_code',
            'village_code',
            'birth_regency_code'
        ]));

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'no_whatsapp' => $user->no_whatsapp,
            'alamat' => $user->alamat,
        ], 'Profil berhasil diperbarui.');
    }

    /**
     * Update Password User
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Password saat ini tidak sesuai.', [], 401);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);

        return $this->sendResponse([], 'Password berhasil diperbarui.');
    }

    /**
     * Update PIN User
     */
    public function updatePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|size:6|regex:/^[0-9]+$/|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();
        $user->update([
            'pin' => Hash::make($request->pin),
        ]);

        return $this->sendResponse([], 'PIN berhasil diperbarui.');
    }

    /**
     * Verify PIN User
     */
    public function verifyPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 400);
        }

        $user = $request->user();

        if (!$user->pin || !Hash::check($request->pin, $user->pin)) {
            return $this->sendError('PIN yang Anda masukkan salah.', [], 401);
        }

        return $this->sendResponse([], 'PIN terverifikasi.');
    }
}
