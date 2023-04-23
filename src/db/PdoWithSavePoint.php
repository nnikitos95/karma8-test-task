<?php

class PdoWithSavePoint extends PDO
{
    /**
     * @var array Database drivers that support SAVEPOINT * statements.
     */
    protected static array $_supportedDrivers = ["pgsql", "mysql"];

    /**
     * @var int the current transaction depth
     */
    protected int $_transactionDepth = 0;

    /**
     * Test if database driver support savepoints
     *
     * @return bool
     */
    protected function hasSavepoint(): bool
    {
        return in_array($this->getAttribute(PDO::ATTR_DRIVER_NAME),
            self::$_supportedDrivers, true);
    }

    /**
     * Start transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        $result = false;
        if ($this->_transactionDepth === 0 || !$this->hasSavepoint()) {
            $result = parent::beginTransaction();
        } else {
            $result = $this->exec("SAVEPOINT LEVEL{$this->_transactionDepth}");
        }

        $this->_transactionDepth++;

        return $result;
    }

    /**
     * Commit current transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        $this->_transactionDepth--;

        if($this->_transactionDepth === 0 || !$this->hasSavepoint()) {
            return parent::commit();
        }

        return (bool) $this->exec("RELEASE SAVEPOINT LEVEL{$this->_transactionDepth}");
    }

    /**
     * Rollback current transaction,
     *
     * @throws PDOException if there is no transaction started
     * @return bool
     */
    public function rollBack(): bool
    {
        if ($this->_transactionDepth === 0) {
            throw new PDOException('Rollback error : There is no transaction started');
        }

        $this->_transactionDepth--;

        if($this->_transactionDepth === 0 || !$this->hasSavepoint()) {
            return parent::rollBack();
        }

        return (bool)$this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->_transactionDepth}");
    }
}