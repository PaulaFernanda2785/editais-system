<?php

declare(strict_types=1);

namespace App\Validators;

class PalavraChaveValidator
{
    /**
     * @return array{
     *     valid: bool,
     *     errors: array<string, string>,
     *     data: array<string, mixed>
     * }
     */
    public function validate(array $payload): array
    {
        $errors = [];

        $termo = trim((string) ($payload['termo'] ?? ''));
        $categoria = trim((string) ($payload['categoria'] ?? ''));
        $pesoRaw = trim((string) ($payload['peso'] ?? '1'));

        if ($termo === '') {
            $errors['termo'] = 'Informe o termo da palavra-chave.';
        } elseif (strlen($termo) > 150) {
            $errors['termo'] = 'Termo excede 150 caracteres.';
        }

        if ($categoria !== '' && strlen($categoria) > 80) {
            $errors['categoria'] = 'Categoria excede 80 caracteres.';
        }

        if ($pesoRaw === '' || !preg_match('/^\d+$/', $pesoRaw)) {
            $errors['peso'] = 'Peso deve ser numero inteiro.';
            $peso = 1;
        } else {
            $peso = (int) $pesoRaw;
            if ($peso < 1) {
                $errors['peso'] = 'Peso minimo permitido: 1.';
            } elseif ($peso > 1000) {
                $errors['peso'] = 'Peso maximo permitido: 1000.';
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'data' => [
                'termo' => $termo,
                'categoria' => $categoria !== '' ? $categoria : null,
                'peso' => $peso ?? 1,
            ],
        ];
    }
}
