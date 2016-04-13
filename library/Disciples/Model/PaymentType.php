<?php
/**
 * PaymentType.php
 * Database interface for PaymentType table
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_PaymentType
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_PaymentType extends Zend_Db_Table_Abstract
{
    protected $_name    = 'payment_type';
    protected $_primary = 'id';

    public function getPaymentTypeById($id)
    {
        $row = $this->find($id)->current();
        return !empty($row) ? $row['type'] : '';
    }

    public function getAllPaymentTypes()
    {
        return $this->getAllRows();
    }

    public function getAllRows()
    {
        $rows = $this->fetchAll($this->select());
        return $rows->toArray();
    }
}