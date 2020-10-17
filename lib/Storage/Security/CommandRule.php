<?php
namespace Storage\Security;

interface CommandRule
{
    public function check(string $execCommand) : bool;
}