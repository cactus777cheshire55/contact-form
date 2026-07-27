<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttemptToAuthenticateUser
{
    /**
     * Attempt to authenticate the user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function authenticate(array $input): User
    {
        // バリデーション
        Validator::make($input, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ])->validate();

        // ユーザーを検索
        $user = User::where('email', $input['email'])->first();

        // ユーザーが存在しない、またはパスワードが一致しない場合
        if (! $user || ! Hash::check($input['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        return $user;
    }
}
