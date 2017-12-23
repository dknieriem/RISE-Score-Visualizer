<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <title>RISE Distance Visualizer by birdyinc</title>
  
  <script type="text/javascript" src="jquery.js"></script>
  <script src="jquery.csv.js"></script>
<style>
.node {
    <!-- fill: #ccc; -->
    <!-- stroke: #fff; -->
    stroke-width: 2px;
}

.link {
    stroke: #777;
    stroke-width: 2px;
}

button {
    position: absolute;
    width: 30px;
}
button#slow {
    margin-left: 40px;
}

    </style>  

    <!--<link rel="stylesheet" type="text/css" href="/css/result-light.css">-->
  <style type="text/css">
    
  </style>

</head>

<body>
    <button id='advance' title='Advance Layout One Increment'>
        <i class='fa fa-step-forward'>></i>
    </button>
    <button id='slow'    title='Run Layout in Slow Motion'>
        <i class='fa fa-play'>>></i>
    </button>

  <script type="text/javascript" src="d3.v3.min.js"></script>
  <script type="text/javascript">//<![CDATA[

  
  window.onload=function(){
//https://gist.github.com/iwek/7154578
RISE = {};
force = {};
var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
graph = {};
graph.width = x;
graph.height = y;
graph.nodeRadius = 25;
graph.nodeStrokeWidth = 2;
RISE.FileLocation = "https://raw.githubusercontent.com/dknieriem/RISE-Score-Visualizer/master/strengths.csv"; //"http://test.absd/strengths.csv"
RISE.Dimensions = ["Relationship Building", "Influencing", "Strategic Thinking", "Executing"];
RISE.data = null;
RISE.StrengthsFileOrder = [];
RISE.Strengths = {
"Achiever" : "Executing",
"Arranger" : "Executing",
"Belief" : "Executing",
"Consistency" : "Executing",
"Deliberative" : "Executing",
"Discipline" : "Executing",
"Focus" : "Executing",
"Responsibility" : "Executing",
"Restorative" : "Executing",
"Activator" : "Influencing",
"Command" : "Influencing",
"Communication" : "Influencing",
"Competition" : "Influencing",
"Maximizer" : "Influencing",
"Self-Assurance" : "Influencing",
"Significance" : "Influencing",
"Woo" : "Influencing",
"Adaptability" : "Relationship Building",
"Connectedness" : "Relationship Building",
"Developer" : "Relationship Building",
"Empathy" : "Relationship Building",
"Harmony" : "Relationship Building",
"Includer" : "Relationship Building",
"Individualization" : "Relationship Building",
"Positivity" : "Relationship Building",
"Relator" : "Relationship Building",
"Analytical" : "Strategic Thinking",
"Context" : "Strategic Thinking",
"Futuristic" : "Strategic Thinking",
"Ideation" : "Strategic Thinking",
"Input" : "Strategic Thinking",
"Intellection" : "Strategic Thinking",
"Learner" : "Strategic Thinking",
"Strategic" : "Strategic Thinking"
}

RISE.StrengthsArray = Object.keys(RISE.Strengths).map(function(key) { return [key, RISE.Strengths[key]]; });

RISE.People = [
{"Name" :"Don", "Strengths" : ["Learner",
"Intellection",
"Input",
"Connectedness",
"Consistency"]},
{"Name" :"JP", "Strengths" : ["Individualization",
"Connectedness",
"Strategic",
"Learner",
"Ideation"]},
{"Name" :"Mike", "Strengths" : ["Individualization",
"Connectedness",
"Strategic",
"Ideation",
"Learner"]},
]

function Score(person){
    person.Score = {};
	person.Score["Relationship Building"] = 0;
	person.Score["Influencing"] = 0;
	person.Score["Strategic Thinking"] = 0;
	person.Score["Executing"] = 0;
    for(strengthNum = 0; strengthNum < 5; strengthNum++){
		scoreIncrease = (5 - strengthNum);
		scoreIncrease = scoreIncrease * scoreIncrease * scoreIncrease;
 
		strength = person.Strengths[strengthNum];
  
		person.Score[strength] = scoreIncrease;
  
		//scoreIncrease = scoreIncrease;
		category = RISE.Strengths[strength];
		person.Score[category] += scoreIncrease; 
		//console.log(strengthNum,strength,category,person.Score);
	}	
}

function ScorePeople(people){

    n = people.length;

    for(i = 0; i < n; i++){
        Score(people[i]);
    }
}       

function Distance(Person1, Person2){

distance = 0;

    for(dim = 0; dim < RISE.Dimensions.length; dim++){
  distance += Math.pow( Math.abs( Person1.Score[RISE.Dimensions[dim]] - Person2.Score[RISE.Dimensions[dim]] ), 2);
  
  }

    for(key = 0; key < RISE.StrengthsArray.length; key++){

distance += 
    Math.abs(isFinite(Person1.Score[RISE.StrengthsArray[key]]) ? Person1.Score[RISE.StrengthsArray[key]] : 0 - isFinite(Person2.Score[RISE.StrengthsArray[key]]) ? Person2.Score[RISE.StrengthsArray[key]] : 0)
        
    }
return distance;
}

function Similarity(distance){

	if(distance == 0)
		return 1;
	else
		return 1 / Math.pow(distance, 2);

}

function AllDistances(){
    RISE.Distances = [];
	RISE.MaxDistance = 0;
  for(i = 0; i < RISE.People.length; i++){
        RISE.Distances[i] = new Array(RISE.People.length);
    RISE.Distances[i][i] = 0;
  }
    for(i = 0; i < RISE.People.length; i++){
        for(j = i + 1; j < RISE.People.length; j++){    
            dist = Distance(RISE.People[i], RISE.People[j]);
      RISE.Distances[i][j] = dist;
      RISE.Distances[j][i] = dist;
        if(dist > RISE.MaxDistance){
			RISE.MaxDistance = dist;
		}
    }

    }

}

function LoadStrengths(FileLocation){
    //RISE.People = [];
    console.log("RISE: " + RISE);
  $.ajax({
  type: 'GET', //'POST'
  url: FileLocation,
  //data: postedData,
  //dataType: 'csv',
  success: LoadStrengthsSuccess,
  error: function(jqXHR, status, error){ console.log("Load error: "+ error);}
});

}

function LoadStrengthsSuccess(csv){ 
    LoadPeople(csv);
	ScorePeople(RISE.People);
//console.log(RISE.People[0].Score);
//console.log(RISE.People[1].Score);
//console.log(Distance(RISE.People[0], RISE.People[1]));
AllDistances();
//console.log(RISE.Distances);
LoadGraph();

  }

  function LoadPeople(csv){
  
	RISE.People = [];
	console.log("RISE csv: " + csv); 
    var data = $.csv.toArrays(csv);//toArrays(csv); 
    console.log("RISE data: " + data);
	var rowNum = 0;
	for(var row in data){
		if(rowNum == 0){
			RISE.StrengthsFileOrder = data[row];
			//console.log("File Order: " + RISE.StrengthsFileOrder);
		} else {
			var colNum = 0;
		
			RISE.People[rowNum - 1] = {};
			var fullName = data[row][0];
			if(fullName == "Jeremy Proffitt")
				name = "JP";
			else if(fullName == "Doug Nottage")
				name = "DougN";
			else {
				fullName = fullName.split(" ");
				name = fullName[0];
			}
			
			RISE.People[rowNum - 1]["Name"] = name;
			RISE.People[rowNum - 1]["Strengths"] = [0,0,0,0,0];
			//console.log("Row: " + data[row]);
			//console.log(RISE.People[rowNum - 1]["Strengths"]);
			for(var column in data[row]){
			if(colNum != 0){
				//console.log("Col: " + data[row][column]);
				var value = data[row][column];
				if(value != ''){
					//console.log("Not Empty: [" + rowNum + ", " + colNum + "]: " + value);
					//console.log(RISE.StrengthsFileOrder[colNum]);
					RISE.People[rowNum - 1]["Strengths"][value - 1] = RISE.StrengthsFileOrder[colNum];
				}
				}
			colNum++;
			}
			console.log("Person: " + RISE.People[rowNum - 1]["Name"] + ", Strengths: " + RISE.People[rowNum - 1]["Strengths"]);
		}
			
			rowNum++;
	}
    RISE.data = data;
	console.log("RISE.People: " + RISE.People);
	
}

function GetInitialNodePositions(n, width, height){
  center = { x: width / 2, y: height/2};
    radius = Math.abs(width - height);
    

  nodes = [];
  for(i = 0; i < n; i++){
    theta = i / n * (2 * Math.PI);
    nodes[i] = { x: center.x + radius * Math.cos(theta), y: center.y + radius * Math.sin(theta) };
	nodes[i].name = RISE.People[i].Name;
    //console.log(i, theta, Math.cos(theta), Math.sin(theta));
  }
  console.log(nodes);
  return nodes;
}

function GetInitialLinks(n){
	var links = [];
	for(i = 0; i < n; i++){
		for(j = i+1; j < n; j++){
			links.push({ source: i, target: j});
		}
	}
//      { source: 0, target: 1 },
//      { source: 0, target: 2 },
//     { source: 1, target: 2 }
//  ];
  
	return links;
}
LoadStrengths(RISE.FileLocation);

function LoadGraph(){
	var width = graph.width,
		height = graph.height;
	var nodes = GetInitialNodePositions(RISE.People.length, width, height);
	var links = GetInitialLinks(RISE.People.length);
  
	function linkDistance(link, index){
		source = link.source.index;
		target = link.target.index;
		distance = RISE.Distances[source][target] / RISE.MaxDistance * height;
		if(distance < (graph.nodeRadius + graph.nodeStrokeWidth) * 2){
			distance = (graph.nodeRadius + graph.nodeStrokeWidth) * 2;
		}
	return distance;
	}
  
	var svg = d3.select('body').append('svg')
      .attr('width', width)
      .attr('height', height);

	 var defs = svg.append('defs');
	 var rect = defs.append('rect')
	 .attr('id', 'rect')
	 .attr('x', '-25')
	 .attr('y', '-25')
	 .attr('width','50')
	 .attr('height','50')
	 .attr('rx','25');
	 
	 var clip = defs.append('clipPath')
	 .attr('id','clip');
	 
	 clip.append('use')
	 .attr('xlink:href','#rect');
	 
	 
	  //NOTE: var force
	force = d3.layout.force() 
		.size([width, height])
		.nodes(nodes)
		.links(links)
		.charge(-1000)
		.chargeDistance(graph.nodeRadius * 4)
		.linkDistance(linkDistance);

  //force.linkDistance(width/2);
		
  
	var link = svg.selectAll('.link')
		.data(links)
		.enter().append('line')
		.attr('stroke-opacity', function(d){
			source = d.source;
			target = d.target;
			distance = RISE.Distances[source][target];
			if(distance == 0)
				return 1;
			else
				return 1 / Math.log(distance + 1);})
		.attr('class', 'link');

	link.append("text")
		.attr("dx", 12)
		.attr("dy", ".35em")
		.text(function(d){
			return "0";
		});
  
	var node = svg.selectAll('.node')
		.data(nodes)
		.enter().append('g') //circle')
		.attr('class', 'node')
		.call(force.drag);

	node.append("text")
		.attr("dx", -12)
		.attr("dy", ".35em")
		.text(function(d){return d.name});
	
	node.append("image")
		.attr("xlink:href", function(d) { return d.name + ".png" })
		.attr('clip-path', 'url(#clip)')
		.attr("x", graph.nodeRadius * -1)
		.attr("y", graph.nodeRadius * -1)
		.attr("width", graph.nodeRadius * 2)
		.attr("height", graph.nodeRadius * 2);

	var animating = false;

	var animationStep = 100;

	force.on('tick', function() {

    node.transition().ease('linear').duration(animationStep)
        .attr('cx', function(d) { return d.x; })
        .attr('cy', function(d) { return d.y; });

    link.transition().ease('linear').duration(animationStep)
        .attr('x1', function(d) { return d.source.x; })
        .attr('y1', function(d) { return d.source.y; })
        .attr('x2', function(d) { return d.target.x; })
        .attr('y2', function(d) { return d.target.y; });

	//console.log(nodes);

    force.stop();


    if (animating) {
        setTimeout(
            function() { force.start(); },
            animationStep
        );
    }

});

// Now let's take care of the user interaction controls.
// We'll add functions to respond to clicks on the individual
// buttons.

// When the user clicks on the "Advance" button, we
// start the force layout (The tick handler will stop
// the layout after one iteration.)

d3.select('#advance').on('click', force.start);

// When the user clicks on the "Play" button, we're
// going to run the force layout until it concludes.

d3.select('#slow').on('click', function() {

    // Since the buttons don't have any effect any more,
    // disable them.

    d3.selectAll('button').attr('disabled','disabled');

    // Indicate that the animation is in progress.

    animating = true;

    // Get the animation rolling

    force.start();

});
  
  force.on('end', function() {

     
      node.attr('r', graph.nodeRadius)
          .attr('x', function(d) { return d.x; })
          .attr('y', function(d) { return d.y; });

      // We also need to update positions of the links.
      // For those elements, the force layout sets the
      // `source` and `target` properties, specifying
      // `x` and `y` values in each case.

      link.attr('x1', function(d) { return d.source.x; })
          .attr('y1', function(d) { return d.source.y; })
          .attr('x2', function(d) { return d.target.x; })
          .attr('y2', function(d) { return d.target.y; });

		node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
  });

  // Okay, everything is set up now so it's time to turn
  // things over to the force layout. Here we go.

  force.start();
} //end function LoadGraph()

};
</script>
</body></html>