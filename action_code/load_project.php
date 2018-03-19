<?php 
$arrayre=array("error"=>0,"message"=>"");
if(isset($_POST['name'])){
	// check folder and creat if not exist
	if(!file_exists($folderSoruce."/".$_POST['name'])){
		$arrayre['error']=1;
		$arrayre['message']= "Project not exist!";
	} 
	else {
	$data_get=
	json_decode(file_get_contents($folderSoruce."/".$_POST['name']."/1.rootdata.txt"),true);
		$arrayre['key_current']=$data_get["key_sever"];
		$arrayre['data_tree']=json_encode($data_get["data_tree"]);
	}
	
}
else {
	$arrayre['error']=1;
	$arrayre['message']= "Miss param";
}
echo json_encode($arrayre);
?>