<?php 
require_once("config.php");
$folderSoruce=ROOT."/data";
if (!is_dir($folderSoruce)) {
    mkdir($folderSoruce, 0777, true);
}
// array folder 
$array_fd=array();
// get all folder project 
$dir = new DirectoryIterator("data");
foreach ($dir as $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        $array_fd[]['nameproject']= $fileinfo->getFilename();
    }
}
if(count($array_fd)>0)
 $array_fd=json_encode($array_fd);
else $array_fd="[]";


?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Flowchart</title>
<meta name="description" content="Interactive flowchart diagram implemented by GoJS in JavaScript for HTML." />
<!-- Copyright 1998-2017 by Northwoods Software Corporation. -->
<meta charset="UTF-8">
<script src="assets/go.js"></script>
 <script src="assets/ColumnResizingTool.js"></script>
 <script src="assets/RowResizingTool.js"></script>
<link href="css/bootstrap.min.css" rel="stylesheet">

<style type="text/css">
  /* CSS for the traditional context menu */
  .contextMenu_tree {
    z-index: 10001;
    position: absolute;
    left: 5px;
    border: 1px solid #444;
    background-color: #F5F5F5;
    display: none;
    box-shadow: 0 0 10px rgba( 0, 0, 0, .4 );
    font-size: 12px;
    font-family: sans-serif;
    font-weight: bold;
  }
  .contextMenu_tree ul {
    list-style: none;
    top: 0;
    left: 0;
    margin: 0;
    padding: 0;
  }
  .contextMenu_tree li a {
    position: relative;
    min-width: 60px;
    color: #444;
    display: inline-block;
    padding: 6px;
    text-decoration: none;
    cursor: pointer;
  }
  .contextMenu_tree li:hover {
    background: #CEDFF2;
    color: #EEE;
  }
  .contextMenu_tree li ul li {
    display: none;
  }
  .contextMenu_tree li ul li a {
    position: relative;
    min-width: 60px;
    padding: 6px;
    text-decoration: none;
    cursor: pointer;
  }
  .contextMenu_tree li:hover ul li {
    display: block;
    margin-left: 0px;
    margin-top: 0px;
  }
</style>
 <!-- this is only for the GoJS Samples framework -->
<script id="code">
function init() {
	var $ = go.GraphObject.make;  // for conciseness in defining templates

    myDiagram =
      $(go.Diagram, "myDiagramDiv",  // must name or refer to the DIV HTML element
        {
			grid: $(go.Panel, "Grid",
                  $(go.Shape, "LineH", { stroke: "lightgray", strokeWidth: 0.5 }),
                  $(go.Shape, "LineH", { stroke: "gray", strokeWidth: 0.5, interval: 10 }),
                  $(go.Shape, "LineV", { stroke: "lightgray", strokeWidth: 0.5 }),
                  $(go.Shape, "LineV", { stroke: "gray", strokeWidth: 0.5, interval: 10 })
                ),
          initialContentAlignment: go.Spot.Center,
          allowDrop: true,  // must be true to accept drops from the Palette
          "LinkDrawn": showLinkLabel,  // this DiagramEvent listener is defined below
          "LinkRelinked": showLinkLabel,
          "animationManager.duration": 800, // slightly longer than default (600ms) animation
          "undoManager.isEnabled": true  // enable undo & redo
        });

    // when the document is modified, add a "*" to the title and enable the "Save" button
    myDiagram.addDiagramListener("Modified", function(e) {
      var button = document.getElementById("SaveButton");
      if (button) button.disabled = !myDiagram.isModified;
      var idx = document.title.indexOf("*");
      if (myDiagram.isModified) {
        if (idx < 0) document.title += "*";
      } else {
        if (idx >= 0) document.title = document.title.substr(0, idx);
      }
    });

    // helper definitions for node templates

    function nodeStyle() {
      return [
        // The Node.location comes from the "loc" property of the node data,
        // converted by the Point.parse static method.
        // If the Node.location is changed, it updates the "loc" property of the node data,
        // converting back using the Point.stringify static method.
        new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
        {
          // the Node.location is at the center of each node
          locationSpot: go.Spot.Center,
          //isShadowed: true,
          //shadowColor: "#888",
          // handle mouse enter/leave events to show/hide the ports
          mouseEnter: function (e, obj) { showPorts(obj.part, true); },
          mouseLeave: function (e, obj) { showPorts(obj.part, false); }
        }
      ];
    }

    // Define a function for creating a "port" that is normally transparent.
    // The "name" is used as the GraphObject.portId, the "spot" is used to control how links connect
    // and where the port is positioned on the node, and the boolean "output" and "input" arguments
    // control whether the user can draw links from or to the port.
    function makePort(name, spot, output, input) {
      // the port is basically just a small circle that has a white stroke when it is made visible
      return $(go.Shape, "Circle",
               {
                  fill: "transparent",
                  stroke: null,  // this is changed to "white" in the showPorts function
                  desiredSize: new go.Size(8, 8),
                  alignment: spot, alignmentFocus: spot,  // align the port on the main Shape
                  portId: name,  // declare this object to be a "port"
                  fromSpot: spot, toSpot: spot,  // declare where links may connect at this port
                  fromLinkable: output, toLinkable: input,  // declare whether the user may draw links to/from here
                  cursor: "pointer"  // show a different cursor to indicate potential link point
               });
    }

    // define the Node templates for regular nodes

    var lightText = 'whitesmoke';

    myDiagram.nodeTemplateMap.add("",  // the default category
      $(go.Node, "Spot", nodeStyle(),
        // the main object is a Panel that surrounds a TextBlock with a rectangular Shape
        $(go.Panel, "Auto",
          $(go.Shape, "Rectangle",
            { fill: "#00A9C9", stroke: null },
            new go.Binding("figure", "figure")),
          $(go.TextBlock,
            {
              font: "bold 11pt Helvetica, Arial, sans-serif",
              stroke: lightText,
              margin: 8,
              maxSize: new go.Size(160, NaN),
              wrap: go.TextBlock.WrapFit,
              editable: true
            },
            new go.Binding("text").makeTwoWay())
        ),
        // four named ports, one on each side:
        makePort("T", go.Spot.Top, false, true),
        makePort("L", go.Spot.Left, true, true),
        makePort("R", go.Spot.Right, true, true),
        makePort("B", go.Spot.Bottom, true, false)
      ));

    myDiagram.nodeTemplateMap.add("Start",
      $(go.Node, "Spot", nodeStyle(),
        $(go.Panel, "Auto",
          $(go.Shape, "Circle",
            { minSize: new go.Size(40, 40), fill: "#79C900", stroke: null }),
          $(go.TextBlock, "Start",
            { font: "bold 11pt Helvetica, Arial, sans-serif", stroke: lightText },
            new go.Binding("text"))
        ),
        // three named ports, one on each side except the top, all output only:
        makePort("L", go.Spot.Left, true, false),
        makePort("R", go.Spot.Right, true, false),
        makePort("B", go.Spot.Bottom, true, false)
      ));

    myDiagram.nodeTemplateMap.add("End",
      $(go.Node, "Spot", nodeStyle(),
        $(go.Panel, "Auto",
          $(go.Shape, "Circle",
            { minSize: new go.Size(40, 40), fill: "#DC3C00", stroke: null }),
          $(go.TextBlock, "End",
            { font: "bold 11pt Helvetica, Arial, sans-serif", stroke: lightText },
            new go.Binding("text"))
        ),
        // three named ports, one on each side except the bottom, all input only:
        makePort("T", go.Spot.Top, false, true),
        makePort("L", go.Spot.Left, false, true),
        makePort("R", go.Spot.Right, false, true)
      ));

    myDiagram.nodeTemplateMap.add("Comment",
      $(go.Node, "Auto", nodeStyle(),
        $(go.Shape, "File",
          { fill: "#EFFAB4", stroke: null }),
        $(go.TextBlock,
          {
            margin: 5,
            maxSize: new go.Size(200, NaN),
            wrap: go.TextBlock.WrapFit,
            textAlign: "center",
            editable: true,
            font: "bold 12pt Helvetica, Arial, sans-serif",
            stroke: '#454545'
          },
          new go.Binding("text").makeTwoWay()),
        // no ports, because no links are allowed to connect with a comment
		makePort("T", go.Spot.Top, false, true),
        makePort("L", go.Spot.Left, true, true),
        makePort("R", go.Spot.Right, true, true),
        makePort("B", go.Spot.Bottom, true, false)
	 ));


    // replace the default Link template in the linkTemplateMap
    myDiagram.linkTemplate =
      $(go.Link,  // the whole link panel
        {
          routing: go.Link.AvoidsNodes,
          curve: go.Link.JumpOver,
          corner: 5, toShortLength: 4,
          relinkableFrom: true,
          relinkableTo: true,
          reshapable: true,
          resegmentable: true,
          // mouse-overs subtly highlight links:
          mouseEnter: function(e, link) { link.findObject("HIGHLIGHT").stroke = "rgba(30,144,255,0.2)"; },
          mouseLeave: function(e, link) { link.findObject("HIGHLIGHT").stroke = "transparent"; }
        },
        new go.Binding("points").makeTwoWay(),
        $(go.Shape,  // the highlight shape, normally transparent
          { isPanelMain: true, strokeWidth: 8, stroke: "transparent", name: "HIGHLIGHT" }),
        $(go.Shape,  // the link path shape
          { isPanelMain: true, stroke: "gray", strokeWidth: 2 }),
        $(go.Shape,  // the arrowhead
          { toArrow: "standard", stroke: null, fill: "gray"}),
        $(go.Panel, "Auto",  // the link label, normally not visible
          { visible: false, name: "LABEL", segmentIndex: 2, segmentFraction: 0.5},
          new go.Binding("visible", "visible").makeTwoWay(),
          $(go.Shape, "RoundedRectangle",  // the label shape
            { fill: "#F8F8F8", stroke: null }),
          $(go.TextBlock, "Yes",  // the label
            {
              textAlign: "center",
              font: "10pt helvetica, arial, sans-serif",
              stroke: "#333333",
              editable: true
            },
            new go.Binding("text").makeTwoWay())
        )
      );

    // Make link labels visible if coming out of a "conditional" node.
    // This listener is called by the "LinkDrawn" and "LinkRelinked" DiagramEvents.
    function showLinkLabel(e) {
      var label = e.subject.findObject("LABEL");
      if (label !== null) label.visible = (e.subject.fromNode.data.figure === "Diamond");
    }

    // temporary links used by LinkingTool and RelinkingTool are also orthogonal:
    myDiagram.toolManager.linkingTool.temporaryLink.routing = go.Link.Orthogonal;
    myDiagram.toolManager.relinkingTool.temporaryLink.routing = go.Link.Orthogonal;

    

    // initialize the Palette that is on the left side of the page
    myPalette =
      $(go.Palette, "myPaletteDiv",  // must name or refer to the DIV HTML element
        {
          "animationManager.duration": 800, // slightly longer than default (600ms) animation
          nodeTemplateMap: myDiagram.nodeTemplateMap,
		  // share the templates used by myDiagram
          model: new go.GraphLinksModel([  // specify the contents of the Palette
            { category: "Start", text: "Start" },
            { text: "Step" },
			{ text: "DB", figure: "Database", fill: "lightgray" },
            { text: "???", figure: "Diamond" },
            { category: "End", text: "End" },
            { category: "Comment", text: "Comment" }
          ]
		  , [
            // the Palette also has a disconnected Link, which the user can drag-and-drop
            { points: new go.List(go.Point).addAll([new go.Point(0, 0), new go.Point(30, 0), new go.Point(30, 40), new go.Point(60, 40)]) }
          ])
        });
 
	  function incrementCounter(){
		  alert("ok");
	  }
    // The following code overrides GoJS focus to stop the browser from scrolling
    // the page when either the Diagram or Palette are clicked or dragged onto.

    function customFocus() {
      var x = window.scrollX || window.pageXOffset;
      var y = window.scrollY || window.pageYOffset;
      go.Diagram.prototype.doFocus.call(this);
      window.scrollTo(x, y);
    }

    myDiagram.doFocus = customFocus;
    myPalette.doFocus = customFocus;

}
//end init 

  // Make all ports on a node visible when the mouse is over the node
  function showPorts(node, show) {
    var diagram = node.diagram;
    if (!diagram || diagram.isReadOnly || !diagram.allowLink) return;
    node.ports.each(function(port) {
        port.stroke = (show ? "white" : null);
      });
  }
  // Show the diagram's model in JSON format that the user may edit
  function save() {
    document.getElementById("mySavedModel").value = myDiagram.model.toJson();
    myDiagram.isModified = false;
  }
  // use to load 
  function load() {
    myDiagram.model = go.Model.fromJson(document.getElementById("mySavedModel").value);
  }
  // load an initial diagram from some JSON text
  //load();  
   // add an SVG rendering of the diagram at the end of this page
  function makeSVG() {
    var svg = myDiagram.makeSvg({
        scale: 1
      });
    svg.style.border = "1px solid black";
    obj = document.getElementById("SVGArea");
    obj.appendChild(svg);
    if (obj.children.length > 0) {
      obj.replaceChild(svg, obj.children[0]);
    }
  }
  function makeImage(){
	var imgsave =  myDiagram.makeImage({
	  scale: 1,
	  // size: new go.Size(NaN,250),
	//background: "rgba(0, 255, 0, 0.5)" ,// semi-transparent green background
	 // background: "AntiqueWhite",
	  background: "White",
	  type: "image/jpeg",
	  // quantity
	  details: 1
	});
	imgsave.style.border = "1px solid black";
	obj = document.getElementById("SVGArea");
    obj.appendChild(imgsave);
    if (obj.children.length > 0) {
      obj.replaceChild(imgsave, obj.children[0]);
    }
  }
