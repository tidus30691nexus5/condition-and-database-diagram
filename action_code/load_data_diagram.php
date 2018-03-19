<?php 
$arrayre=array("error"=>0,"message"=>"");
if(isset($_POST['project']) && isset($_POST['source'])){
	// check folder and creat if not exist
	if(!file_exists($folderSoruce."/".$_POST['project']."/".$_POST['source'])){
		$arrayre['error']=1;
		$arrayre['message']= "File not exist!";
	} 
	else {
	$data_get=
	file_get_contents($folderSoruce."/".$_POST['project']."/".$_POST['source']);
		$arrayre['data']=$data_get;
	}
	
}
else {
	$arrayre['error']=1;
	$arrayre['message']= "Miss param";
}
echo json_encode($arrayre);
?>