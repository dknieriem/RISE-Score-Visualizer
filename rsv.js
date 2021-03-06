
 //TODO: - refactor nodeInfo as a single element which is appended to the current node
 //TODO: - when clicking one node and then a second, display the relationship
 //TODO: - add a histogram of distances to the nodeInfo window
 //TODO: - highlight links to most distant and most similar nodes on hover
 //TODO: - give relationship info when hovering over a link (make them thicker so easier to hover?)
  
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
RISE.NumStrengths = 5;
RISE.FileLocation = "https://raw.githubusercontent.com/dknieriem/RISE-Score-Visualizer/master/strengths.csv"; //"http://test.absd/strengths.csv"
RISE.CustomNames = {
    "Jeremy Proffitt" : "JP",
    "Doug Nottage" : "DougN"
};
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

LoadStrengths(RISE.FileLocation);

};

function LoadGraph(){
    var width = graph.width,
        height = graph.height;
    var nodes = GetInitialNodePositions(RISE.People.length, width, height);
    var links = GetInitialLinks(RISE.People.length);
  
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
        .charge(-10000)
        .gravity(10)
        .chargeDistance(graph.nodeRadius * 2)
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
        .attr('class', 'node');
        //.call(force.drag);

   /* node.append("text")
        .attr("dx", -12)
        .attr("dy", ".35em")
        .text(function(d){return d.name});
    */
    
    node.append("image")
        .attr("xlink:href", function(d) { return d.name + ".png" })
        .attr("aria-label", function(d) { return d.name})
        .attr('clip-path', 'url(#clip)')
        .attr("x", graph.nodeRadius * -1)
        .attr("y", graph.nodeRadius * -1)
        .attr("width", graph.nodeRadius * 2)
        .attr("height", graph.nodeRadius * 2);

    var nodeInfo = node.append('g')
        .attr('visibility', 'hidden')
        .attr('class', 'node-info');
        
    nodeInfo.append('rect')
        .attr('x', graph.nodeRadius)
        .attr('y', 0)
        .attr('width', 200)
        .attr('height', 150);
    
    //nodeInfo - name
    nodeInfo.append('text')
        .attr("dx", 30)
        .attr("dy", "1em")
        .text(function(d){ return d.name });
        
    //nodeInfo - strengths
    
    for(i = 0; i < RISE.NumStrengths; i++){
    
        nodeInfo.append('text')
            .attr("dx", 30)
            .attr("dy", i+2 + "em")
            .text(function(d){ 
                return i+1 + ": " + d.strengths[i];
            });
    }
    
    nodeInfo.append('text')
        .attr("dx", 30)
        .attr("dy", "8em")
        .text(function(d){
            [maxIndex, maxDistance] = MostDistant(d.index); 
            return "Most Distant: " + RISE.People[maxIndex].Name + "(" + maxDistance + ")";
        });
    
    nodeInfo.append('text')
        .attr("dx", 30)
        .attr("dy", "9em")
        .text(function(d){
            [minIndex, minDistance] = MostSimilar(d.index); 
            return "Most Similar: " + RISE.People[minIndex].Name + "(" + minDistance + ")";
        });
    node.on('mouseover', function(d){
            //console.log(d);
            var index = d.index;
            var [distIndex, distDistance] = MostDistant(index);
            var [simIndex, simDistance] = MostSimilar(index);
            //console.log(index, distIndex, simIndex);
            
            link1 = GetLink(index, distIndex);
            link2 = GetLink(index, simIndex);
            
            //TODO: select the line for this link and highlight it
            //var link1Line = d3.select(link1).select('line');
            
            var curNode = d3.select(this).select('.node-info');
            curNode.attr('visibility', 'visible');
            
        });
    node.on('mouseout', function(d){
            //console.log(d);
            var curNode = d3.select(this).select('.node-info');
            curNode.attr('visibility', 'hidden');
        });
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

    //force.stop();


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

    d3.select('#advance').on('click', function(){
        force.start();
        force.tick();
        force.stop();
    });

  
    d3.select('#run').on('click', function() {

        animating = false;
        force.start();

        for(i = 0; i < 100; i++){
            force.tick();
        }
    
        force.stop();
    });
  
    force.on('end', function() {
        UpdatePositions();
     });
      
  // Okay, everything is set up now so it's time to turn
  // things over to the force layout. Here we go.

//  force.start();
//  force.tick();
//  force.stop();

    UpdatePositions();
} //end function LoadGraph()

      
function UpdatePositions(){
    
    var svg = d3.select('svg');
    var node = svg.selectAll('.node');
    var link = svg.selectAll('.link');
    //TODO: figure out why line opacity doesn't work on initialization of d3
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
}

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
        distance += Math.abs((isFinite(Person1.Score[RISE.StrengthsArray[key][0]]) ? Person1.Score[RISE.StrengthsArray[key][0]] : 0) - (isFinite(Person2.Score[RISE.StrengthsArray[key][0]]) ? Person2.Score[RISE.StrengthsArray[key][0]] : 0));
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

