<?php 
$arrayre=array("error"=>0,"message"=>"");
$arrayre['type']="rename";
if(isset($_POST['project']) && isset($_POST['namefile']) && isset($_POST['keyparent'])){
	// check folder and creat if not exist
	
	$data_get=
	json_decode(file_get_contents($folderSoruce."/".$_POST['project']."/1.rootdata.txt"),true);
		$infokey=$data_get["key_sever"];
	
		
		// update value by key parent 
		$index=0;
		foreach($data_get["data_tree"]  as $val){
			if($val['key']==$_POST['keyparent'])
			{
				$val['name']=$_POST['namefile'];
				$data_get["data_tree"][$index]=$val;
				break;
			}
			$index++;
		} 
		
		// save data tree root 
	$info_project=array("key_sever"=>$infokey,
	"data_tree"=>$data_get["data_tree"]);
	file_put_contents($folderSoruce."/".$_POST['project']."/1.rootdata.txt",json_encode($info_project));
	
	
}
else {
	$arrayre['error']=1;
	$arrayre['message']= "Miss param";
}
echo json_encode($arrayre);
?>