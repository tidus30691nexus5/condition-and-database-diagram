<?php 
$arrayre=array("error"=>0,"message"=>"");
$arrayre['type']="creatfile";
if(isset($_POST['project']) && isset($_POST['namefile']) && isset($_POST['keyparent'])){
	// check folder and creat if not exist
	
	$data_get=
	json_decode(file_get_contents($folderSoruce."/".$_POST['project']."/1.rootdata.txt"),true);
		$infokey=$data_get["key_sever"];
	
		// creat key file 
		$infokey=$infokey+1;
		$arrayre['key_file']=$infokey;
		
		//creat source file of file 
		$name_source=$arrayre['key_file']."_".time().".txt";
		file_put_contents($folderSoruce."/".$_POST['project']."/".$name_source,json_encode(array()));
		// add file to data tree 
		$data_get["data_tree"][]=array("key"=>$arrayre['key_file'],"name"=>$_POST['namefile'],"parent"=>$_POST['keyparent']
		,"source"=>$name_source);
		
		
		// save name source to reuslt
		$arrayre['source']=$name_source;
		
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