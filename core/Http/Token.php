<?php

namespace Myframework\Http;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

class Token
{

    private static $data;
    public static $error = [];
    public static function decode(string $token): \stdClass|string
    {

        try {
            JWT::$leeway = 60; // $leeway in seconds
            $decoded = JWT::decode($token, new Key(getKey(), 'HS256'));
            return $decoded->data;

        } catch (InvalidArgumentException $e) {
            // provided key/key-array is empty or malformed.
            return 'Invalid Token';
        } catch (DomainException $e) {
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
            return 'Invalid Token';

        } catch (SignatureInvalidException $e) {
            // provided JWT signature verification failed.
            return 'Invalid Token';


        } catch (BeforeValidException $e) {
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
            return 'Invalid Token';

        } catch (ExpiredException $e) {
            // provided JWT is trying to be used after "exp" claim.
            // dd($e->getMessage());
            return 'Invalid Token';

        } catch (UnexpectedValueException $e) {
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
            return 'Invalid Token';
        }

    }
    public static function encode(array $payload)
    {
        $expirationTime = time() + 6;
        $payload = [
            'iss' => 'localhost',
            'aud' => 'localhost',
            "iat" => time(),
            "exp" => $expirationTime,
            'data' => [
                ...$payload
            ],
        ];

        $token = JWT::encode($payload, getKey(), 'HS256');

        return $token;
    }

    public static function get()
    {
        return self::$data;
    }

}