<?php 
$arrayre=array("error"=>0,"message"=>"");
if(isset($_POST['name'])){
	// check folder and creat if not exist
	if(file_exists($folderSoruce."/".$_POST['name'])){
		$arrayre['error']=1;
		$arrayre['message']= "Have exist project. PLease choose another name!";
	} 
	else {
		mkdir($folderSoruce."/".$_POST['name']);
	$data_project=array();
	// parent
	$data_project[]=array("key"=>0,"name"=>$_POST['name']);
	$data_project[]=array("key"=>1,"name"=>"Note","parent"=>0);
	
	$info_project=array("key_sever"=>1,
	"data_tree"=>$data_project);
	file_put_contents($folderSoruce."/".$_POST['name']."/1.rootdata.txt",json_encode($info_project));
	
	}
	
}
else {
	$arrayre['error']=1;
	$arrayre['message']= "Miss param";
}
echo json_encode($arrayre);
?>