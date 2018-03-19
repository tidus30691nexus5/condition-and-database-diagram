<?php 
$arrayre=array("error"=>0,"message"=>"");
$arrayre['type']="f_delete";
if(isset($_POST['project']) && isset($_POST['dataupdate'])){
	// check folder and creat if not exist
	
	$data_get=
	json_decode(file_get_contents($folderSoruce."/".$_POST['project']."/1.rootdata.txt"),true);
		$infokey=$data_get["key_sever"];
		
		/*
		// get index of key parrent 
		$index=0;
		foreach($data_get["data_tree"]  as $val){
			if($val['key']==$_POST['keyparent'])
			{

				break;
			}
			$index++;
		}
		// delete and index again array after deleted
		//array_splice($data_get["data_tree"],$index,1);
		
		*/
		// save data tree root 
	$info_project=array("key_sever"=>$infokey,
	"data_tree"=>json_decode($_POST["dataupdate"],true)['nodeDataArray']);
	file_put_contents($folderSoruce."/".$_POST['project']."/1.rootdata.txt",json_encode($info_project));
	
	
}
else {
	$arrayre['error']=1;
	$arrayre['message']= "Select Node of tree first!";
}
echo json_encode($arrayre);


?>