</script>
<style>
 .nbryellow{
	 color:yellow;
 }
[v-cloak] { display: none; }
</style>
</head>
<body  onload="init()">
<div id="app" v-cloak>
<!-- menu -->
<div style="height:55px;position:fixed;top:0px;background:green;width:100%;z-index:99;color:white">
	<span style="display: inline-block;width:40%;">
		project: <b style="color:yellow">{{project_selected_current}}</b><button @click="open_modal_add_sub('choose_project')">Choose Project</button><br>
		Path: {{current_path_select}}
	</span>
	<span style="display: inline-block;"><button @click="save_data_diagram">Save Data Diagram</button>
	 <button @click="export_to_svg">Render as SVG</button>
   <button @click="export_to_img">Render as IMG</button>
   <button v-show="show_button_database" @click="show_db_diagram">Change Diagram Database</button>
	</span>
</div>
<div style="margin-top:60px;width:100%; white-space:nowrap;">
 <div style="position: relative;display: inline-block;">
 <div id="treeviewproject" style="display: inline-block;border: 1px solid black; width: 200px; height: 680px"></div>
  <div class="contextMenu_tree" id="contextMenu_tree">
        <ul>
          <li id="c_f_file" onclick="cxcommand(event)"><a>Creat Sub Folder (File in folder)</a></li>
          <li id="c_file" onclick="cxcommand(event)"><a>Creat File</a></li>
          
		  <li id="cut" onclick="cxcommand(event)"><a>Cut</a></li>
		  <li id="paste" onclick="cxcommand(event)"><a>Paste</a></li>
          
		 <li id="f_rename" onclick="cxcommand(event)"><a>Rename</a></li>
          
		 <!--
		  <li id="color" class="hasSubMenu"><a href="#" target="_self">Color</a>
           -->
		  <li id="delete" onclick="cxcommand(event)"><a>Delete</a></li>
          <!--
		  <ul class="subMenu" id="colorSubMenu">
                <li style="background: crimson;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Red</a></li>
                <li style="background: chartreuse;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Green</a></li>
                <li style="background: aquamarine;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Blue</a></li>
                <li style="background: gold;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Yellow</a></li>
            </ul>
			  -->
          </li>
        </ul>
      </div>
	</div>  
	 
  <span v-show="typetableDB==0" style="display: inline-block; vertical-align: top; width:100px">
      <div id="myPaletteDiv" style="border: solid 1px black; height: 680px"></div>
    </span>

    <span v-show="typetableDB==0" style="display: inline-block; vertical-align: top; width:80%">
      <div id="myDiagramDiv" style="border: solid 1px black; height: 680px"></div>
    </span>

	<!-- diagram db -->
	<!-- diagram data table -->
	<span v-show="typetableDB==1" style="display: inline-block; vertical-align: top; width:100px">
      <div id="myPaletteDiv_cusDB" style="border: solid 1px black; height: 680px">
			<br><button style="margin-left:5px" @click="add_new_table_db">Add Table</button>
	  </div>
    </span>
