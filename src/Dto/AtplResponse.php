<?php

namespace App\Dto;

class AtplResponse
{
    const ACTION = 'action';
    const DATA = 'data';
    const SUCCESS = 'success';
    const TOTAL = 'total';

    private $response = [
        self::ACTION => "",
        self::DATA => [],
        self::TOTAL => -1,
        self::SUCCESS => false
    ];

    /**
     * AtplResponse constructor.
     * @param string $action
     */
    public function __construct($action = "") {
        $this->response[self::ACTION] = $action;
    }

    public function setData($data = []) {
        $this->response[self::DATA] = $data;
    }

    public function setSuccess() {
        $this->response[self::SUCCESS] = true;
    }

    public function setTotal(int $total) {
        $this->response[self::TOTAL] = $total;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }
}