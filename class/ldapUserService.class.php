<?php
/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 29/01/2018
 * Time: 09:37
 */

/**
 * Class ldapService
 * A singleton class to interact with OpenLdap active diretory
 */
include_once("ldapUser.class.php");
include_once("ldapConnect.class.php");
include_once("ldapUtil.class.php");

class ldapUserService {

    private static $USER_TOP_CLASS = "top";
    private static $USER_PERSON_CLASS = "person";
    private $ldapConnect;
    private static $instance;
    private $ldapUtil;

    public static function getInstance() : ldapUserService {
        if (self::$instance == null) {
            self::$instance = new ldapUserService();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->ldapConnect = ldapConnect::getInstance();
        $this->ldapUtil = ldapUtil::getInstance();
    }

    /**
     * @return ldapUser[]
     */
    public function getUsers() {
        $users = array();
        $connection = $this->ldapConnect->connect();
        $domainDn = ldapConnect::$ldapBaseDn;
        if ($connection != null) {
            $search_filter = '(objectClass=person)';
            $attributes = ["givenname","samaccountname","sn"];
            $result = ldap_search($connection, $domainDn, $search_filter, $attributes);
            if (FALSE !== $result){
                $entries = ldap_get_entries($connection, $result);
                for ($cnt = 0; $cnt < count($entries); $cnt++) {
                    $surname = $entries[$cnt]["sn"][0];
                    $name = $entries[$cnt]["givenname"][0];
                    //if (!empty($surname) && !empty($name)) {
                        $user = new ldapUser($surname,$name);
                        $users[] = $user;
                    //}
                }
            }
            $this->ldapConnect->disconnect($connection);
        } else {
            echo "LDAP connection failed..." . ldap_error($connection);
        }
        return $users;
    }

    /**
     * Service method which add new user
     *
     * @param $name
     * @param $surname
     */
    public function addUser($name, $surname){
        $connection = $this->ldapConnect->connect();

        if ($connection != null) {

            // another time check var
            if(!empty($name) && !empty($surname)) {

                $info["uid"] = $name[0].$surname;
                $info["cn"] = $name . " " . $surname;
                $info["sn"] = $surname;
                $info["givenname"] = $name;
                $info['objectClass'][0] = self::$USER_TOP_CLASS;
                $info["objectClass"][1] = self::$USER_PERSON_CLASS;

                $dn = $this->ldapUtil->buildUserDn($surname, $name);
                // add data to directory
                $r = ldap_add($connection, $dn, $info);
                echo ldap_error($connection);
                var_dump($r);
            }

            $this->ldapConnect->disconnect($connection);
        } else {
            echo "LDAP connection failed..." . ldap_error($connection);
        }
    }

    public function delUser($uid){

        $connection = $this->ldapConnect->connect();

        if ($connection != null) {

            // build dn with a known uid
            $dn = $this->ldapUtil->buildUserDnWithUid($uid);

            // delete user by uid
            ldap_delete($connection, $dn);
            echo ldap_error($connection);

        }else{
            echo "LDAP connection failed..." . ldap_error($connection);
        }
    }
}

?>