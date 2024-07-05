<?php

namespace COT;

use COT\Exception\HttpException;

class HttpClient
{
    /**
     * @var int
     */
    const TIMEOUT = 5;

    /**
     * @param string $url - URL to get from
     * @param array|null $headers - Headers to send
     * @return mixed
     * @throws HttpException
     */
    public static function get($url, $headers = null)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => HttpClient::TIMEOUT,
        ]);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, false);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new HttpException(
                "Error occured when getting from " . $url . " -> Message: "
                    . (empty($error_msg) ? 'No Message' : $error_msg)
                    . " Error Code: " . curl_errno($ch),
                curl_errno($ch)
            );
        }

        curl_close($ch);

        return json_decode($response);
    }

    /**
     * @param string $url - URL to post to
     * @param array|null $headers - Headers to send
     * @param array|null $data - Data to send
     * @return mixed
     * @throws HttpException
     */
    public static function post($url, $headers = null, $data = null)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => HttpClient::TIMEOUT
        ]);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, false);
        }

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new HttpException(
                "Error occured when posting to " . $url . " -> Message: "
                    . (empty($error_msg) ? 'No Message' : $error_msg)
                    . " Error Code: " . curl_errno($ch),
                curl_errno($ch)
            );
        }

        curl_close($ch);

        return json_decode($response);
    }
}
