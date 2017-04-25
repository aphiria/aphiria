<?php
namespace Opulence\Router\Requests;

/**
 * Defines a parser that reads the request headers from the $_SERVER super global
 */
class RequestHeaderParser
{
    /** @var array These headers do not have the HTTP_ prefix */
    private static $specialCaseHeaders = [
        'AUTH_TYPE' => true,
        'CONTENT_LENGTH' => true,
        'CONTENT_TYPE' => true,
        'PHP_AUTH_DIGEST' => true,
        'PHP_AUTH_PW' => true,
        'PHP_AUTH_TYPE' => true,
        'PHP_AUTH_USER' => true
    ];

    /**
     * Parses headers from the $_SERVER super global
     *
     * @param array $server The $_SERVER super global
     * @return array The mapping of header names => values
     */
    public function parseHeaders(array $server) : array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            $uppercasedKey = strtoupper($key);

            if (isset(self::$specialCaseHeaders[$uppercasedKey]) || strpos($uppercasedKey, 'HTTP_') === 0) {
                if (!is_array($value)) {
                    $value = [$value];
                }

                $headers[$this->normalizeName($key)] = $value;
            }
        }

        return $headers;
    }

    /**
     * Normalizes a name
     *
     * @param string $name The name to normalize
     * @return string The normalized name
     */
    private function normalizeName($name)
    {
        $dashedName = strtr($name, '_', '-');

        if (strpos(strtoupper($dashedName), 'HTTP-') === 0) {
            $dashedName = substr($dashedName, 5);
        }

        return $dashedName;
    }
}
