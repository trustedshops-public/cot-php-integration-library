<?php

namespace TRSTDExampleIntegration\COT;

use Db;
use TRSTDExampleIntegration\COT\COTAuth;
use TRSTD\COT\Token;

final class COTAuthRepository
{
    /**
     * @var Db
     */
    private $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * @param Token $token
     * @param string $sub
     */
    public function save($token, $sub)
    {
        $cotAuth = $this->get($sub);
        if (!$cotAuth) {
            $this->create($token, $sub);
        } else {
            $this->update($token, $sub);
        }
    }

    /**
     * @param string $sub
     */
    public function delete($sub)
    {
        $this->db->delete(COTAuth::$definition['table'], 'id_sub = "' . pSQL($sub) . '"');
    }

    /**
     * @param string $sub
     * @return COTAuth|null
     */
    public function get($sub)
    {
        $request = "SELECT * FROM " . _DB_PREFIX_ . COTAuth::$definition['table'] . " WHERE id_sub = '" . pSQL($sub) . "'";
        $result = $this->db->getRow($request);
        if (!$result) {
            return null;
        }

        $result = json_decode(json_encode($result));

        $cotAuth = new COTAuth();
        $cotAuth->id_sub = $result->id_sub;
        $cotAuth->id_token = $result->id_token;
        $cotAuth->refresh_token = $result->refresh_token;
        $cotAuth->access_token = $result->access_token;

        return $cotAuth;
    }

    private function create($sub, Token $token)
    {
        $this->db->insert(
            COTAuth::$definition['table'],
            [
                'id_sub' => pSQL($sub),
                'id_token' => pSQL($token->idToken),
                'refresh_token' => pSQL($token->refreshToken),
                'access_token' => pSQL($token->accessToken)
            ]
        );
    }

    private function update($sub, Token $token)
    {
        $this->db->update(
            COTAuth::$definition['table'],
            [
                'id_token' => pSQL($token->idToken),
                'refresh_token' => pSQL($token->refreshToken),
                'access_token' => pSQL($token->accessToken)
            ],
            'id_sub = "' . pSQL($sub) . '"',
            1,
            true
        );
    }

    // TODO to be removed. This is only for testing purposes
    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . COTAuth::$definition['table'] . '` (
            `id_sub` varchar(36) NOT NULL,
            `id_token` TEXT NOT NULL,
            `refresh_token` TEXT NOT NULL,
            `access_token` TEXT NOT NULL,
            PRIMARY KEY (`id_sub`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return $this->db->execute($sql);
    }

    // TODO to be removed. This is only for testing purposes
    public function uninstall()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . COTAuth::$definition['table'] . '`';

        return $this->db->execute($sql);
    }
}
