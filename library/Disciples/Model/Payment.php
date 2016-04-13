<?php
/**
 * Payment.php
 * Database interface for Payment table
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
/**
 *  Disciples_Model_Payment
 *
 * @package Disciples
 * @author Sangwoo Han<linkedkorean@gmail.com>
 */
class Disciples_Model_Payment extends Zend_Db_Table_Abstract
{
    protected $_name    = 'payment';
    protected $_primary = 'id';

    public function getAllPayment()
    {
        return $this->getAllRows();
    }

    public function getAllRows()
    {
        $rows = $this->fetchAll($this->select());
        return $rows->toArray();
    }

    public function getPaymentByStudentId($studentId)
    {
        $rows = $this->fetchAll(
            $this->select()->where('student_id = ?', $studentId)
        );

        if (count($rows) > 0) {
            return $this->_loadPaymentInfo($rows->toArray());
        } else {
            return array();
        }
    }

    private function _loadPaymentInfo($rows)
    {
        $paymentTypeTable = new Disciples_Model_PaymentType();
        $paymentReasonCodeTable = new Disciples_Model_PaymentReasonCode();

        foreach ($rows as $id => $row) {
            $paymentType = $paymentTypeTable->getPaymentTypeById($row['type']);

            if (!empty($paymentType)) {
                $rows[$id]['type_str'] = $paymentType;
            }

            $paymentReason = $paymentReasonCodeTable->getPaymentReasonById($row['reason_code']);

            if (!empty($paymentReason)) {
                $rows[$id]['reason'] = $paymentReason;
            }

        }

        return $rows;
    }

    public function getPaymentBySelection($data)
    {
        if ($data['year'] == 'all') {
            return $this->getPaymentByStudentId($data['student_id']);
        } else {

            $where = $this->select()
                ->where('student_id = ?', $data['student_id']);

            if ($data['year'] != 'all') {
                $startDate = $data['year'] . '-01-01';
                $endDate = ($data['year'] + 1) . '-01-01';

                if ($data['semester'] == 'spring') {
                    $startDate = $data['year'] . '-01-01';
                    $endDate = $data['year'] . '-07-01';
                } else if ($data['semester'] == 'fall') {
                    $startDate = $data['year'] . '-07-01';
                    $endDate = ($data['year'] + 1) . '-01-01';
                }

                $where = $where->where('date >= ?', $startDate)
                               ->where('date < ?', $endDate);
            }

            $rows = $this->fetchAll($where);

            if (count($rows) > 0) {
                return $this->_loadPaymentInfo($rows->toArray());
            } else {
                return array();
            }
        }
    }

    public function addPayment($data)
    {
        if ($this->_validatePayment($data)) {
            $this->insert($data);
            return $this->getAdapter()->lastInsertId();
        }

        return false;
    }

    public function removePayment($id)
    {
        $this->delete($this->getAdapter()->quoteInto('id = ?', $id));
        return true;
    }

    public function updatePayment($data)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $data['id']);
        $this->update($data, $where);
        return true;
    }

    private function _validatePayment($data)
    {
        // TODO: add logic to validate the data for payment

        return true;
    }
}