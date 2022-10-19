<?php
class Controller_Delete extends Controller { 
    function action_index() { 
        $this->view->generate('delete_view.php', 'template_view.php'); 
    } 
}
?>