<?php
namespace Storage\Security;

final class FixedCommandRule implements CommandRule
{
    private $fixedCommand;

    public function FixedCommandRule(string $fixedCommand)
    {
        $this->fixedCommand = $fixedCommand;
    }

    public function check(string $execCommand): bool
    {
        return $this->fixedCommand === $execCommand;
    }
}

