<?php

namespace Myframework\Http;

class Response
{
    // Set a custom header
    public function setHeader(string $headerString)
    {
        return header($headerString);
    }

    // Set the HTTP response code
    public function code($code = 200)
    {
        http_response_code($code);
    }

    // Set a response header
    public function header(string $header)
    {
        header($header);
    }

    // Set response headers to disable caching
    public function withNoCache()
    {
        $this->header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->header('Pragma: no-cache');
        $this->header('Expires: 0');
    }

    // Set the Content-Type header
    public function withContentType(string $type)
    {
        $this->header("Content-Type: $type");
    }

    // Set a cookie
    public function withCookie(string $name, array $data)
    {
        setcookie($name, $data);
    }

    // Send a file as a download attachment
    public function withFile(string $path, string $filename)
    {
        $mime = mime_content_type($path);
        $this->header('Content-Type: ' . $mime);
        $this->header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
        exit();
    }

    // Send a JSON error response
    public function withJsonError(string $message, int $statusCode = 500)
    {
        $this->withJson(['error' => $message], $statusCode);
    }

    // Send a JSON response
    public function withJson(mixed $data, int $status = 1)
    {
        $this->header("Content-Type: application/json");
        exit(json_encode(['status' => $status, 'data' => $data]));
    }

    // Send an HTML response
    public function withHtml(string $html)
    {
        $this->header("Content-Type: text/html");
        exit($html);
    }

    // Factory method to create an instance of the class
    public static function create()
    {
        return new self();
    }

    // Additional methods

    // Redirect the request
    public function redirect(string $url, int $statusCode = 302)
    {
        $this->header("Location: " . $url);
        $this->code($statusCode);
        exit;
    }

    // Send an error response
    public function withError(mixed $errorMessage)
    {
        $this->withJson(['error' => $errorMessage], 0);
    }

    // Send a plain text response
    public function withText(string $text)
    {
        $this->header("Content-Type: text/plain");
        exit($text);
    }

    // Download a file
    public function withDownload(string $filePath, string $fileName = null)
    {
        if (!file_exists($filePath)) {
            $this->withError('File not found');
        }

        $fileName = $fileName ?? basename($filePath);

        $this->header("Content-Type: application/octet-stream");
        $this->header("Content-Disposition: attachment; filename=\"$fileName\"");
        $this->header("Content-Length: " . filesize($filePath));

        readfile($filePath);
        exit;
    }

    // Redirect to another URL
    public function withRedirect(string $url, int $statusCode = 302)
    {
        $this->header("Location: " . $url);
        $this->code($statusCode);
        exit;
    }

    // Set a custom header
    public function withHeader(string $name, string $value)
    {
        $this->header("$name: $value");
    }

    // Send an XML response
    public function withXml(mixed $data)
    {
        $this->header("Content-Type: application/xml");
        exit($data);
    }

    // Send a 404 Not Found response
    public function withNotFound(string $message = 'Not Found')
    {
        $this->withError($message, 404);
    }

    // Send a 405 Method Not Allowed response
    public function withNotAllowed(string $message = 'Method Not Allowed')
    {
        $this->withError($message, 405);
    }

    // Send a 400 Bad Request response
    public function withBadRequest(string $message = 'Bad Request')
    {
        $this->withError($message, 400);
    }

    // ... add more if neccessary
}