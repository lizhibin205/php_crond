<?php
/*
 * 安全性配置
 */
use Storage\Security\FixedCommandRule;

return [
    new FixedCommandRule("echo"),
];