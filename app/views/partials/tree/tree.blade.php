<style>
    html,
    body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
    }

    #tree {
        width: 100%;
        height: 100%;
        background: repeating-conic-gradient(#0e0e0e 0% 25%, #101010 0% 50%) 50% / 80px 80px;
        cursor: grab;
        border: 1px solid rgba(99, 102, 241, 0.5);
        border-radius: 5px
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


<svg id="tree" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid meet"></svg>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    const data = @json($tree);

    const svg = d3.select("#tree");
    const g = svg.append("g");

    const zoom = d3.zoom()
        .scaleExtent([0.3, 2])
        .on("zoom", (event) => {
            g.attr("transform", event.transform);
        });

    svg.call(zoom);

    const treeLayout = d3.tree().size([1000, 1000]);

    const root = d3.hierarchy(data);
    treeLayout(root);

    g.selectAll(".link")
        .data(root.links())
        .join("path")
        .attr("class", "link")
        .attr("d", d3.linkHorizontal()
            .x(d => d.y)
            .y(d => d.x)
        );

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
