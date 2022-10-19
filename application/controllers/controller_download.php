<?php
class Controller_Download extends Controller { 
    function action_index() { 
        $this->view->generate('download_view.php', 'template_view.php'); 
    } 
}
?>