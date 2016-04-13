<?php
/**
 * PaymentReasonCode.php
 * Database interface for PaymentReasonCode table
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_PaymentReasonCode
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_PaymentReasonCode extends Zend_Db_Table_Abstract
{
    protected $_name    = 'payment_reason_code';
    protected $_primary = 'id';

    public function getPaymentReasonById($id)
    {
        $row = $this->find($id)->current();
        return !empty($row) ? $row->toArray() : array();
    }

    public function getPaymentReasonByReasonCode($code)
    {
        $row = $this->fetchRow($this->select()->where('reason_code = ?', $code));
        return !empty($row) ? $row->toArray() : array();
    }

    public function getAllPaymentReasonCodes()
    {
        return $this->getAllRows();
    }

    public function getAllRows()
    {
        $rows = $this->fetchAll($this->select());
        return $rows->toArray();
    }
}