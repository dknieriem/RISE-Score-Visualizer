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
    fill: #ccc;
    stroke: #fff;
    stroke-width: 2px;
}

.link {
    stroke: #777;
    stroke-width: 2px;
}

    </style>  

    <!--<link rel="stylesheet" type="text/css" href="/css/result-light.css">-->
  <style type="text/css">
    
  </style>

</head>

<body>

  <script type="text/javascript" src="d3.v3.min.js"></script>
  <script type="text/javascript">//<![CDATA[
window.onload=function(){
//https://gist.github.com/iwek/7154578
RISE = {};

RISE.FileLocation = "http://test.absd/strengths.csv"
RISE.Dimensions = ["Relationship Building", "Influencing", "Strategic Thinking", "Executing"];
RISE.data = null;
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
{"Name" :"JPP", "Strengths" : ["Individualization",
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
  strength = person.Strengths[strengthNum];
  
  person.Score[strength] = scoreIncrease;
  
  
  scoreIncrease = scoreIncrease*scoreIncrease;
  category = RISE.Strengths[strength];
  person.Score[category] += scoreIncrease; 
    console.log(strengthNum,strength,category,person.Score);
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

    for(key = 0; key <      RISE.StrengthsArray.length; key++){

distance += 
    Math.abs(isFinite(Person1.Score[RISE.StrengthsArray[key]]) ? Person1.Score[RISE.StrengthsArray[key]] : 0 - isFinite(Person2.Score[RISE.StrengthsArray[key]]) ? Person2.Score[RISE.StrengthsArray[key]] : 0)
        
    }
return distance;
}

function AllDistances(){
    RISE.Distances = [];
  for(i = 0; i < RISE.People.length; i++){
        RISE.Distances[i] = new Array(RISE.People.length);
    RISE.Distances[i][i] = 0;
  }
    for(i = 0; i < RISE.People.length; i++){
        for(j = i + 1; j < RISE.People.length; j++){    
            dist = Distance(RISE.People[i], RISE.People[j]);
      RISE.Distances[i][j] = dist;
      RISE.Distances[j][i] = dist;
        
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
    console.log("RISE csv: " + csv); 
    var data = $.csv.toObjects(csv);//toArrays(csv); 
    console.log("RISE data: " + data); 
    RISE.data = data;
  }

function GetInitialNodePositions(n, width, height){
  center = { x: width / 2, y: height/2};
    radius = Math.abs(width - height);
    

  nodes = [];
  for(i = 0; i < n; i++){
    theta = i / n * (2 * Math.PI);
    nodes[i] = { x: center.x + radius * Math.cos(theta), y: center.y + radius * Math.sin(theta) };
    console.log(i, theta, Math.cos(theta), Math.sin(theta));
  }
  console.log(nodes);
  return nodes;
}

LoadStrengths(RISE.FileLocation);
ScorePeople(RISE.People);
console.log(RISE.People[0].Score);
console.log(RISE.People[1].Score);
console.log(Distance(RISE.People[0], RISE.People[1]));
AllDistances();
console.log(RISE.Distances);

function LoadGraph(){
  var width = 640,
      height = 480;

  var nodes = GetInitialNodePositions(RISE.People.length, width, height);

  var links = [
      { source: 0, target: 1 },
      { source: 0, target: 2 },
     { source: 1, target: 2 }
  ];

  var svg = d3.select('body').append('svg')
      .attr('width', width)
      .attr('height', height);

  var force = d3.layout.force()
      .size([width, height])
      .nodes(nodes)
      .links(links);

  force.linkDistance(width/2);

  var link = svg.selectAll('.link')
      .data(links)
      .enter().append('line')
      .attr('class', 'link');

  var node = svg.selectAll('.node')
      .data(nodes)
      .enter().append('circle')
      .attr('class', 'node');

  force.on('end', function() {

     
      node.attr('r', width/25)
          .attr('cx', function(d) { return d.x; })
          .attr('cy', function(d) { return d.y; });

      // We also need to update positions of the links.
      // For those elements, the force layout sets the
      // `source` and `target` properties, specifying
      // `x` and `y` values in each case.

      link.attr('x1', function(d) { return d.source.x; })
          .attr('y1', function(d) { return d.source.y; })
          .attr('x2', function(d) { return d.target.x; })
          .attr('y2', function(d) { return d.target.y; });

  });

  // Okay, everything is set up now so it's time to turn
  // things over to the force layout. Here we go.

  force.start();
} //end function LoadGraph()

};
</script>
</body></html>