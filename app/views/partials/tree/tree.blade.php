<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Semantic Tree</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #tree {
            width: 100vw;
            height: 100vh;
            background: repeating-conic-gradient(#0e0e0e 0% 25%, #101010 0% 50%) 50% / 80px 80px;
            cursor: grab;
        }

        #tree:active {
            cursor: grabbing;
        }

        .node circle {
            fill: #c6c9fa;
        }

        .node text {
            fill: #ccc;
            font-size: 12px;
        }

        .link {
            fill: none;
            stroke: #888;
            stroke-width: 1.5px;
        }
    </style>
</head>

<body>
    <svg id="tree"></svg>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        const data = @json($tree);
        console.log(data);

        const svg = d3.select("#tree");
        const width = window.innerWidth;
        const height = window.innerHeight;

        const g = svg.append("g");

        const zoom = d3.zoom()
            .scaleExtent([0.3, 2])
            .on("zoom", (event) => {
                g.attr("transform", event.transform);
            });

        svg.call(zoom).call(zoom.transform, d3.zoomIdentity.translate(100, height / 2));

        const treeLayout = d3.tree().size([height - 100, width - 300]);

        const root = d3.hierarchy(data);
        treeLayout(root);

        // Links
        g.selectAll(".link")
            .data(root.links())
            .join("path")
            .attr("class", "link")
            .attr("d", d3.linkHorizontal()
                .x(d => d.y)
                .y(d => d.x)
            );

        // Nodes
        const node = g.selectAll(".node")
            .data(root.descendants())
            .join("g")
            .attr("class", "node")
            .attr("transform", d => `translate(${d.y},${d.x})`);

        node.append("circle")
            .attr("r", 5);

        node.append("text")
            .attr("dy", 4)
            .attr("x", d => d.children ? -10 : 10)
            .style("text-anchor", d => d.children ? "end" : "start")
            .text(d => d.data.topic);
    </script>
</body>

</html>
