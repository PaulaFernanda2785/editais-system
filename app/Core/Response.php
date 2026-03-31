<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    private string $content = '';
    private bool $sent = false;

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function view(string $view, array $data = [], int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        $this->setContent(View::make($view, $data));
        $this->send();
    }

    public function json(array $data, int $statusCode = 200): void
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = json_encode(['error' => 'Falha ao serializar JSON.'], JSON_UNESCAPED_UNICODE);
            $statusCode = 500;
        }

        $this->setStatusCode($statusCode);
        $this->header('Content-Type', 'application/json; charset=UTF-8');
        $this->setContent((string) $payload);
        $this->send();
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode);
        $this->header('Location', $url);
        $this->send();
    }

    public function send(): void
    {
        if ($this->sent) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }

        echo $this->content;
        $this->sent = true;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }
}