<div v-show="typetableDB==1" style="position: relative;display: inline-block;width:80%;">
  <div id="myDiagramDiv_database" style="border: solid 1px black; width:100%; height:680px"></div>
 
 <div class="contextMenu_tree" id="contextMenu_tree_database">
        <ul>
		<li style="background:blue;color:white"><a style="color:white"><span id="selected_field_db">Name</span></a></li>
 <li id="dtb_add" onclick="cxcommand_DTBASE(event)"><a>Add Before Row</a></li>
 <li id="dtb_after" onclick="cxcommand_DTBASE(event)"><a>Add After Row</a></li>
		  <li id="dtb_removekey" onclick="cxcommand_DTBASE(event)"><a>Remove Primary Key</a></li>
          <li id="dtb_setkey" onclick="cxcommand_DTBASE(event)"><a>Set Primary Key</a></li>
        
		
        
		  <li id="dtb_delete" onclick="cxcommand_DTBASE(event)"><a>Delete</a></li>
          <!--
		  <ul class="subMenu" id="colorSubMenu">
                <li style="background: crimson;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Red</a></li>
                <li style="background: chartreuse;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Green</a></li>
                <li style="background: aquamarine;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Blue</a></li>
                <li style="background: gold;" onclick="cxcommand(event, 'color')"><a href="#" target="_self">Yellow</a></li>
            </ul>
			  -->
          </li>
        </ul>
      </div>
 </div>
	<!-- end -->
	
	
  </div>
  <div>
  
   
  <div id="SVGArea"></div>
  </div>
 
 
 	<!-- modal del -->
<div v-if="background_modal" style="display: block; padding-right: 17px;" class="modal show fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 v-if="type_modal=='choose_project'" class="modal-title" id="exampleModalLabel">Choose project:</h5>
         <h5 v-else-if="type_modal=='file'" class="modal-title" id="exampleModalLabel">Creat File: <b style="color:blue">{{data_node_treeview.name}}</b></h5>
        <h5 v-else-if="type_modal=='rename'" class="modal-title" id="exampleModalLabel">Rename: <b style="color:blue">{{data_node_treeview.name}}</b></h5>
        <h5 v-else-if="type_modal=='f_delete'" class="modal-title" id="exampleModalLabel">Delete: <b style="color:blue">{{data_node_treeview.name}}</b></h5>
		
		 <h5 v-else="type_modal=='folder_file'" class="modal-title" id="exampleModalLabel">Creat SubFolder And File: <b style="color:blue">{{data_node_treeview.name}}</b></h5>
        
		<button @click="hide_modal_creat_sub" type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<form @submit.stop.prevent="creatdiagram_sub">
			<div v-if="type_modal=='choose_project'">
				<label for="basic-url">Select Your Project:<select v-model="project_selected_current">
		
				<option v-for="row in list_projects" :key="row" :value="row.nameproject">{{row.nameproject}}</option>
			</select>
			<button type="button" :disabled="project_selected_current==''" @click="load_project_choose">Load Project</button>
			</label>
				<br>
				<label for="basic-url">Creat New Project</label>
				<input :class="{'is-invalid':errors.has('dt_nameproject')}" data-vv-name="dt_nameproject" v-validate data-vv-rules="required" type="text" class="form-control" v-model="dt_nameproject" placeholder="Input name project">
				
			</div>
			<!-- creat folder file -->
			<div v-else-if="type_modal=='folder_file'">
				<label for="basic-url">SubFolder Name:</label>
				<input :class="{'is-invalid':errors.has('dt_foldername')}" data-vv-name="dt_foldername" v-validate data-vv-rules="required" type="text" class="form-control" v-model="dt_foldername" placeholder="Input subfolder name">
				
				<label for="basic-url">File Name:</label>
				<input :class="{'is-invalid':errors.has('dt_filename')}" data-vv-name="dt_filename" v-validate data-vv-rules="required" type="text" class="form-control" v-model="dt_filename" placeholder="Input file name ">
				
			</div>
			<!-- creat  file -->
			<div v-else-if="type_modal=='file'">
				<label for="basic-url">File Name:</label>
				<input :class="{'is-invalid':errors.has('dt_filename')}" data-vv-name="dt_filename" v-validate data-vv-rules="required" type="text" class="form-control" v-model="dt_filename" placeholder="Input file name ">
			</div>
			<!-- creat rename -->
			<div v-else-if="type_modal=='rename'">
				<label for="basic-url">New Name:</label>
				<input :class="{'is-invalid':errors.has('dt_rename')}" data-vv-name="dt_rename" v-validate data-vv-rules="required" type="text" class="form-control" v-model="dt_rename" placeholder="Input file name ">
			</div>
			<!-- creat delete -->
			<div v-else-if="type_modal=='f_delete'">
				<label for="basic-url">Are you sure delete: <b>{{data_node_treeview.name}}</b></label>
				
			</div>
				
				

				
				<br>
			
		</form>
	   </div>
      <div class="modal-footer">
        <button @click="hide_modal_creat_sub" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button @click="creatdiagram_sub" type="button" class="btn btn-primary">
		<span v-if="type_modal=='folder_file'">Creat SubFolder and File</span>
		<span v-else-if="type_modal=='file'">Creat File</span>
		<span v-else-if="type_modal=='rename'">Rename</span>
		<span v-else-if="type_modal=='f_delete'">Delete</span>
		
		<span v-else-if="type_modal=='choose_project'">Creat Project</span>
		</button>
      
	  </div>
    </div>
  </div>
</div>
</div>
 <script src="js/pl_nbr.js"></script>
	<script src="js/vue.min.js"></script>
	<script src="js/vee-validate.min.js"></script>
	<script src="js/axios.min.js"></script>

