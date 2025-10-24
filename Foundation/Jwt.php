<?php
namespace Modules\Feishu\Foundation;

class Jwt
{
    public static function encode(array $payload, string $secret, string $alg = 'HS256'): string
    {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $segments = [];
        $segments[] = base64_encode(json_encode($header));
        $segments[] = base64_encode(json_encode($payload));
        $signingInput = implode('.', $segments);
        $signature = self::sign($signingInput, $secret, $alg);
        $segments[] = base64_encode($signature);
        return implode('.', $segments);
    }

    public static function decode(string $jwt, string $secret, array $allowedAlgs = ['HS256']): array
    {
        $segments = explode('.', $jwt);
        if (count($segments) !== 3) {
            throw new \InvalidArgumentException('Wrong number of segments');
        }
        list($headerB64, $payloadB64, $signatureB64) = $segments;
        $header = json_decode(base64_decode($headerB64), true);
        if (null === $header) {
            throw new \InvalidArgumentException('Invalid header encoding');
        }
        if (!isset($header['alg']) || !in_array($header['alg'], $allowedAlgs)) {
            throw new \InvalidArgumentException('Algorithm not allowed');
        }
        $payload = json_decode(base64_decode($payloadB64), true);
        if (null === $payload) {
            throw new \InvalidArgumentException('Invalid payload encoding');
        }
        $signature = base64_decode($signatureB64);
        if (!self::verify("$headerB64.$payloadB64", $signature, $secret, $header['alg'])) {
            throw new \InvalidArgumentException('Signature verification failed');
        }
        return $payload;
    }

    private static function sign(string $input, string $secret, string $alg): string
    {
        switch ($alg) {
            case 'HS256':
                return hash_hmac('sha256', $input, $secret, true);
            case 'RS256':
                $privateKey = openssl_pkey_get_private($secret);
                if (!$privateKey) {
                    throw new \InvalidArgumentException('Invalid private key for RS256');
                }
                $signature = '';
                openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256);
                return $signature;
            default:
                throw new \InvalidArgumentException('Unsupported algorithm');
        }
    }

    private static function verify(string $input, string $signature, string $secret, string $alg): bool
    {
        switch ($alg) {
            case 'HS256':
                $expectedSig = hash_hmac('sha256', $input, $secret, true);
                return hash_equals($expectedSig, $signature);
            case 'RS256':
                $publicKey = openssl_pkey_get_public($secret);
                if (!$publicKey) {
                    throw new \InvalidArgumentException('Invalid public key for RS256');
                }
                return (bool)openssl_verify($input, $signature, $publicKey, OPENSSL_ALGO_SHA256);
            default:
                throw new \InvalidArgumentException('Unsupported algorithm');
            }
    }
}