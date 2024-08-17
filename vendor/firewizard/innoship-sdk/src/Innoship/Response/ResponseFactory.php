<?php

namespace CurieRO\Innoship\Response;

class ResponseFactory
{
    public static function make($responseBody, $statusCode, $error, $headers = []): Contract
    {
        if ($statusCode == 401) {
            return new Unauthorized('Unauthorized', 'Unauthorized');
        }
        if ($statusCode == 400) {
            if (static::shouldDecode($headers['content-type'] ?? [])) {
                $decodedBody = json_decode($responseBody, \true);
            }
            if (empty($decodedBody['errors'])) {
                $error = 'Unknown error';
            } else {
                $errors = array_column($decodedBody['errors'], 'message');
                if (!empty($errors)) {
                    $error = implode(', ', $errors);
                } else {
                    $error = implode("&", array_map(function ($a) {
                        return implode(",", $a);
                    }, $decodedBody['errors']));
                }
            }
            return new BadRequest($error, $responseBody);
        }
        if ($statusCode == 200 || $statusCode == 201) {
            if (static::shouldDecode($headers['content-type'] ?? [])) {
                $responseBody = json_decode($responseBody, \true);
            }
            return new Response($responseBody);
        }
        return new BadRequest("Error {$statusCode}", $responseBody);
    }
    protected static function shouldDecode(array $contentTypes)
    {
        foreach ($contentTypes as $type) {
            if (\false !== strpos(strtolower($type), 'json')) {
                return \true;
            }
        }
        return \false;
    }
}