<!-- diagram database -->
 <script >
  var data_select;
  var myDiagram_DBTB;
    function init_database_tb() {
      //if (window.goSamples) goSamples();  // init for these samples -- you don't need to call this
      var $ = go.GraphObject.make;  // for conciseness in defining templates

      myDiagram_DBTB =
        $(go.Diagram, "myDiagramDiv_database",
          {
			  grid: $(go.Panel, "Grid",
                  $(go.Shape, "LineH", { stroke: "lightgray", strokeWidth: 0.5 }),
                  $(go.Shape, "LineH", { stroke: "gray", strokeWidth: 0.5, interval: 10 }),
                  $(go.Shape, "LineV", { stroke: "lightgray", strokeWidth: 0.5 }),
                  $(go.Shape, "LineV", { stroke: "gray", strokeWidth: 0.5, interval: 10 })
                ),
            initialContentAlignment: go.Spot.Center,
            validCycle: go.Diagram.CycleNotDirected,  // don't allow loops
            "undoManager.isEnabled": true
          });

      myDiagram_DBTB.toolManager.mouseDownTools.add(new RowResizingTool());
      myDiagram_DBTB.toolManager.mouseDownTools.add(new ColumnResizingTool());

	  
	   // This is the actual HTML context menu:
  var cxElement_DBTB = document.getElementById("contextMenu_tree_database");

  // Since we have only one main element, we don't have to declare a hide method,
  // we can set mainElement and GoJS will hide it automatically
  var myContextMenu_DBTB = $(go.HTMLInfo, {
    show: showContextMenu_DBTB,
    mainElement: cxElement_DBTB
  });
  
  function showContextMenu_DBTB(obj, diagram, tool) {
    // Show only the relevant buttons given the current state.
    console.log("TEE:"+obj.findObject("rownbr").row);
	var cmd = diagram.commandHandler;
	var shape = obj.findObject("rownbr");
    shape.fill = "#6DAB80";
    shape.stroke = "white";
    var text = obj.findObject("colnbr");
    text.stroke = "green";
	var text = obj.findObject("colnbr1");
    text.stroke = "green";
	//app.set_data_node_treeview(obj.data);
	console.log("OBJEC ne:"+obj.data.name);
	data_select=obj;
	// set name field select 
	document.getElementById("selected_field_db").innerHTML = obj.data.name+" ("+obj.data.info+")";
	//check is key will show remove key 
	if(obj.data.figure=="Diamond"){
		document.getElementById("dtb_removekey").style.display =  "block" ;
    	
	}
	else document.getElementById("dtb_removekey").style.display =  "none" ;
	//check is key will show  set primary Key
	if(obj.data.figure!="Diamond"){
		document.getElementById("dtb_setkey").style.display =  "block" ;
    	
	}
	else document.getElementById("dtb_setkey").style.display =  "none" ;
	
	document.getElementById("dtb_add").style.display = "block" ;
    
	document.getElementById("dtb_after").style.display = "block" ;document.getElementById("dtb_delete").style.display = "block" ;
    //document.getElementById("color").style.display = (obj !== null ? "block" : "none");

    // Now show the whole context menu element
    cxElement_DBTB.style.display = "block";
    // we don't bother overriding positionContextMenu, we just do it here:
    var mousePt = diagram.lastInput.viewPoint;
    cxElement_DBTB.style.left = mousePt.x + "px";
    cxElement_DBTB.style.top = mousePt.y + "px";
  }
  
    // We don't want the div acting as a context menu to have a (browser) context menu!
  cxElement_DBTB.addEventListener("contextmenu", function(e) {
    e.preventDefault();
    return false;
  }, false);
  
  
  function mouseEnter(e, obj) {
    var shape = obj.findObject("rownbr");
    shape.fill = "#6DAB80";
    shape.stroke = "white";
    var text = obj.findObject("colnbr");
    text.stroke = "green";
	var text = obj.findObject("colnbr1");
    text.stroke = "green";
  };

  function mouseLeave(e, obj) {
  
    var shape = obj.findObject("rownbr");
    // Return the Shape's fill and stroke to the defaults
    shape.fill = obj.data.color;
    shape.stroke = null;
    // Return the TextBlock's stroke to its default
    var text = obj.findObject("colnbr");
    text.stroke = "black";
	var text = obj.findObject("colnbr1");
    text.stroke = "black";
  };
  
      // This template is a Panel that is used to represent each item in a Panel.itemArray.
      // The Panel is data bound to the item object.
      var fieldTemplate =
        $(go.Panel, "TableRow",  // this Panel is a row in the containing Table
          { contextMenu: myContextMenu_DBTB ,
		  mouseEnter: mouseEnter,
        mouseLeave: mouseLeave
		  },
		 new go.Binding("portId", "name"),  // this Panel is a "port"
          { background: "transparent",  // so this port's background can be picked by the mouse
          //  fromSpot: go.Spot.Right,  // links only go from the right side to the left side
          //  toSpot: go.Spot.Left,
		  
            // allow drawing links from or to this port:
            fromLinkable: true, toLinkable: true
		   },
          $(go.Shape,
            {
				name: "rownbr",
              column: 0,
              width: 12, height: 12, margin: 4,
			  
			  // will not allow table link again table have link it
              // but disallow drawing links from or to this shape:
              //fromLinkable: false, toLinkable: false
            },
            new go.Binding("figure", "figure"),
            new go.Binding("fill", "color")),
          $(go.TextBlock,
            {
			name: "colnbr",
              column: 1,
              margin: new go.Margin(0, 2),
              stretch: go.GraphObject.Horizontal,
              font: "bold 13px sans-serif",
              wrap: go.TextBlock.None,
              overflow: go.TextBlock.OverflowEllipsis, 
			  editable: true,
              // and disallow drawing links from or to this text:
              fromLinkable: false, toLinkable: false
            },
            new go.Binding("text", "name").makeTwoWay()),
          $(go.TextBlock,
            {
			name: "colnbr1",
              column: 2,
              margin: new go.Margin(0, 2),
              stretch: go.GraphObject.Horizontal,
              font: "13px sans-serif",
              maxLines: 3,
              overflow: go.TextBlock.OverflowEllipsis,
              editable: true
            },
            new go.Binding("text", "info").makeTwoWay())
        );

      // Return initialization for a RowColumnDefinition, specifying a particular column
      // and adding a Binding of RowColumnDefinition.width to the IDX'th number in the data.widths Array
      function makeWidthBinding(idx) {
        // These two conversion functions are closed over the IDX variable.
        // This source-to-target conversion extracts a number from the Array at the given index.
        function getColumnWidth(arr) {
          if (Array.isArray(arr) && idx < arr.length) return arr[idx];
          return NaN;
        }
        // This target-to-source conversion sets a number in the Array at the given index.
        function setColumnWidth(w, data) {
          var arr = data.widths;
          if (!arr) arr = [];
          if (idx >= arr.length) {
            for (var i = arr.length; i <= idx; i++) arr[i] = NaN;  // default to NaN
          }
          arr[idx] = w;
          return arr;  // need to return the Array (as the value of data.widths)
        }
        return [
          { column: idx },
          new go.Binding("width", "widths", getColumnWidth).makeTwoWay(setColumnWidth)
        ]
      }

      // This template represents a whole "record".
      myDiagram_DBTB.nodeTemplate =
        $(go.Node, "Auto",
          new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
          // this rectangular shape surrounds the content of the node
          $(go.Shape,
            { fill: "#EEEEEE" }),
          // the content consists of a header and a list of items
          $(go.Panel, "Vertical",
            { stretch: go.GraphObject.Horizontal, alignment: go.Spot.TopLeft },
            // this is the header for the whole node
            $(go.Panel, "Auto",
              { stretch: go.GraphObject.Horizontal },  // as wide as the whole node
              $(go.Shape,
                { fill: "#1570A6", stroke: null }),
              $(go.TextBlock,
                {
					name:"name_table",
					row: 0,
                  alignment: go.Spot.Center,
                  margin: 5,
                  stroke: "white",
                  textAlign: "center",
                  font: "bold 12pt sans-serif",
				  editable: true
                },
                new go.Binding("text", "key").makeTwoWay()),
				 $("PanelExpanderButton", "TABLE",  // the name of the element whose visibility this button toggles
            {  row: 0,alignment: go.Spot.TopRight }),
				
				),
				// the collapse/expand button
         
            // this Panel holds a Panel for each item object in the itemArray;
            // each item Panel is defined by the itemTemplate to be a TableRow in this Table
            $(go.Panel, "Table",
              {
			  row: 1,
                name: "TABLE", stretch: go.GraphObject.Horizontal,
                minSize: new go.Size(100, 10),
                defaultAlignment: go.Spot.Left,
                defaultStretch: go.GraphObject.Horizontal,
                defaultColumnSeparatorStroke: "gray",
                defaultRowSeparatorStroke: "gray",
                itemTemplate: fieldTemplate
              },
              $(go.RowColumnDefinition, makeWidthBinding(0)),
              $(go.RowColumnDefinition, makeWidthBinding(1)),
              $(go.RowColumnDefinition, makeWidthBinding(2)),
              new go.Binding("itemArray", "fields")
            )  // end Table Panel of items
          )  // end Vertical Panel
        );  // end Node

      myDiagram_DBTB.linkTemplate =
        $(go.Link,
          { 
		  /*
		  relinkableFrom: true,
		  relinkableTo: true, 
		  toShortLength: 4 
		  */
		  selectionAdorned: true,
          layerName: "Foreground",
          reshapable: true,
          routing: go.Link.AvoidsNodes,
          corner: 5,
          curve: go.Link.JumpOver
		  },  // let user reconnect links
          $(go.Shape, { strokeWidth: 1.5 }),
          $(go.Shape, { toArrow: "Standard", stroke: null }),
		  $(go.TextBlock,  // the "from" label
          {
            textAlign: "center",
            font: "bold 14px sans-serif",
            stroke: "#1967B3",
            segmentIndex: 0,
            segmentOffset: new go.Point(NaN, NaN),
            segmentOrientation: go.Link.OrientUpright,
			editable: true
          },
          new go.Binding("text", "text").makeTwoWay()),
        $(go.TextBlock,  // the "to" label
          {
            textAlign: "center",
            font: "bold 14px sans-serif",
            stroke: "#1967B3",
            segmentIndex: -1,
            segmentOffset: new go.Point(NaN, NaN),
            segmentOrientation: go.Link.OrientUpright,
			editable: true
          },
          new go.Binding("text", "toText").makeTwoWay())
        );

      myDiagram_DBTB.model =
        $(go.GraphLinksModel,
          {
            linkFromPortIdProperty: "fromPort",
            linkToPortIdProperty: "toPort",
           
            nodeDataArray: [
             
            ],
            linkDataArray: [
              ]
          });

		 //showModel();  // show the diagram's initial model

      function showModel() {
        document.getElementById("mySavedModel").textContent = myDiagram_DBTB.model.toJson();
      }
    }
	
	//https://gojs.net/latest/samples/customContextMenu.html#
  // This is the general menu command handler, parameterized by the name of the command.