function MostDistant(nodeIndex){

    maxIndex = 0;
    maxDistance = 0;

    for(i = 0; i < RISE.People.length; i++){
        if(i != nodeIndex){
            if (RISE.Distances[nodeIndex][i] > maxDistance){
                maxDistance = RISE.Distances[nodeIndex][i];
                maxIndex = i;
            }
        }
    }

return [maxIndex, maxDistance];
}

function MostSimilar(nodeIndex){

    minIndex = 0;
    minDistance = 10e6;

    for(i = 0; i < RISE.People.length; i++){
        if(i != nodeIndex){
            if (RISE.Distances[nodeIndex][i] < minDistance){
                minDistance = RISE.Distances[nodeIndex][i];
                minIndex = i;
            }
        }
    }

return [minIndex, minDistance];
}

function LoadStrengths(FileLocation){
    $.ajax({
        type: 'GET', //'POST'
        url: FileLocation,
        //data: postedData,
        //dataType: 'csv',
        success: LoadStrengthsSuccess,
        error: function(jqXHR, status, error){ 
            console.log("Load error: "+ error); 
            //for now, still run on the sample data
            ScorePeople(RISE.People); 
            AllDistances(); 
            LoadGraph(); 
        }
    });

}

function LoadStrengthsSuccess(csv){ 
    LoadPeople(csv);
    ScorePeople(RISE.People);
    AllDistances();
    LoadGraph();
}

function LoadPeople(csv){
  
    RISE.People = [];
    //console.log("RISE csv: " + csv); 
    var data = $.csv.toArrays(csv);//toArrays(csv); 
    //console.log("RISE data: " + data);
    var rowNum = 0;
    for(var row in data){
        if(rowNum == 0){
            RISE.StrengthsFileOrder = data[row];
            //console.log("File Order: " + RISE.StrengthsFileOrder);
        } else {
            var colNum = 0;
        
            RISE.People[rowNum - 1] = {};
            var fullName = data[row][0];
            
            if(RISE.CustomNames[fullName]){
                name = RISE.CustomNames[fullName];
            }
            /*if(fullName == "Jeremy Proffitt")
                name = "JP";
            else if(fullName == "Doug Nottage")
                name = "DougN";
                */
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
            //console.log("Person: " + RISE.People[rowNum - 1]["Name"] + ", Strengths: " + RISE.People[rowNum - 1]["Strengths"]);
        }
            
            rowNum++;
    }
    RISE.data = data;
    //console.log("RISE.People: " + RISE.People);
    
}

function GetInitialNodePositions(n, width, height){
  center = { x: width / 2, y: height/2};
    radius = Math.abs(width - height);
    
  nodes = [];
  for(i = 0; i < n; i++){
    theta = i / n * (2 * Math.PI);
    nodes[i] = { x: center.x + radius * Math.cos(theta), y: center.y + radius * Math.sin(theta) };
    nodes[i].name = RISE.People[i].Name;
    nodes[i].strengths = RISE.People[i].Strengths;
    nodes[i].index = i;
    //console.log(i, theta, Math.cos(theta), Math.sin(theta));
  }
  //console.log(nodes);
  return nodes;
}

function GetLink(nodeX, nodeY){

    links = force.links();

    for(i = 0; i < links.length; i++){
        source = links[i].source.index;
        target = links[i].target.index; 
        if(source == nodeX && target == nodeY || source == nodeY && target == nodeX){
            return links[i];
        }
    }
}

function GetInitialLinks(n){
    var links = [];
    for(i = 0; i < n; i++){
        for(j = i+1; j < n; j++){
            links.push({ source: i, target: j});
        }
    }
 
    return links;
}

function linkDistance(link, index){
    source = link.source.index;
    target = link.target.index;
    distance = RISE.Distances[source][target] / RISE.MaxDistance * graph.height;
    link.distance = distance;
    if(distance < (graph.nodeRadius + graph.nodeStrokeWidth) * 2){
        distance = (graph.nodeRadius + graph.nodeStrokeWidth) * 2;
    }
    return distance;
}
