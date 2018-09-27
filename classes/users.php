<?php
/*
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

require_once dirname(__FILE__) . "/../config.php";

// Store and manage users info
class token_user {

    private $_mysqli;
    private $addr;
    public function __construct($addr = null, $host = DIGIID_DB_HOST, $user = DIGIID_DB_USER, $pass = DIGIID_DB_PASS, $name = DIGIID_DB_NAME) {
        @$this->_mysqli = new mysqli($host, $user, $pass, $name);
	if ($this->_mysqli->connect_errno) die ($this->_mysqli->connect_error);
	$this->addr = $addr;

        $this->checkInstalled();
    }

    /**
      * Create tables if not exists
      * @return bool
      */
    public function checkInstalled() {
        $required_tables = array (
            DIGIID_TBL_PREFIX . 'users' => '
                CREATE TABLE `' . DIGIID_TBL_PREFIX . 'users` (
                    `addr` varchar(46) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                    `fio` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        foreach ($required_tables as $name => $sql) {
            $table_exists = ($test = $this->_mysqli->query("SHOW TABLES LIKE '$name'")) && $test->num_rows == 1;
            if (!$table_exists) $this->_mysqli->query($sql);
        }
    }

    /**
     * Insert user detail in the database
     *
     * @param $addr
     * @param array $info
     * @return bool|mysqli_result
     */
    public function insert($info) {
        return $this->_mysqli->query(sprintf("INSERT INTO " . DIGIID_TBL_PREFIX . "users (`addr`, `fio`) VALUES ('%s', '%s')", $this->_mysqli->real_escape_string($this->addr), $this->_mysqli->real_escape_string($info['fio'])));
    }

    /**
     * Update table with user info
     *
     * @param $nonce
     * @param $address
     * @return bool|mysqli_result
     */
    public function update($info) {
        return $this->_mysqli->query(sprintf("UPDATE " . DIGIID_TBL_PREFIX . "users SET fio = '%s' WHERE addr = '%s' ", $this->_mysqli->real_escape_string($info['fio']), $this->_mysqli->real_escape_string($this->addr)));
    }

    /**
     * Forget some user
     *
     * @param $address
     * @return bool|mysqli_result
     */
    public function delete() {
        return $this->_mysqli->query(sprintf("DELETE FROM " . DIGIID_TBL_PREFIX . "users WHERE addr = '%s' ", $this->_mysqli->real_escape_string($this->addr)));
    }

    /**
     * Get user info
     *
     * @return array
     */
    public function get_info() {
        $result = $this->_mysqli->query($sql = sprintf("SELECT fio FROM " . DIGIID_TBL_PREFIX . "users WHERE addr = '%s'", $this->_mysqli->real_escape_string($this->addr)));
        if($result) {
            $row = $result->fetch_assoc();
            if(count($row)) return $row;
        }
        return false;
    }

}