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
     * @param string $ctcId
     */
    public function saveAuth($token, $ctcId)
    {
        $cotAuth = $this->getAuthByCtcId($ctcId);
        if (!$cotAuth) {
            $this->createAuth($token, $ctcId);
        } else {
            $this->updateAuth($token, $ctcId);
        }
    }

    /**
     * @param string $ctcId
     */
    public function deleteAuthByCtcId($ctcId)
    {
        $this->db->delete(COTAuth::$definition['table'], 'id_ctc = "' . pSQL($ctcId) . '"');
    }

    /**
     * @param string $ctcId
     * @return COTAuth|null
     */
    public function getAuthByCtcId($ctcId)
    {
        $request = "SELECT * FROM " . _DB_PREFIX_ . COTAuth::$definition['table'] . " WHERE id_ctc = '" . pSQL($ctcId) . "'";
        $result = $this->db->getRow($request);
        if (!$result) {
            return null;
        }

        $result = json_decode(json_encode($result));

        $cotAuth = new COTAuth();
        $cotAuth->id_ctc = $result->id_ctc;
        $cotAuth->id_token = $result->id_token;
        $cotAuth->refresh_token = $result->refresh_token;
        $cotAuth->access_token = $result->access_token;

        return $cotAuth;
    }

    private function createAuth(Token $token, $ctcId)
    {
        $this->db->insert(
            COTAuth::$definition['table'],
            [
                'id_ctc' => pSQL($ctcId),
                'id_token' => pSQL($token->idToken),
                'refresh_token' => pSQL($token->refreshToken),
                'access_token' => pSQL($token->accessToken)
            ]
        );
    }

    private function updateAuth(Token $token, $ctcId)
    {
        $this->db->update(
            COTAuth::$definition['table'],
            [
                'id_token' => pSQL($token->idToken),
                'refresh_token' => pSQL($token->refreshToken),
                'access_token' => pSQL($token->accessToken)
            ],
            'id_ctc = "' . pSQL($ctcId) . '"',
            1,
            true
        );
    }

    // TODO to be removed. This is only for testing purposes
    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . COTAuth::$definition['table'] . '` (
            `id_ctc` varchar(36) NOT NULL,
            `id_token` TEXT NOT NULL,
            `refresh_token` TEXT NOT NULL,
            `access_token` TEXT NOT NULL,
            PRIMARY KEY (`id_ctc`)
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
