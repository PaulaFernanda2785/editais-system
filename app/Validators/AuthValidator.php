<?php

declare(strict_types=1);

namespace App\Validators;

class AuthValidator
{
    public function validateLogin(array $payload): array
    {
        $errors = [];

        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $senha = (string) ($payload['senha'] ?? '');

        if ($email === '') {
            $errors['email'] = 'Informe o e-mail.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Informe um e-mail valido.';
        }

        if ($senha === '') {
            $errors['senha'] = 'Informe a senha.';
        } elseif (strlen($senha) < 6) {
            $errors['senha'] = 'A senha deve ter pelo menos 6 caracteres.';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'data' => [
                'email' => $email,
                'senha' => $senha,
            ],
        ];
    }
}
