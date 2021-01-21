<?php
namespace Test;

class Some {
    public function get(string $a = null) {
        return "Test\Some::get, \$a = `{$a}`";
    }

    public function auto($a, Bar $Bar) {
        echo "Test\Some::auto, \$a =`{$a}`";
    }
}