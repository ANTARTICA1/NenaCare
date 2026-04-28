<?php
require_once 'Facility.php';

class FurnitureFacility extends Facility {
    
    public function __construct($name, $description) {
        parent::__construct($name, $description);
    }

    public function getType() {
        return 'Furnitur';
    }
}
?>