function cxcommand_DTBASE(event, val) {
  if (val === undefined) val = event.currentTarget.id;
  var diagram = myDiagram_DBTB;
  switch (val) {
	  case "dtb_add":{
		 // alert("creatfoler");
		 var n = diagram.selection.first();
		 
    if (n === null) return;
    var d = n.data;
	//alert(data_select.findObject("rownbr").row);
		  diagram.startTransaction("insertIntoTable");
    // add item as second in the list, at index #1
   // -1 will add end list array 
    // of course this new data could be more realistic:

    diagram.model.insertArrayItem(d.fields, data_select.findObject("rownbr").row, {
 name: "t_"+Date.now(), info: "t_"+Date.now(), color: "#00BCF2", figure: "Ellipse" 
                
    });
    diagram.commitTransaction("insertIntoTable");
		 //app.open_modal_add_sub("folder_file");
		  break;
	  }
	  case "dtb_after":{
		 // alert("creatfoler");
		 var n = diagram.selection.first();
		 
    if (n === null) return;
    var d = n.data;
	//alert(data_select.findObject("rownbr").row);
		  diagram.startTransaction("insertIntoTable");
    // add item as second in the list, at index #1
   // -1 will add end list array 
    // of course this new data could be more realistic:
    diagram.model.insertArrayItem(d.fields, data_select.findObject("rownbr").row+1, {
 name: "t_"+Date.now(), info: "t_"+Date.now(), color: "#00BCF2", figure: "Ellipse" 
                
    });
    diagram.commitTransaction("insertIntoTable");
		 //app.open_modal_add_sub("folder_file");
		  break;
	  }
	  case "dtb_removekey":{
		var n = diagram.selection.first();
		if (n === null) return;
		
		var d = n.data;
		
		var data = diagram.model.findNodeDataForKey(n.key);
		// This will NOT change the color of the "Delta" Node
		 diagram.startTransaction("change color");
		if (data !== null){
			//alert("saas"+n.key);
			diagram.model.setDataProperty(data.fields[data_select.findObject("rownbr").row],"figure","Ellipse");
			diagram.model.setDataProperty(data.fields[data_select.findObject("rownbr").row],"color","#00BCF2");
			//alert("saas"+n.key);
		} 
		diagram.commitTransaction("change color");
		/*
		d.fields[data_select.findObject("rownbr").row].figure="Ellipse";
		d.fields[data_select.findObject("rownbr").row].color="#00BCF2";
		// save temp old value 
		var temp_old_vl=d.fields[data_select.findObject("rownbr").row];
	   // delete current row select 
	    diagram.startTransaction("removeFromTable");
		// remove second item of list, at index #1
    diagram.model.removeArrayItem(d.fields, data_select.findObject("rownbr").row);
	   // update value row temp save 
	   diagram.model.insertArrayItem(d.fields, data_select.findObject("rownbr").row,temp_old_vl);
    diagram.commitTransaction("insertIntoTable");
		   */
		  break;
		 
	  }
	  case "dtb_setkey":{
	  var n = diagram.selection.first();
		if (n === null) return;
		var d = n.data;
		var data = diagram.model.findNodeDataForKey(n.key);
		// This will NOT change the color of the "Delta" Node
		 diagram.startTransaction("change color");
		if (data !== null){
			//alert("saas"+n.key);
			diagram.model.setDataProperty(data.fields[data_select.findObject("rownbr").row],"figure","Diamond");
			diagram.model.setDataProperty(data.fields[data_select.findObject("rownbr").row],"color","#FFB900");
			//alert("saas"+n.key);
		} 
		diagram.commitTransaction("change color");
		/*
		d.fields[data_select.findObject("rownbr").row].figure="Diamond";
		d.fields[data_select.findObject("rownbr").row].color="#FFB900";
		// save temp old value 
		var temp_old_vl=d.fields[data_select.findObject("rownbr").row];
	   var temp_old_vt=data_select.findObject("rownbr").row;
	   // delete current row select 
	    diagram.startTransaction("removeFromTable");
		// remove second item of list, at index #1
    diagram.model.removeArrayItem(d.fields, temp_old_vt);
	   // update value row temp save 
	   diagram.model.insertArrayItem(d.fields, temp_old_vt,temp_old_vl);
    diagram.commitTransaction("insertIntoTable");
		  */
		  
		  break;
	  }
	  case "dtb_delete":{
		   var n = diagram.selection.first();
    if (n === null) return;
    var d = n.data;
    diagram.startTransaction("removeFromTable");
    // remove second item of list, at index #1
    diagram.model.removeArrayItem(d.fields, data_select.findObject("rownbr").row);
    diagram.commitTransaction("removeFromTable");
		  
		  break;
	  }
	 
  }
  myDiagram_DBTB.currentTool.stopTool();
  //myDiagramTreeview.currentTool.doCancel()
}

// get index of row table by name 
function findColumnDefinitionForName(nodedata, attrname) {
    var columns = nodedata.columnDefinitions;
    for (var i = 0; i < columns.length; i++) {
      if (columns[i].attr === attrname) return columns[i];
    }
    return null;
  }
  
 // init_database_tb();
  </script>

