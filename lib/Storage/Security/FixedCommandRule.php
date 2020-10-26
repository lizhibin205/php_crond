<?php
namespace Storage\Security;

final class FixedCommandRule implements CommandRule
{
    private $fixedCommand;

    public function __construct(string $fixedCommand)
    {
        $this->fixedCommand = $fixedCommand;
    }

    public function check(string $execCommand): bool
    {
        return $this->fixedCommand === $execCommand;
    }

    public function __toString() {
        return "FixedCommandRule({$this->fixedCommand})";
    }
}

