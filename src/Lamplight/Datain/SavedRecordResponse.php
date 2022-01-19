<?php

namespace Lamplight\Datain;

/**
 *
 */
class SavedRecordResponse {


    /**
     * @var int
     */
    protected int $id;
    /**
     * @var bool
     */
    protected bool $success;
    /**
     * @var int
     */
    protected int $error_code = 0;
    /**
     * @var string
     */
    protected string $error_message = '';


    /**
     * @param int $id
     * @param bool $success
     * @param int $error_code
     * @param string $error_message
     */
    public function __construct (int $id, bool $success, int $error_code = 0, string $error_message = '') {
        $this->id = $id;
        $this->success = $success;
        $this->error_code = $error_code;
        $this->error_message = $error_message;
    }

    /**
     * @return int
     */
    public function getId (): int {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function success (): bool {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getErrorCode (): int {
        return $this->error_code;
    }

    /**
     * @return string
     */
    public function getErrorMessage (): string {
        return $this->error_message;
    }

}
