<?php 
$arrayre=array("error"=>0,"message"=>"");
if(isset($_POST['project']) && isset($_POST['source']) && isset($_POST['data'])){
	
	$data_get=
	file_put_contents($folderSoruce."/".$_POST['project']."/".$_POST['source'],$_POST['data']);
	
	
	
}
else {
	$arrayre['error']=1;
	$arrayre['message']= "Miss param";
}
echo json_encode($arrayre);
?>