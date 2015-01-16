<?php
interface AutoJudgeConnector {
    public function compile(AutoJudgeConnectorInput $input);

    public function getConfigFields();

    public function getName();

    public function getSupportedLanguages();

    public function supportsInput();
}

class AutoJudgeConnectorResult {
    public $compileStatus;

    public $output;

    const COMPILE_STATUS_OK = 'OK';
}

class AutoJudgeConnectorInput {
    public $input;

    public $code;

    public $lang;
}