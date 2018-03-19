<?php 
require_once("config.php");
$folderSoruce=ROOT."/data";


if(validpostnbr()){
	if(isset($_POST['type'])){
		// creat project 
		if($_POST['type']=="creatproject"){
			include_once("action_code/creat_project.php");
		}
		// load project 
		else if($_POST['type']=="load_project"){
			include_once("action_code/load_project.php");
		}
		// creat subfolder 
		else if($_POST['type']=="creatsubfolder"){
			include_once("action_code/creatsubfolder.php");
		}
		// creat file
		else if($_POST['type']=="creatfile"){
			include_once("action_code/creatfile.php");
		}		
		// rename file
		else if($_POST['type']=="rename"){
			include_once("action_code/rename_file.php");
		}	
		// rename file
		else if($_POST['type']=="f_delete"){
			include_once("action_code/delete_file.php");
		}	
		// load data diagram 
		else if($_POST['type']=="load_data_diagram"){
			include_once("action_code/load_data_diagram.php");
		}
		// save data diagram 
		else if($_POST['type']=="save_data_diagram"){
			include_once("action_code/save_data_diagram.php");
		}
		
	}
}

?>