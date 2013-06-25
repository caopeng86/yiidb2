<?php

/**
 * CIbmDB2PdoAdapter class file.
 *
 * @author Edgard L. Messias <edgardmessias@gmail.com>
 * @link https://github.com/edgardmessias/yiidb2
 */

/**
 *
 * @author Edgard L. Messias <edgardmessias@gmail.com>
 * @package ext.yiidb2
 */
class CIbmDB2PdoAdapter extends PDO {

    private $_conn = null;
    
    public function __construct($dsn, $username, $passwd, $options) {
        $options[DB2_ATTR_CASE] = DB2_CASE_LOWER;

        $dsn = substr($dsn, (int) strpos($dsn, ':') + 1);

        $dsn = rtrim($dsn, ";") . ";";

        if (stripos($dsn, "UID") === false) {
            $dsn .= "UID=" . $username . ";";
        }
        if (stripos($dsn, "PWD") === false) {
            $dsn .= "PWD=" . $passwd . ";";
        }

        $isPersistant = (isset($options['persistent']) && $options['persistent'] == true);

        if ($isPersistant) {
            $this->_conn = db2_pconnect($dsn, '', '', $options);
        } else {
            $this->_conn = db2_connect($dsn, '', '', $options);
        }
        if (!$this->_conn) {
            throw new CIbmDB2PdoException(db2_conn_errormsg());
        }
    }

    public function prepare($statement, $driver_options = array()) {
        return new CIbmDB2PdoStatement($this->_conn, $statement);
    }

    public function query($statement) {
        $stmt = $this->prepare($statement);
        $stmt->execute();
        return $stmt;
    }

    public function quote($string, $parameter_type = PDO::PARAM_STR) {
        $string = db2_escape_string($string);
        if ($parameter_type == PDO::PARAM_INT) {
            return $string;
        } else {
            return "'" . $string . "'";
        }
    }

    public function exec($statement) {
        $stmt = $this->prepare($statement);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function lastInsertId($name = null) {
        return db2_last_insert_id($this->_conn);
    }

    public function beginTransaction() {
        db2_autocommit($this->_conn, DB2_AUTOCOMMIT_OFF);
    }

    public function commit() {
        if (!db2_commit($this->_conn)) {
            throw new CIbmDB2PdoException(db2_conn_errormsg($this->_conn));
        }
        db2_autocommit($this->_conn, DB2_AUTOCOMMIT_ON);
    }

    public function rollBack() {
        if (!db2_rollback($this->_conn)) {
            throw new CIbmDB2PdoException(db2_conn_errormsg($this->_conn));
        }
        db2_autocommit($this->_conn, DB2_AUTOCOMMIT_ON);
    }
    
    public function inTransaction() {
        return !((bool) db2_autocommit($this->_conn));
    }

    public function errorCode() {
        return db2_conn_error($this->_conn);
    }

    public function errorInfo() {
        return array(
            0 => db2_conn_errormsg($this->_conn),
            1 => $this->errorCode(),
        );
    }

    public function setAttribute($attribute, $value) {
        
    }

    public function getAttribute($attribute) {
        
    }

}
