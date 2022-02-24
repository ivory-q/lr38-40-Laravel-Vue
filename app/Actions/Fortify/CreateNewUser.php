<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        // Проверяем валидность введенных при регистрации данных
        Validator::make($input, [
            // Имя (обязательно, текст, макс. длина - 255 символов)
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            // Телефон (обязательно, текст, макс. длина - 255 символов,
            //          уникальная запись по таблице users, состоит из 11 чисел)
            'phone' => ['required', 'string', 'max:255', 'unique:users', 'digits:11'],
            // Встроенная проверка пароля laravel
            // return ['required', 'string', new Password, 'confirmed'];
            'password' => $this->passwordRules(),
            // Принятие правил конфиденциальности (выключено по-умолчанию, лучше не убирать)
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();
        // Создание нового пользователя в БД с введенными данными
        return User::create([
            // input['first_name'] - поля в форме регистрации
            'first_name' => $input['first_name'],
            'surname' => $input['surname'],
            'phone' => $input['phone'],
            // Hash::make - преобразует пароль в хэш, для безопасности
            // testtest => $2y$10$f9oFLhQBmlb6Y7NPQtI6GOo.58rXUGVygYI8b4pvCfbXQiO0vDh3.
            'password' => Hash::make($input['password']),
        ]);
    }
}
