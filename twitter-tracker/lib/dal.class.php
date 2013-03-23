<?php

/**
 *  Database Access Layer.
 *
 * @version    SVN: $Id$
 */
class dal {

    protected $_link;
    protected $_result;

    /**
     * Connect
     */
    public function connect()
    {
        if ($this->_link !== null) {
            return $this->_link;
        }

        if (($this->_link = @mysqli_connect(DBHOST, DBUSER, DBPASS, DBBASE))) {
            return $this->_link;
        }

        throw new Exception('Error connecting to the server : ' . mysqli_connect_error());
    }

    /**
     * Execute the specified query
     */
    public function query($query)
    {
        if (!is_string($query) || empty($query)) {
            throw new Exception('The specified query is not valid.');  
        }

        $this->connect();
        if ($this->_result = mysqli_query($this->_link, $query)) {
            return $this->_result;
        }

        throw new Exception('Error executing the specified query ' . $query . mysqli_error($this->_link));
    }

    /**
     * Perform a SELECT statement
     */
    public function select($table, $where = '', $fields = '*', $order = '', $limit = null, $offset = null)
    {
        $query = 'SELECT ' . $fields . ' FROM ' . $table
               . (($where) ? ' WHERE ' . $where : '')
               . (($limit) ? ' LIMIT ' . $limit : '')
               . (($offset && $limit) ? ' OFFSET ' . $offset : '')
               . (($order) ? ' ORDER BY ' . $order : '');               
        $this->query($query);
        return $this->countRows();
    }

    /**
     * Perform an INSERT statement
     */ 
    public function insert($table, array $data)
    {
        $fields = implode(',', array_keys($data));
        $values = implode(',', array_map(array($this, 'quoteValue'), array_values($data)));
        $query = 'INSERT INTO ' . $table . ' (' . $fields . ') ' . ' VALUES (' . $values . ')';
        $this->query($query);
        return $this->getInsertId();
    }

    /**
     * Perform an UPDATE statement
     */
    public function update($table, array $data, $where = '')
    {
        $set = array();
        foreach ($data as $field => $value) {
            $set[] = $field . '=' . $this->quoteValue($value);
        }
        $set = implode(',', $set);
        $query = 'UPDATE ' . $table . ' SET ' . $set
               . (($where) ? ' WHERE ' . $where : '');
        $this->query($query);
        return $this->getAffectedRows(); 
    }

    /**
     * Escape the specified value
     */
    public function quoteValue($value)
    {
        $this->connect();
        if ($value === null) {
            $value = 'NULL';
        }
        else if (!is_numeric($value)) {
            $value = "'" . mysqli_real_escape_string($this->_link, $value) . "'";
        }
        return $value;
    }
   
    /**
     * Fetch a single row from the current result set
     */
    public function fetch()
    {
        if ($this->_result !== null) {
            if (($row = mysqli_fetch_array($this->_result, MYSQLI_ASSOC)) !== false) {
                return $row;
            }
            $this->freeResult();
            return false;
        }
        return null;
    }

    /**
     * Fetch all rows from the current result set
     */
    public function fetchAll()
    {
        if ($this->_result !== null) {
            $all = array();
            while ($row = mysqli_fetch_assoc($this->_result)) {
            $all[] = $row;
            }
            return $all;

            $this->freeResult();
            return false;
        }
        return null;
    }

    /**
     * Get the insertion ID
     */
    public function getInsertId()
    {
        return $this->_link !== null ?
               mysqli_insert_id($this->_link) :
               null;
    }
 
    /**
     * Get the number of rows returned by the current result set
     */ 
    public function countRows()
    {
        return $this->_result !== null ?
               mysqli_num_rows($this->_result) :
               0;
    }
    
    /**
     * Get the number of affected rows
     */
    public function getAffectedRows()
    {
        return $this->_link !== null ?
               mysqli_affected_rows($this->_link) :
               0;
    }
 
    /**
     * Free up the current result set
     */
    public function freeResult()
    {
        if ($this->_result !== null) {
            mysqli_free_result($this->_result);
            return true;
        }
        return false;
    }

    /**
     * Close explicitly the database connection
     */
    public function disconnect()
    {
        if ($this->_link !== null) {
            mysqli_close($this->_link);
            $this->_link = null;
            return true;
        }
        return false;
    }
    
    /**
     * Close automatically
     */
    public function __destruct()
    {
        $this->disconnect();
    }

}

