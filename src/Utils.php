<?php

namespace PrestaShop\Module\Arengu\Auth;

class Utils
{
    public function getApacheHeaders()
    {
        static $headers = null;

        if ($headers === null) {
            if(function_exists('apache_request_headers')) {
                $headers = apache_request_headers();

                if ($headers === false) {
                    $headers = [];
                } else {
                    $headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
                }
            } else {
                $headers = [];
            }
        }

        return $headers;
    }

    public function getAuthorizationHeader()
    {
        // since some environments insist on deleting the 'Authorization' header
        // from the request, try to fall back to our own non-standard header
        $names = ['authorization', 'arengu-authorization'];

        // https://bugs.php.net/bug.php?id=72915
        // https://github.com/symfony/symfony/issues/19693
        // https://datatracker.ietf.org/doc/html/rfc3875#section-9.2
        foreach ($names as $lower) {
            $upper = strtoupper($lower);
            $prefixed = 'HTTP_' . str_replace('-', '_', $upper);

            // fpm/cli
            if (!empty($_SERVER[$prefixed])) {
                return $_SERVER[$prefixed];
            }
            
            // old cgi?
            if (!empty($_SERVER[$upper])) {
                return $_SERVER[$upper];
            }

            // apache
            $apache_headers = $this->getApacheHeaders();

            if (!empty($apache_headers[$lower])) {
                return $apache_headers[$lower];
            }
        }

        return '';
    }

    public function getFormattedErrors(\FormInterface $form)
    {
        $output = [];

        foreach ($form->getErrors() as $field => $errors) {
            // make generic errors a bit more accessible
            if ($field === '') {
                $field = '_request';
            }

            foreach ($errors as $error) {
                $output[$field] =
                    (isset($output[$field]) ? ' ' : '') .
                    rtrim($error, '.');
            }
        }

        return $output;
    }

    public function presentUser(\Customer $user)
    {
        $groups = array_map(
            function ($group) { return (int) $group['id']; },
            $user->getWsGroups()
        );

        return [
            'id' => (int) $user->id,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'birthday' => $user->birthday,
            'id_gender' => $user->id_gender,
            'company' => $user->company,
            'newsletter' => $user->newsletter,
            'optin' => $user->optin,
            'default_group' => (int) $user->id_default_group,
            'groups' => $groups,
        ];
    }

    public function getTrimmedString($arr, $key)
    {
        return
            !empty($arr[$key]) && (
                is_string($arr[$key]) ||
                is_int($arr[$key]) ||
                is_float($arr[$key]) ||
                is_bool($arr[$key])
            ) ?
            (string) trim($arr[$key]) :
            '';
    }

    public function parseBody()
    {
        $body = @file_get_contents('php://input');

        if ($body === false) {
            throw new \Exception('Failed to read POST body');
        }

        $parsed = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse JSON data');
        }

        return $parsed;
    }
}