<!-- end diagram database -->
	
 <script>
 var myDiagramTreeview;
 // tree view 
 function init_treeview() {
   var $ = go.GraphObject.make;  // for conciseness in defining templates

   // This is the actual HTML context menu:
  var cxElement = document.getElementById("contextMenu_tree");

  // Since we have only one main element, we don't have to declare a hide method,
  // we can set mainElement and GoJS will hide it automatically
  var myContextMenu = $(go.HTMLInfo, {
    show: showContextMenu,
    mainElement: cxElement
  });
  
  function showContextMenu(obj, diagram, tool) {
    // Show only the relevant buttons given the current state.
    var cmd = diagram.commandHandler;
	app.set_data_node_treeview(obj.data);
	console.log("OBJEC ne:"+obj.data.name);
	// can creat folder with file in folder 
	if(cmd.canCollapseTree(obj) || cmd.canExpandTree(obj)){
		document.getElementById("c_f_file").style.display =  "block" ;
    	
	}
	else document.getElementById("c_f_file").style.display =  "none" ;
	// can creat file not include creat folder 
	if(cmd.canCollapseTree(obj) || cmd.canExpandTree(obj)){
		document.getElementById("c_file").style.display =  "block" ;
    	
	}
	else document.getElementById("c_file").style.display =  "none" ;
	
	document.getElementById("cut").style.display = cmd.canCutSelection() ? "block" : "none";
    //document.getElementById("copy").style.display = cmd.canCopySelection() ? "block" : "none";
    document.getElementById("paste").style.display = cmd.canPasteSelection() ? "block" : "none";
	document.getElementById("f_rename").style.display = "block" ;
    document.getElementById("delete").style.display = "block" ;
    //document.getElementById("color").style.display = (obj !== null ? "block" : "none");

    // Now show the whole context menu element
    cxElement.style.display = "block";
    // we don't bother overriding positionContextMenu, we just do it here:
    var mousePt = diagram.lastInput.viewPoint;
    cxElement.style.left = mousePt.x + "px";
    cxElement.style.top = mousePt.y + "px";
  }
  
    // We don't want the div acting as a context menu to have a (browser) context menu!
  cxElement.addEventListener("contextmenu", function(e) {
    e.preventDefault();
    return false;
  }, false);
    myDiagramTreeview =
      $(go.Diagram, "treeviewproject",
        {
		//	"undoManager.isEnabled": true  // enable undo & redo
       // ,
          allowMove: false,
          allowCopy: false,
		  "commandHandler.copiesTree": true,
          "commandHandler.copiesParentKey": true,
          allowDelete: false,
		  "commandHandler.deletesTree": true,
		  
		 // allowDragOut: false,
           // allowDrop: true,
          allowHorizontalScroll: false,
          layout:
            $(go.TreeLayout,
              {
                alignment: go.TreeLayout.AlignmentStart,
                angle: 0,
                compaction: go.TreeLayout.CompactionNone,
                layerSpacing: 16,
                layerSpacingParentOverlap: 1,
                nodeIndent: 2,
                nodeIndentPastParent: 0.88,
                nodeSpacing: 0,
                setsPortSpot: false,
                setsChildPortSpot: false
              }),
			  "Modified":function(e){
				console.log("sua doi roi ne");  
			  },
			  // clear paste 
			   "ClipboardPasted": function(e) { e.diagram.commandHandler.copyToClipboard(null); }
      });

    myDiagramTreeview.nodeTemplate =
      $(go.Node,
	  { contextMenu: myContextMenu },
        {
		
		// no Adornment: instead change panel background color by binding to Node.isSelected
          selectionAdorned: false,
          // a custom function to allow expanding/collapsing on double-click
          // this uses similar logic to a TreeExpanderButton
          doubleClick: function(e, node) {
            var cmd = myDiagram.commandHandler;
			// check case not collapseTree & can expand 
			// => click load data 
			if(!cmd.canCollapseTree(node) && !cmd.canExpandTree(node)){
			//	alert("event ne "+node.data.name);
				//app.set_file_select_current(node.data);
				app.load_data_diagram(node.data);
				return;
			}
            if (node.isTreeExpanded) {
              if (!cmd.canCollapseTree(node)) return;
            } else {
              if (!cmd.canExpandTree(node)) return;
            }
            e.handled = true;
            if (node.isTreeExpanded) {
              cmd.collapseTree(node);
            } else {
              cmd.expandTree(node);
            }
          }
        },
        $("TreeExpanderButton",
          {
            width: 14,
            "ButtonBorder.fill": "whitesmoke",
            "ButtonBorder.stroke": null,
            "_buttonFillOver": "rgba(0,128,255,0.25)",
            "_buttonStrokeOver": null
          }),
        $(go.Panel, "Horizontal",
          { position: new go.Point(16, 0) },
          new go.Binding("background", "isSelected", function (s) { return (s ? "lightblue" : "white"); }).ofObject(),
          $(go.Picture,
            {
              width: 18, height: 18,
              margin: new go.Margin(0, 4, 0, 0),
              imageStretch: go.GraphObject.Uniform
            },
            // bind the picture source on two properties of the Node
            // to display open folder, closed folder, or document
            new go.Binding("source", "isTreeExpanded", imageConverter).ofObject(),
            new go.Binding("source", "isTreeLeaf", imageConverter).ofObject()),
          $(go.TextBlock,
            { font: '9pt Verdana, sans-serif' },
            new go.Binding("text", "name", function(s) { return  s; }))
        )  // end Horizontal Panel
      );  // end Node

    // without lines
    myDiagramTreeview.linkTemplate = $(go.Link);

    // // with lines
    // myDiagram.linkTemplate =
    //   $(go.Link,
    //     { selectable: false,
    //       routing: go.Link.Orthogonal,
    //       fromEndSegmentLength: 4,
    //       toEndSegmentLength: 4,
    //       fromSpot: new go.Spot(0.001, 1, 7, 0),
    //       toSpot: go.Spot.Left },
    //     $(go.Shape,
    //       { stroke: 'gray', strokeDashArray: [1,2] }));

    }
  
  function addmoreNodeLoaded(){
	  myDiagramTreeview.startTransaction("add node and link");
var childdata = { key: 5, name:"aadd more node ", parent: nodeDataArray[0].key };
      
	  myDiagramTreeview.model.addNodeData(childNode);
myDiagramTreeview.commitTransaction("add node and link");
  }
   // takes a property change on either isTreeLeaf or isTreeExpanded and selects the correct image to use
  function imageConverter(prop, picture) {
    var node = picture.part;
    if (node.isTreeLeaf) {
      return "images/document.png";
    } else {
      if (node.isTreeExpanded) {
        return "images/openFolder.png";
      } else {
        return "images/closedFolder.png";
      }
    }
  }
  
  
 
  //https://gojs.net/latest/samples/customContextMenu.html#
  // This is the general menu command handler, parameterized by the name of the command.
function cxcommand(event, val) {
  if (val === undefined) val = event.currentTarget.id;
  var diagram = myDiagramTreeview;
  switch (val) {
	  case "c_f_file":{
		 // alert("creatfoler");
		 
		 app.open_modal_add_sub("folder_file");
		  break;
	  }
	  case "cut":{
		  myDiagramTreeview.commandHandler.cutSelection(); 
		  break;
		  
	  }
	  case "paste":{
		  myDiagramTreeview.commandHandler.pasteSelection(diagram.lastInput.documentPoint); 

		  break;
	  }
	  case "c_file":{
		  app.open_modal_add_sub("file");
		  break;
	  }
	  case "f_rename":{
		  app.open_modal_add_sub("rename");
		  break;
	  }
	  case "delete":{
		  //alert("delete");
		  app.open_modal_add_sub("f_delete");
		   break;
	  }
	  /*
    case "cut": diagram.commandHandler.cutSelection(); break;
    case "copy": diagram.commandHandler.copySelection(); break;
    case "paste": diagram.commandHandler.pasteSelection(diagram.lastInput.documentPoint); break;
    case "delete": diagram.commandHandler.deleteSelection(); break;
    case "color": {
        var color = window.getComputedStyle(document.elementFromPoint(event.clientX, event.clientY).parentElement)['background-color'];
        changeColor(diagram, color); break;
    }
	*/
  }
  myDiagramTreeview.currentTool.stopTool();
  //myDiagramTreeview.currentTool.doCancel()
}
 </script>
 <script>
 
 var baseURL="<?php echo $BASE_URL ?>";
