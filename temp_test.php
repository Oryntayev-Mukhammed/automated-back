<?php
require 'bootstrap/app.php';
\ = new Modules\\Case\\Http\\Controllers\\CaseController();
echo \->index()->getContent();

