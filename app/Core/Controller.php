<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected ?Request $request = null;
    protected ?Response $response = null;

    public function setRequestResponse(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
    }

    protected function view(string $view, array $data = []): string
    {
        return View::make($view, $data);
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        if ($this->response === null) {
            $this->response = new Response();
        }

        $this->response->redirect($url, $statusCode);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        if ($this->response === null) {
            $this->response = new Response();
        }

        $this->response->json($data, $statusCode);
    }
}