var timeout=30000;
// creat instance ajax
var CancelToken = axios.CancelToken;
var nbrProcess = CancelToken.source();
var instance = axios.create({
  baseURL: baseURL,
  // timeout 5s per request
  timeout: timeout,
  // avoid cross domain cookies
  withCredentials:true,
  //responseType: 'json',
 // xsrfCookieName: "%xsrfCookieName%",
 
  // set cancel process ajax | nbrProcess.cancel("resean");
  cancelToken: nbrProcess.token
});
var configVeeValidate = {
	delay: 200, 
 // locale: store.getters.getLang, 
  events: 'input|blur' // default value.
};
Vue.use(VeeValidate,configVeeValidate);
var app = new Vue({
  el: '#app',
  data: function(){
	  return {
		  // key define of data get from sever 
		  // use increase to creat file or folder file 
		  key_current:0,
		  project_selected_current:"",
		  file_select_current:"",
		  nameinfo_file_select_current:"",
		  // list data project 
		  list_projects: <?php echo $array_fd;?>,
		  // type creat folder_file
		  // type creat file 
		  // type rename
	      // type choose_project file 
		  type_modal:"",
		  //value node treeview folder 
		  data_treeview_sever:[{"key":0,"name":"CMS NBR","__gohashid":450},{"key":1,"name":"Login HE","parent":0,"__gohashid":451},{"key":2,"name":"Login","parent":1,"__gohashid":452},{"key":3,"name":"anna","parent":0,"__gohashid":453},{"key":4,"name":"Teea","parent":0,"__gohashid":454},{"key":5,"name":"aadd more node ","parent":1,"__gohashid":455},{"key":6,"name":"sub new  ","parent":5,"__gohashid":456},{"key":-8,"name":"aadd more node ","parent":1,"__gohashid":1521},{"key":-9,"name":"sub new  ","parent":5,"__gohashid":1587}],
		  data_node_treeview:{},
		  // modal del 
		background_modal:0,
		//data input 
		dt_nameproject:"",
		dt_filename:"",
		dt_foldername:"",
		dt_rename:"",
		stepcreatdiagram:0,
		step_load_data_diagram:0,
		
		// case database diagram 
		typetableDB:0,
		// show button_ dtabase 
		show_button_database:0
	  }
  },computed: {
	  current_path_select:function(){
		  if(this.file_select_current=="")
			return "";
		  // check dequi get array name parent if have 
		  //this.get_info_parent_node(this.file_select_current,"",false);
		 // return this.nameinfo_file_select_current+this.file_select_current.name;
	  }
  },
  created: function() {
	  this.$nextTick(function () {
		  init_treeview();
		  init_database_tb();
		   // create a random tree
    /*
	this.data_treeview_sever = [{ key: 0 , name:"CMS NBR"}];
    var childdata = { key: 1, name:"Login HE", parent: this.data_treeview_sever[0].key };
      this.data_treeview_sever.push(childdata);
	  var childdata = { key: 2, name:"Login", parent: this.data_treeview_sever[1].key };
      this.data_treeview_sever.push(childdata);
	  
	  var childdata = { key: 3, name:"anna", parent: this.data_treeview_sever[0].key };
      this.data_treeview_sever.push(childdata);
	  var childdata = { key: 4, name:"Teea", parent: this.data_treeview_sever[0].key };
      this.data_treeview_sever.push(childdata);
   */
   // myDiagramTreeview.model = new go.TreeModel(this.data_treeview_sever);
	// myDiagramTreeview.model =new go.TreeModel(this.data_treeview_sever);
		   //this.update_data_tree();
	  });
  },
  methods: {
	  // get all key parent of node tree 
	  // return string folder/folder if have 
	  get_info_parent_node: function(node,nameinfo,doloop){
		  if(node.parent)
		  {
			  // find index of parent 
			  var objIndex = this.data_treeview_sever.findIndex((obj => obj.key == node.parent));
				console.log(node.parent+ " stt ss:"+objIndex +" name:"+this.data_treeview_sever[objIndex].name);
				this.nameinfo_file_select_current= this.data_treeview_sever[objIndex].name+"/"+nameinfo;
				console.log("nae:"+this.nameinfo_file_select_current);
				//dequi
				this.get_info_parent_node(this.data_treeview_sever[objIndex],this.nameinfo_file_select_current,true);
				//return nameinfo;
		 }
		 else {
			 if(!doloop)
				 this.nameinfo_file_select_current="";
		 }
		 
		 
	  },
	  //select file current 
	  set_file_select_current: function(data){
		  this.file_select_current=data;
	  },
	  //set data node treeview ,
	  set_data_node_treeview:function(data){
		this.data_node_treeview=data; 
			console.log("data ne test:"+data.name+ " - "+this.data_node_treeview.name);
	  },
	  open_modal_add_sub: function(type){
		  this.type_modal=type;
		  this.background_modal=1;
		  console.log("TYPE NE:"+type);
	  },
	  hide_modal_creat_sub: function(){
		  this.background_modal=0;
	  },
	  // type creat folder_file
	  // type creat file 
	  // type rename
	   // type choose_project file 
	  creatdiagram_sub:function(){
		  if(this.stepcreatdiagram)
			  return;
		  // catch when user click input will valid form 
		  this.$validator.validateAll().then(function(){}).catch(function(){});
		  // check all error 
		  if(this.errors.any()){
				  return ;
			  }
		  if(this.type_modal=="choose_project"){
			  
			  var selfNBR=this;
			  this.stepcreatdiagram=1;
				 instance.post('/action.php',{
					 name:this.dt_nameproject,
					type:'creatproject'
				}).then(function(response) {
					data = response.data;
					//alert(data);
					selfNBR.stepcreatdiagram=0;
					 
					
					if(data.error==1){
						alert("error:"+data.message);
						}
					else {
						selfNBR.list_projects.push({nameproject:selfNBR.dt_nameproject});
						selfNBR.dt_nameproject="";
					}
					
				  
				}).catch(function(thrown) {
				  if (axios.isCancel(thrown)) {
					selfNBR.stepcreatdiagram=0;
				  } else {
					selfNBR.stepcreatdiagram=0;
				  }
				
				});
			  
		  }
		  else 
		  { 
			  if(this.type_modal=="f_delete"){
			/*	 
				 // allow delete
				  myDiagramTreeview.allowDelete= true;
				  myDiagramTreeview.commandHandler.deleteSelection(); 
					// lock again 
					myDiagramTreeview.allowDelete= false;
					// clear file select current 
					this.file_select_current="";
					*/
					myDiagramTreeview.allowDelete= true;
				  myDiagramTreeview.commandHandler.deleteSelection(); 
					// lock again 
					myDiagramTreeview.allowDelete= false;
								
								this.data_node_treeview={};
					var dataparam={
					project:this.project_selected_current,
					//keyparent:this.data_node_treeview.key,
					dataupdate:myDiagramTreeview.model.toJson(),
					type:'f_delete'
				};
			  }
			  else if(this.type_modal=="file"){
				  var dataparam={
					project:this.project_selected_current,
					keyparent:this.data_node_treeview.key,
					 namefile:this.dt_filename,
					type:'creatfile'
				};
			  }
			 else if(this.type_modal=="rename"){
				 /*
				 //Find index of specific object using findIndex method.    
				var objIndex = this.data_treeview_sever.findIndex((obj => obj.key == this.data_node_treeview.key));
				//Log object to Console.
				console.log("Before update: ", this.data_treeview_sever[objIndex]);

				//Update object's name property.
				this.data_treeview_sever[objIndex].name = this.dt_rename;

				//Log object to console again.
				console.log("After update: ", this.data_treeview_sever[objIndex]);
				
				//update interface dataview 
				myDiagramTreeview.model =new go.TreeModel(this.data_treeview_sever);
*/
				var dataparam={
					project:this.project_selected_current,
					keyparent:this.data_node_treeview.key,
					 namefile:this.dt_rename,
					type:'rename'
				};
			 }
			 // case add file 
			  else {
				  /*
				  myDiagramTreeview.startTransaction("add node and link");
	var childdata = { key: 9, name:"aadt more node ", parent: 5 };
		  
		  myDiagramTreeview.model.addNodeData(childdata);
		myDiagramTreeview.commitTransaction("add node and link");
		
		// add sub 
		myDiagramTreeview.startTransaction("add node and link");
	var childdata = { key: 10, name:"4 4sub new  ", parent: 9 };
		  
		  myDiagramTreeview.model.addNodeData(childdata);
		myDiagramTreeview.commitTransaction("add node and link");
		//JSON.parse(text);
		 */
			var dataparam={
					project:this.project_selected_current,
					keyparent:this.data_node_treeview.key,
					 nameFolder:this.dt_foldername,
					 namefile:this.dt_filename,
					type:'creatsubfolder'
				};
			  }
			 
		//console.log("DATA TREEVIEW: "+JSON.stringify(myDiagramTreeview.model.nodeDataArray));
			var selfNBR=this;
			  this.stepcreatdiagram=1;
				 instance.post('/action.php',
				 dataparam
				 ).then(function(response) {
					data = response.data;
					//alert(data);
					selfNBR.stepcreatdiagram=0;
					 
					
					if(data.error==1){
						alert("error:"+data.message);
						}
					else {
						if(data.type=="creatsubfolder"){
							
							 myDiagramTreeview.startTransaction("add node and link");
							var childdata = { key: data.key_folder, name:selfNBR.dt_foldername, parent: selfNBR.data_node_treeview.key };
		  
								  myDiagramTreeview.model.addNodeData(childdata);
								myDiagramTreeview.commitTransaction("add node and link");
								
								// add sub 
								myDiagramTreeview.startTransaction("add node and link");
							var childdata = { key: data.key_file, name:selfNBR.dt_filename,source:data.source, parent: data.key_folder };
								  
								  myDiagramTreeview.model.addNodeData(childdata);
								myDiagramTreeview.commitTransaction("add node and link");
								
								// RESET 
								selfNBR.dt_filename="";
								selfNBR.dt_foldername="";
						}
						else if(data.type=="creatfile"){
							
							
							myDiagramTreeview.startTransaction("add node and link");
							var childdata = { key: data.key_file, name:selfNBR.dt_filename,source:data.source, parent: selfNBR.data_node_treeview.key };
								  
								  myDiagramTreeview.model.addNodeData(childdata);
								myDiagramTreeview.commitTransaction("add node and link");
						// RESET 
							selfNBR.dt_filename="";
						}
						// case rename 
						else if(data.type=="rename")
						{
							var n = myDiagramTreeview.selection.first();
							if (n === null) return;
							var data = myDiagramTreeview.model.findNodeDataForKey(n.key);
							// This will NOT change the color of the "Delta" Node
							 myDiagramTreeview.startTransaction("change color");
							if (data !== null){
								//alert("saas"+n.key);
								myDiagramTreeview.model.setDataProperty(data,"name",selfNBR.dt_rename);
								
								//alert("saas"+n.key);
							} 
							myDiagramTreeview.commitTransaction("change color");
							 selfNBR.dt_rename=""; 
						}
						// case delete 
						else if(data.type=="f_delete"){
							/*
							var n = myDiagramTreeview.selection.first();
							if (n === null) return;
						   
							myDiagramTreeview.startTransaction("removeFromTable");
							// remove second item of list, at index #1
							//console.log("afas"+n.data);
						   // diagram.model.removeArrayItem(d.data_tree, 6);
							
							myDiagramTreeview.model.removeNodeData(n.data);
							myDiagramTreeview.commitTransaction("removeFromTable");
								*/
								// allow delete
								/*
				  myDiagramTreeview.allowDelete= true;
				  myDiagramTreeview.commandHandler.deleteSelection(); 
					// lock again 
					myDiagramTreeview.allowDelete= false;
								
								selfNBR.data_node_treeview={}; 
								*/
						}
					}
					
				  
				}).catch(function(thrown) {
				  if (axios.isCancel(thrown)) {
					selfNBR.stepcreatdiagram=0;
				  } else {
					selfNBR.stepcreatdiagram=0;
				  }
				
				});
		}
	  },
	  // save data of treeview 
	  save_data_treeview: function(){
		  
	  },
	  save_data_diagram: function(){
		  var selfNBR=this;
		  // check case database 
		  if(this.typetableDB==1){
			 var db_savenbr=myDiagram_DBTB.model.toJson(); 
		  }
		  else {
			  var db_savenbr=myDiagram.model.toJson();
		  }
		  //console.log("LOG SAVE:"+db_savenbr);
		  // or normal 
			  //alert(this.file_select_current.source);
				 instance.post('/action.php',{
					 source:this.file_select_current.source,
					 data:db_savenbr,
					 project:this.project_selected_current,
					type:'save_data_diagram'
				}).then(function(response) {
					data = response.data;
					//alert(data);
					
					 
					
					if(data.error==1){
						alert("error:"+data.message);
						}
					else {
						alert("Save completed!");
					}
					
				  
				}).catch(function(thrown) {
				  if (axios.isCancel(thrown)) {
					//selfNBR.step_load_data_diagram=0;
				  } else {
					//selfNBR.step_load_data_diagram=0;
				  }
				
				});
	  },
	  update_data_tree:function(){
		  myDiagramTreeview.model =new go.TreeModel(JSON.parse(this.data_treeview_sever));
	  },
	  load_data_diagram: function(data){
		  if(this.step_load_data_diagram){
			  alert("Loading data diagram. Please waiting process done!");
		  }
		  else {
			  this.step_load_data_diagram=1;
			  this.set_file_select_current(data);
			  var selfNBR=this;
			  //alert(this.file_select_current.source);
				 instance.post('/action.php',{
					 source:this.file_select_current.source,
					 project:this.project_selected_current,
					type:'load_data_diagram'
				}).then(function(response) {
					data = response.data;
					//alert(data);
					selfNBR.step_load_data_diagram=0;
					 
					
					if(data.error==1){
						alert("error:"+data.message);
						}
					else {
						// check case database 
						//console.log("JSON"+JSON.parse(data.data).linkFromPortIdProperty);
						if(JSON.parse(data.data).linkFromPortIdProperty &&
						JSON.parse(data.data).linkToPortIdProperty){
							selfNBR.typetableDB=1;
							// reset all and init new db diagram.
							//init_database_tb();
							myDiagram_DBTB.model = go.Model.fromJson(data.data);
						//	console.log("DATA TABLE LOAD:"+data.data);
						}
						// diagram normal 
						else {
							selfNBR.typetableDB=0;
							myDiagram.model = go.Model.fromJson(data.data);
						
						}
						//console.log("Load data diagram:"+data.data);
						// show button load database 
						if(data.data=="[]")
						{
							selfNBR.show_button_database=1;
						}
						else {
							selfNBR.show_button_database=0;
						}
					}
					
				  
				}).catch(function(thrown) {
				  if (axios.isCancel(thrown)) {
					selfNBR.step_load_data_diagram=0;
				  } else {
					selfNBR.step_load_data_diagram=0;
				  }
				
				});
		  }
	  },
	  load_project_choose:function(){
		  if(this.project_selected_current!=""){
			  var selfNBR=this;
			  
				 instance.post('/action.php',{
					 name:this.project_selected_current,
					type:'load_project'
				}).then(function(response) {
					data = response.data;
					//alert(data);

					 
					
					if(data.error==1){
						alert("error:"+data.message);
						}
					else {
						selfNBR.data_treeview_sever=data.data_tree;
						selfNBR.key_current=data.key_current;
						console.log("DATASS:"+selfNBR.data_treeview_sever);
					selfNBR.update_data_tree();
						//alert(data.data_tree);
						
						
					}
					
				  
				}).catch(function(thrown) {
				  if (axios.isCancel(thrown)) {
					//selfNBR.stepcreatdiagram=0;
				  } else {
					//selfNBR.stepcreatdiagram=0;
				  }
				
				});
		  }
		  else {
			  alert("Please select your project before load");
		  }
	  }
	  ,
	  //save img diagram 
	  export_to_svg: function(){
		  if(this.typetableDB==1){
				var svg = myDiagram_DBTB.makeSvg({
				scale: 1
				});
		  }
		  else {
			  var svg = myDiagram.makeSvg({
				scale: 1
				});
		  }
		  
			svg.style.border = "1px solid black";
			obj = document.getElementById("SVGArea");
			obj.appendChild(svg);
			if (obj.children.length > 0) {
			  obj.replaceChild(svg, obj.children[0]);
			}
	  },
	  export_to_img: function(){
		  // type database 
		  if(this.typetableDB==1){
			  var imgsave =  myDiagram_DBTB.makeImage({
			  scale: 1,
			  // size: new go.Size(NaN,250),
			//background: "rgba(0, 255, 0, 0.5)" ,// semi-transparent green background
			 // background: "AntiqueWhite",
			  background: "White",
			  type: "image/jpeg",
			  // quantity
			  details: 1
			});
		  }
		  else {
			  var imgsave =  myDiagram.makeImage({
			  scale: 1,
			  // size: new go.Size(NaN,250),
			//background: "rgba(0, 255, 0, 0.5)" ,// semi-transparent green background
			 // background: "AntiqueWhite",
			  background: "White",
			  type: "image/jpeg",
			  // quantity
			  details: 1
			});
			
		  }
		  imgsave.style.border = "1px solid black";
			obj = document.getElementById("SVGArea");
			obj.appendChild(imgsave);
			if (obj.children.length > 0) {
			  obj.replaceChild(imgsave, obj.children[0]);
			}
	  }
	  ,
	  // case database diagram
	  show_db_diagram: function(){
		  if(this.typetableDB==0){
			   this.typetableDB=1;
			   // reset all and init new db diagram.
			   //init_database_tb();
			   myDiagram_DBTB.model= go.Model.fromJson('{ "class": "go.GraphLinksModel","linkFromPortIdProperty": "fromPort","linkToPortIdProperty": "toPort","nodeDataArray": [],"linkDataArray": []}');
				
		  } 
		  else this.typetableDB=0;
		  console.log("diamga DB:"+this.typetableDB);
	  }
	  ,
	  add_new_table_db:function(){
		   myDiagram_DBTB.startTransaction("add node and link");
							var childdata = { key: "Table",
                widths: [ NaN, NaN, 60 ],
                fields: [
                  { name: "t_"+Date.now(), info: "t_"+Date.now(), color: "#00BCF2", figure: "Ellipse" }
                ],
                loc: "-200 10" };
								  
		myDiagram_DBTB.model.addNodeData(childdata);
		myDiagram_DBTB.commitTransaction("add node and link");
      
	  }
	  // END CASE DATABASE 
  }
  });
</script>

</body>
</html>
