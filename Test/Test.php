<?php 
namespace Test;

class Test {
    protected $Some;
	public $a;
    public function __construct(Some $Some, string $a = null) {
        $this->Some = $Some;
		$this->a = $a;
    }

    public function get() {
        return $this->Some->get();
    }
}