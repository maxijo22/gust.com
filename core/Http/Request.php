<?php

namespace Myframework\Http;

use Myframework\Validation\MessageBag;
use Myframework\Validation\ValidationException;
use Myframework\Validation\Validator;

class Request
{
    public function __construct(
        public readonly array $post,
        public readonly array $server,
        public readonly array $get,
        public readonly array $files,
    ) {
    }

    public function getToken(): string
    {
        $token = $_COOKIE['_Auth_SID_Token'] ?? $this->post['_Auth_SID_Token'] ?? '';
        return $token;
    }

    public static function createFromGlobal(): self
    {
        return new static(post: $_POST, get: $_GET, server: $_SERVER, files: $_FILES);
    }
    public function method(): string
    {
        return $this->post['_METHOD'] ?? $this->server['REQUEST_METHOD'];
    }
    public function url(): string
    {
        return $_SERVER['REQUEST_URI'];
    }
    public function path(): string
    {
        $path = strtok($this->url(), '?');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }
    public function getParams(): array
    {
        return $this->get;
    }
    public function postParams(): array
    {
        $post = $this->post;
        unset($post['_token']);
        return $post;
    }
    public function input($field_name)
    {
        return $this->post[$field_name] ?? null;
    }
    public function header($header_name)
    {
        $header_name = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
        return $this->server[$header_name] ?? null;
    }
    public function cookie($cookie_name)
    {
        return $this->server[$cookie_name] ?? null;
    }
    public function isSecure()
    {
        return isset($this->server['HTTPS']) && $this->server['HTTPS'] === 'on';
    }
    public function ip()
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }
    public function userAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }
    public function referrer()
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    public function isValid()
    {
        // Check if the HTTP method is allowed
        $allowed_methods = ['GET', 'POST', 'PUT', 'DELETE'];
        if (!in_array($this->method(), $allowed_methods)) {
            return false;
        }

        // Check if the request is not too large
        $max_size = 1024 * 1024; // 1 MB
        if (array_sum(array_map('strlen', $this->postparams())) > $max_size) {
            return false;
        }

        // Check if the user is authenticated
        if (!isset($_SESSION['user'])) {
            return false;
        }

        // All checks passed
        return true;
    }
    public function isAjax()
    {
        return !empty($this->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    public function has($key)
    {
        return array_key_exists($key, $this->getparams()) || array_key_exists($key, $this->postparams());
    }
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }
    public function all()
    {
        return array_merge($this->getparams(), $this->postparams());
    }

    public function crsfToken()
    {
        return $this->post['_token'] ?? null;
    }
}