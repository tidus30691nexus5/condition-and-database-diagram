<?php 
$arrayre=array("error"=>0,"message"=>"");
$arrayre['type']="creatsubfolder";
if(isset($_POST['project']) && isset($_POST['namefile']) && isset($_POST['nameFolder'])){
	// check folder and creat if not exist
	
	$data_get=
	json_decode(file_get_contents($folderSoruce."/".$_POST['project']."/1.rootdata.txt"),true);
		//creat key subfolder
		$infokey=$data_get["key_sever"];
		$infokey=$infokey+1;
		$arrayre['key_folder']=$infokey;
		// creat key file 
		$infokey=$infokey+1;
		$arrayre['key_file']=$infokey;
		
		// add subfolder to data tree 
		$data_get["data_tree"][]=array("key"=>$arrayre['key_folder'],"name"=>$_POST['nameFolder'],"parent"=>$_POST['keyparent']);
		//creat source file of file 
		$name_source=$arrayre['key_file']."_".time().".txt";
		file_put_contents($folderSoruce."/".$_POST['project']."/".$name_source,json_encode(array()));
		// add file to data tree 
		$data_get["data_tree"][]=array("key"=>$arrayre['key_file'],"name"=>$_POST['namefile'],"parent"=>$arrayre['key_folder']
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