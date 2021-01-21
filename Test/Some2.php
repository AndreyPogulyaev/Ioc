<?php

namespace Test;

class Some2 extends Some {
	public $a;
    public $b;
    public function __construct($a, $b) {
        $this->a = $a;
        $this->b = $b;
    }
    public function get($a = null) {
		return "Test\Some2::get, \$a = `{$this->a}`, \$b = `{$this->b}`";
    }
}